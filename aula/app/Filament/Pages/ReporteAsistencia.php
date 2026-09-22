<?php

namespace App\Filament\Pages;

use App\Exports\AsistenciaMensualExport;
use App\Models\Child;
use App\Models\Environment;
use App\Support\CicloEscolar;
use App\Support\ReporteAsistenciaMensual;
use BackedEnum;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use UnitEnum;

/**
 * Reporte mensual de asistencia por niño, en PDF y Excel. Es una
 * herramienta de uso interno: nunca aparece en el portal de familias
 * (canAccess replica exactamente al de RegistrarAsistencia).
 */
class ReporteAsistencia extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static string|UnitEnum|null $navigationGroup = 'Gestión Académica';

    protected static ?int $navigationSort = 26;

    protected static ?string $navigationLabel = 'Reportes de Asistencia';

    protected static ?string $title = 'Reportes de Asistencia';

    protected string $view = 'filament.pages.reporte-asistencia';

    public ?int $environment_id = null;

    public ?int $child_id = null;

    public string $periodo = 'mes';

    public string $mes = '';

    public string $semana = '';

    public function mount(): void
    {
        $this->mes = now()->format('Y-m');
        $this->semana = now()->startOfWeek(CarbonImmutable::MONDAY)->format('Y-m-d');
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user !== null && ($user->isStaff() || $user->isGuia());
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('environment_id')
                    ->label('Ambiente')
                    ->options(fn (): array => $this->opcionesAmbientes())
                    ->live()
                    ->afterStateUpdated(fn () => $this->child_id = null)
                    ->required(),
                Select::make('child_id')
                    ->label('Niño')
                    ->options(fn (): array => $this->opcionesNinos())
                    ->live()
                    ->required(),
                Select::make('periodo')
                    ->label('Período')
                    ->options([
                        'semana' => 'Semana',
                        'mes' => 'Mes',
                        'anio' => 'Año (ciclo)',
                    ])
                    ->live()
                    ->required(),
                Select::make('semana')
                    ->label('Semana')
                    ->options($this->opcionesSemanas())
                    ->live()
                    ->visible(fn (): bool => $this->periodo === 'semana')
                    ->required(fn (): bool => $this->periodo === 'semana'),
                Select::make('mes')
                    ->label('Mes')
                    ->options($this->opcionesMeses())
                    ->live()
                    ->visible(fn (): bool => $this->periodo === 'mes')
                    ->required(fn (): bool => $this->periodo === 'mes'),
            ])
            ->columns(3);
    }

    /**
     * Staff ve todos los ambientes activos; la guía solo los suyos.
     */
    public function opcionesAmbientes(): array
    {
        $query = Environment::query()->where('active', true);

        if (auth()->user()?->isGuia()) {
            $query->where('teacher_id', auth()->id());
        }

        return $query->orderBy('name')->pluck('name', 'id')->all();
    }

    public function opcionesNinos(): array
    {
        if (! $this->environment_id) {
            return [];
        }

        return Child::query()
            ->where('environment_id', $this->environment_id)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    /**
     * Meses del ciclo lectivo vigente (septiembre ... agosto siguiente).
     */
    public function opcionesMeses(): array
    {
        $anio = CicloEscolar::vigente();
        $inicio = CicloEscolar::inicio($anio);

        $opciones = [];

        for ($i = 0; $i < 12; $i++) {
            $mes = $inicio->addMonths($i);
            $opciones[$mes->format('Y-m')] = ucfirst($mes->locale('es')->isoFormat('MMMM YYYY'));
        }

        return $opciones;
    }

    /**
     * Semanas (lun-dom) del ciclo lectivo vigente, de punta a punta.
     */
    public function opcionesSemanas(): array
    {
        return CicloEscolar::opcionesSemanas(CicloEscolar::vigente());
    }

    /**
     * El niño elegido, validando que la guía no pida el reporte de un
     * ambiente ajeno manipulando el request.
     */
    public function child(): ?Child
    {
        if (! $this->child_id) {
            return null;
        }

        $child = Child::find($this->child_id);

        if ($child && auth()->user()?->isGuia() && $child->environment?->teacher_id !== auth()->id()) {
            return null;
        }

        return $child;
    }

    public function mesElegido(): ?CarbonImmutable
    {
        if (! $this->mes) {
            return null;
        }

        return CarbonImmutable::createFromFormat('Y-m', $this->mes)->startOfMonth();
    }

    public function semanaElegida(): ?CarbonImmutable
    {
        if (! $this->semana) {
            return null;
        }

        return CarbonImmutable::createFromFormat('Y-m-d', $this->semana)->startOfWeek(CarbonImmutable::MONDAY);
    }

    /**
     * Arma el reporte según el período elegido y su título/nombre de
     * archivo. Null cuando falta un dato para calcularlo (incluido el
     * caso "Año" sin ningún ciclo lectivo ya cerrado todavía).
     *
     * @return array{reporte: array{dias: array, resumen: array<string, int>}, titulo: string, archivo: string}|null
     */
    private function armarReporte(Child $child): ?array
    {
        return match ($this->periodo) {
            'semana' => $this->semanaElegida() ? [
                'reporte' => ReporteAsistenciaMensual::generarSemana($child, $this->semanaElegida()),
                'titulo' => CicloEscolar::etiquetaSemana($this->semanaElegida()),
                'archivo' => 'semana-'.$this->semanaElegida()->format('Y-m-d'),
            ] : null,
            'mes' => $this->mesElegido() ? [
                'reporte' => ReporteAsistenciaMensual::generar($child, $this->mesElegido()),
                'titulo' => ucfirst($this->mesElegido()->locale('es')->isoFormat('MMMM YYYY')),
                'archivo' => $this->mesElegido()->format('Y-m'),
            ] : null,
            'anio' => ($anio = CicloEscolar::ultimoCerrado()) !== null ? [
                'reporte' => ReporteAsistenciaMensual::generarAnual($child, $anio),
                'titulo' => 'Ciclo '.CicloEscolar::etiqueta($anio),
                'archivo' => 'ciclo-'.CicloEscolar::etiqueta($anio),
            ] : null,
            default => null,
        };
    }

    /**
     * @return array{dias: array, resumen: array<string, int>}|null
     */
    public function reporte(): ?array
    {
        $child = $this->child();

        if (! $child) {
            return null;
        }

        return $this->armarReporte($child)['reporte'] ?? null;
    }

    /**
     * Título legible del período elegido para mostrarlo en pantalla.
     */
    public function tituloPeriodo(): ?string
    {
        $child = $this->child();

        if (! $child) {
            return null;
        }

        return $this->armarReporte($child)['titulo'] ?? null;
    }

    public function descargarPdf(): ?StreamedResponse
    {
        $child = $this->child();
        $datos = $child ? $this->armarReporte($child) : null;

        if (! $datos) {
            $this->avisarSeleccionIncompleta();

            return null;
        }

        $pdf = Pdf::loadView('pdf.asistencia-mensual', [
            'child' => $child,
            'titulo' => $datos['titulo'],
            'dias' => $datos['reporte']['dias'],
            'resumen' => $datos['reporte']['resumen'],
        ]);

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            $this->nombreArchivo($child, $datos['archivo'], 'pdf'),
        );
    }

    public function descargarExcel(): ?StreamedResponse
    {
        $child = $this->child();
        $datos = $child ? $this->armarReporte($child) : null;

        if (! $datos) {
            $this->avisarSeleccionIncompleta();

            return null;
        }

        // ZipStream (que arma el .xlsx por dentro) procesa en bloques de
        // 16MB: con el memory_limit por defecto del servidor (128M) eso
        // alcanza a reventarlo. Es un export puntual, no hace falta subir
        // el límite global de PHP para esto.
        ini_set('memory_limit', '512M');

        $bytes = Excel::raw(new AsistenciaMensualExport($datos['reporte'], $datos['titulo']), ExcelFormat::XLSX);

        return response()->streamDownload(
            fn () => print ($bytes),
            $this->nombreArchivo($child, $datos['archivo'], 'xlsx'),
        );
    }

    private function avisarSeleccionIncompleta(): void
    {
        if ($this->periodo === 'anio' && CicloEscolar::ultimoCerrado() === null) {
            Notification::make()
                ->title('Todavía no hay ningún ciclo lectivo cerrado para reportar')
                ->warning()
                ->send();

            return;
        }

        Notification::make()
            ->title('Elegí un ambiente, un niño y el período del reporte')
            ->warning()
            ->send();
    }

    private function nombreArchivo(Child $child, string $sufijo, string $extension): string
    {
        return 'asistencia-'.Str::slug($child->name).'-'.$sufijo.'.'.$extension;
    }
}
