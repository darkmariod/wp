<?php

namespace App\Services;

use App\Models\AsientoContable;
use App\Models\DetalleAsiento;
use App\Models\PlanCuenta;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CierreMensualService
{
    /**
     * Cierra un mes contable creando el asiento de cierre.
     *
     * El asiento de cierre traslada el resultado del ejercicio:
     *   DEBE:  4.x.x Ingresos (cerrar saldos acreedores)
     *   HABER: 5.x.x Gastos/Costos (cerrar saldos deudores)
     *   Diferencia → 3.1.1.04 Resultado del Ejercicio
     *
     * @param  int  $mes  Mes a cerrar (1-12)
     * @param  int  $anio Año del cierre
     */
    public function cerrarMes(int $mes, int $anio): AsientoContable
    {
        $fechaInicio = Carbon::create($anio, $mes, 1);
        $fechaFin = $fechaInicio->copy()->endOfMonth();

        $totalIngresos = $this->obtenerTotalGrupo('ingreso', $fechaInicio, $fechaFin);
        $totalGastos = $this->obtenerTotalGrupo('gasto', $fechaInicio, $fechaFin);
        $totalCostos = $this->obtenerTotalGrupo('costo', $fechaInicio, $fechaFin);

        $gastosYCostos = bcadd($totalGastos, $totalCostos, 2);
        // Ingresos son negativos (haber > debe), gastos son positivos (debe > haber)
        $resultado = bcsub(bcsub('0', $totalIngresos, 2), $gastosYCostos, 2);
        // Monto absoluto de ingresos para el asiento de cierre
        $montoIngresos = ltrim($totalIngresos, '-');

        $cuentaResultado = PlanCuenta::where('codigo', '3.1.1.04')->firstOrFail();

        return DB::transaction(function () use (
            $montoIngresos,
            $gastosYCostos,
            $resultado,
            $cuentaResultado,
            $mes,
            $anio,
            $fechaFin,
        ) {
            $asiento = AsientoContable::create([
                'numero_asiento' => "CIE-{$anio}-" . str_pad((string) $mes, 2, '0', STR_PAD_LEFT),
                'fecha' => $fechaFin->toDateString(),
                'descripcion' => "Cierre mensual {$mes}/{$anio}",
                'tipo' => 'cierre',
                'estado' => 'borrador',
                'usuario_creacion' => auth()->id(),
            ]);

            // DEBE: Ingresos (cerrar saldos acreedores → al debe)
            if (bccomp($montoIngresos, '0', 2) > 0) {
                $cuentaIngresos = PlanCuenta::where('codigo', '4')->firstOrFail();
                $asiento->detalles()->create([
                    'cuenta_id' => $cuentaIngresos->id,
                    'debe' => $montoIngresos,
                    'haber' => 0,
                    'referencia' => "Cierre ingresos {$mes}/{$anio}",
                ]);
            }

            // HABER: Gastos y Costos (cerrar saldos deudores → al haber)
            if (bccomp($gastosYCostos, '0', 2) > 0) {
                $cuentaGastos = PlanCuenta::where('codigo', '5')->firstOrFail();
                $asiento->detalles()->create([
                    'cuenta_id' => $cuentaGastos->id,
                    'debe' => 0,
                    'haber' => $gastosYCostos,
                    'referencia' => "Cierre gastos/costos {$mes}/{$anio}",
                ]);
            }

            // Resultado del ejercicio
            $comp = bccomp($resultado, '0', 2);
            if ($comp > 0) {
                // Utilidad: HABER en resultado
                $asiento->detalles()->create([
                    'cuenta_id' => $cuentaResultado->id,
                    'debe' => 0,
                    'haber' => $resultado,
                    'referencia' => "Resultado {$mes}/{$anio}",
                ]);
            } elseif ($comp < 0) {
                // Pérdida: DEBE en resultado (invertir signo manualmente)
                $perdida = ltrim($resultado, '-');
                $asiento->detalles()->create([
                    'cuenta_id' => $cuentaResultado->id,
                    'debe' => $perdida,
                    'haber' => 0,
                    'referencia' => "Resultado {$mes}/{$anio}",
                ]);
            }

            return $asiento->load('detalles');
        });
    }

    /**
     * Obtiene el total de un grupo contable (ingreso/gasto/costo) en un rango de fechas.
     * Saldo = Σ DEBE - Σ HABER (para grupo=gasto y grupo=costo es positivo cuando hay gasto real).
     */
    private function obtenerTotalGrupo(string $grupo, Carbon $fechaInicio, Carbon $fechaFin): string
    {
        $detalles = DetalleAsiento::whereHas('cuenta', function ($q) use ($grupo) {
            $q->where('grupo', $grupo);
        })
            ->whereHas('asiento', function ($q) use ($fechaInicio, $fechaFin) {
                $q->whereBetween('fecha', [$fechaInicio, $fechaFin])
                    ->where('estado', 'aprobado');
            })
            ->get();

        $total = '0';
        foreach ($detalles as $detalle) {
            $total = bcadd($total, (string) $detalle->debe, 2);
            $total = bcsub($total, (string) $detalle->haber, 2);
        }

        return $total;
    }
}
