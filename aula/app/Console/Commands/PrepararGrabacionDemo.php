<?php

namespace App\Console\Commands;

use App\Models\Child;
use App\Models\Content;
use App\Models\Evidence;
use App\Models\Family;
use App\Models\Feedback;
use App\Models\Media;
use App\Models\Observation;
use App\Models\User;
use Database\Seeders\Demo\GrabacionTareasDemoSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Deja la base LOCAL lista para grabar el flujo de tareas (docente publica,
 * familia entrega, docente responde) y, con --limpiar, la devuelve al punto
 * de partida para repetir la toma.
 *
 * A diferencia de aula:reiniciar-demo, este comando solo toca datos de su
 * propio escenario: nunca borra familias, cuentas ni contenido reales.
 */
class PrepararGrabacionDemo extends Command
{
    protected $signature = 'aula:preparar-grabacion
        {--limpiar : Deja el escenario como al inicio: borra las entregas y las tareas creadas durante la grabación}';

    protected $description = 'Prepara el escenario local para grabar el flujo de tareas: docentes, familias, niños y tareas de demostración.';

    private const HOST = '127.0.0.1:8000';

    // `artisan serve` lanza el servidor sin los -d del comando padre, así que
    // los límites de subida solo llegan si se levanta php -S directamente.
    private const SERVIDOR = 'cd public && php -d upload_max_filesize=64M -d post_max_size=70M -S '.self::HOST.' ../vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php';

    public function handle(): int
    {
        if (app()->isProduction()) {
            $this->error('Este comando prepara datos de demostración y no corre en producción: no se escribió ni se borró nada.');

            return self::FAILURE;
        }

        $semilla = app(GrabacionTareasDemoSeeder::class)->setContainer(app())->setCommand($this);

        // Antes de borrar nada: si la demo no puede sembrarse, no se limpia a medias.
        if (($conflicto = $semilla->conflicto()) !== null) {
            $this->error($conflicto);

            return self::FAILURE;
        }

        if ($this->option('limpiar')) {
            $this->limpiar();
        }

        $semilla->run();

        $this->resumen();

        return self::SUCCESS;
    }

    /**
     * Borra solo lo que dejó una toma: las entregas de los niños de la demo y
     * las tareas que sus docentes crearon además de las seis sembradas. La
     * base va en una transacción; los archivos se borran cuando ya se confirmó.
     */
    private function limpiar(): void
    {
        $borrado = DB::transaction(fn (): array => $this->borrarDatosDeLaToma());

        $disco = Storage::disk('local');
        $disco->delete($borrado['archivos']);

        foreach ($borrado['carpetas'] as $carpeta) {
            $disco->deleteDirectory($carpeta);
        }

        Storage::disk('public')->delete($borrado['portadas']);

        $this->info(sprintf(
            'Limpieza lista: %d entrega(s), %d respuesta(s), %d tarea(s) de la toma y %d archivo(s) borrados.',
            $borrado['evidencias'],
            $borrado['observaciones'],
            $borrado['tareas'],
            count($borrado['archivos']) + count($borrado['portadas']),
        ));
    }

