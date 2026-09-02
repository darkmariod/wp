<?php

namespace App\Services;

use App\Models\Obra;
use App\Models\Presupuesto;
use Illuminate\Support\Facades\DB;

class AIUService
{
    /**
     * Calcula el AIU (Administración, Imprevistos, Utilidad) sobre un costo directo.
     *
     * Fórmula:
     *   AIU = Costo Directo × (Admin% + Imprevistos% + Utilidad%) / 100
     *   Precio Venta = Costo Directo + AIU
     *
     * @return array{administracion: float, imprevistos: float, utilidad: float, total_aiu: float, precio_venta: float}
     */
    public function calcularAIU(Obra $obra, float $costoDirecto): array
    {
        $costoStr = number_format($costoDirecto, 2, '.', '');
        $adminPct = (string) $obra->aiu_administracion;
        $impPct = (string) $obra->aiu_imprevistos;
        $utilPct = (string) $obra->aiu_utilidad;

        $administracion = bcdiv(bcmul($costoStr, $adminPct, 4), '100', 2);
        $imprevistos = bcdiv(bcmul($costoStr, $impPct, 4), '100', 2);
        $utilidad = bcdiv(bcmul($costoStr, $utilPct, 4), '100', 2);
        $totalAiu = bcadd(bcadd($administracion, $imprevistos, 2), $utilidad, 2);
        $precioVenta = bcadd($costoStr, $totalAiu, 2);

        return [
            'administracion' => (float) $administracion,
            'imprevistos' => (float) $imprevistos,
            'utilidad' => (float) $utilidad,
            'total_aiu' => (float) $totalAiu,
            'precio_venta' => (float) $precioVenta,
        ];
    }

    /**
     * Recalcula subtotal_costo y subtotal_venta de un presupuesto
     * sumando sus detalles APU.
     *
     *   subtotal_costo = Σ (DetalleAPU.costo_total)
     *   subtotal_venta = cantidad × precio_venta_unitario
     */
    public function recalcularPresupuesto(Presupuesto $presupuesto): Presupuesto
    {
        $detalles = $presupuesto->detalleAPUs;

        $subtotalCosto = '0';
        foreach ($detalles as $detalle) {
            $subtotalCosto = bcadd($subtotalCosto, (string) $detalle->costo_total, 2);
        }

        $subtotalVenta = bcmul(
            (string) $presupuesto->cantidad,
            (string) $presupuesto->precio_venta_unitario,
            2,
        );

        DB::transaction(function () use ($presupuesto, $subtotalCosto, $subtotalVenta) {
            $presupuesto->update([
                'subtotal_costo' => $subtotalCosto,
                'subtotal_venta' => $subtotalVenta,
            ]);
        });

        return $presupuesto->fresh();
    }
}
