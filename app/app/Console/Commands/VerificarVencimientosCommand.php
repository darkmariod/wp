<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\VencimientoNotification;
use App\Services\AlertaService;
use Illuminate\Console\Command;

class VerificarVencimientosCommand extends Command
{
    protected $signature = 'contable:verificar-vencimientos';

    protected $description = 'Verificar vencimientos de cuentas por cobrar y pagar';

    public function handle(AlertaService $alertaService): int
    {
        $this->info('Verificando vencimientos...');

        $alertas = $alertaService->verificarVencimientos();

        $total = 0;

        foreach ($alertas['criticas'] as $cuenta) {
            User::all()->each(function (User $user) use ($cuenta) {
                $user->notify(new VencimientoNotification(
                    'critica',
                    "Vencida: {$cuenta->numero_comprobante} - {$cuenta->obra->nombre}",
                    [
                        'documento_id' => $cuenta->id,
                        'tipo_documento' => class_basename($cuenta),
                        'numero_comprobante' => $cuenta->numero_comprobante,
                        'monto' => number_format((float) $cuenta->monto_total, 2),
                        'fecha_vencimiento' => $cuenta->fecha_vencimiento->format('d/m/Y'),
                        'obra' => $cuenta->obra->nombre,
                    ],
                ));
            });
            $total++;
        }

        foreach ($alertas['urgentes'] as $cuenta) {
            User::all()->each(function (User $user) use ($cuenta) {
                $user->notify(new VencimientoNotification(
                    'urgente',
                    "Por vencer: {$cuenta->numero_comprobante} - {$cuenta->obra->nombre}",
                    [
                        'documento_id' => $cuenta->id,
                        'tipo_documento' => class_basename($cuenta),
                        'numero_comprobante' => $cuenta->numero_comprobante,
                        'monto' => number_format((float) $cuenta->monto_total, 2),
                        'fecha_vencimiento' => $cuenta->fecha_vencimiento->format('d/m/Y'),
                        'obra' => $cuenta->obra->nombre,
                    ],
                ));
            });
            $total++;
        }

        foreach ($alertas['informativas'] as $cuenta) {
            User::all()->each(function (User $user) use ($cuenta) {
                $user->notify(new VencimientoNotification(
                    'informativa',
                    "Próxima a vencer: {$cuenta->numero_comprobante} - {$cuenta->obra->nombre}",
                    [
                        'documento_id' => $cuenta->id,
                        'tipo_documento' => class_basename($cuenta),
                        'numero_comprobante' => $cuenta->numero_comprobante,
                        'monto' => number_format((float) $cuenta->monto_total, 2),
                        'fecha_vencimiento' => $cuenta->fecha_vencimiento->format('d/m/Y'),
                        'obra' => $cuenta->obra->nombre,
                    ],
                ));
            });
            $total++;
        }

        $this->info("Se generaron {$total} notificaciones de vencimiento.");

        return self::SUCCESS;
    }
}
