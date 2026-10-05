<?php

namespace Database\Seeders\Demo;

use App\Enums\ResourceStatus;
use App\Enums\ResourceType;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\LibraryResource;
use App\Models\ResourceCategory;
use App\Models\ResourceFile;
use App\Models\User;
use App\Services\Biblioteca\ResourceFileService;
use Closure;
use Database\Seeders\BibliotecaSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Datos de demostración de la Biblioteca, SOLO para local y pruebas: cuentas
 * con contraseña conocida, cursos, recursos y archivos reales mínimos. No lo
 * llama DatabaseSeeder y se niega a correr en producción.
 *
 * Es idempotente: todo se busca por correo, nombre o título antes de crearse,
 * y no modifica ni borra ninguna cuenta o dato que ya exista.
 */
class BibliotecaDemoSeeder extends Seeder
{
    private const PASSWORD = 'password';

    private const ADMIN = 'admin.biblioteca@pestalozzi.test';

    private const PROFESOR = 'profesor@pestalozzi.test';

    private const PROFESORA = 'profesora2@pestalozzi.test';

    private const ESTUDIANTE = 'estudiante@pestalozzi.test';

    private const ESTUDIANTE_2 = 'estudiante2@pestalozzi.test';

    private const ESTUDIANTE_3 = 'estudiante3@pestalozzi.test';

    /** @var array<string, User> correo => usuario */
    private array $usuarios = [];

    /** @var array<string, Course> nombre => curso */
    private array $cursos = [];

    /** @var array<string, array<int, CourseModule>> curso => [posición (desde 1) => módulo] */
    private array $modulos = [];

    /** @var list<int> */
    private array $recursoIds = [];

    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->error('Los datos de demostración de la Biblioteca no se cargan en producción: no se escribió nada.');

