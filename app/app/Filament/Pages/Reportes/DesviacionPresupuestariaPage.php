<?php

namespace App\Filament\Pages\Reportes;

use App\Models\Obra;
use App\Services\Reportes\DesviacionPresupuestariaService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;

class DesviacionPresupuestariaPage extends Page implements HasForms
{
    use InteractsWithForms;

    public static function getNavigationGroup(): string
    {
        return 'Reportes';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-chart-bar';
    }

    public static function getNavigationLabel(): string
    {
        return 'Desviacion Presupuestaria';
    }





    protected string $view = 'filament.pages.desviacion-presupuestaria';

    protected static ?string $title = 'Desviacion Presupuestaria';

    public ?array $data = [];

    public ?array $resultado = null;

    public function mount(): void
    {
        $this->form->fill([
            'obra_id' => null,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Select::make('obra_id')
                ->label('Obra')
                ->options(fn () => Obra::orderBy('codigo')->pluck('nombre', 'id'))
                ->searchable()
                ->preload()
                ->required(),
        ]);
    }

    public function generar(): void
    {
        try {
            $data = $this->form->getState();

            $this->resultado = app(DesviacionPresupuestariaService::class)
                ->generar((int) $data['obra_id']);

            Notification::make()
                ->title('Desviacion Presupuestaria generada correctamente')
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Error al generar la desviacion presupuestaria')
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
                ->modalHeading('Exportar Desviacion Presupuestaria')
                ->modalDescription('Se generara el PDF con la desviacion presupuestaria actual.')
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

        $html = view('reports.desviacion-presupuestaria', [
            'data' => $this->resultado,
            'titulo' => 'Desviacion Presupuestaria',
            'generado_en' => now()->format('d/m/Y H:i:s'),
        ])->render();

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        return response($dompdf->output())
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="desviacion_presupuestaria.pdf"');
    }
}
