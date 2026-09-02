<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('contable:verificar-vencimientos')->dailyAt('07:00');
        $schedule->command('contable:verificar-asistencias')->dailyAt('18:00');
        $schedule->command('contable:cerrar-mes reminder')->lastDayOfMonth()->at('23:00');
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
