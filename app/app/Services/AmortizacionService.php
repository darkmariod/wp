<?php

namespace App\Services;

use App\Models\AmortizacionAnticipo;
use App\Models\AnticipoCliente;
use App\Models\AsientoContable;
use Illuminate\Support\Facades\DB;

class AmortizacionService
{
    private const CUENTA_INGRESOS_CONTRATOS = '4.1.1.01';

    /**
     * Calcula la amortización de un anticipo de cliente según el avance de obra.
     *
     * Fórmula:
     *   Monto Amortización = Anticipo Total × (% Avance actual / 100)
     *   Amortización Acumulada = Σ amортizaciones_previas.monto_amortizado + monto_actual
     *   Saldo Pendiente = Anticipo Total - Amortización Acumulada
     *
     * @return array{monto_a_amortizar: float, amortizacion_acumulada: float, saldo_pendiente: float}
     */
    public function calcularAmortizacion(AnticipoCliente $anticipo, float $avancePorcentaje): array
    {
        $montoTotal = (string) $anticipo->monto_total;
        $avanceStr = number_format($avancePorcentaje, 2, '.', '');

        $montoAmortizar = bcmul($montoTotal, bcdiv($avanceStr, '100', 4), 2);

        $acumuladaAnterior = $anticipo->amortizaciones()
            ->pluck('monto_amortizado')
            ->reduce(fn (string $carry, string $val) => bcadd($carry, $val, 2), '0');

        $amortizacionAcumulada = bcadd($acumuladaAnterior, $montoAmortizar, 2);
        $saldoPendiente = bcsub($montoTotal, $amortizacionAcumulada, 2);

        if (bccomp($saldoPendiente, '0', 2) < 0) {
            $saldoPendiente = '0';
        }

        return [
            'monto_a_amortizar' => (float) $montoAmortizar,
            'amortizacion_acumulada' => (float) $amortizacionAcumulada,
            'saldo_pendiente' => (float) $saldoPendiente,
        ];
    }

    /**
     * Genera el asiento contable para una amortización de anticipo.
     *
     * DEBE: 4.1.1.01 Ingresos por Contratos (reconocimiento de ingreso)
     * HABER: Anticipo Cliente (reducción del pasivo por anticipo)
     */
    public function generarAsientoAmortizacion(AmortizacionAnticipo $amortizacion): AsientoContable
    {
        $anticipo = $anticipo = $amortizacion->anticipo;
        $obra = $anticipo->obra;

        $cuentaIngresos = \App\Models\PlanCuenta::where('codigo', self::CUENTA_INGRESOS_CONTRATOS)->firstOrFail();
        $cuentaAnticipo = \App\Models\PlanCuenta::where('codigo', '2.1.1.02')->firstOrFail();

        $montoStr = (string) $amortizacion->monto_amortizado;

        return DB::transaction(function () use ($montoStr, $cuentaIngresos, $cuentaAnticipo, $obra, $amortizacion) {
            $asiento = AsientoContable::create([
                'numero_asiento' => 'AMI-' . now()->format('YmdHis'),
                'fecha' => $amortizacion->fecha_amortizacion,
                'descripcion' => "Amortización de anticipo #{$amortizacion->numero_amortizacion}",
                'obra_id' => $obra->id,
                'tipo' => 'automatico',
                'estado' => 'borrador',
                'usuario_creacion' => auth()->id(),
            ]);

            $asiento->detalles()->create([
                'cuenta_id' => $cuentaIngresos->id,
                'debe' => $montoStr,
                'haber' => 0,
                'referencia' => "Amortización anticipo #{$amortizacion->numero_amortizacion}",
            ]);

            $asiento->detalles()->create([
                'cuenta_id' => $cuentaAnticipo->id,
                'debe' => 0,
                'haber' => $montoStr,
                'referencia' => "Amortización anticipo #{$amortizacion->numero_amortizacion}",
            ]);

            $amortizacion->update(['asiento_id' => $asiento->id]);

            return $asiento->load('detalles');
        });
    }
}
