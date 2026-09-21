<?php

namespace App\Console\Commands;

use App\Models\Child;
use App\Models\Environment;
use App\Models\Family;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Carga en bloque familias reales al VPS: crea la familia, su usuario
 * de acceso (con contraseña generada) y sus niños, a partir de un
 * archivo de texto — para no tener que darlos de alta uno por uno
 * desde el panel cuando el colegio manda la lista completa.
 *
 * Formato del archivo, un bloque por familia separado por una línea
 * en blanco:
 *
 *   FAMILIA: Pérez García
 *   TELEFONO: 0991234567
 *   CORREO: juan.perez@gmail.com
 *   NINO: Juan Pérez | 2020-05-10 | Inicial 1
 *   NINO: Ana Pérez | 2022-03-15 | Inicial 1
 *
 *   FAMILIA: Torres López
 *   CORREO: maria.torres@gmail.com
 *   NINO: Pedro Torres | 2019-08-20 | Primero de Básica
 *
 * El ambiente de cada NINO (tercer campo, opcional) tiene que ser el
 * nombre exacto de un ambiente ya creado en el panel — si no existe,
 * ese niño se saltea con un aviso, no se inventa el ambiente.
 */
class ImportarFamilias extends Command
{
    protected $signature = 'familias:importar {archivo}';

    protected $description = 'Crea en bloque familias, su usuario de acceso y sus niños desde un archivo de texto';

    public function handle(): int
    {
        $ruta = $this->argument('archivo');

        if (! file_exists($ruta)) {
            $this->error("No se encontró el archivo: {$ruta}");

            return self::FAILURE;
        }

        $bloques = preg_split('/\n\s*\n/', trim(file_get_contents($ruta)));
        $credenciales = [];
        $errores = [];

        foreach ($bloques as $indice => $bloque) {
            $datos = $this->leerBloque($bloque);

            if (blank($datos['FAMILIA'] ?? null) || blank($datos['CORREO'] ?? null)) {
                $errores[] = 'Bloque '.($indice + 1).': falta FAMILIA o CORREO, se saltea.';

                continue;
            }

            if (User::where('email', $datos['CORREO'])->exists()) {
                $errores[] = "\"{$datos['FAMILIA']}\": ya existe un usuario con el correo {$datos['CORREO']}, se saltea.";

                continue;
            }

            $ninosValidos = [];
            foreach ($datos['NINO'] as $partes) {
                [$nombre, $nacimiento, $ambienteNombre] = array_pad($partes, 3, null);
                if (blank($nombre)) {
                    continue;
                }

                $ambiente = null;
                if (filled($ambienteNombre)) {
                    $ambiente = Environment::whereRaw('LOWER(name) = ?', [mb_strtolower($ambienteNombre)])->first();
                    if (! $ambiente) {
                        $errores[] = "\"{$datos['FAMILIA']}\" · {$nombre}: no existe el ambiente \"{$ambienteNombre}\", se saltea este niño.";

                        continue;
                    }
                }

                $ninosValidos[] = ['nombre' => $nombre, 'nacimiento' => $nacimiento ?: null, 'ambiente' => $ambiente];
            }

            if (! $ninosValidos) {
                $errores[] = "\"{$datos['FAMILIA']}\": sin ningún niño válido, se saltea la familia completa.";

                continue;
            }

            $family = Family::create([
                'name' => $datos['FAMILIA'],
                'phone' => $datos['TELEFONO'] ?? null,
            ]);

            foreach ($ninosValidos as $nino) {
                Child::create([
                    'family_id' => $family->id,
                    'environment_id' => $nino['ambiente']?->id,
                    'name' => $nino['nombre'],
                    'birth_date' => $nino['nacimiento'],
                    'status' => 'active',
                ]);
            }

            $password = Str::password(12);
            User::create([
                'name' => $datos['FAMILIA'],
                'email' => $datos['CORREO'],
                'password' => $password,
                'role' => User::ROLE_FAMILIA,
                'family_id' => $family->id,
                'active' => true,
            ]);

            $credenciales[] = [$datos['FAMILIA'], $datos['CORREO'], $password, count($ninosValidos)];
        }

        if ($credenciales) {
            $this->info(count($credenciales).' familia(s) creada(s):');
            $this->table(['Familia', 'Correo', 'Contraseña', 'Niños'], $credenciales);
        }

        if ($errores) {
            $this->newLine();
            $this->warn('Avisos:');
            foreach ($errores as $error) {
                $this->line('  - '.$error);
            }
        }

        if (! $credenciales) {
            $this->error('No se creó ninguna familia.');

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * @return array{FAMILIA?: string, TELEFONO?: string, CORREO?: string, NINO: array<int, array<int, string>>}
     */
    private function leerBloque(string $bloque): array
    {
        $datos = ['NINO' => []];

        foreach (explode("\n", $bloque) as $cruda) {
            $linea = trim($cruda);
            if ($linea === '' || str_starts_with($linea, '#') || ! str_contains($linea, ':')) {
                continue;
            }

            [$clave, $valor] = explode(':', $linea, 2);
            $clave = strtoupper(trim($clave));
            $valor = trim($valor);

            if ($clave === 'NINO') {
                if ($valor !== '') {
                    $datos['NINO'][] = array_map('trim', explode('|', $valor));
                }
            } elseif ($valor !== '') {
                $datos[$clave] = $valor;
            }
        }

        return $datos;
    }
}
