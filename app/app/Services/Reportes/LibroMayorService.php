<?php

namespace App\Services\Reportes;

use App\Models\DetalleAsiento;
use App\Models\PlanCuenta;
use Illuminate\Support\Carbon;

class LibroMayorService
{
    public function generar(int $cuentaId, Carbon $fechaInicio, Carbon $fechaFin): array
    {
        $cuenta = PlanCuenta::findOrFail($cuentaId);

        $detalles = DetalleAsiento::where('cuenta_id', $cuentaId)
            ->whereHas('asiento', function ($query) use ($fechaInicio, $fechaFin) {
                $query->whereBetween('fecha', [$fechaInicio, $fechaFin])
                    ->orderBy('fecha')
                    ->orderBy('numero_asiento');
            })
            ->with('asiento')
            ->get();

        $saldoInicial = $this->calcularSaldoAnterior($cuentaId, $fechaInicio);
        $saldo = $saldoInicial;

        $movimientos = [];

        foreach ($detalles as $detalle) {
            $debe = (string) $detalle->debe;
            $haber = (string) $detalle->haber;

            if ($cuenta->tipo === 'deudor') {
                $saldo = bcsub(bcadd($saldo, $debe, 2), $haber, 2);
            } else {
                $saldo = bcsub(bcadd($saldo, $haber, 2), $debe, 2);
            }

            $movimientos[] = [
                'fecha' => $detalle->asiento->fecha->format('d/m/Y'),
                'numero_asiento' => $detalle->asiento->numero_asiento,
                'referencia' => $detalle->referencia,
                'debe' => (float) $debe,
                'haber' => (float) $haber,
                'saldo' => (float) $saldo,
            ];
        }

        return [
            'cuenta' => [
                'id' => $cuenta->id,
                'codigo' => $cuenta->codigo,
                'nombre' => $cuenta->nombre,
                'tipo' => $cuenta->tipo,
            ],
            'periodo' => [
                'inicio' => $fechaInicio->format('d/m/Y'),
                'fin' => $fechaFin->format('d/m/Y'),
            ],
            'saldo_inicial' => (float) $saldoInicial,
            'movimientos' => $movimientos,
            'saldo_final' => (float) $saldo,
            'total_debe' => (float) collect($movimientos)->sum('debe'),
            'total_haber' => (float) collect($movimientos)->sum('haber'),
        ];
    }

    private function calcularSaldoAnterior(int $cuentaId, Carbon $fechaInicio): string
    {
        $cuenta = PlanCuenta::findOrFail($cuentaId);

        $result = DetalleAsiento::where('cuenta_id', $cuentaId)
            ->whereHas('asiento', function ($query) use ($fechaInicio) {
                $query->where('fecha', '<', $fechaInicio)
                    ->where('estado', 'aprobado');
            })
            ->selectRaw('SUM(debe) as total_debe, SUM(haber) as total_haber')
            ->first();

        $totalDebe = (string) ($result->total_debe ?? 0);
        $totalHaber = (string) ($result->total_haber ?? 0);

        if ($cuenta->tipo === 'deudor') {
            return bcsub($totalDebe, $totalHaber, 2);
        }

        return bcsub($totalHaber, $totalDebe, 2);
    }
}
