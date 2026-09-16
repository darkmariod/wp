<?php

namespace App\Livewire\MiEscuelita;

use App\Http\Controllers\MiEscuelita\Concerns\ResolvesCurrentChild;
use App\Models\Attendance;
use App\Support\CicloEscolar;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Asistencia extends Component
{
    use ResolvesCurrentChild;

    /**
     * Mes elegido como anio*100 + mes (ej. 202609 = septiembre 2026).
     * Null = mes actual.
     */
    public ?int $mes = null;

    protected $queryString = [
        'mes' => ['except' => null],
    ];

    /**
     * Nino actual resuelto desde la sesion (una familia puede tener varios).
     */
    #[Computed]
    public function nino()
    {
        return $this->currentChild();
    }

    /**
     * Resumen del mes elegido (o del actual): conteos por estado.
     */
    #[Computed]
    public function resumenMes(): array
    {
        $child = $this->currentChild();

        if (! $child) {
            return [];
        }

        return CicloEscolar::resumenMes($this->mesElegido(), $child);
    }

    /**
     * Resumen anual SOLO del ultimo ciclo cerrado. Devuelve null cuando el
     * ciclo vigente no esta cerrado y el anterior no tiene registros (o no
     * existe: prehistoria pre-2025) para no mostrar un anual vacio.
     */
    #[Computed]
    public function resumenAnual(): ?array
    {
        $anio = CicloEscolar::ultimoCerrado();

        if ($anio === null) {
            return null;
        }

        $child = $this->currentChild();

        if (! $child) {
            return null;
        }

        $counts = CicloEscolar::resumenCiclo($anio, $child);

        if (array_sum($counts) === 0) {
            return null;
        }

        return [
            'anio' => $anio,
            'etiqueta' => CicloEscolar::etiqueta($anio),
            'counts' => $counts,
        ];
    }

    /**
     * Dia por dia del mes elegido, en orden cronologico.
     */
    #[Computed]
    public function detalleDias(): Collection
    {
        $child = $this->currentChild();

        if (! $child) {
            return new Collection();
        }

        $inicio = $this->mesElegido()->startOfMonth();
        $fin = $this->mesElegido()->endOfMonth();

        return $child->attendances()
            ->whereBetween('date', [$inicio, $fin])
            ->orderBy('date')
            ->get(['id', 'child_id', 'date', 'status', 'notes']);
    }

    /**
     * Semanas (lun-dom) del mes elegido, cada celda con su estado si lo
     * tiene. null = fuera del mes (relleno para alinear la grilla).
     *
     * @return array<int, array<int, array{fecha: CarbonImmutable, status: ?string, esHoy: bool, esFinDeSemana: bool}|null>>
     */
    #[Computed]
    public function calendario(): array
    {
        if (! $this->currentChild()) {
            return [];
        }

        $inicio = $this->mesElegido()->startOfMonth();
        $fin = $inicio->endOfMonth();
        $hoy = CarbonImmutable::now()->startOfDay();

        $porFecha = $this->detalleDias->keyBy(fn ($dia) => $dia->date->toDateString());

        $celdas = array_fill(0, $inicio->dayOfWeekIso - 1, null);

        for ($dia = $inicio; $dia->lte($fin); $dia = $dia->addDay()) {
            $registro = $porFecha->get($dia->toDateString());

            $celdas[] = [
                'fecha' => $dia,
                'status' => $registro?->status,
                'esHoy' => $dia->isSameDay($hoy),
                'esFinDeSemana' => $dia->isWeekend(),
            ];
        }

        while (count($celdas) % 7 !== 0) {
            $celdas[] = null;
        }

        return array_chunk($celdas, 7);
    }

    /**
     * Los 12 meses del ciclo vigente para el selector.
     */
    #[Computed]
    public function opcionesMeses(): array
    {
        $inicio = CicloEscolar::inicio(CicloEscolar::vigente());

        $opciones = [];

        for ($i = 0; $i < 12; $i++) {
            $mes = $inicio->addMonths($i);
            $opciones[$mes->year * 100 + $mes->month] = ucfirst($mes->locale('es')->isoFormat('MMMM YYYY'));
        }

        return $opciones;
    }

    private function mesElegido(): CarbonImmutable
    {
        if (! $this->mes) {
            return CarbonImmutable::now()->startOfMonth();
        }

        return CarbonImmutable::createFromDate(intdiv($this->mes, 100), $this->mes % 100, 1);
    }

    public function render(): View
    {
        return view('livewire.mi-escuelita.asistencia');
    }
}