    /**
     * @return array{evidencias: int, observaciones: int, tareas: int, archivos: list<string>, carpetas: list<string>, portadas: list<string>}
     */
    private function borrarDatosDeLaToma(): array
    {
        $usuarios = User::query()->whereIn('email', GrabacionTareasDemoSeeder::correos())->get(['id', 'role', 'family_id']);
        $docenteIds = $usuarios->where('role', User::ROLE_GUIA)->pluck('id')->all();

        // Una familia es de la demo solo si tiene su cuenta y su nombre.
        $familiaIds = Family::query()
            ->whereIn('id', $usuarios->pluck('family_id')->filter()->all())
            ->whereIn('name', GrabacionTareasDemoSeeder::nombresFamilias())
            ->pluck('id')
            ->all();
        $ninoIds = Child::withTrashed()->whereIn('family_id', $familiaIds)->pluck('id')->all();

        $tareaIds = Content::withTrashed()
            ->whereIn('teacher_id', $docenteIds)
            ->whereNotIn('slug', GrabacionTareasDemoSeeder::slugsTareas())
            ->pluck('id')
            ->all();

        $evidenciaIds = Evidence::withTrashed()
            ->where(fn ($q) => $q->whereIn('child_id', $ninoIds)->orWhereIn('family_id', $familiaIds)->orWhereIn('content_id', $tareaIds))
            ->pluck('id')
            ->all();
        $observacionIds = Observation::withTrashed()
            ->where(fn ($q) => $q->whereIn('child_id', $ninoIds)->orWhereIn('content_id', $tareaIds))
            ->pluck('id')
            ->all();

        // Media es polimórfica y no tiene llave foránea: no cae sola con su dueño.
        $mediaEvidencias = Media::query()->where('mediable_type', (new Evidence)->getMorphClass())->whereIn('mediable_id', $evidenciaIds);
        $mediaTareas = Media::query()->where('mediable_type', (new Content)->getMorphClass())->whereIn('mediable_id', $tareaIds);

        $archivos = array_merge($mediaEvidencias->pluck('path')->all(), $mediaTareas->pluck('path')->all());
        $carpetas = array_map(fn (int $id): string => "evidence/{$id}", $evidenciaIds);
        $portadas = Content::withTrashed()->whereIn('id', $tareaIds)->whereNotNull('cover_image')->pluck('cover_image')->all();

        Feedback::withTrashed()->whereIn('observation_id', $observacionIds)->forceDelete();
        Observation::withTrashed()->whereIn('id', $observacionIds)->forceDelete();
        $mediaEvidencias->delete();
        $mediaTareas->delete();
        Evidence::withTrashed()->whereIn('id', $evidenciaIds)->forceDelete();
        Content::withTrashed()->whereIn('id', $tareaIds)->forceDelete();

        DB::table('notifications')
            ->where('notifiable_type', (new User)->getMorphClass())
            ->whereIn('notifiable_id', $usuarios->pluck('id')->all())
            ->delete();

        return [
            'evidencias' => count($evidenciaIds),
            'observaciones' => count($observacionIds),
            'tareas' => count($tareaIds),
            'archivos' => $archivos,
            'carpetas' => $carpetas,
            'portadas' => $portadas,
        ];
    }

    private function resumen(): void
    {
        $this->newLine();
        $this->info('Cuentas de la demo');

        $usuarios = User::query()
            ->whereIn('email', GrabacionTareasDemoSeeder::correos())
            ->with(['family', 'environments'])
            ->get()
            ->keyBy('email');

        $this->table(
            ['Correo', 'Nombre', 'Rol', 'Ambiente o familia', 'Contraseña'],
            collect(GrabacionTareasDemoSeeder::cuentas())->map(function (array $cuenta, string $correo) use ($usuarios): array {
                $usuario = $usuarios->get($correo);

                return [
                    $correo,
                    $cuenta['name'],
                    $cuenta['role'] === User::ROLE_GUIA ? 'Docente' : 'Familia',
                    $usuario?->family?->name ?? $usuario?->environments->pluck('name')->implode(', '),
                    GrabacionTareasDemoSeeder::PASSWORD,
                ];
            })->values()->all(),
        );

        $this->line('Entrada para todos: http://'.self::HOST.'/login');
        $this->line('Panel de docentes:  http://'.self::HOST.'/admin');

        $this->newLine();
        $this->info('Tareas publicadas (piden entrega)');

        $tareas = Content::query()
            ->whereIn('slug', GrabacionTareasDemoSeeder::slugsTareas())
            ->with('environment')
            ->get()
            ->keyBy('slug');

        $this->table(
            ['Ambiente', 'Tarea', 'Fecha límite', 'Estado'],
            collect(GrabacionTareasDemoSeeder::tareas())->map(function (array $datos) use ($tareas): array {
                $tarea = $tareas->get($datos['slug']);
                $dias = (int) today()->diffInDays($tarea->due_date, false);

                return [
                    $datos['environment'],
                    $datos['title'],
                    $tarea->due_date->format('d/m/Y'),
                    match (true) {
                        $dias < 0 => 'vencida',
                        $dias === 0 => 'vence hoy',
                        $dias === 1 => 'vence mañana',
                        default => 'abierta',
                    },
                ];
            })->all(),
        );

        $this->info('Para que entren fotos del teléfono, levanta el servidor así:');
        $this->line('  '.self::SERVIDOR);
        $this->line('Para repetir una toma: php artisan aula:preparar-grabacion --limpiar');
    }
}
