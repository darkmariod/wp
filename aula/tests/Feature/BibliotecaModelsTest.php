<?php

namespace Tests\Feature;

use App\Enums\ResourceStatus;
use App\Enums\ResourceType;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\LibraryResource;
use App\Models\ResourceAccessLog;
use App\Models\ResourceCategory;
use App\Models\ResourceFile;
use App\Models\ResourcePermission;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Esquema de la Biblioteca: modelos, relaciones, slugs, cascadas y factories.
 */
class BibliotecaModelsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Recurso enlazado a un curso y a uno de sus módulos.
     *
     * @return array{recurso: LibraryResource, curso: Course, modulo: CourseModule}
     */
    private function recursoEnModulo(int $orden = 1): array
    {
        $curso = Course::factory()->create();
        $modulo = CourseModule::factory()->create(['course_id' => $curso->id]);
        $recurso = LibraryResource::factory()->published()->create();
        $recurso->courses()->attach($curso->id, ['module_id' => $modulo->id, 'sort_order' => $orden]);

        return compact('recurso', 'curso', 'modulo');
    }

    // ---------------------------------------------------------------
    // Base de datos
    // ---------------------------------------------------------------

    /**
     * Sin esto las pruebas de cascada serían decorativas: SQLite ignora
     * las llaves foráneas si la conexión no las activa.
     */
    public function test_las_llaves_foraneas_estan_activas_en_la_conexion_de_pruebas(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            $this->markTestSkipped('El pragma solo existe en SQLite.');
        }

        $this->assertSame(1, (int) DB::selectOne('PRAGMA foreign_keys')->foreign_keys);
    }

    public function test_las_nueve_tablas_de_la_biblioteca_existen(): void
    {
        foreach ([
            'resource_categories', 'courses', 'modules', 'course_enrollments', 'resources',
            'resource_files', 'course_resource', 'resource_permissions', 'resource_access_logs',
        ] as $tabla) {
            $this->assertTrue(Schema::hasTable($tabla), "Falta la tabla {$tabla}.");
        }
    }

    public function test_el_recurso_no_usa_borrado_suave(): void
    {
        $this->assertFalse(Schema::hasColumn('resources', 'deleted_at'));
    }

    // ---------------------------------------------------------------
    // Casts y enums
    // ---------------------------------------------------------------

    public function test_el_recurso_castea_tipo_estado_y_booleanos(): void
    {
        $recurso = LibraryResource::factory()->published()->downloadable()->create();
        $recurso = $recurso->fresh();

        $this->assertSame(ResourceType::Pdf, $recurso->type);
        $this->assertSame(ResourceStatus::Published, $recurso->status);
        $this->assertTrue($recurso->is_downloadable);
        $this->assertInstanceOf(CarbonInterface::class, $recurso->published_at);

        $this->assertSame('pdf', $recurso->getRawOriginal('type'));
        $this->assertSame('published', $recurso->getRawOriginal('status'));
    }

    public function test_el_recurso_acepta_el_enum_al_asignar_tipo_y_estado(): void
    {
        $recurso = LibraryResource::factory()->create([
            'type' => ResourceType::Video,
            'status' => ResourceStatus::Archived,
        ]);

        $this->assertSame('video', $recurso->fresh()->getRawOriginal('type'));
        $this->assertSame(ResourceStatus::Archived, $recurso->fresh()->status);
    }

    public function test_los_permisos_y_la_categoria_castean_sus_booleanos(): void
    {
        $permiso = ResourcePermission::factory()->create(['can_view' => 1, 'can_download' => 0]);
        $categoria = ResourceCategory::factory()->create(['show_in_tabs' => 1, 'is_active' => 0, 'sort_order' => '4']);

        $this->assertTrue($permiso->fresh()->can_view);
        $this->assertFalse($permiso->fresh()->can_download);
        $this->assertTrue($categoria->fresh()->show_in_tabs);
        $this->assertFalse($categoria->fresh()->is_active);
        $this->assertSame(4, $categoria->fresh()->sort_order);
    }

    public function test_el_archivo_castea_el_tamano_a_entero(): void
    {
        $archivo = ResourceFile::factory()->create(['size' => '2048']);

        $this->assertSame(2048, $archivo->fresh()->size);
    }

    // ---------------------------------------------------------------
    // Relaciones
    // ---------------------------------------------------------------

    public function test_el_recurso_pertenece_a_una_categoria_y_la_categoria_lista_sus_recursos(): void
    {
        $categoria = ResourceCategory::factory()->create();
        $recurso = LibraryResource::factory()->create(['category_id' => $categoria->id]);

        $this->assertTrue($recurso->category->is($categoria));
        $this->assertTrue($categoria->resources->first()->is($recurso));
        $this->assertCount(1, $categoria->resources);
    }

    public function test_el_recurso_tiene_archivos_y_el_archivo_principal_es_el_primero(): void
    {
        $recurso = LibraryResource::factory()->create();
        $primero = ResourceFile::factory()->create(['resource_id' => $recurso->id]);
        $segundo = ResourceFile::factory()->create(['resource_id' => $recurso->id]);

        $recurso = $recurso->fresh();

        $this->assertCount(2, $recurso->files);
        $this->assertTrue($recurso->primaryFile()->is($primero));
        $this->assertTrue($primero->resource->is($recurso));
        $this->assertFalse($recurso->primaryFile()->is($segundo));
    }

    public function test_un_recurso_sin_archivos_no_tiene_archivo_principal(): void
    {
        $this->assertNull(LibraryResource::factory()->create()->primaryFile());
    }

    public function test_el_recurso_registra_creador_editor_y_publicador(): void
    {
        [$creador, $editor, $publicador] = User::factory()->guia()->count(3)->create()->all();

        $recurso = LibraryResource::factory()->create([
            'created_by' => $creador->id,
            'updated_by' => $editor->id,
            'published_by' => $publicador->id,
        ]);

        $this->assertTrue($recurso->creator->is($creador));
        $this->assertTrue($recurso->updater->is($editor));
        $this->assertTrue($recurso->publisher->is($publicador));
    }

    public function test_el_recurso_se_relaciona_con_cursos_con_modulo_y_orden_en_el_pivote(): void
    {
        ['recurso' => $recurso, 'curso' => $curso, 'modulo' => $modulo] = $this->recursoEnModulo(orden: 3);

        $enCurso = $recurso->courses()->first();

        $this->assertTrue($enCurso->is($curso));
        $this->assertSame($modulo->id, (int) $enCurso->pivot->module_id);
        $this->assertSame(3, (int) $enCurso->pivot->sort_order);
        $this->assertNotNull($enCurso->pivot->created_at);

        $this->assertTrue($curso->resources->first()->is($recurso));
        $this->assertSame($modulo->id, (int) $curso->resources->first()->pivot->module_id);
        $this->assertSame(3, (int) $curso->resources->first()->pivot->sort_order);
    }

    public function test_un_recurso_puede_estar_en_un_curso_sin_modulo(): void
    {
        $curso = Course::factory()->create();
        $recurso = LibraryResource::factory()->create();
        $recurso->courses()->attach($curso->id);

        $this->assertNull($recurso->courses()->first()->pivot->module_id);
        $this->assertSame(0, (int) $recurso->courses()->first()->pivot->sort_order);
    }

    public function test_el_modulo_pertenece_a_un_curso_y_lista_sus_recursos(): void
    {
        ['recurso' => $recurso, 'curso' => $curso, 'modulo' => $modulo] = $this->recursoEnModulo(orden: 5);

        $this->assertTrue($modulo->course->is($curso));
        $this->assertCount(1, $modulo->resources);
        $this->assertTrue($modulo->resources->first()->is($recurso));
        $this->assertSame(5, (int) $modulo->resources->first()->pivot->sort_order);
    }

    public function test_el_modulo_usa_la_tabla_modules(): void
    {
        $this->assertSame('modules', (new CourseModule)->getTable());
        $this->assertSame('resources', (new LibraryResource)->getTable());
    }

    public function test_el_curso_tiene_docente_modulos_ordenados_y_alumnos(): void
    {
        $docente = User::factory()->guia()->create();
        $curso = Course::factory()->create(['teacher_id' => $docente->id]);
        CourseModule::factory()->create(['course_id' => $curso->id, 'name' => 'Tercero', 'sort_order' => 2]);
        CourseModule::factory()->create(['course_id' => $curso->id, 'name' => 'Primero', 'sort_order' => 0]);
        CourseModule::factory()->create(['course_id' => $curso->id, 'name' => 'Segundo', 'sort_order' => 1]);
        $alumno = User::factory()->estudiante()->create();
        $curso->students()->attach($alumno->id, ['enrolled_at' => now()]);

        $this->assertTrue($curso->teacher->is($docente));
        $this->assertSame(['Primero', 'Segundo', 'Tercero'], $curso->modules->pluck('name')->all());
        $this->assertTrue($curso->students->first()->is($alumno));
        $this->assertNotNull($curso->students->first()->pivot->enrolled_at);
    }

    public function test_el_usuario_tiene_cursos_dictados_cursos_matriculados_y_recursos_creados(): void
    {
        $docente = User::factory()->guia()->create();
        $alumno = User::factory()->estudiante()->create();
        $curso = Course::factory()->create(['teacher_id' => $docente->id]);
        $curso->students()->attach($alumno->id);
        $recurso = LibraryResource::factory()->create(['created_by' => $docente->id]);

        $this->assertTrue($docente->taughtCourses->first()->is($curso));
        $this->assertTrue($alumno->enrolledCourses->first()->is($curso));
        $this->assertTrue($docente->libraryResources->first()->is($recurso));
        $this->assertCount(0, $alumno->taughtCourses);
        $this->assertCount(0, $docente->enrolledCourses);
    }

    public function test_el_recurso_tiene_permisos_y_registros_de_acceso(): void
    {
        $recurso = LibraryResource::factory()->create();
        $alumno = User::factory()->estudiante()->create();
        $permiso = ResourcePermission::factory()->forUser($alumno)->create(['resource_id' => $recurso->id]);
        $registro = ResourceAccessLog::factory()->create([
            'resource_id' => $recurso->id,
            'user_id' => $alumno->id,
        ]);

        $this->assertTrue($recurso->permissions->first()->is($permiso));
        $this->assertTrue($permiso->resource->is($recurso));
        $this->assertTrue($permiso->user->is($alumno));
        $this->assertTrue($recurso->accessLogs->first()->is($registro));
        $this->assertTrue($registro->resource->is($recurso));
        $this->assertTrue($registro->user->is($alumno));
    }

    // ---------------------------------------------------------------
    // Unicidad
    // ---------------------------------------------------------------

    public function test_un_alumno_no_se_matricula_dos_veces_en_el_mismo_curso(): void
    {
        $curso = Course::factory()->create();
        $alumno = User::factory()->estudiante()->create();
        $curso->students()->attach($alumno->id);

        $this->expectException(QueryException::class);

        $curso->students()->attach($alumno->id);
    }

    public function test_un_recurso_no_se_enlaza_dos_veces_al_mismo_curso(): void
    {
        $curso = Course::factory()->create();
        $recurso = LibraryResource::factory()->create();
        $recurso->courses()->attach($curso->id);

        $this->expectException(QueryException::class);

        $recurso->courses()->attach($curso->id);
    }

    public function test_la_base_de_datos_rechaza_slugs_repetidos(): void
    {
        LibraryResource::factory()->create(['slug' => 'repetido']);

        $this->expectException(QueryException::class);

        LibraryResource::factory()->create(['slug' => 'repetido']);
    }

    // ---------------------------------------------------------------
    // Slugs
    // ---------------------------------------------------------------

    public function test_el_recurso_genera_su_slug_a_partir_del_titulo(): void
    {
        $recurso = LibraryResource::factory()->create(['title' => 'Guía de Cocina Básica']);

        $this->assertSame('guia-de-cocina-basica', $recurso->slug);
    }

    public function test_el_slug_repetido_agrega_un_sufijo_numerico(): void
    {
        $primero = LibraryResource::factory()->create(['title' => 'Manual de panadería']);
        $segundo = LibraryResource::factory()->create(['title' => 'Manual de panadería']);
        $tercero = LibraryResource::factory()->create(['title' => 'Manual de panadería']);

        $this->assertSame('manual-de-panaderia', $primero->slug);
        $this->assertSame('manual-de-panaderia-2', $segundo->slug);
        $this->assertSame('manual-de-panaderia-3', $tercero->slug);
    }

    public function test_el_slug_no_cambia_cuando_cambia_el_titulo(): void
    {
        $recurso = LibraryResource::factory()->create(['title' => 'Título original']);

        $recurso->update(['title' => 'Título totalmente distinto']);

        $this->assertSame('titulo-original', $recurso->fresh()->slug);
    }

    public function test_un_slug_explicito_se_respeta(): void
    {
        $recurso = LibraryResource::factory()->create(['title' => 'Cualquier cosa', 'slug' => 'mi-slug']);

        $this->assertSame('mi-slug', $recurso->slug);
    }

    public function test_un_titulo_sin_letras_usa_el_slug_de_respaldo(): void
    {
        $primero = LibraryResource::factory()->create(['title' => '¿¿¿???']);
        $segundo = LibraryResource::factory()->create(['title' => '!!!']);

        $this->assertSame('recurso', $primero->slug);
        $this->assertSame('recurso-2', $segundo->slug);
    }

    public function test_un_titulo_muy_largo_genera_un_slug_que_cabe_en_la_columna(): void
    {
        $recurso = LibraryResource::factory()->create(['title' => str_repeat('palabra ', 80)]);

        $this->assertLessThanOrEqual(190, strlen($recurso->slug));
        $this->assertFalse(str_ends_with($recurso->slug, '-'));
    }

    public function test_la_categoria_genera_su_slug_a_partir_del_nombre(): void
    {
        $primera = ResourceCategory::factory()->create(['name' => 'Recetas y Técnicas']);
        $segunda = ResourceCategory::factory()->create(['name' => 'Recetas y Técnicas']);
        $vacia = ResourceCategory::factory()->create(['name' => '???']);

        $this->assertSame('recetas-y-tecnicas', $primera->slug);
        $this->assertSame('recetas-y-tecnicas-2', $segunda->slug);
        $this->assertSame('categoria', $vacia->slug);

        $primera->update(['name' => 'Otro nombre']);
        $this->assertSame('recetas-y-tecnicas', $primera->fresh()->slug);
    }

    public function test_el_curso_genera_su_slug_a_partir_del_nombre(): void
    {
        $primero = Course::factory()->create(['name' => 'Gastronomía Profesional']);
        $segundo = Course::factory()->create(['name' => 'Gastronomía Profesional']);
        $vacio = Course::factory()->create(['name' => '???']);

        $this->assertSame('gastronomia-profesional', $primero->slug);
        $this->assertSame('gastronomia-profesional-2', $segundo->slug);
        $this->assertSame('curso', $vacio->slug);

        $primero->update(['name' => 'Otro nombre']);
        $this->assertSame('gastronomia-profesional', $primero->fresh()->slug);
    }

    // ---------------------------------------------------------------
    // Cascadas
    // ---------------------------------------------------------------

    public function test_borrar_un_recurso_elimina_sus_archivos_permisos_registros_y_enlaces(): void
    {
        ['recurso' => $recurso, 'curso' => $curso, 'modulo' => $modulo] = $this->recursoEnModulo();
        ResourceFile::factory()->create(['resource_id' => $recurso->id]);
        ResourcePermission::factory()->create(['resource_id' => $recurso->id]);
        ResourceAccessLog::factory()->create(['resource_id' => $recurso->id]);
        $otro = LibraryResource::factory()->create();
        ResourceFile::factory()->create(['resource_id' => $otro->id]);

        $recurso->delete();

        $this->assertSame(0, ResourceFile::where('resource_id', $recurso->id)->count());
        $this->assertSame(0, ResourcePermission::where('resource_id', $recurso->id)->count());
        $this->assertSame(0, ResourceAccessLog::where('resource_id', $recurso->id)->count());
        $this->assertSame(0, DB::table('course_resource')->where('resource_id', $recurso->id)->count());
        $this->assertSame(1, ResourceFile::where('resource_id', $otro->id)->count());
        $this->assertNotNull($curso->fresh());
        $this->assertNotNull($modulo->fresh());
    }

    public function test_borrar_un_curso_elimina_sus_modulos_matriculas_y_enlaces_pero_no_los_recursos(): void
    {
        ['recurso' => $recurso, 'curso' => $curso, 'modulo' => $modulo] = $this->recursoEnModulo();
        $alumno = User::factory()->estudiante()->create();
        $curso->students()->attach($alumno->id);

        $curso->delete();

        $this->assertNull(CourseModule::find($modulo->id));
        $this->assertSame(0, DB::table('course_enrollments')->where('course_id', $curso->id)->count());
        $this->assertSame(0, DB::table('course_resource')->where('course_id', $curso->id)->count());
        $this->assertNotNull($recurso->fresh());
        $this->assertNotNull($alumno->fresh());
    }

    public function test_borrar_un_usuario_anula_su_autoria_y_elimina_matriculas_y_permisos(): void
    {
        $usuario = User::factory()->guia()->create();
        $recurso = LibraryResource::factory()->create([
            'created_by' => $usuario->id,
            'updated_by' => $usuario->id,
            'published_by' => $usuario->id,
        ]);
        $curso = Course::factory()->create(['teacher_id' => $usuario->id]);
        $curso->students()->attach($usuario->id);
        $permiso = ResourcePermission::factory()->forUser($usuario)->create(['resource_id' => $recurso->id]);
        $registro = ResourceAccessLog::factory()->create(['resource_id' => $recurso->id, 'user_id' => $usuario->id]);

        $usuario->delete();

        $recurso = $recurso->fresh();
        $this->assertNull($recurso->created_by);
        $this->assertNull($recurso->updated_by);
        $this->assertNull($recurso->published_by);
        $this->assertNull($curso->fresh()->teacher_id);
        $this->assertSame(0, DB::table('course_enrollments')->where('user_id', $usuario->id)->count());
        $this->assertNull(ResourcePermission::find($permiso->id));
        $this->assertNull($registro->fresh()->user_id);
        $this->assertNotNull($registro->fresh());
    }

    public function test_borrar_una_categoria_deja_sus_recursos_sin_categoria(): void
    {
        $categoria = ResourceCategory::factory()->create();
        $recurso = LibraryResource::factory()->create(['category_id' => $categoria->id]);

        $categoria->delete();

        $this->assertNotNull($recurso->fresh());
        $this->assertNull($recurso->fresh()->category_id);
    }

    public function test_borrar_un_modulo_deja_el_recurso_en_el_curso_sin_modulo(): void
    {
        ['recurso' => $recurso, 'curso' => $curso, 'modulo' => $modulo] = $this->recursoEnModulo();

        $modulo->delete();

        $enCurso = $recurso->courses()->first();
        $this->assertTrue($enCurso->is($curso));
        $this->assertNull($enCurso->pivot->module_id);
    }

    // ---------------------------------------------------------------
    // Scopes
    // ---------------------------------------------------------------

    public function test_el_scope_published_solo_devuelve_recursos_publicados(): void
    {
        $publicado = LibraryResource::factory()->published()->create();
        LibraryResource::factory()->draft()->create();
        LibraryResource::factory()->archived()->create();

        $resultado = LibraryResource::published()->get();

        $this->assertCount(1, $resultado);
        $this->assertTrue($resultado->first()->is($publicado));
    }

    public function test_el_scope_of_type_filtra_por_tipo(): void
    {
        $video = LibraryResource::factory()->ofType(ResourceType::Video)->create();
        LibraryResource::factory()->ofType(ResourceType::Pdf)->create();
        LibraryResource::factory()->ofType(ResourceType::Link)->create();

        $resultado = LibraryResource::ofType(ResourceType::Video)->get();

        $this->assertCount(1, $resultado);
        $this->assertTrue($resultado->first()->is($video));
    }

    // ---------------------------------------------------------------
    // Factories
    // ---------------------------------------------------------------

    public function test_la_factory_del_recurso_crea_un_pdf_en_borrador_con_categoria(): void
    {
        $recurso = LibraryResource::factory()->create();

        $this->assertSame(ResourceType::Pdf, $recurso->type);
        $this->assertSame(ResourceStatus::Draft, $recurso->status);
        $this->assertNotNull($recurso->category);
        $this->assertFalse($recurso->is_downloadable);
        $this->assertNull($recurso->published_at);
        $this->assertNull($recurso->created_by);
        $this->assertNotSame('', $recurso->slug);
    }

    public function test_los_estados_de_la_factory_del_recurso(): void
    {
        $publicado = LibraryResource::factory()->published()->create();
        $borrador = LibraryResource::factory()->published()->draft()->create();
        $archivado = LibraryResource::factory()->archived()->create();
        $enlace = LibraryResource::factory()->ofType(ResourceType::Link)->create();
        $descargable = LibraryResource::factory()->downloadable()->create();
        $autor = User::factory()->guia()->create();
        $propio = LibraryResource::factory()->createdBy($autor)->create();

        $this->assertSame(ResourceStatus::Published, $publicado->status);
        $this->assertNotNull($publicado->published_at);
        $this->assertSame(ResourceStatus::Draft, $borrador->status);
        $this->assertNull($borrador->published_at);
        $this->assertSame(ResourceStatus::Archived, $archivado->status);
        $this->assertSame(ResourceType::Link, $enlace->type);
        $this->assertNotNull($enlace->external_url);
        $this->assertTrue($descargable->is_downloadable);
        $this->assertTrue($propio->creator->is($autor));
    }

    public function test_las_factories_de_curso_modulo_archivo_permiso_y_registro_resuelven(): void
    {
        $curso = Course::factory()->create();
        $modulo = CourseModule::factory()->create();
        $archivo = ResourceFile::factory()->create();
        $permisoRol = ResourcePermission::factory()->forRole(User::ROLE_ESTUDIANTE)->create();
        $permisoUsuario = ResourcePermission::factory()->forUser(User::factory()->estudiante()->create())->create();
        $vista = ResourceAccessLog::factory()->viewed()->create();
        $descarga = ResourceAccessLog::factory()->downloaded()->create();

        $this->assertTrue($curso->is_active);
        $this->assertNull($curso->teacher_id);
        $this->assertNotNull($modulo->course);
        $this->assertNotNull($archivo->resource);
        $this->assertSame('biblioteca', $archivo->disk);
        $this->assertSame(User::ROLE_ESTUDIANTE, $permisoRol->role);
        $this->assertNull($permisoRol->user_id);
        $this->assertNull($permisoUsuario->role);
        $this->assertNotNull($permisoUsuario->user_id);
        $this->assertSame(ResourceAccessLog::ACTION_VIEWED, $vista->action);
        $this->assertSame(ResourceAccessLog::ACTION_DOWNLOADED, $descarga->action);
    }

    // ---------------------------------------------------------------
    // Registro de accesos
    // ---------------------------------------------------------------

    public function test_el_registro_de_acceso_rellena_created_at_solo_y_no_tiene_updated_at(): void
    {
        $this->freezeSecond();
        $recurso = LibraryResource::factory()->create();

        $registro = ResourceAccessLog::create([
            'resource_id' => $recurso->id,
            'user_id' => null,
            'action' => ResourceAccessLog::ACTION_DOWNLOADED,
        ]);

        $this->assertInstanceOf(CarbonInterface::class, $registro->fresh()->created_at);
        $this->assertTrue($registro->fresh()->created_at->equalTo(now()));
        $this->assertFalse(Schema::hasColumn('resource_access_logs', 'updated_at'));
        $this->assertSame('downloaded', ResourceAccessLog::ACTION_DOWNLOADED);
        $this->assertSame('viewed', ResourceAccessLog::ACTION_VIEWED);
    }

    public function test_el_registro_de_acceso_respeta_un_created_at_explicito(): void
    {
        $this->freezeSecond();
        $fecha = now()->subDays(3);

        $registro = ResourceAccessLog::factory()->create(['created_at' => $fecha]);

        $this->assertTrue($registro->fresh()->created_at->equalTo($fecha));
    }
}
