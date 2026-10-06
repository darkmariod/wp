<?php

namespace Tests\Feature;

use App\Enums\ResourceStatus;
use App\Enums\ResourceType;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\LibraryResource;
use App\Models\ResourceCategory;
use App\Models\ResourceFile;
use App\Models\ResourcePermission;
use App\Models\User;
use Database\Seeders\BibliotecaSeeder;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\Demo\BibliotecaDemoSeeder;
use Database\Seeders\Demo\DemoFileFactory;
use finfo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Seeders de la Biblioteca: la base (categorías) es segura para producción y
 * se corre a mano; la demo es solo para local y pruebas. El seeder de
 * entrega (DatabaseSeeder) no debe tocar nada de esto.
 */
class BibliotecaSeedersTest extends TestCase
{
    use RefreshDatabase;

    private const MANUAL_COCINA = 'Manual de Cocina Básica';

    private const MANIPULACION = 'Manual de Manipulación de Alimentos';

    private const CORTES = 'Técnicas de Cortes';

    private const COCTELERIA = 'Introducción a Coctelería';

    private const VEGETARIANA = 'Recetario de Cocina Vegetariana';

    private const PANIFICACION = 'Guía de Panificación';

    private const PASTELERIA_BORRADOR = 'Receta de Pastelería (borrador)';

    private const PRECIOS = 'Lista de Precios 2025';

    private const IMAGENES = 'Banco de imágenes de emplatado';

    private const AUDIO = 'Audio: Pronunciación de términos culinarios';

    private const MINISTERIO = 'Sitio del Ministerio de Educación';

    private const EQUIVALENCIAS = 'Tabla de Equivalencias';

    private const MASAS = 'Fundamentos de Masas y Cremas';

    private const ADMIN = 'admin.biblioteca@pestalozzi.test';

    private const PROFESOR = 'profesor@pestalozzi.test';

    private const PROFESORA = 'profesora2@pestalozzi.test';

    private const ESTUDIANTE = 'estudiante@pestalozzi.test';

    private const ESTUDIANTE_2 = 'estudiante2@pestalozzi.test';

