<?php

namespace App\Services;

use App\Models\CuentaPorCobrar;
use App\Models\CuentaPorPagar;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class VencimientoService
{
    /**
     * Verifica vencimientos de cuentas por cobrar y por pagar.
     *
     * Auto-actualiza a 'vencida' las cuentas cuya fecha_vencimiento ya pasó.
     * Clasifica en vencidas y por vencer (con alertas a 7, 3 y 1 día).
     *
     * @return array{
     *     vencidos_cobrar: Collection,
     *     por_vencer_cobrar: Collection,
     *     vencidos_pagar: Collection,
     *     por_vencer_pagar: Collection,
     * }
     */
    public function verificarVencimientos(): array
    {
        $hoy = Carbon::now()->startOfDay();

        $this->actualizarVencidas($hoy);

        return [
            'vencidos_cobrar' => $this->obtenerVencidosCobrar($hoy),
            'por_vencer_cobrar' => $this->obtenerPorVencerCobrar($hoy),
            'vencidos_pagar' => $this->obtenerVencidosPagar($hoy),
            'por_vencer_pagar' => $this->obtenerPorVencerPagar($hoy),
        ];
    }

    /**
     * Actualiza a 'vencida' todas las cuentas cuya fecha de vencimiento ya pasó
     * y que aún no están en estado terminal (cobrada/pagada).
     */
    private function actualizarVencidas(Carbon $hoy): void
    {
        CuentaPorCobrar::where('fecha_vencimiento', '<', $hoy)
            ->whereNotIn('estado', ['cobrada', 'vencida', 'mora'])
            ->update(['estado' => 'vencida']);

        CuentaPorPagar::where('fecha_vencimiento', '<', $hoy)
            ->whereNotIn('estado', ['pagada', 'vencida'])
            ->update(['estado' => 'vencida']);
    }

    /**
     * Cuentas por cobrar vencidas (fecha < hoy, no cobradas).
     */
    private function obtenerVencidosCobrar(Carbon $hoy): Collection
    {
        return CuentaPorCobrar::where('fecha_vencimiento', '<', $hoy)
            ->whereNotIn('estado', ['cobrada'])
            ->with(['obra', 'cliente'])
            ->get();
    }

    /**
     * Cuentas por cobrar próximas a vencer (alertas a 7, 3 y 1 día).
     */
    private function obtenerPorVencerCobrar(Carbon $hoy): Collection
    {
        return CuentaPorCobrar::where('fecha_vencimiento', '>=', $hoy)
            ->where('fecha_vencimiento', '<=', $hoy->copy()->addDays(7))
            ->whereNotIn('estado', ['cobrada'])
            ->with(['obra', 'cliente'])
            ->get();
    }

    /**
     * Cuentas por pagar vencidas (fecha < hoy, no pagadas).
     */
    private function obtenerVencidosPagar(Carbon $hoy): Collection
    {
        return CuentaPorPagar::where('fecha_vencimiento', '<', $hoy)
            ->whereNotIn('estado', ['pagada'])
            ->with(['obra', 'proveedor'])
            ->get();
    }

    /**
     * Cuentas por pagar próximas a vencer (alertas a 7, 3 y 1 día).
     */
    private function obtenerPorVencerPagar(Carbon $hoy): Collection
    {
        return CuentaPorPagar::where('fecha_vencimiento', '>=', $hoy)
            ->where('fecha_vencimiento', '<=', $hoy->copy()->addDays(7))
            ->whereNotIn('estado', ['pagada'])
            ->with(['obra', 'proveedor'])
            ->get();
    }
}
