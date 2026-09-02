<?php

namespace App\Filament\Pages\Reportes;

use App\Models\Obra;
use App\Services\Reportes\LibroDiarioService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;

class LibroDiarioPage extends Page implements HasForms
{
    use InteractsWithForms;

    public static function getNavigationGroup(): string
    {
        return 'Reportes';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-book-open';
    }

    public static function getNavigationLabel(): string
    {
        return 'Libro Diario';
    }





    protected string $view = 'filament.pages.libro-diario';

    protected static ?string $title = 'Libro Diario';

    public ?array $data = [];

    public ?array $resultado = null;

    public function mount(): void
    {
        $this->form->fill([
            'fecha_inicio' => now()->startOfMonth()->toDateString(),
            'fecha_fin' => now()->toDateString(),
            'obra_id' => null,
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

            Select::make('obra_id')
                ->label('Obra (Opcional)')
                ->options(fn () => Obra::pluck('nombre', 'id'))
                ->searchable()
                ->preload()
                ->nullable()
                ->default(null),
        ]);
    }

    public function generar(): void
    {
        try {
            $data = $this->form->getState();

            $this->resultado = app(LibroDiarioService::class)
                ->generar(
                    Carbon::parse($data['fecha_inicio']),
                    Carbon::parse($data['fecha_fin']),
                    $data['obra_id'] ? (int) $data['obra_id'] : null,
                )
                ->toArray();

            Notification::make()
                ->title('Libro Diario generado correctamente')
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Error al generar el libro diario')
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
                ->modalHeading('Exportar Libro Diario')
                ->modalDescription('Se generara el PDF con el libro diario actual.')
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

        $html = view('reports.libro-diario', [
            'data' => ['asientos' => $this->resultado],
            'titulo' => 'Libro Diario',
            'generado_en' => now()->format('d/m/Y H:i:s'),
        ])->render();

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        return response($dompdf->output())
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="libro_diario.pdf"');
    }
}