    private const ESTUDIANTE_3 = 'estudiante3@pestalozzi.test';

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('biblioteca');
        config(['biblioteca.disk' => 'biblioteca']);
    }

    private function sembrarDemo(): void
    {
        $this->seed(BibliotecaDemoSeeder::class);
    }

    private function recurso(string $titulo): LibraryResource
    {
        return LibraryResource::query()->where('title', $titulo)->firstOrFail();
    }

    private function usuario(string $correo): User
    {
        return User::query()->where('email', $correo)->firstOrFail();
    }

    /**
     * Títulos que el usuario ve. Comprueba de paso que el scope y la política
     * den el mismo resultado para cada recurso de la demo.
     *
     * @return list<string>
     */
    private function titulosVisibles(string $correo): array
    {
        $usuario = $this->usuario($correo);

        $porScope = LibraryResource::visibleTo($usuario)->pluck('title')->all();
        $porPolitica = LibraryResource::all()
            ->filter(fn (LibraryResource $recurso) => $usuario->can('view', $recurso))
            ->pluck('title')
            ->all();

        $this->assertEqualsCanonicalizing($porPolitica, $porScope, "El scope y la política difieren para {$correo}.");

        return $porScope;
    }

    /**
     * @return array<string, int>
     */
    private function conteos(): array
    {
        return [
            'usuarios' => User::count(),
            'roles' => DB::table('roles')->count(),
            'categorias' => ResourceCategory::count(),
            'cursos' => Course::count(),
            'modulos' => CourseModule::count(),
            'matriculas' => DB::table('course_enrollments')->count(),
            'recursos' => LibraryResource::count(),
            'archivos' => ResourceFile::count(),
            'vinculos' => DB::table('course_resource')->count(),
            'permisos' => ResourcePermission::count(),
            'objetos' => count(Storage::disk('biblioteca')->allFiles()),
        ];
    }

    /**
     * Prueba que el seeder base crea las cuatro categorías estructurales.
     */
    public function test_el_seeder_base_crea_las_cuatro_categorias(): void
    {
        $this->seed(BibliotecaSeeder::class);

        $categorias = ResourceCategory::query()->orderBy('sort_order')->get();

        $this->assertSame(
            ['Libros', 'Material de clase', 'Material académico', 'Material complementario'],
            $categorias->pluck('name')->all(),
        );
        $this->assertSame(
            ['libros', 'material-de-clase', 'material-academico', 'material-complementario'],
            $categorias->pluck('slug')->all(),
        );
        $this->assertSame([true, true, false, false], $categorias->pluck('show_in_tabs')->all());
        $this->assertSame([1, 2, 3, 4], $categorias->pluck('sort_order')->all());
        $this->assertTrue($categorias->every(fn (ResourceCategory $categoria) => $categoria->is_active));
    }

    /**
     * Prueba que correr el seeder base dos veces no duplica ni pisa cambios.
     */
    public function test_el_seeder_base_es_idempotente_y_respeta_los_cambios(): void
    {
        $this->seed(BibliotecaSeeder::class);
        ResourceCategory::query()->where('slug', 'libros')->update(['description' => 'Descripción editada', 'is_active' => false]);

        $this->seed(BibliotecaSeeder::class);

        $this->assertSame(4, ResourceCategory::count());
        $libros = ResourceCategory::query()->where('slug', 'libros')->firstOrFail();
        $this->assertSame('Descripción editada', $libros->description);
        $this->assertFalse($libros->is_active);
    }

    /**
     * Prueba que el seeder de entrega no crea nada de la Biblioteca ni usuarios demo.
     */
    public function test_el_seeder_de_entrega_no_crea_datos_de_la_biblioteca(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(0, ResourceCategory::count());
        $this->assertSame(0, Course::count());
        $this->assertSame(0, CourseModule::count());
        $this->assertSame(0, LibraryResource::count());
        $this->assertSame(0, ResourceFile::count());
        $this->assertSame(0, DB::table('course_enrollments')->count());
        $this->assertSame(1, User::count());
        $this->assertSame(0, User::query()->where('email', 'like', '%@pestalozzi.test')->count());
        $this->assertSame([], Storage::disk('biblioteca')->allFiles());
    }

    /**
     * Prueba que la demo crea los usuarios con el rol y la contraseña esperados.
     */
    public function test_la_demo_crea_los_usuarios_con_sus_roles(): void
    {
        $this->sembrarDemo();

        $esperados = [
            self::ADMIN => [User::ROLE_ADMINISTRADOR, 'Administrador Biblioteca'],
            self::PROFESOR => [User::ROLE_GUIA, 'Profesor Demo'],
            self::PROFESORA => [User::ROLE_GUIA, 'Profesora Dos'],
            self::ESTUDIANTE => [User::ROLE_ESTUDIANTE, 'Estudiante Demo'],
            self::ESTUDIANTE_2 => [User::ROLE_ESTUDIANTE, 'Estudiante Dos'],
            self::ESTUDIANTE_3 => [User::ROLE_ESTUDIANTE, 'Estudiante Tres'],
        ];

        $this->assertSame(count($esperados), User::count());

        foreach ($esperados as $correo => [$rol, $nombre]) {
            $usuario = $this->usuario($correo);

            $this->assertSame($rol, $usuario->role, $correo);
            $this->assertSame($nombre, $usuario->name, $correo);
            $this->assertTrue($usuario->active, $correo);
            $this->assertNotNull($usuario->email_verified_at, $correo);
            $this->assertTrue(password_verify('password', $usuario->password), $correo);
            $this->assertTrue($usuario->hasRole($rol), $correo);
            $this->assertTrue($usuario->canUseBiblioteca(), $correo);
        }

        // Es administrador a secas: no hereda el bypass de super_admin.
        $this->assertFalse($this->usuario(self::ADMIN)->hasRole('super_admin'));
    }

    /**
     * Prueba que la demo crea los cursos, sus módulos y las matrículas.
     */
    public function test_la_demo_crea_cursos_modulos_y_matriculas(): void
    {
        $this->sembrarDemo();

        $this->assertSame(4, Course::count());
        $this->assertSame(8, CourseModule::count());
        $this->assertSame(5, DB::table('course_enrollments')->count());

        $gastronomia = Course::query()->where('name', 'Gastronomía Profesional')->firstOrFail();
        $this->assertSame(
            ['Módulo 1: Cocina básica', 'Módulo 2: Cocina caliente', 'Módulo 3: Cocina vegetariana'],
            $gastronomia->modules->pluck('name')->all(),
        );
        $this->assertSame([1, 2, 3], $gastronomia->modules->pluck('sort_order')->all());
        $this->assertSame($this->usuario(self::PROFESOR)->id, $gastronomia->teacher_id);

        $this->assertCount(2, Course::query()->where('name', 'Bartender Profesional')->firstOrFail()->modules);

        $panaderia = Course::query()->where('name', 'Panadería')->firstOrFail();
        $this->assertCount(2, $panaderia->modules);
        $this->assertSame($this->usuario(self::PROFESORA)->id, $panaderia->teacher_id);

        $pasteleria = Course::query()->where('name', 'Pastelería')->firstOrFail();
        $this->assertFalse($pasteleria->is_active);
        $this->assertCount(1, $pasteleria->modules);
        $this->assertTrue(Course::query()->whereIn('name', ['Gastronomía Profesional', 'Bartender Profesional', 'Panadería'])->get()->every->is_active);

        $this->assertEqualsCanonicalizing(
            ['Gastronomía Profesional', 'Bartender Profesional'],
            $this->usuario(self::ESTUDIANTE)->enrolledCourses->pluck('name')->all(),
        );
        $this->assertSame(['Gastronomía Profesional'], $this->usuario(self::ESTUDIANTE_2)->enrolledCourses->pluck('name')->all());
        $this->assertEqualsCanonicalizing(
            ['Panadería', 'Pastelería'],
            $this->usuario(self::ESTUDIANTE_3)->enrolledCourses->pluck('name')->all(),
        );
    }

    /**
     * Prueba que la demo mezcla las categorías base con las de ejemplo.
     */
    public function test_la_demo_completa_las_categorias(): void
    {
        $this->sembrarDemo();

        $this->assertEqualsCanonicalizing(
            [
                'Libros', 'Material de clase', 'Material académico', 'Material complementario',
                'Cocina', 'Bartender', 'Panadería', 'Videos', 'Manuales',
            ],
            ResourceCategory::pluck('name')->all(),
        );
        $this->assertSame(['Libros', 'Material de clase'], ResourceCategory::query()->where('show_in_tabs', true)->orderBy('sort_order')->pluck('name')->all());
    }

    /**
     * Prueba que cada recurso queda con su tipo, estado, autoría y vínculo a curso.
     */
    public function test_la_demo_crea_los_recursos_con_su_estado_y_su_curso(): void
    {
        $this->sembrarDemo();

        $this->assertSame(13, LibraryResource::count());

        $esperados = [
            // título => [tipo, estado, descargable, curso, módulo, creador]
            self::MANUAL_COCINA => [ResourceType::Pdf, ResourceStatus::Published, true, 'Gastronomía Profesional', 'Módulo 1: Cocina básica', self::PROFESOR],
            self::MANIPULACION => [ResourceType::Pdf, ResourceStatus::Published, false, null, null, self::ADMIN],
            self::CORTES => [ResourceType::Video, ResourceStatus::Published, false, 'Gastronomía Profesional', 'Módulo 2: Cocina caliente', self::PROFESOR],
            self::COCTELERIA => [ResourceType::Pdf, ResourceStatus::Published, true, 'Bartender Profesional', null, self::PROFESOR],
            self::VEGETARIANA => [ResourceType::Pdf, ResourceStatus::Published, true, 'Gastronomía Profesional', 'Módulo 3: Cocina vegetariana', self::PROFESOR],
            self::PANIFICACION => [ResourceType::Pdf, ResourceStatus::Published, false, 'Panadería', null, self::PROFESORA],
            self::PASTELERIA_BORRADOR => [ResourceType::Pdf, ResourceStatus::Draft, false, 'Pastelería', null, self::PROFESOR],
            self::PRECIOS => [ResourceType::Pdf, ResourceStatus::Archived, false, null, null, self::ADMIN],
            self::IMAGENES => [ResourceType::Image, ResourceStatus::Published, true, null, null, self::PROFESOR],
            self::AUDIO => [ResourceType::Audio, ResourceStatus::Published, false, 'Gastronomía Profesional', 'Módulo 1: Cocina básica', self::PROFESOR],
            self::MINISTERIO => [ResourceType::Link, ResourceStatus::Published, false, null, null, self::ADMIN],
            self::EQUIVALENCIAS => [ResourceType::Pdf, ResourceStatus::Published, false, null, null, self::ADMIN],
            self::MASAS => [ResourceType::Pdf, ResourceStatus::Published, true, 'Pastelería', null, self::PROFESOR],
        ];

        foreach ($esperados as $titulo => [$tipo, $estado, $descargable, $curso, $modulo, $creador]) {
            $recurso = $this->recurso($titulo);

            $this->assertSame($tipo, $recurso->type, $titulo);
            $this->assertSame($estado, $recurso->status, $titulo);
            $this->assertSame($descargable, $recurso->is_downloadable, $titulo);
            $this->assertSame($this->usuario($creador)->id, $recurso->created_by, $titulo);
            $this->assertNotNull($recurso->category_id, $titulo);
            $this->assertNotEmpty($recurso->description, $titulo);

            if ($estado === ResourceStatus::Published) {
                $this->assertNotNull($recurso->published_at, $titulo);
                $this->assertNotNull($recurso->published_by, $titulo);
            } else {
                $this->assertNull($recurso->published_at, $titulo);
                $this->assertNull($recurso->published_by, $titulo);
            }

            if ($curso === null) {
                $this->assertCount(0, $recurso->courses, $titulo);

                continue;
            }

            $this->assertCount(1, $recurso->courses, $titulo);
            $vinculo = $recurso->courses->first();
            $this->assertSame($curso, $vinculo->name, $titulo);

            if ($modulo !== null) {
                $this->assertSame($modulo, CourseModule::find($vinculo->pivot->module_id)?->name, $titulo);
            } else {
                // Cuando no se pide un módulo, igual queda dentro de uno del mismo curso.
                $this->assertSame($vinculo->id, CourseModule::find($vinculo->pivot->module_id)?->course_id, $titulo);
            }
        }

        $this->assertSame('https://www.educacion.gob.ec/', $this->recurso(self::MINISTERIO)->external_url);
        $this->assertStringStartsWith('https://', (string) $this->recurso(self::CORTES)->external_url);
        $this->assertSame(0, $this->recurso(self::CORTES)->files()->count());
    }

    /**
     * Prueba que los videos y archivos se ordenan dentro del módulo con sort_order.
     */
    public function test_los_recursos_de_un_modulo_llevan_su_orden(): void
    {
        $this->sembrarDemo();

        $modulo = CourseModule::query()->where('name', 'Módulo 1: Cocina básica')->firstOrFail();
        $recursos = $modulo->resources()->orderByPivot('sort_order')->pluck('title')->all();

        $this->assertSame([self::MANUAL_COCINA, self::AUDIO], $recursos);
    }

    /**
     * Prueba que cada archivo existe de verdad en el disco y su contenido es del tipo declarado.
     */
    public function test_cada_archivo_demo_existe_en_el_disco_con_el_tipo_que_le_corresponde(): void
    {
        $this->sembrarDemo();

        $disco = Storage::disk('biblioteca');
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $archivos = ResourceFile::with('resource')->get();

        $this->assertCount(11, $archivos);

        foreach ($archivos as $archivo) {
            $this->assertTrue($disco->exists($archivo->path), $archivo->path);
            $this->assertSame('biblioteca', $archivo->disk);
            $this->assertGreaterThan(0, $archivo->size);

            $detectado = strtolower((string) $finfo->file($disco->path($archivo->path)));
            $aceptados = array_map('strtolower', config("biblioteca.extensions.{$archivo->extension}"));

            $this->assertContains($detectado, $aceptados, "{$archivo->resource->title}: {$detectado}");
            $this->assertSame($detectado, $archivo->mime_type);
            $this->assertContains($archivo->extension, config("biblioteca.types.{$archivo->resource->type->value}.extensions"));
            $this->assertStringStartsWith("resources/{$archivo->resource_id}/", $archivo->path);
        }

        // Todo recurso que usa archivo tiene uno, salvo el video por URL.
        foreach (LibraryResource::all() as $recurso) {
            $debeTener = $recurso->type->usesFile() && $recurso->title !== self::CORTES;

            $this->assertSame($debeTener, $recurso->files()->exists(), $recurso->title);
        }
    }

    /**
     * Prueba que las miniaturas existen en el disco y son imágenes reales.
     */
    public function test_las_miniaturas_demo_existen_en_el_disco(): void
    {
        $this->sembrarDemo();

        $disco = Storage::disk('biblioteca');
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $conMiniatura = LibraryResource::query()->whereNotNull('thumbnail')->get();

        $this->assertCount(5, $conMiniatura);

        foreach ($conMiniatura as $recurso) {
            $this->assertTrue($disco->exists($recurso->thumbnail), $recurso->title);
            $this->assertSame('image/png', $finfo->file($disco->path($recurso->thumbnail)), $recurso->title);
        }
    }

    /**
     * Prueba que correr la demo dos veces conserva exactamente los mismos datos y archivos.
     */
    public function test_la_demo_es_idempotente(): void
    {
        $this->sembrarDemo();

        $antes = $this->conteos();
        $rutas = ResourceFile::pluck('path')->sort()->values()->all();
        $miniaturas = LibraryResource::whereNotNull('thumbnail')->pluck('thumbnail', 'id')->sortKeys()->all();
        $objetos = Storage::disk('biblioteca')->allFiles();
        sort($objetos);

        $this->sembrarDemo();

        $this->assertSame($antes, $this->conteos());
        $this->assertSame($rutas, ResourceFile::pluck('path')->sort()->values()->all());
        $this->assertSame($miniaturas, LibraryResource::whereNotNull('thumbnail')->pluck('thumbnail', 'id')->sortKeys()->all());

        $despues = Storage::disk('biblioteca')->allFiles();
        sort($despues);
        $this->assertSame($objetos, $despues);
    }

    /**
     * Prueba que la demo respeta lo que ya existe en la base: no toca ni duplica usuarios.
     */
    public function test_la_demo_no_toca_a_los_usuarios_existentes(): void
    {
        $adminReal = User::factory()->administrador()->create(['name' => 'Admin Real', 'email' => 'real@colegio.test']);
        $familia = User::factory()->familia()->create(['name' => 'Familia Real', 'email' => 'familia@colegio.test']);
        $previo = User::factory()->estudiante()->create(['name' => 'Nombre Previo', 'email' => self::ESTUDIANTE]);

        $this->sembrarDemo();

        // 3 existentes + 5 nuevos: el estudiante que ya existía no se duplica.
        $this->assertSame(8, User::count());
        $this->assertSame('Nombre Previo', $previo->fresh()->name);
        $this->assertSame('Admin Real', $adminReal->fresh()->name);
        $this->assertSame(User::ROLE_ADMINISTRADOR, $adminReal->fresh()->role);
        $this->assertSame('Familia Real', $familia->fresh()->name);
        $this->assertSame(User::ROLE_FAMILIA, $familia->fresh()->role);
        $this->assertSame($familia->family_id, $familia->fresh()->family_id);
    }

    /**
     * Prueba que en producción la demo no escribe nada y avisa con un error.
     */
    public function test_la_demo_no_escribe_nada_en_produccion(): void
    {
        $this->app['env'] = 'production';

        $this->artisan('db:seed', ['--class' => BibliotecaDemoSeeder::class, '--force' => true])
            ->expectsOutputToContain('producción')
            ->assertSuccessful();

        $this->assertSame(0, User::count());
        $this->assertSame(0, ResourceCategory::count());
        $this->assertSame(0, Course::count());
        $this->assertSame(0, LibraryResource::count());
        $this->assertSame([], Storage::disk('biblioteca')->allFiles());
    }

    /**
     * Prueba que el estudiante ve lo suyo y lo general, nunca otro curso, borradores ni archivados.
     */
    public function test_el_estudiante_demo_ve_sus_cursos_y_lo_general(): void
    {
        $this->sembrarDemo();

        $this->assertEqualsCanonicalizing(
            [
                self::MANUAL_COCINA, self::MANIPULACION, self::CORTES, self::COCTELERIA, self::VEGETARIANA,
                self::IMAGENES, self::AUDIO, self::MINISTERIO, self::EQUIVALENCIAS,
            ],
            $this->titulosVisibles(self::ESTUDIANTE),
        );
    }

    /**
     * Prueba que el estudiante matriculado solo en Gastronomía no ve el material de Bartender.
     */
    public function test_el_estudiante_dos_no_ve_el_material_de_bartender(): void
    {
        $this->sembrarDemo();

        $visibles = $this->titulosVisibles(self::ESTUDIANTE_2);

        $this->assertContains(self::MANUAL_COCINA, $visibles);
        $this->assertNotContains(self::COCTELERIA, $visibles);
        $this->assertNotContains(self::PANIFICACION, $visibles);
        $this->assertCount(8, $visibles);
    }

    /**
     * Prueba que un curso inactivo no concede acceso, y que al activarlo sí.
     */
    public function test_el_estudiante_tres_no_ve_el_material_de_un_curso_inactivo(): void
    {
        $this->sembrarDemo();

        $visibles = $this->titulosVisibles(self::ESTUDIANTE_3);

        $this->assertEqualsCanonicalizing(
            [self::PANIFICACION, self::MANIPULACION, self::IMAGENES, self::MINISTERIO, self::EQUIVALENCIAS],
            $visibles,
        );
        $this->assertNotContains(self::MASAS, $visibles);
        $this->assertNotContains(self::PASTELERIA_BORRADOR, $visibles);

        // Contraprueba: el recurso publicado queda oculto solo por el curso inactivo.
        Course::query()->where('name', 'Pastelería')->update(['is_active' => true]);

        $this->assertContains(self::MASAS, $this->titulosVisibles(self::ESTUDIANTE_3));
        $this->assertNotContains(self::PASTELERIA_BORRADOR, $this->titulosVisibles(self::ESTUDIANTE_3));
    }

    /**
     * Prueba que el profesor ve lo que creó (borradores incluidos) y lo general, pero no lo de otra docente.
     */
    public function test_el_profesor_demo_ve_sus_recursos_incluidos_los_borradores(): void
    {
        $this->sembrarDemo();

        $visibles = $this->titulosVisibles(self::PROFESOR);

        $this->assertEqualsCanonicalizing(
            [
                self::MANUAL_COCINA, self::MANIPULACION, self::CORTES, self::COCTELERIA, self::VEGETARIANA,
                self::PASTELERIA_BORRADOR, self::IMAGENES, self::AUDIO, self::MINISTERIO, self::EQUIVALENCIAS, self::MASAS,
            ],
            $visibles,
        );
        $this->assertNotContains(self::PANIFICACION, $visibles);
        $this->assertNotContains(self::PRECIOS, $visibles);
    }

    /**
     * Prueba que la segunda docente no ve los recursos de Gastronomía, Bartender ni Pastelería.
     */
    public function test_la_profesora_dos_no_ve_los_recursos_de_otros_cursos(): void
    {
        $this->sembrarDemo();

        $visibles = $this->titulosVisibles(self::PROFESORA);

        $this->assertEqualsCanonicalizing(
            [self::PANIFICACION, self::MANIPULACION, self::IMAGENES, self::MINISTERIO, self::EQUIVALENCIAS],
            $visibles,
        );

        foreach ([self::MANUAL_COCINA, self::CORTES, self::VEGETARIANA, self::AUDIO, self::COCTELERIA] as $titulo) {
            $this->assertNotContains($titulo, $visibles);
        }
    }

    /**
     * Prueba que el administrador ve todos los recursos, también borradores y archivados.
     */
    public function test_el_administrador_demo_ve_todo(): void
    {
        $this->sembrarDemo();

        $visibles = $this->titulosVisibles(self::ADMIN);

        $this->assertCount(13, $visibles);
        $this->assertContains(self::PRECIOS, $visibles);
        $this->assertContains(self::PASTELERIA_BORRADOR, $visibles);
    }

    /**
     * Prueba que Tabla de Equivalencias se descarga por la concesión explícita y Manual de Manipulación no.
     */
    public function test_la_concesion_explicita_permite_descargar_solo_la_tabla_de_equivalencias(): void
    {
        $this->sembrarDemo();

        $estudiante = $this->usuario(self::ESTUDIANTE);
        $tabla = $this->recurso(self::EQUIVALENCIAS);

        $this->assertFalse($tabla->is_downloadable);
        $this->assertTrue($estudiante->can('download', $tabla));
        $this->assertTrue($this->usuario(self::ESTUDIANTE_2)->can('download', $tabla));
        $this->assertFalse($estudiante->can('download', $this->recurso(self::MANIPULACION)));

        // La concesión es para el rol estudiante: el docente que ve la tabla no la descarga.
        $this->assertTrue($this->usuario(self::PROFESOR)->can('view', $tabla));
        $this->assertFalse($this->usuario(self::PROFESOR)->can('download', $tabla));

        $concesion = ResourcePermission::query()->where('resource_id', $tabla->id)->sole();
        $this->assertSame(User::ROLE_ESTUDIANTE, $concesion->role);
        $this->assertNull($concesion->user_id);
        $this->assertTrue($concesion->can_view);
        $this->assertTrue($concesion->can_download);
        $this->assertSame(1, ResourcePermission::count());
    }

    /**
     * Prueba que la bandera de descarga y la existencia de archivo gobiernan la descarga del resto.
     */
    public function test_la_descarga_depende_de_la_bandera_y_de_que_haya_archivo(): void
    {
        $this->sembrarDemo();

        $estudiante = $this->usuario(self::ESTUDIANTE);

        $this->assertTrue($estudiante->can('download', $this->recurso(self::MANUAL_COCINA)));
        $this->assertTrue($estudiante->can('download', $this->recurso(self::IMAGENES)));
        $this->assertFalse($estudiante->can('download', $this->recurso(self::AUDIO)));
        $this->assertFalse($estudiante->can('download', $this->recurso(self::CORTES)));
        $this->assertFalse($estudiante->can('download', $this->recurso(self::MINISTERIO)));
        $this->assertFalse($estudiante->can('download', $this->recurso(self::PRECIOS)));
    }

    /**
     * Prueba que el PDF demo es un PDF bien formado, con tabla xref correcta y el texto del título.
     */
    public function test_el_pdf_demo_es_valido_y_lleva_el_titulo(): void
    {
        $pdf = DemoFileFactory::pdf('Guía de (Panificación)');

        $this->assertStringStartsWith('%PDF-', $pdf);
        $this->assertStringEndsWith("%%EOF\n", $pdf);
        $this->assertSame('application/pdf', (new finfo(FILEINFO_MIME_TYPE))->buffer($pdf));

        // El texto va en Windows-1252 y con los paréntesis escapados.
        $this->assertStringContainsString("Gu\xEDa de \\(Panificaci\xF3n\\)", $pdf);

        $this->assertSame(1, preg_match('/startxref\n(\d+)\n/', $pdf, $inicio));
        $this->assertSame('xref', substr($pdf, (int) $inicio[1], 4));

        preg_match_all('/^(\d{10}) 00000 n $/m', $pdf, $entradas);
        $this->assertCount(5, $entradas[1]);

        foreach ($entradas[1] as $i => $desplazamiento) {
            $this->assertStringStartsWith(($i + 1).' 0 obj', substr($pdf, (int) $desplazamiento, 8));
        }
    }

    /**
     * Prueba que el PNG y el WAV demo se reconocen por su contenido y se aceptan por la configuración.
     */
    public function test_el_png_y_el_wav_demo_tienen_contenido_real(): void
    {
        $finfo = new finfo(FILEINFO_MIME_TYPE);

        $png = DemoFileFactory::png('Cocina');
        $this->assertSame('image/png', $finfo->buffer($png));
        $this->assertNotFalse(getimagesizefromstring($png));
        $this->assertNotSame($png, DemoFileFactory::png('Bartender'));

        $wav = DemoFileFactory::wav();
        $this->assertContains(strtolower($finfo->buffer($wav)), config('biblioteca.extensions.wav'));
        $this->assertStringStartsWith('RIFF', $wav);
    }
}
