<?php

namespace App\Services\Reportes;

use App\Models\DetalleAsiento;
use App\Models\PlanCuenta;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class BalanceGeneralService
{
    public function generar(Carbon $fecha): array
    {
        $gruposMap = [
            'activo_corriente' => ['11', '12'],
            'activo_no_corriente' => ['13', '14', '15', '16', '17', '18', '19'],
            'pasivo_corriente' => ['21', '22', '23', '24', '25'],
            'pasivo_no_corriente' => ['26', '27', '28', '29'],
            'patrimonio' => ['31', '32', '33', '34', '35'],
        ];

        $grupos = [];

        foreach ($gruposMap as $grupo => $prefijos) {
            $cuentas = PlanCuenta::where('grupo', match (true) {
                str_starts_with($prefijos[0], '1') => 'activo',
                str_starts_with($prefijos[0], '2') => 'pasivo',
                default => 'patrimonio',
            })
            ->where('activa', true)
            ->where(function ($query) use ($prefijos) {
                foreach ($prefijos as $prefijo) {
                    $query->orWhere('codigo', 'LIKE', $prefijo . '%');
                }
            })
            ->get();

            $saldoGrupo = '0';
            $items = [];

            foreach ($cuentas as $cuenta) {
                $saldo = $this->calcularSaldo($cuenta->id, $fecha);

                if (bccomp($saldo, '0', 2) !== 0) {
                    $items[] = [
                        'codigo' => $cuenta->codigo,
                        'nombre' => $cuenta->nombre,
                        'saldo' => (float) $saldo,
                    ];
                    $saldoGrupo = bcadd($saldoGrupo, $saldo, 2);
                }
            }

            $grupos[$grupo] = [
                'items' => $items,
                'total' => (float) $saldoGrupo,
            ];
        }

        $totalActivo = bcadd(
            number_format($grupos['activo_corriente']['total'], 2, '.', ''),
            number_format($grupos['activo_no_corriente']['total'], 2, '.', ''),
            2,
        );

        $totalPasivo = bcadd(
            number_format($grupos['pasivo_corriente']['total'], 2, '.', ''),
            number_format($grupos['pasivo_no_corriente']['total'], 2, '.', ''),
            2,
        );

        $totalPasivoPatrimonio = bcadd(
            $totalPasivo,
            number_format($grupos['patrimonio']['total'], 2, '.', ''),
            2,
        );

        return [
            'activo' => $grupos,
            'pasivo' => [
                'corriente' => $grupos['pasivo_corriente'],
                'no_corriente' => $grupos['pasivo_no_corriente'],
            ],
            'patrimonio' => $grupos['patrimonio'],
            'total_activo' => (float) $totalActivo,
            'total_pasivo_patrimonio' => (float) $totalPasivoPatrimonio,
        ];
    }

    private function calcularSaldo(int $cuentaId, Carbon $fecha): string
    {
        $cuenta = PlanCuenta::findOrFail($cuentaId);

        $detalles = DetalleAsiento::where('cuenta_id', $cuentaId)
            ->whereHas('asiento', function ($query) use ($fecha) {
                $query->where('fecha', '<=', $fecha)
                    ->where('estado', 'aprobado');
            })
            ->first();

        if (! $detalles) {
            return '0';
        }

        $result = DetalleAsiento::where('cuenta_id', $cuentaId)
            ->whereHas('asiento', function ($query) use ($fecha) {
                $query->where('fecha', '<=', $fecha)
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
