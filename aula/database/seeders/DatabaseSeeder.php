<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Child;
use App\Models\Content;
use App\Models\Environment;
use App\Models\Family;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Datos para probar el flujo completo a mano: dos ambientes reales,
     * áreas obligatorias y abiertas, 30 niños con sus familias y avatares
     * generados localmente, y contenido publicado en ambos ambientes.
     * No es data real de la institución.
     */
    public function run(): void
    {
        $admin = User::factory()->administrador()->create([
            'name' => 'Administrador',
            'email' => 'admin@pestalozzi.test',
        ]);

        $guia = User::factory()->guia()->create([
            'name' => 'Teffy Haro',
            'email' => 'guia@pestalozzi.test',
        ]);

        $absorbentes = Environment::factory()->create([
            'name' => 'Mentes Absorbentes',
            'age_range' => '3 a 6 años',
            'stage' => Environment::STAGE_ABSORBENTES,
            'teacher_id' => $guia->id,
            'order' => 1,
        ]);

        $razonadoras = Environment::factory()->razonadoras()->create([
            'name' => 'Mentes Razonadoras',
            'age_range' => '6 a 9 años',
            'stage' => Environment::STAGE_RAZONADORAS,
            'teacher_id' => $guia->id,
            'order' => 2,
        ]);

        $areas = $this->crearAreas();

        // María Pérez queda en Mentes Absorbentes y su familia usa el
        // usuario demo familia@pestalozzi.test para el flujo manual.
        $this->crearNino($absorbentes, 'María', 'Familia Pérez', 0, 'familia@pestalozzi.test');
        $this->crearNinos($absorbentes, 14, excludedNames: ['María']);
        $this->crearNinos($razonadoras, 15);

        $this->crearContenido($absorbentes, $razonadoras, $areas, $guia);
    }

    /**
     * Áreas obligatorias (Lectura, Matemática, Inglés) y abiertas
     * (Vida Práctica, Arte y Expresión, Cultura y Naturaleza).
     */
    private function crearAreas(): Collection
    {
        $obligatorias = [
            ['name' => 'Lectura', 'icon' => '📖'],
            ['name' => 'Matemática', 'icon' => '➗'],
            ['name' => 'Inglés', 'icon' => '🇬🇧'],
        ];

        $abiertas = [
            ['name' => 'Vida Práctica', 'icon' => '🧺'],
            ['name' => 'Arte y Expresión', 'icon' => '🎨'],
            ['name' => 'Cultura y Naturaleza', 'icon' => '🌱'],
        ];

        $areas = collect();

        foreach ($obligatorias as $i => $datos) {
            $areas->push(Area::factory()->create([...$datos, 'order' => $i, 'is_required' => true]));
        }

        foreach ($abiertas as $i => $datos) {
            $areas->push(Area::factory()->create([...$datos, 'order' => count($obligatorias) + $i, 'is_required' => false]));
        }

        return $areas;
    }

    /**
     * Crea $cantidad niños en el ambiente, cada uno con su familia,
     * avatar SVG local y usuario de familia.
     *
     * @return Collection<int, Child>
     */
    private function crearNinos(Environment $ambiente, int $cantidad, array $excludedNames = []): Collection
    {
        $ninos = collect();

        for ($i = 0; $i < $cantidad; $i++) {
            $nombre = fake()->firstName();

            if (in_array($nombre, $excludedNames, true)) {
                $i--;

                continue;
            }

            $apellido = fake()->lastName();
            $ninos->push($this->crearNino(
                $ambiente,
                $nombre,
                'Familia '.$apellido,
                $i,
                fake()->safeEmail(),
            ));
        }

        return $ninos;
    }

    private function crearNino(
        Environment $ambiente,
        string $nombre,
        string $nombreFamilia,
        int $indice,
        ?string $email = null,
        ?string $avatarPath = null
    ): Child {
        $familia = Family::factory()->create(['name' => $nombreFamilia]);

        $nino = Child::factory()->create([
            'family_id' => $familia->id,
            'environment_id' => $ambiente->id,
            'name' => $nombre,
            'photo_path' => $avatarPath ?? $this->generarAvatar($nombre),
        ]);

        User::factory()->familia($familia)->create([
            'name' => $nombreFamilia,
            'email' => $email ?? 'nino-'.$nino->id.'@pestalozzi.test',
        ]);

        return $nino;
    }

    /**
     * Avatar SVG generado localmente (texto plano, sin servicios externos
     * ni GD): un rectángulo de color con las iniciales en blanco.
     */
    private function generarAvatar(string $nombre): string
    {
        $iniciales = $this->iniciales($nombre);
        $colores = ['#126333', '#0e7490', '#d97706', '#e11d48', '#4f46e5', '#0d9488', '#c026d3', '#3f6212'];

        $color = $colores[abs(crc32($nombre)) % count($colores)];

        $svg = "<svg xmlns='http://www.w3.org/2000/svg' width='160' height='160' viewBox='0 0 160 160'>"
            ."<rect width='160' height='160' rx='80' fill='{$color}'/>"
            ."<text x='50%' y='54%' font-family='DM Sans, sans-serif' font-size='56' fill='#ffffff' text-anchor='middle' dominant-baseline='middle'>{$iniciales}</text>"
            .'</svg>';

        $path = 'avatars/child-'.Str::slug($nombre).'-'.uniqid().'.svg';

        Storage::disk('public')->put($path, $svg);

        return $path;
    }

    private function iniciales(string $nombre): string
    {
        $partes = preg_split('/\s+/', trim($nombre)) ?: [];

        if (count($partes) === 1) {
            return mb_strtoupper(mb_substr($partes[0], 0, 2));
        }

        return mb_strtoupper(mb_substr($partes[0], 0, 1).mb_substr(end($partes), 0, 1));
    }

    /**
     * Contenido publicado en ambos ambientes. En Mentes Absorbentes se
     * incluye una experiencia con requiere_evidence para el flujo manual
     * de subida. El ambiente razonadoras solo recibe lecturas y tareas.
     */
    private function crearContenido(
        Environment $absorbentes,
        Environment $razonadoras,
        Collection $areas,
        User $guia
    ): void {
        $lectura = $areas->firstWhere('name', 'Lectura');
        $matematica = $areas->firstWhere('name', 'Matemática');
        $vidaPractica = $areas->firstWhere('name', 'Vida Práctica');

        // Mentes Absorbentes — una experiencia con evidencia para María.
        Content::factory()->published()->requiresEvidence()->create([
            'title' => 'Clasificamos hojas',
            'slug' => 'clasificamos-hojas',
            'type' => Content::TYPE_EXPERIENCE,
            'description' => 'Hoy exploraremos las diferentes formas, colores y tamaños que encontramos en la naturaleza.',
            'teacher_id' => $guia->id,
            'environment_id' => $absorbentes->id,
            'area_id' => $vidaPractica->id,
        ]);

        Content::factory()->published()->create([
            'title' => 'El tesoro de las vocales',
            'slug' => 'el-tesoro-de-las-vocales',
            'type' => Content::TYPE_READING,
            'description' => 'Descubrimos las vocales a través de un cuento y canciones.',
            'teacher_id' => $guia->id,
            'environment_id' => $absorbentes->id,
            'area_id' => $lectura->id,
        ]);

        Content::factory()->published()->create([
            'title' => 'Contamos manzanas',
            'slug' => 'contamos-manzanas',
            'type' => Content::TYPE_TASK,
            'description' => 'Una actividad para practicar el conteo del 1 al 10.',
            'teacher_id' => $guia->id,
            'environment_id' => $absorbentes->id,
            'area_id' => $matematica->id,
        ]);

        // Mentes Razonadoras — solo lecturas y tareas.
        Content::factory()->published()->create([
            'title' => 'Lectura: La tortuga y la liebre',
            'slug' => 'la-tortuga-y-la-liebre',
            'type' => Content::TYPE_READING,
            'description' => 'Leemos juntos la fábula y conversamos sobre ella.',
            'teacher_id' => $guia->id,
            'environment_id' => $razonadoras->id,
            'area_id' => $lectura->id,
        ]);

        Content::factory()->published()->create([
            'title' => 'Tarea: Sumas hasta el 20',
            'slug' => 'sumas-hasta-el-20',
            'type' => Content::TYPE_TASK,
            'description' => 'Practicamos sumas sencillas hasta el veinte.',
            'teacher_id' => $guia->id,
            'environment_id' => $razonadoras->id,
            'area_id' => $matematica->id,
        ]);

        // Aviso general sin ambiente (visible para todos).
        Content::factory()->published()->create([
            'title' => 'Bienvenida al ciclo 2026-2027',
            'slug' => 'bienvenida-ciclo',
            'type' => Content::TYPE_ANNOUNCEMENT,
            'teacher_id' => $guia->id,
            'environment_id' => null,
            'requires_evidence' => false,
        ]);
    }
}
