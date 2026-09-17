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

    public string $mes = '';

    public function mount(): void
    {
        $this->mes = now()->format('Y-m');
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
                Select::make('mes')
                    ->label('Mes')
                    ->options($this->opcionesMeses())
                    ->live()
                    ->required(),
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

    /**
     * @return array{dias: array, resumen: array<string, int>}|null
     */
    public function reporte(): ?array
    {
        $child = $this->child();
        $mes = $this->mesElegido();

        if (! $child || ! $mes) {
            return null;
        }

        return ReporteAsistenciaMensual::generar($child, $mes);
    }

    public function descargarPdf(): ?StreamedResponse
    {
        $child = $this->child();
        $mes = $this->mesElegido();

        if (! $child || ! $mes) {
            $this->avisarSeleccionIncompleta();

            return null;
        }

        $reporte = ReporteAsistenciaMensual::generar($child, $mes);

        $pdf = Pdf::loadView('pdf.asistencia-mensual', [
            'child' => $child,
            'mes' => $mes,
            'dias' => $reporte['dias'],
            'resumen' => $reporte['resumen'],
        ]);

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            $this->nombreArchivo($child, $mes, 'pdf'),
        );
    }

    public function descargarExcel(): ?StreamedResponse
    {
        $child = $this->child();
        $mes = $this->mesElegido();

        if (! $child || ! $mes) {
            $this->avisarSeleccionIncompleta();

            return null;
        }

        // ZipStream (que arma el .xlsx por dentro) procesa en bloques de
        // 16MB: con el memory_limit por defecto del servidor (128M) eso
        // alcanza a reventarlo. Es un export puntual, no hace falta subir
        // el límite global de PHP para esto.
        ini_set('memory_limit', '512M');

        $bytes = Excel::raw(new AsistenciaMensualExport($child, $mes), ExcelFormat::XLSX);

        return response()->streamDownload(
            fn () => print ($bytes),
            $this->nombreArchivo($child, $mes, 'xlsx'),
        );
    }

    private function avisarSeleccionIncompleta(): void
    {
        Notification::make()
            ->title('Elegí un ambiente, un niño y un mes')
            ->warning()
            ->send();
    }

    private function nombreArchivo(Child $child, CarbonImmutable $mes, string $extension): string
    {
        return 'asistencia-'.Str::slug($child->name).'-'.$mes->format('Y-m').'.'.$extension;
    }
}
