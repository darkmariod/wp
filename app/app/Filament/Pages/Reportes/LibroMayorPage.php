<?php

namespace App\Filament\Pages\Reportes;

use App\Models\PlanCuenta;
use App\Services\Reportes\LibroMayorService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;

class LibroMayorPage extends Page implements HasForms
{
    use InteractsWithForms;

    public static function getNavigationGroup(): string
    {
        return 'Reportes';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-clipboard-document-list';
    }

    public static function getNavigationLabel(): string
    {
        return 'Libro Mayor';
    }





    protected string $view = 'filament.pages.libro-mayor';

    protected static ?string $title = 'Libro Mayor';

    public ?array $data = [];

    public ?array $resultado = null;

    public function mount(): void
    {
        $this->form->fill([
            'cuenta_id' => null,
            'fecha_inicio' => now()->startOfMonth()->toDateString(),
            'fecha_fin' => now()->toDateString(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Select::make('cuenta_id')
                ->label('Cuenta Contable')
                ->options(fn () => PlanCuenta::where('activa', true)
                    ->orderBy('codigo')
                    ->get()
                    ->mapWithKeys(fn (PlanCuenta $c) => [
                        $c->id => "{$c->codigo} - {$c->nombre}",
                    ]))
                ->searchable()
                ->preload()
                ->required(),

            DatePicker::make('fecha_inicio')
                ->label('Fecha Inicio')
                ->required()
                ->native(false),

            DatePicker::make('fecha_fin')
                ->label('Fecha Fin')
                ->required()
                ->native(false),
        ]);
    }

    public function generar(): void
    {
        try {
            $data = $this->form->getState();

            $this->resultado = app(LibroMayorService::class)
                ->generar(
                    (int) $data['cuenta_id'],
                    Carbon::parse($data['fecha_inicio']),
                    Carbon::parse($data['fecha_fin']),
                );

            Notification::make()
                ->title('Libro Mayor generado correctamente')
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Error al generar el libro mayor')
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
                ->modalHeading('Exportar Libro Mayor')
                ->modalDescription('Se generara el PDF con el libro mayor actual.')
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

        $html = view('reports.libro-mayor', [
            'data' => $this->resultado,
            'titulo' => 'Libro Mayor',
            'generado_en' => now()->format('d/m/Y H:i:s'),
        ])->render();

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        return response($dompdf->output())
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="libro_mayor.pdf"');
    }
}
