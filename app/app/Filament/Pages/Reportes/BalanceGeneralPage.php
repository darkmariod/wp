<?php

namespace App\Filament\Pages\Reportes;

use App\Services\Reportes\BalanceGeneralService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;

class BalanceGeneralPage extends Page implements HasForms
{
    use InteractsWithForms;

    public static function getNavigationGroup(): string
    {
        return 'Reportes';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-calculator';
    }

    public static function getNavigationLabel(): string
    {
        return 'Balance General';
    }





    protected string $view = 'filament.pages.balance-general';

    protected static ?string $title = 'Balance General';

    public ?array $data = [];

    public ?array $resultado = null;

    public function mount(): void
    {
        $this->form->fill([
            'fecha' => now()->toDateString(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            DatePicker::make('fecha')
                ->label('Fecha del Balance')
                ->required()
                ->native(false),
        ]);
    }

    public function generar(): void
    {
        try {
            $data = $this->form->getState();

            $this->resultado = app(BalanceGeneralService::class)
                ->generar(Carbon::parse($data['fecha']));

            Notification::make()
                ->title('Balance generado correctamente')
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Error al generar el balance')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function getHeaderActions(): array
    {
        return [
            Action::make('exportar_pdf')
                ->label('Exportar PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Exportar Balance General')
                ->modalDescription('Se generara el PDF con el balance actual.')
                ->modalSubmitActionLabel('Exportar')
                ->action(fn () => $this->exportarPDF()),
        ];
    }

    public function exportarPDF()
    {
        if (empty($this->resultado)) {
            Notification::make()
                ->title('Primero genere el reporte')
                ->warning()
                ->send();

            return null;
        }

        $html = view('reports.balance-general', [
            'data' => $this->resultado,
            'titulo' => 'Balance General',
            'generado_en' => now()->format('d/m/Y H:i:s'),
        ])->render();

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        return response($dompdf->output())
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="balance_general.pdf"');
    }
}
