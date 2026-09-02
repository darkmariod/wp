<?php

namespace App\Filament\Pages\Reportes;

use App\Services\Reportes\EstadoResultadosService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;

class EstadoResultadosPage extends Page implements HasForms
{
    use InteractsWithForms;

    public static function getNavigationGroup(): string
    {
        return 'Reportes';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-document-text';
    }

    public static function getNavigationLabel(): string
    {
        return 'Estado de Resultados';
    }





    protected string $view = 'filament.pages.estado-resultados';

    protected static ?string $title = 'Estado de Resultados';

    public ?array $data = [];

    public ?array $resultado = null;

    public function mount(): void
    {
        $this->form->fill([
            'fecha_inicio' => now()->startOfMonth()->toDateString(),
            'fecha_fin' => now()->toDateString(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
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

            $this->resultado = app(EstadoResultadosService::class)
                ->generar(
                    Carbon::parse($data['fecha_inicio']),
                    Carbon::parse($data['fecha_fin']),
                );

            Notification::make()
                ->title('Estado de Resultados generado correctamente')
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Error al generar el reporte')
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
                ->modalHeading('Exportar Estado de Resultados')
                ->modalDescription('Se generara el PDF con el estado de resultados actual.')
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

        $html = view('reports.estado-resultados', [
            'data' => $this->resultado,
            'titulo' => 'Estado de Resultados',
            'generado_en' => now()->format('d/m/Y H:i:s'),
        ])->render();

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        return response($dompdf->output())
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="estado_resultados.pdf"');
    }
}
