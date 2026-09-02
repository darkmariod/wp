<?php

namespace App\Console\Commands;

use App\Models\AsientoContable;
use App\Services\CierreMensualService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CerrarMesCommand extends Command
{
    protected $signature = 'contable:cerrar-mes {mes?} {anio?} {mode?}';

    protected $description = 'Cerrar mes contable';

    public function handle(CierreMensualService $cierreService): int
    {
        $mode = $this->argument('mode');

        if ($mode === 'reminder') {
            $this->warn('Recordatorio: El cierre mensual está programado para hoy a las 23:00.');

            return self::SUCCESS;
        }

        $mes = (int) $this->argument('mes');
        $anio = (int) $this->argument('anio');

        if ($mes === 0 || $anio === 0) {
            $this->error('Uso: contable:cerrar-mes {mes} {anio}');
            $this->line('Ejemplo: contable:cerrar-mes 7 2026');

            return self::FAILURE;
        }

        if ($mes < 1 || $mes > 12) {
            $this->error("Mes inválido: {$mes}. Debe ser entre 1 y 12.");

            return self::FAILURE;
        }

        $numeroAsiento = "CIE-{$anio}-" . str_pad((string) $mes, 2, '0', STR_PAD_LEFT);
        $yaCerrado = AsientoContable::where('numero_asiento', $numeroAsiento)
            ->where('tipo', 'cierre')
            ->exists();

        if ($yaCerrado) {
            $this->error("El mes {$mes}/{$anio} ya fue cerrado.");

            return self::FAILURE;
        }

        $this->info("Cerrando mes {$mes}/{$anio}...");

        try {
            $asiento = $cierreService->cerrarMes($mes, $anio);
            $this->info("Asiento de cierre creado: {$asiento->numero_asiento}");
            $this->line("Detalles: {$asiento->detalles->count()} partidas contables.");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Error al cerrar mes: {$e->getMessage()}");

            return self::FAILURE;
        }
    }
}