            return;
        }

        $this->callSilent(BibliotecaSeeder::class);

        $this->crearCategorias();
        $this->crearUsuarios();
        $this->crearCursos();
        $this->matricular();
        $this->crearRecursos();
        $this->resumen();
    }

    /**
     * Las cinco categorías de ejemplo se suman a las cuatro estructurales.
     */
    private function crearCategorias(): void
    {
        foreach (['Cocina', 'Bartender', 'Panadería', 'Videos', 'Manuales'] as $i => $nombre) {
            ResourceCategory::firstOrCreate(
                ['slug' => Str::slug($nombre)],
                ['name' => $nombre, 'sort_order' => 5 + $i, 'show_in_tabs' => false, 'is_active' => true],
            );
        }
    }

    private function crearUsuarios(): void
    {
        $cuentas = [
            [self::ADMIN, 'Administrador Biblioteca', User::ROLE_ADMINISTRADOR],
            [self::PROFESOR, 'Profesor Demo', User::ROLE_GUIA],
            [self::PROFESORA, 'Profesora Dos', User::ROLE_GUIA],
            [self::ESTUDIANTE, 'Estudiante Demo', User::ROLE_ESTUDIANTE],
            [self::ESTUDIANTE_2, 'Estudiante Dos', User::ROLE_ESTUDIANTE],
            [self::ESTUDIANTE_3, 'Estudiante Tres', User::ROLE_ESTUDIANTE],
        ];

        foreach ($cuentas as [$correo, $nombre, $rol]) {
            $this->usuarios[$correo] = User::query()->where('email', $correo)->first()
                ?? $this->nuevoUsuario($rol, $nombre, $correo);
        }
    }

    private function nuevoUsuario(string $rol, string $nombre, string $correo): User
    {
        $fabrica = match ($rol) {
            User::ROLE_ADMINISTRADOR => User::factory()->administrador(),
            User::ROLE_GUIA => User::factory()->guia(),
            default => User::factory()->estudiante(),
        };

        return $fabrica->create([
            'name' => $nombre,
            'email' => $correo,
            'password' => Hash::make(self::PASSWORD),
            'active' => true,
        ]);
    }

    private function crearCursos(): void
    {
        $cursos = [
            [
                'Gastronomía Profesional', 'Formación integral en cocina profesional: técnicas básicas, cocina caliente y propuestas vegetarianas.',
                self::PROFESOR, true, [
                    ['Módulo 1: Cocina básica', 'Organización de la cocina, cortes, fondos y salsas madre.'],
                    ['Módulo 2: Cocina caliente', 'Cocciones y técnicas de cocina caliente.'],
                    ['Módulo 3: Cocina vegetariana', 'Platos vegetarianos y sustitución de ingredientes.'],
                ],
            ],
            [
                'Bartender Profesional', 'Fundamentos de barra, medidas y coctelería clásica.',
                self::PROFESOR, true, [
                    ['Módulo 1: Fundamentos de coctelería', 'Herramientas, medidas y técnicas de preparación.'],
                    ['Módulo 2: Coctelería clásica', 'Los cócteles clásicos y su presentación.'],
                ],
            ],
            [
                'Panadería', 'Elaboración de masas y panes artesanales.',
                self::PROFESORA, true, [
                    ['Módulo 1: Masas básicas', 'Amasado, fermentación y formado.'],
                    ['Módulo 2: Panes artesanales', 'Panes de masa madre y de corteza crujiente.'],
                ],
            ],
            [
                'Pastelería', 'Masas y cremas de pastelería. Curso inactivo en este período.',
                self::PROFESOR, false, [
                    ['Módulo 1: Masas y cremas', 'Masas base y cremas de pastelería.'],
                ],
            ],
        ];

        foreach ($cursos as [$nombre, $descripcion, $docente, $activo, $modulos]) {
            $curso = Course::firstOrCreate(
                ['name' => $nombre],
                ['description' => $descripcion, 'teacher_id' => $this->usuarios[$docente]->id, 'is_active' => $activo],
            );

            foreach ($modulos as $i => [$nombreModulo, $descripcionModulo]) {
                $this->modulos[$nombre][$i + 1] = CourseModule::firstOrCreate(
                    ['course_id' => $curso->id, 'name' => $nombreModulo],
                    ['description' => $descripcionModulo, 'sort_order' => $i + 1],
                );
            }

            $this->cursos[$nombre] = $curso;
        }
    }

    private function matricular(): void
    {
        $matriculas = [
            self::ESTUDIANTE => ['Gastronomía Profesional', 'Bartender Profesional'],
            self::ESTUDIANTE_2 => ['Gastronomía Profesional'],
            self::ESTUDIANTE_3 => ['Panadería', 'Pastelería'],
        ];

        foreach ($matriculas as $correo => $nombres) {
            $estudiante = $this->usuarios[$correo];

            foreach ($nombres as $nombre) {
                $curso = $this->cursos[$nombre];

                // Un attach condicional: sync pisaría la fecha de matrícula en cada corrida.
                if (! $curso->students()->whereKey($estudiante->id)->exists()) {
                    $curso->students()->attach($estudiante->id, ['enrolled_at' => now()->subDays(14)]);
                }
            }
        }
    }

    private function crearRecursos(): void
    {
        foreach ($this->recursos() as $datos) {
            $recurso = LibraryResource::query()->where('title', $datos['titulo'])->first()
                ?? $this->nuevoRecurso($datos);

            $this->vincularACurso($recurso, $datos);
            $this->guardarArchivos($recurso, $datos);

            if ($datos['concesion'] !== null) {
                $this->conceder($recurso, $datos['concesion']);
            }

            $this->recursoIds[] = $recurso->id;
        }
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    private function nuevoRecurso(array $datos): LibraryResource
    {
        $creador = $this->usuarios[$datos['creador']];
        $publicado = $datos['estado'] === ResourceStatus::Published;

        return LibraryResource::create([
            'title' => $datos['titulo'],
            'description' => $datos['descripcion'],
            'type' => $datos['tipo'],
            'category_id' => ResourceCategory::query()->where('slug', Str::slug($datos['categoria']))->value('id'),
            'external_url' => $datos['url'],
            'status' => $datos['estado'],
            'is_downloadable' => $datos['descargable'],
            'created_by' => $creador->id,
            'updated_by' => $creador->id,
            'published_by' => $publicado ? $creador->id : null,
            'published_at' => $publicado ? now()->subDays($datos['dias']) : null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    private function vincularACurso(LibraryResource $recurso, array $datos): void
    {
        if ($datos['curso'] === null) {
            return;
        }

        $curso = $this->cursos[$datos['curso']];

        if ($recurso->courses()->whereKey($curso->id)->exists()) {
            return;
        }

        $recurso->courses()->attach($curso->id, [
            'module_id' => $this->modulos[$datos['curso']][$datos['modulo']]->id,
            'sort_order' => $datos['orden'],
        ]);
    }

    /**
     * Guarda el archivo y la miniatura por ResourceFileService, así las filas
     * y las rutas salen como las de una subida real. Si el recurso ya tiene
     * su archivo (o su miniatura) no se vuelve a crear.
     *
     * @param  array<string, mixed>  $datos
     */
    private function guardarArchivos(LibraryResource $recurso, array $datos): void
    {
        $servicio = app(ResourceFileService::class);
        $base = Str::slug($recurso->title);

        // El video de la demo vive en una URL externa: no lleva archivo.
        if ($recurso->type->usesFile() && $recurso->external_url === null && ! $recurso->files()->exists()) {
            [$contenido, $nombre, $mime] = match ($recurso->type) {
                ResourceType::Image => [DemoFileFactory::png($recurso->title, 1280, 800), "{$base}.png", 'image/png'],
                ResourceType::Audio => [DemoFileFactory::wav(2), "{$base}.wav", 'audio/x-wav'],
                default => [DemoFileFactory::pdf($recurso->title), "{$base}.pdf", 'application/pdf'],
            };

            $this->subir($contenido, $nombre, $mime, fn (UploadedFile $archivo) => $servicio->store($recurso, $archivo));
        }

        if ($datos['miniatura'] && ! $recurso->thumbnail) {
            $this->subir(
                DemoFileFactory::png($recurso->title),
                "{$base}.png",
                'image/png',
                fn (UploadedFile $archivo) => $servicio->storeThumbnail($recurso, $archivo),
            );
        }
    }

    /**
     * Pasa los bytes por un archivo temporal real y los entrega envueltos
     * como una subida del navegador (el último argumento la marca "de prueba"
     * para que PHP no exija que venga de un POST).
     *
     * @param  Closure(UploadedFile): mixed  $guardar
     */
    private function subir(string $contenido, string $nombre, string $mime, Closure $guardar): void
    {
        $ruta = (string) tempnam(sys_get_temp_dir(), 'bibdemo');
        file_put_contents($ruta, $contenido);

        try {
            $guardar(new UploadedFile($ruta, $nombre, $mime, null, true));
        } finally {
            @unlink($ruta);
        }
    }

    /**
     * Concesión explícita de ver y descargar para todo un rol.
     */
    private function conceder(LibraryResource $recurso, string $rol): void
    {
        if ($recurso->permissions()->where('role', $rol)->whereNull('user_id')->exists()) {
            return;
        }

        $recurso->permissions()->create(['role' => $rol, 'user_id' => null, 'can_view' => true, 'can_download' => true]);
    }

    private function resumen(): void
    {
        $this->command?->newLine();
        $this->command?->info('Demo de la Biblioteca lista. Contraseña de todas las cuentas: '.self::PASSWORD);
        $this->command?->info('  Administrador: '.self::ADMIN);
        $this->command?->info('  Docentes: '.self::PROFESOR.' (Profesor Demo), '.self::PROFESORA.' (Profesora Dos)');
        $this->command?->info('  Estudiantes: '.self::ESTUDIANTE.', '.self::ESTUDIANTE_2.', '.self::ESTUDIANTE_3);
        $this->command?->info(sprintf(
            '  Cursos: %d, módulos: %d, recursos: %d, archivos: %d.',
            count($this->cursos),
            array_sum(array_map('count', $this->modulos)),
            count($this->recursoIds),
            ResourceFile::query()->whereIn('resource_id', $this->recursoIds)->count(),
        ));
    }

    /**
     * Los recursos de ejemplo. `modulo` es la posición (desde 1) dentro del
     * curso; `dias` es hace cuántos días se publicó.
     *
     * @return list<array<string, mixed>>
     */
    private function recursos(): array
    {
        $porDefecto = [
            'tipo' => ResourceType::Pdf,
            'estado' => ResourceStatus::Published,
            'descargable' => false,
            'curso' => null,
            'modulo' => 1,
            'orden' => 1,
            'miniatura' => false,
            'url' => null,
            'concesion' => null,
            'dias' => 10,
        ];

        $recursos = [
            [
                'titulo' => 'Manual de Cocina Básica',
                'descripcion' => 'Guía de iniciación a la cocina profesional: organización de la cocina, utensilios, fondos, salsas madre y cocciones fundamentales.',
                'categoria' => 'Libros',
                'creador' => self::PROFESOR,
                'descargable' => true,
                'curso' => 'Gastronomía Profesional',
                'modulo' => 1,
                'orden' => 1,
                'miniatura' => true,
                'dias' => 30,
            ],
            [
                'titulo' => 'Manual de Manipulación de Alimentos',
                'descripcion' => 'Normas de higiene, control de temperaturas y prevención de la contaminación cruzada. Solo se puede consultar en línea.',
                'categoria' => 'Manuales',
                'creador' => self::ADMIN,
                'dias' => 45,
            ],
            [
                'titulo' => 'Técnicas de Cortes',
                'descripcion' => 'Video demostrativo de los cortes clásicos: brunoise, juliana, chiffonade y macedonia.',
                'tipo' => ResourceType::Video,
                'url' => 'https://www.youtube.com/watch?v=aqz-KE-bpKQ',
                'categoria' => 'Videos',
                'creador' => self::PROFESOR,
                'curso' => 'Gastronomía Profesional',
                'modulo' => 2,
                'miniatura' => true,
                'dias' => 21,
            ],
            [
                'titulo' => 'Introducción a Coctelería',
                'descripcion' => 'Fundamentos de la barra: herramientas, medidas, técnicas de preparación y los cócteles clásicos que todo bartender debe dominar.',
                'categoria' => 'Bartender',
                'creador' => self::PROFESOR,
                'descargable' => true,
                'curso' => 'Bartender Profesional',
                'miniatura' => true,
                'dias' => 18,
            ],
            [
                'titulo' => 'Recetario de Cocina Vegetariana',
                'descripcion' => 'Recetas vegetarianas con sus fichas técnicas, porciones y sustituciones de ingredientes.',
                'categoria' => 'Cocina',
                'creador' => self::PROFESOR,
                'descargable' => true,
                'curso' => 'Gastronomía Profesional',
                'modulo' => 3,
                'miniatura' => true,
                'dias' => 12,
            ],
            [
                'titulo' => 'Guía de Panificación',
                'descripcion' => 'Pasos y tiempos de la elaboración del pan: amasado, fermentación, formado y horneado, con la tabla de porcentajes del panadero.',
                'categoria' => 'Panadería',
                'creador' => self::PROFESORA,
                'curso' => 'Panadería',
                'miniatura' => true,
                'dias' => 9,
            ],
            [
                'titulo' => 'Receta de Pastelería (borrador)',
                'descripcion' => 'Borrador de la receta base de masa quebrada y crema pastelera. Aún está en revisión.',
                'estado' => ResourceStatus::Draft,
                'categoria' => 'Panadería',
                'creador' => self::PROFESOR,
                'curso' => 'Pastelería',
            ],
            [
                'titulo' => 'Lista de Precios 2025',
                'descripcion' => 'Lista de precios de insumos del año 2025. Está archivada porque ya existe una versión más reciente.',
                'estado' => ResourceStatus::Archived,
                'categoria' => 'Material complementario',
                'creador' => self::ADMIN,
            ],
            [
                'titulo' => 'Banco de imágenes de emplatado',
                'descripcion' => 'Imágenes de referencia de emplatado para inspirar la presentación de los platos.',
                'tipo' => ResourceType::Image,
                'categoria' => 'Material complementario',
                'creador' => self::PROFESOR,
                'descargable' => true,
                'dias' => 6,
            ],
            [
                'titulo' => 'Audio: Pronunciación de términos culinarios',
                'descripcion' => 'Audio para practicar la pronunciación de los términos en francés más usados en la cocina profesional.',
                'tipo' => ResourceType::Audio,
                'categoria' => 'Material de clase',
                'creador' => self::PROFESOR,
                'curso' => 'Gastronomía Profesional',
                'modulo' => 1,
                'orden' => 2,
                'dias' => 5,
            ],
            [
                'titulo' => 'Sitio del Ministerio de Educación',
                'descripcion' => 'Portal oficial del Ministerio de Educación del Ecuador con currículos, guías y normativa vigente.',
                'tipo' => ResourceType::Link,
                'url' => 'https://www.educacion.gob.ec/',
                'categoria' => 'Material académico',
                'creador' => self::ADMIN,
                'dias' => 60,
            ],
            [
                'titulo' => 'Tabla de Equivalencias',
                'descripcion' => 'Equivalencias de pesos, volúmenes y temperaturas que se usan en las recetas. Se comparte con los estudiantes para que puedan descargarla.',
                'categoria' => 'Material de clase',
                'creador' => self::ADMIN,
                'concesion' => User::ROLE_ESTUDIANTE,
                'dias' => 3,
            ],
            [
                'titulo' => 'Fundamentos de Masas y Cremas',
                'descripcion' => 'Introducción a las masas base y cremas de pastelería: quebrada, hojaldre, crema pastelera y chantilly.',
                'categoria' => 'Panadería',
                'creador' => self::PROFESOR,
                'descargable' => true,
                'curso' => 'Pastelería',
                'orden' => 2,
                'dias' => 15,
            ],
        ];

        return array_map(fn (array $recurso) => [...$porDefecto, ...$recurso], $recursos);
    }
}
