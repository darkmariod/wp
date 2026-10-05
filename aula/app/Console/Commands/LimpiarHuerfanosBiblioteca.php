<?php

namespace App\Console\Commands;

use App\Models\LibraryResource;
use App\Models\ResourceFile;
use App\Services\Biblioteca\ResourceFileService;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Busca en el disco de la Biblioteca los archivos que ningún registro
 * referencia (quedan cuando una subida se corta o un borrado falla a medias).
 *
 * Por defecto solo informa. Con --force elimina únicamente los huérfanos más
 * antiguos que --horas, para no tocar una subida que está en curso. Los
 * registros cuyo archivo físico falta SOLO se reportan: arreglarlos es una
 * decisión de una persona.
 */
class LimpiarHuerfanosBiblioteca extends Command
{
    protected $signature = 'biblioteca:limpiar-huerfanos {--force : Elimina de verdad} {--horas=24 : Antigüedad mínima}';

    protected $description = 'Lista (y con --force elimina) los archivos de la Biblioteca que ningún registro referencia; reporta los registros sin archivo físico.';

    public function handle(ResourceFileService $archivos): int
    {
        $horas = filter_var($this->option('horas'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

        if ($horas === false) {
            $this->error('La opción --horas debe ser un número entero mayor o igual a 0.');

            return self::FAILURE;
        }

        $nombreDisco = $archivos->diskName();
        $disco = Storage::disk($nombreDisco);

        $this->info("Disco: {$nombreDisco}");

        $this->limpiarHuerfanos($disco, $nombreDisco, $horas, (bool) $this->option('force'));
        $this->reportarRegistrosSinArchivo($disco, $nombreDisco);

        return self::SUCCESS;
    }

    private function limpiarHuerfanos(Filesystem $disco, string $nombreDisco, int $horas, bool $eliminar): void
    {
        $referenciados = array_flip([
            ...ResourceFile::query()->where('disk', $nombreDisco)->pluck('path')->all(),
            ...LibraryResource::query()->whereNotNull('thumbnail')->pluck('thumbnail')->all(),
        ]);

        // Solo estas dos carpetas son de la Biblioteca: el resto del disco no se toca.
        $huerfanos = array_values(array_filter(
            [...$disco->allFiles('resources'), ...$disco->allFiles('thumbnails')],
            fn (string $ruta) => ! isset($referenciados[$ruta]),
        ));

        if ($huerfanos === []) {
            $this->line('No hay archivos huérfanos.');

            return;
        }

        $this->warn('Archivos huérfanos: '.count($huerfanos));

        $ahora = now()->getTimestamp();
        $limite = now()->subHours($horas)->getTimestamp();
        $eliminados = 0;

        foreach ($huerfanos as $ruta) {
            $modificado = $this->modificadoEn($disco, $ruta);

            if ($modificado === null) {
                $this->line("  {$ruta}: no se pudo leer su fecha, se conserva");

                continue;
            }

            $edad = max(0, intdiv($ahora - $modificado, 3600));

            if ($modificado > $limite) {
                $this->line("  {$ruta} ({$edad} h): reciente, se conserva");
            } elseif (! $eliminar) {
                $this->line("  {$ruta} ({$edad} h): se eliminaría con --force");
            } elseif ($disco->delete($ruta)) {
                $eliminados++;
                $this->line("  {$ruta} ({$edad} h): eliminado");
            } else {
                $this->line("  {$ruta} ({$edad} h): no se pudo eliminar");
            }
        }

        if ($eliminar) {
            $this->info("Eliminados: {$eliminados} de ".count($huerfanos).' huérfanos.');
        } else {
            $this->comment("Simulación: no se eliminó nada. Ejecuta con --force para borrar los huérfanos de más de {$horas} h.");
        }
    }

    private function reportarRegistrosSinArchivo(Filesystem $disco, string $nombreDisco): void
    {
        $faltantes = [];

        ResourceFile::query()->where('disk', $nombreDisco)->chunkById(200, function ($filas) use ($disco, &$faltantes) {
            foreach ($filas as $fila) {
                if (! $disco->exists($fila->path)) {
                    $faltantes[] = "resource_files #{$fila->id}: {$fila->path}";
                }
            }
        });

        LibraryResource::query()->whereNotNull('thumbnail')->chunkById(200, function ($recursos) use ($disco, &$faltantes) {
            foreach ($recursos as $recurso) {
                if (! $disco->exists($recurso->thumbnail)) {
                    $faltantes[] = "resources #{$recurso->id} (miniatura): {$recurso->thumbnail}";
                }
            }
        });

        if ($faltantes === []) {
            $this->line('No hay registros sin archivo físico.');

            return;
        }

        $this->warn('Registros sin archivo físico (no se tocan): '.count($faltantes));

        foreach ($faltantes as $faltante) {
            $this->line("  {$faltante}");
        }
    }

    private function modificadoEn(Filesystem $disco, string $ruta): ?int
    {
        try {
            return $disco->lastModified($ruta);
        } catch (Throwable) {
            return null;
        }
    }
}
