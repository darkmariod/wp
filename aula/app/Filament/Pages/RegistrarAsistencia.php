<?php

namespace App\Filament\Pages;

use App\Models\Attendance;
use App\Models\Child;
use App\Models\Environment;
use BackedEnum;
use Carbon\CarbonImmutable;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use UnitEnum;

class RegistrarAsistencia extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static string|UnitEnum|null $navigationGroup = 'Gestión Académica';

    protected static ?int $navigationSort = 25;

    protected static ?string $navigationLabel = 'Registro de Asistencia';

    protected static ?string $title = 'Registro de Asistencia';

    protected string $view = 'filament.pages.registrar-asistencia';

    public ?int $environment_id = null;

    public string $fecha = '';

    public array $estados = [];

    public function mount(): void
    {
        $this->fecha = now()->toDateString();
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
                    ->required(),
                DatePicker::make('fecha')
                    ->label('Fecha')
                    ->default(now()->toDateString())
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->required(),
            ])
            ->columns(2);
    }

    /**
     * Staff ve todos los ambientes activos; la guia solo los suyos.
     */
    public function opcionesAmbientes(): array
    {
        $query = Environment::query()->where('active', true);

        if (auth()->user()?->isGuia()) {
            $query->where('teacher_id', auth()->id());
        }

        return $query->orderBy('name')->pluck('name', 'id')->all();
    }

    public function updatedEnvironmentId(): void
    {
        $this->estados = [];
        $this->cargarEstadosDelDia();
    }

    public function updatedFecha(): void
    {
        $this->estados = [];
        $this->cargarEstadosDelDia();
    }

    /**
     * Precarga los estados ya registrados del dia para poder corregirlos.
     */
    protected function cargarEstadosDelDia(): void
    {
        if (! $this->environment_id || ! $this->fecha) {
            return;
        }

        $this->estados = Attendance::query()
            ->whereIn('child_id', $this->ninos()->pluck('id'))
            ->where('date', CarbonImmutable::parse($this->fecha))
            ->pluck('status', 'child_id')
            ->all();
    }

    /**
     * Ninos activos del ambiente elegido, listos para marcar.
     */
    #[Computed]
    public function ninos(): Collection
    {
        if (! $this->environment_id) {
            return new Collection();
        }

        $query = Child::query()
            ->where('environment_id', $this->environment_id)
            ->where('status', 'active');

        if (auth()->user()?->isGuia()) {
            $query->whereHas('environment', fn ($q) => $q->where('teacher_id', auth()->id()));
        }

        return $query->orderBy('name')->get();
    }

    public function guardar(): void
    {
        $this->validate();

        // La guia solo marque en ambientes propios: si la request viene
        // manipulada con un environment_id ajeno, se corta antes de
        // escribir nada.
        $ambiente = Environment::find($this->environment_id);

        if (auth()->user()?->isGuia() && $ambiente?->teacher_id !== auth()->id()) {
            Notification::make()
                ->title('Ambiente no permitido')
                ->warning()
                ->send();

            return;
        }

        $marcados = collect($this->estados)
            ->filter(fn ($estado) => is_string($estado)
                && in_array($estado, [
                    Attendance::STATUS_PRESENTE,
                    Attendance::STATUS_ATRASO,
                    Attendance::STATUS_FALTA_JUSTIFICADA,
                    Attendance::STATUS_FALTA_INJUSTIFICADA,
                ], true));

        if ($marcados->isEmpty()) {
            Notification::make()
                ->title('No marcaste ningún niño')
                ->warning()
                ->send();

            return;
        }

        $ninosDelAmbiente = $this->ninos()->keyBy('id');
        $fecha = CarbonImmutable::parse($this->fecha);

        DB::transaction(function () use ($marcados, $ninosDelAmbiente, $fecha) {
            foreach ($marcados as $childId => $estado) {
                if (! $ninosDelAmbiente->has($childId)) {
                    continue;
                }

                Attendance::updateOrCreate(
                    ['child_id' => $childId, 'date' => $fecha],
                    ['status' => $estado, 'recorded_by' => auth()->id(), 'notes' => null],
                );
            }
        });

        Notification::make()
            ->title('Asistencia guardada')
            ->success()
            ->send();
    }
}