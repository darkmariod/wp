<?php

namespace App\Console\Commands;

use App\Models\Family;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Borra toda la data de familias/niños/docentes de prueba antes de
 * cargar el colegio real, sin tocar lo que SÍ es estructura real:
 * la cuenta de administrador, los ambientes (Mentes Absorbentes,
 * Mentes Razonadoras) y las áreas curriculares.
 *
 * Las llaves foráneas ya están armadas con cascadeOnDelete/nullOnDelete
 * (evidence, attendances, content, feedback, observations dependen de
 * families o de un usuario) — borrar Family y los usuarios no-admin
 * alcanza para que la base quede limpia, sin dejar nada huérfano.
 */
class ReiniciarDatosDemo extends Command
{
    protected $signature = 'aula:reiniciar-demo {--force : Corre sin pedir confirmación}';

    protected $description = 'Borra familias, niños y personal de prueba, dejando solo la cuenta de administrador y la estructura real (ambientes, áreas).';

    public function handle(): int
    {
        $familias = Family::count();
        $personalNoAdmin = User::where('role', '!=', User::ROLE_ADMINISTRADOR)->count();

        $this->info("Esto va a borrar {$familias} familia(s) (con sus niños, evidencias y asistencias) y {$personalNoAdmin} cuenta(s) de personal que no sean administrador.");

        if (! $this->option('force') && ! $this->confirm('¿Confirmás?')) {
            $this->warn('Cancelado, no se borró nada.');

            return self::FAILURE;
        }

        DB::transaction(function () {
            Family::query()->delete();
            User::where('role', '!=', User::ROLE_ADMINISTRADOR)->delete();
        });

        $this->info('Listo. Quedaron solo la cuenta de administrador, los ambientes y las áreas.');

        return self::SUCCESS;
    }
}
