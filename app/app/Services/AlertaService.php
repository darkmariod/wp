<?php

namespace App\Services;

use App\Models\AnticipoCliente;
use App\Models\DetalleAsiento;
use App\Models\Obra;
use App\Models\Trabajador;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AlertaService
{
    private VencimientoService $vencimientoService;
    private DesviacionService $desviacionService;

    public function __construct(VencimientoService $vencimientoService, DesviacionService $desviacionService)
    {
        $this->vencimientoService = $vencimientoService;
        $this->desviacionService = $desviacionService;
    }

    /**
     * Verifica vencimientos de cuentas por cobrar y por pagar.
     * Retorna alertas agrupadas por urgencia.
     *
     * @return array{
     *     criticas: Collection,
     *     urgentes: Collection,
     *     informativas: Collection,
     * }
     */
    public function verificarVencimientos(): array
    {
        $hoy = Carbon::now()->startOfDay();

        $vencimientos = $this->vencimientoService->verificarVencimientos();

        $todas = $vencimientos['vencidos_cobrar']
            ->concat($vencimientos['vencidos_pagar'])
            ->concat($vencimientos['por_vencer_cobrar'])
            ->concat($vencimientos['por_vencer_pagar']);

        $criticas = $todas->filter(function ($item) use ($hoy) {
            $fechaVenc = Carbon::parse($item->fecha_vencimiento)->startOfDay();

            return $fechaVenc->lte($hoy);
        });

        $urgentes = $todas->filter(function ($item) use ($hoy) {
            $fechaVenc = Carbon::parse($item->fecha_vencimiento)->startOfDay();

            return $fechaVenc->gt($hoy) && $fechaVenc->lte($hoy->copy()->addDays(3));
        });

        $informativas = $todas->filter(function ($item) use ($hoy) {
            $fechaVenc = Carbon::parse($item->fecha_vencimiento)->startOfDay();

            return $fechaVenc->gt($hoy->copy()->addDays(3)) && $fechaVenc->lte($hoy->copy()->addDays(7));
        });

        return [
            'criticas' => $criticas,
            'urgentes' => $urgentes,
            'informativas' => $informativas,
        ];
    }

    /**
     * Verifica asistencias diarias pendientes.
     * Alerta si algún trabajador activo no registró horas hoy.
     *
     * @return array{trabajadores_sin_registro: Collection}
     */
    public function verificarAsistenciasPendientes(): array
    {
        $hoy = Carbon::now()->startOfDay();

        $trabajadoresActivos = Trabajador::where('activo', true)
            ->whereHas('asistencias', function ($q) use ($hoy) {
                $q->where('fecha', '>=', $hoy->copy()->subDays(30));
            })
            ->get();

        $trabajadoresConRegistro = $trabajadoresActivos->filter(function ($trabajador) use ($hoy) {
            return $trabajador->asistencias()
                ->where('fecha', $hoy)
                ->exists();
        });

        $sinRegistro = $trabajadoresActivos->diff($trabajadoresConRegistro)->map(function ($trabajador) {
            return [
                'trabajador_id' => $trabajador->id,
                'nombre' => trim("{$trabajador->nombres} {$trabajador->apellidos}"),
                'ultima_asistencia' => $trabajador->asistencias()
                    ->latest('fecha')
                    ->value('fecha'),
            ];
        });

        return [
            'trabajadores_sin_registro' => $sinRegistro,
        ];
    }

    /**
     * Verifica obras con avance alto y anticipo sin amortizar completamente.
     *
     * @return array{obras_con_anticipos: Collection}
     */
    public function verificarAmortizacionesPendientes(): array
    {
        $obras = Obra::whereIn('estado', ['planificada', 'en_curse'])
            ->with(['anticipoClientes.amortizaciones', 'presupuestos'])
            ->get();

        $alertas = $obras->filter(function ($obra) {
            $totalPresupuesto = $obra->presupuestos->sum('subtotal_costo');
            $contratoMonto = (float) $obra->contrato_monto;

            if ($contratoMonto <= 0) {
                return false;
            }

            $avance = bcmul(
                bcdiv(
                    number_format($totalPresupuesto, 2, '.', ''),
                    number_format($contratoMonto, 2, '.', ''),
                    6,
                ),
                '100',
                2,
            );

            if ((float) $avance <= 80) {
                return false;
            }

            return $obra->anticipoClientes->contains(function (AnticipoCliente $anticipo) {
                $amortizado = $anticipo->amortizaciones->sum('monto_amortizado');
                $saldo = bcsub(
                    (string) $anticipo->monto_total,
                    number_format((float) $amortizado, 2, '.', ''),
                    2,
                );

                return bccomp($saldo, '0', 2) > 0;
            });
        })->map(function (Obra $obra) {
            $totalPresupuesto = $obra->presupuestos->sum('subtotal_costo');
            $avance = bcmul(
                bcdiv(
                    number_format((float) $totalPresupuesto, 2, '.', ''),
                    number_format((float) $obra->contrato_monto, 2, '.', ''),
                    6,
                ),
                '100',
                2,
            );

            $anticiposPendientes = $obra->anticipoClientes->filter(function (AnticipoCliente $anticipo) {
                $amortizado = $anticipo->amortizaciones->sum('monto_amortizado');
                $saldo = bcsub(
                    (string) $anticipo->monto_total,
                    number_format((float) $amortizado, 2, '.', ''),
                    2,
                );

                return bccomp($saldo, '0', 2) > 0;
            });

            $saldoTotal = $anticiposPendientes->sum(function (AnticipoCliente $anticipo) {
                $amortizado = $anticipo->amortizaciones->sum('monto_amortizado');

                return bcsub(
                    (string) $anticipo->monto_total,
                    number_format((float) $amortizado, 2, '.', ''),
                    2,
                );
            });

            return [
                'obra_id' => $obra->id,
                'nombre' => $obra->nombre,
                'avance_porcentaje' => (float) $avance,
                'anticipos_pendientes' => $anticiposPendientes->count(),
                'saldo_pendiente' => (float) $saldoTotal,
            ];
        });

        return [
            'obras_con_anticipos' => $alertas->values(),
        ];
    }

    /**
     * Verifica desviaciones presupuestarias significativas (>10%).
     *
     * @return array{desviaciones_significativas: Collection}
     */
    public function verificarDesviaciones(): array
    {
        $obras = Obra::whereIn('estado', ['planificada', 'en_curse'])
            ->with(['presupuestos'])
            ->get();

        $desviaciones = $obras->map(function (Obra $obra) {
            $presupuestoTotal = (float) $obra->presupuestos->sum('subtotal_costo');

            $ejecutado = DetalleAsiento::whereHas('asiento', function ($q) use ($obra) {
                $q->where('obra_id', $obra->id)
                    ->where('estado', 'aprobado');
            })
                ->whereHas('cuenta', function ($q) {
                    $q->whereIn('grupo', ['costo', 'gasto']);
                })
                ->sum('debe');

            $resultado = $this->desviacionService->calcularDesviacion($presupuestoTotal, (float) $ejecutado);

            $resultado['obra_id'] = $obra->id;
            $resultado['nombre'] = $obra->nombre;
            $resultado['presupuesto'] = $presupuestoTotal;
            $resultado['ejecutado'] = (float) $ejecutado;

            return $resultado;
        });

        $filtradas = $desviaciones->filter(function (array $desviacion) {
            return abs($desviacion['porcentaje']) > 10;
        })->values();

        return [
            'desviaciones_significativas' => $filtradas,
        ];
    }
}
