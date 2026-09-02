<?php

namespace App\Services;

use App\Models\Obra;

class PuntoEquilibrioService
{
    /**
     * Calcula el punto de equilibrio de una obra.
     *
     * Fórmula:
     *   Unidades = Costos Fijos / (Precio Unitario - Costo Variable Unitario)
     *   Monto   = Unidades × Precio Unitario
     *   Margen de Contribución = Precio Unitario - Costo Variable Unitario
     *
     * @return array{unidades: float, monto: float, margen_contribucion: float}
     */
    public function calcularPuntoEquilibrio(
        Obra $obra,
        float $costoFijoMensual,
        float $costoVariableUnitario,
        float $precioUnitario,
    ): array {
        $costoFijo = number_format($costoFijoMensual, 2, '.', '');
        $costoVar = number_format($costoVariableUnitario, 2, '.', '');
        $precio = number_format($precioUnitario, 2, '.', '');

        $margenContribucion = bcsub($precio, $costoVar, 4);

        if (bccomp($margenContribucion, '0', 4) <= 0) {
            return [
                'unidades' => 0.0,
                'monto' => 0.0,
                'margen_contribucion' => (float) $margenContribucion,
            ];
        }

        $unidades = bcdiv($costoFijo, $margenContribucion, 2);
        $monto = bcmul($unidades, $precio, 2);

        return [
            'unidades' => (float) $unidades,
            'monto' => (float) $monto,
            'margen_contribucion' => (float) $margenContribucion,
        ];
    }
}
