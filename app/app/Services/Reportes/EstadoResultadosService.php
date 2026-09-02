<?php

namespace App\Services\Reportes;

use App\Models\DetalleAsiento;
use App\Models\PlanCuenta;
use Illuminate\Support\Carbon;

class EstadoResultadosService
{
    public function generar(Carbon $fechaInicio, Carbon $fechaFin): array
    {
        $gruposConfig = [
            'ingresos' => [
                'grupo_contable' => 'ingreso',
                'prefijos' => ['41', '42', '43'],
            ],
            'costos' => [
                'grupo_contable' => 'costo',
                'prefijos' => ['61', '62', '63'],
            ],
            'gastos_administrativos' => [
                'grupo_contable' => 'gasto',
                'prefijos' => ['51', '52'],
            ],
            'gastos_venta' => [
                'grupo_contable' => 'gasto',
                'prefijos' => ['53', '54'],
            ],
            'gastos_financieros' => [
                'grupo_contable' => 'gasto',
                'prefijos' => ['55', '56'],
            ],
        ];

        $grupos = [];
        $totales = [];

        foreach ($gruposConfig as $key => $config) {
            $cuentas = PlanCuenta::where('grupo', $config['grupo_contable'])
                ->where('activa', true)
                ->where(function ($query) use ($config) {
                    foreach ($config['prefijos'] as $prefijo) {
                        $query->orWhere('codigo', 'LIKE', $prefijo . '%');
                    }
                })
                ->get();

            $saldoGrupo = '0';
            $items = [];

            foreach ($cuentas as $cuenta) {
                $saldo = $this->calcularSaldoPeriodo($cuenta->id, $fechaInicio, $fechaFin);

                if (bccomp($saldo, '0', 2) !== 0) {
                    $items[] = [
                        'codigo' => $cuenta->codigo,
                        'nombre' => $cuenta->nombre,
                        'saldo' => (float) $saldo,
                    ];
                    $saldoGrupo = bcadd($saldoGrupo, $saldo, 2);
                }
            }

            $grupos[$key] = [
                'items' => $items,
                'total' => (float) $saldoGrupo,
            ];
            $totales[$key] = $saldoGrupo;
        }

        $totalIngresos = $totales['ingresos'];
        $totalCostos = $totales['costos'];
        $totalGastosAdmin = $totales['gastos_administrativos'];
        $totalGastosVenta = $totales['gastos_venta'];
        $totalGastosFinancieros = $totales['gastos_financieros'];

        $utilidadBruta = bcsub($totalIngresos, $totalCostos, 2);

        $totalGastosOperativos = bcadd(
            bcadd($totalGastosAdmin, $totalGastosVenta, 2),
            $totalGastosFinancieros,
            2,
        );

        $utilidadOperativa = bcsub($utilidadBruta, $totalGastosOperativos, 2);
        $utilidadAntesImpuestos = $utilidadOperativa;
        $utilidadNeta = $utilidadAntesImpuestos;

        return [
            'periodo' => [
                'inicio' => $fechaInicio->format('d/m/Y'),
                'fin' => $fechaFin->format('d/m/Y'),
            ],
            'grupos' => $grupos,
            'calculado' => [
                'utilidad_bruta' => [
                    'monto' => (float) $utilidadBruta,
                    'porcentaje' => $this->calcularPorcentaje($utilidadBruta, $totalIngresos),
                ],
                'utilidad_operativa' => [
                    'monto' => (float) $utilidadOperativa,
                    'porcentaje' => $this->calcularPorcentaje($utilidadOperativa, $totalIngresos),
                ],
                'utilidad_antes_impuestos' => [
                    'monto' => (float) $utilidadAntesImpuestos,
                    'porcentaje' => $this->calcularPorcentaje($utilidadAntesImpuestos, $totalIngresos),
                ],
                'utilidad_neta' => [
                    'monto' => (float) $utilidadNeta,
                    'porcentaje' => $this->calcularPorcentaje($utilidadNeta, $totalIngresos),
                ],
            ],
        ];
    }

    private function calcularSaldoPeriodo(int $cuentaId, Carbon $fechaInicio, Carbon $fechaFin): string
    {
        $cuenta = PlanCuenta::findOrFail($cuentaId);

        $result = DetalleAsiento::where('cuenta_id', $cuentaId)
            ->whereHas('asiento', function ($query) use ($fechaInicio, $fechaFin) {
                $query->whereBetween('fecha', [$fechaInicio, $fechaFin])
                    ->where('estado', 'aprobado');
            })
            ->selectRaw('SUM(debe) as total_debe, SUM(haber) as total_haber')
            ->first();

        $totalDebe = (string) ($result->total_debe ?? 0);
        $totalHaber = (string) ($result->total_haber ?? 0);

        if (in_array($cuenta->grupo, ['ingreso', 'patrimonio'])) {
            return bcsub($totalHaber, $totalDebe, 2);
        }

        return bcsub($totalDebe, $totalHaber, 2);
    }

    private function calcularPorcentaje(string $valor, string $base): float
    {
        if (bccomp($base, '0', 2) === 0) {
            return 0.0;
        }

        return (float) bcdiv(
            bcmul($valor, '100', 4),
            $base,
            2,
        );
    }
}
