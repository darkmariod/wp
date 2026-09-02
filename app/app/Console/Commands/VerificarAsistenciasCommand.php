<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\AsistenciaPendienteNotification;
use App\Services\AlertaService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class VerificarAsistenciasCommand extends Command
{
    protected $signature = 'contable:verificar-asistencias';

    protected $description = 'Verificar asistencias diarias pendientes';

    public function handle(AlertaService $alertaService): int
    {
        $this->info('Verificando asistencias pendientes...');

        $alertas = $alertaService->verificarAsistenciasPendientes();
        $trabajadoresSinRegistro = $alertas['trabajadores_sin_registro'];

        if ($trabajadoresSinRegistro->isEmpty()) {
            $this->info('Todos los trabajadores activos registraron asistencia hoy.');

            return self::SUCCESS;
        }

        $fecha = Carbon::now()->format('d/m/Y');
        $total = 0;

        User::all()->each(function (User $user) use ($trabajadoresSinRegistro, $fecha, &$total) {
            foreach ($trabajadoresSinRegistro as $trabajador) {
                $user->notify(new AsistenciaPendienteNotification(
                    $trabajador['nombre'],
                    'N/A',
                    $fecha,
                ));
                $total++;
            }
        });

        $this->warn("Se encontraron {$trabajadoresSinRegistro->count()} trabajadores sin asistencia registrada.");
        $this->info("Se generaron {$total} notificaciones.");

        return self::SUCCESS;
    }
}
