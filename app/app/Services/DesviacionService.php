<?php

namespace App\Services;

class DesviacionService
{
    /**
     * Calcula la desviación entre un valor presupuestado y uno ejecutado.
     *
     * Fórmula:
     *   Desviación = Ejecutado - Presupuestado
     *   Porcentaje = (Ejecutado - Presupuestado) / Presupuestado × 100
     *
     * @return array{desviacion: float, porcentaje: float, tipo: 'sobrecosto'|'subconsumo'|'exacto'}
     */
    public function calcularDesviacion(float $presupuestado, float $ejecutado): array
    {
        $presupuestadoStr = number_format($presupuestado, 2, '.', '');
        $ejecutadoStr = number_format($ejecutado, 2, '.', '');

        $desviacion = bcsub($ejecutadoStr, $presupuestadoStr, 2);

        if (bccomp($presupuestadoStr, '0', 2) === 0) {
            return [
                'desviacion' => (float) $desviacion,
                'porcentaje' => 0.0,
                'tipo' => bccomp($desviacion, '0', 2) === 0 ? 'exacto' : 'sobrecosto',
            ];
        }

        $porcentaje = bcdiv(
            bcmul($desviacion, '100', 4),
            $presupuestadoStr,
            2,
        );

        $comp = bccomp($desviacion, '0', 2);

        $tipo = match (true) {
            $comp > 0 => 'sobrecosto',
            $comp < 0 => 'subconsumo',
            default => 'exacto',
        };

        return [
            'desviacion' => (float) $desviacion,
            'porcentaje' => (float) $porcentaje,
            'tipo' => $tipo,
        ];
    }
}
