<?php

namespace App\Services;

use App\Models\FlujoCaja;
use App\Models\Obra;

class FlujoCajaService
{
    /**
     * Registra un ingreso en el flujo de caja de una obra.
     */
    public function registrarIngreso(Obra $obra, string $categoria, float $monto, string $referencia): FlujoCaja
    {
        return FlujoCaja::create([
            'obra_id' => $obra->id,
            'fecha' => now()->toDateString(),
            'tipo' => 'ingreso',
            'categoria' => $categoria,
            'monto' => number_format($monto, 2, '.', ''),
            'referencia' => $referencia,
        ]);
    }

    /**
     * Registra un egreso en el flujo de caja de una obra.
     */
    public function registrarEgreso(Obra $obra, string $categoria, float $monto, string $referencia): FlujoCaja
    {
        return FlujoCaja::create([
            'obra_id' => $obra->id,
            'fecha' => now()->toDateString(),
            'tipo' => 'egreso',
            'categoria' => $categoria,
            'monto' => number_format($monto, 2, '.', ''),
            'referencia' => $referencia,
        ]);
    }

    /**
     * Calcula el resultado neto (ingresos - egresos) de una obra.
     *
     * Fórmula: Resultado = SUM(ingresos) - SUM(egresos)
     */
    public function resultadoNeto(Obra $obra): float
    {
        $totales = $this->saldoPorObra($obra);

        return $totales['resultado'];
    }

    /**
     * Retorna el desglose de ingresos, egresos y resultado de una obra.
     *
     * @return array{ingresos: float, egresos: float, resultado: float}
     */
    public function saldoPorObra(Obra $obra): array
    {
        $ingresos = (string) $obra->flujoCajas()
            ->where('tipo', 'ingreso')
            ->sum('monto');

        $egresos = (string) $obra->flujoCajas()
            ->where('tipo', 'egreso')
            ->sum('monto');

        $resultado = bcsub($ingresos, $egresos, 2);

        return [
            'ingresos' => (float) $ingresos,
            'egresos' => (float) $egresos,
            'resultado' => (float) $resultado,
        ];
    }
}
