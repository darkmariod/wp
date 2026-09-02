<?php

namespace App\Services\Reportes;

use App\Models\AsientoContable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class LibroDiarioService
{
    public function generar(Carbon $fechaInicio, Carbon $fechaFin, ?int $obraId = null): Collection
    {
        $query = AsientoContable::with(['detalles.cuenta', 'obra'])
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->orderBy('fecha')
            ->orderBy('numero_asiento');

        if ($obraId !== null) {
            $query->where('obra_id', $obraId);
        }

        $asientos = $query->get();

        return $asientos->map(function (AsientoContable $asiento) {
            $totalDebe = '0';
            $totalHaber = '0';

            $detalles = $asiento->detalles->map(function ($detalle) use (&$totalDebe, &$totalHaber) {
                $totalDebe = bcadd($totalDebe, (string) $detalle->debe, 2);
                $totalHaber = bcadd($totalHaber, (string) $detalle->haber, 2);

                return [
                    'cuenta_codigo' => $detalle->cuenta->codigo ?? '—',
                    'cuenta_nombre' => $detalle->cuenta->nombre ?? '—',
                    'referencia' => $detalle->referencia,
                    'debe' => (float) $detalle->debe,
                    'haber' => (float) $detalle->haber,
                ];
            });

            return [
                'id' => $asiento->id,
                'numero_asiento' => $asiento->numero_asiento,
                'fecha' => $asiento->fecha->format('d/m/Y'),
                'descripcion' => $asiento->descripcion,
                'tipo' => $asiento->tipo,
                'estado' => $asiento->estado,
                'obra' => $asiento->obra->codigo ?? '—',
                'detalles' => $detalles,
                'total_debe' => (float) $totalDebe,
                'total_haber' => (float) $totalHaber,
            ];
        });
    }
}
