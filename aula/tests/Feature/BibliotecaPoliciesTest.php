<?php

namespace Tests\Feature;

use App\Enums\ResourceType;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\LibraryResource;
use App\Models\ResourceAccessLog;
use App\Models\ResourceCategory;
use App\Models\ResourceFile;
use App\Models\ResourcePermission;
use App\Models\User;
use App\Services\Biblioteca\ResourceAccessLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

/**
 * Reglas de acceso de la Biblioteca (T04): políticas, scopes `visibleTo` y
 * registro de accesos. Se usan usuarios planos de la factory, nunca
 * `super_admin`, porque ese rol se salta toda política con Gate::before.
 */
class BibliotecaPoliciesTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------
    // Ayudantes
    // ---------------------------------------------------------------

    private function usuario(string $rol, array $atributos = []): User
    {
        return User::factory()->{$rol}()->create($atributos);
    }

    /**
     * Un usuario de cada rol, más las cuentas inactivas y un rol desconocido.
     *
     * @return array<string, User>
     */
    private function plantel(): array
    {
        return [
            'administrador' => $this->usuario('administrador'),
            'coordinacion' => $this->usuario('coordinacion'),
            'guia' => $this->usuario('guia'),
            'estudiante' => $this->usuario('estudiante'),
            'familia' => $this->usuario('familia'),
            'administrador inactivo' => $this->usuario('administrador', ['active' => false]),
            'guia inactiva' => $this->usuario('guia', ['active' => false]),
            'estudiante inactivo' => $this->usuario('estudiante', ['active' => false]),
            'rol desconocido' => User::factory()->create(['role' => 'invitado', 'family_id' => null]),
        ];
    }

    private function recurso(string $estado = 'published', ?User $creador = null, array $atributos = []): LibraryResource
    {
        return LibraryResource::factory()->{$estado}()->create([
            'created_by' => $creador?->id,
            ...$atributos,
        ]);
    }

    private function conArchivo(LibraryResource $recurso): LibraryResource
    {
        ResourceFile::factory()->create(['resource_id' => $recurso->id]);

        return $recurso;
    }

    private function enCurso(LibraryResource $recurso, Course $curso): LibraryResource
    {
        $recurso->courses()->attach($curso->id);

        return $recurso;
    }

    private function curso(?User $docente = null, bool $activo = true, array $estudiantes = []): Course
    {
        $curso = Course::factory()->create([
            'teacher_id' => $docente?->id,
            'is_active' => $activo,
        ]);

        foreach ($estudiantes as $estudiante) {
            $curso->students()->attach($estudiante->id);
        }

        return $curso;
    }

    private function permiso(LibraryResource $recurso, User|string $destino, bool $ver = true, bool $descargar = false): ResourcePermission
    {
        return ResourcePermission::factory()->create([
            'resource_id' => $recurso->id,
            'role' => is_string($destino) ? $destino : null,
            'user_id' => $destino instanceof User ? $destino->id : null,
            'can_view' => $ver,
            'can_download' => $descargar,
        ]);
    }

    private function permite(User $usuario, string $habilidad, mixed $objetivo): bool
    {
        return Gate::forUser($usuario)->allows($habilidad, $objetivo);
    }

    /**
     * Comprueba una habilidad contra una tabla "quién -> permitido".
     *
     * @param  array<string, bool>  $esperado  clave = nombre en el plantel
     * @param  array<string, User>  $usuarios
     */
    private function assertMatriz(array $esperado, string $habilidad, mixed $objetivo, array $usuarios): void
    {
        foreach ($esperado as $quien => $permitido) {
            $this->assertSame(
                $permitido,
                $this->permite($usuarios[$quien], $habilidad, $objetivo),
                "{$habilidad} para {$quien} debía ser ".($permitido ? 'permitido' : 'denegado').'.',
            );
        }
    }

    // ---------------------------------------------------------------
    // Listados (viewAny)
    // ---------------------------------------------------------------

    /**
     * Quién puede abrir la Biblioteca y sus listados.
     */
    public function test_ver_el_listado_solo_para_roles_de_la_biblioteca_activos(): void
    {
        $usuarios = $this->plantel();
        $esperado = [
            'administrador' => true,
            'coordinacion' => true,
            'guia' => true,
            'estudiante' => true,
            'familia' => false,
            'administrador inactivo' => false,
            'guia inactiva' => false,
            'estudiante inactivo' => false,
            'rol desconocido' => false,
        ];

        foreach ([LibraryResource::class, ResourceCategory::class, Course::class, CourseModule::class] as $modelo) {
            $this->assertMatriz($esperado, 'viewAny', $modelo, $usuarios);
        }
    }

    // ---------------------------------------------------------------
    // Recurso: ver
    // ---------------------------------------------------------------

    /**
     * Administración y coordinación ven todo, también borradores y archivados.
     */
    public function test_el_personal_ve_recursos_en_cualquier_estado(): void
    {
        $curso = $this->curso();

        foreach (['draft', 'published', 'archived'] as $estado) {
            $general = $this->recurso($estado);
            $deCurso = $this->enCurso($this->recurso($estado), $curso);

            foreach (['administrador', 'coordinacion'] as $rol) {
                $usuario = $this->usuario($rol);
                $this->assertTrue($this->permite($usuario, 'view', $general), "{$rol} / {$estado} general");
                $this->assertTrue($this->permite($usuario, 'view', $deCurso), "{$rol} / {$estado} de curso");
            }
        }
    }

    /**
     * Quien creó el recurso lo ve siempre, aunque nadie más pueda.
     */
    public function test_el_creador_ve_su_recurso_en_cualquier_estado(): void
    {
        $curso = $this->curso();

        foreach (['guia', 'estudiante'] as $rol) {
            $creador = $this->usuario($rol);

            foreach (['draft', 'published', 'archived'] as $estado) {
                $this->assertTrue(
                    $this->permite($creador, 'view', $this->recurso($estado, $creador)),
                    "{$rol} creador / {$estado} general",
                );
                $this->assertTrue(
                    $this->permite($creador, 'view', $this->enCurso($this->recurso($estado, $creador), $curso)),
                    "{$rol} creador / {$estado} de curso ajeno",
                );
            }
        }
    }

    /**
     * Ser creador no salta la puerta: familia y cuentas inactivas siguen fuera.
     */
    public function test_el_creador_sin_acceso_a_la_biblioteca_no_ve_su_recurso(): void
    {
        $familia = $this->usuario('familia');
        $inactivo = $this->usuario('guia', ['active' => false]);

        $this->assertFalse($this->permite($familia, 'view', $this->recurso('published', $familia)));
        $this->assertFalse($this->permite($inactivo, 'view', $this->recurso('published', $inactivo)));
    }

    /**
     * Borradores y archivados de otra persona nunca son visibles para quien no es personal.
     */
    public function test_la_guia_no_ve_borradores_ni_archivados_de_otra_persona(): void
    {
        $guia = $this->usuario('guia');
        $otra = $this->usuario('guia');

        foreach (['draft', 'archived'] as $estado) {
            $this->assertFalse($this->permite($guia, 'view', $this->recurso($estado, $otra)), "general / {$estado}");

            $propio = $this->curso($guia);
            $this->assertFalse(
                $this->permite($guia, 'view', $this->enCurso($this->recurso($estado, $otra), $propio)),
                "de su curso / {$estado}",
            );
        }
    }

    /**
     * Un recurso publicado sin curso es de la biblioteca general.
     */
    public function test_un_recurso_general_publicado_lo_ve_todo_el_que_usa_la_biblioteca(): void
    {
        $usuarios = $this->plantel();
        $recurso = $this->recurso('published', $this->usuario('guia'));

        $this->assertMatriz([
            'administrador' => true,
            'coordinacion' => true,
            'guia' => true,
            'estudiante' => true,
            'familia' => false,
            'administrador inactivo' => false,
            'guia inactiva' => false,
            'estudiante inactivo' => false,
            'rol desconocido' => false,
        ], 'view', $recurso, $usuarios);
    }

    /**
     * El estudiante ve el material publicado de los cursos activos donde está matriculado.
     */
    public function test_el_estudiante_ve_recursos_de_sus_cursos_activos(): void
    {
        $estudiante = $this->usuario('estudiante');
        $curso = $this->curso(null, true, [$estudiante]);
        $recurso = $this->enCurso($this->recurso('published'), $curso);

        $this->assertTrue($this->permite($estudiante, 'view', $recurso));
    }

    /**
     * Sin matrícula en el curso, el recurso del curso no se ve.
     */
    public function test_el_estudiante_no_ve_recursos_de_cursos_donde_no_esta_matriculado(): void
    {
        $estudiante = $this->usuario('estudiante');
        $compañero = $this->usuario('estudiante');
        $curso = $this->curso(null, true, [$compañero]);
        $recurso = $this->enCurso($this->recurso('published'), $curso);

        $this->assertFalse($this->permite($estudiante, 'view', $recurso));
        $this->assertTrue($this->permite($compañero, 'view', $recurso));
    }

    /**
     * La matrícula en un curso inactivo no abre su material.
     */
    public function test_el_estudiante_no_ve_recursos_de_un_curso_inactivo(): void
    {
        $estudiante = $this->usuario('estudiante');
        $curso = $this->curso(null, false, [$estudiante]);
        $recurso = $this->enCurso($this->recurso('published'), $curso);

        $this->assertFalse($this->permite($estudiante, 'view', $recurso));
    }

    /**
     * Basta un curso activo con matrícula entre varios cursos del recurso.
     */
    public function test_un_recurso_en_varios_cursos_se_ve_si_alguno_es_activo_y_matriculado(): void
    {
        $estudiante = $this->usuario('estudiante');
        $inactivo = $this->curso(null, false, [$estudiante]);
        $ajeno = $this->curso();
        $activo = $this->curso(null, true, [$estudiante]);

        $recurso = $this->enCurso($this->recurso('published'), $inactivo);
        $this->enCurso($recurso, $ajeno);
        $this->assertFalse($this->permite($estudiante, 'view', $recurso));

        $this->enCurso($recurso, $activo);
        $this->assertTrue($this->permite($estudiante, 'view', $recurso));
    }

    /**
     * La matrícula no destapa borradores ni archivados.
     */
    public function test_el_estudiante_no_ve_borradores_ni_archivados_de_su_curso(): void
    {
        $estudiante = $this->usuario('estudiante');
        $curso = $this->curso(null, true, [$estudiante]);
        $autor = $this->usuario('guia');

        foreach (['draft', 'archived'] as $estado) {
            $recurso = $this->enCurso($this->recurso($estado, $autor), $curso);
            $this->assertFalse($this->permite($estudiante, 'view', $recurso), $estado);
        }
    }

    /**
     * La guía ve el material publicado de los cursos que dicta (activos o no).
     */
    public function test_la_guia_ve_recursos_publicados_de_los_cursos_que_dicta(): void
    {
        $docente = $this->usuario('guia');
        $autor = $this->usuario('guia');

        foreach ([true, false] as $activo) {
            $curso = $this->curso($docente, $activo);
            $recurso = $this->enCurso($this->recurso('published', $autor), $curso);

            $this->assertTrue($this->permite($docente, 'view', $recurso), $activo ? 'activo' : 'inactivo');
        }
    }

    /**
     * Otra guía no ve el material de un curso que no dicta.
     */
    public function test_la_guia_no_ve_recursos_de_cursos_ajenos(): void
    {
        $docente = $this->usuario('guia');
        $otra = $this->usuario('guia');
        $recurso = $this->enCurso($this->recurso('published'), $this->curso($docente));

        $this->assertTrue($this->permite($docente, 'view', $recurso));
        $this->assertFalse($this->permite($otra, 'view', $recurso));
    }

    /**
     * Estar matriculada no da acceso a una guía: solo cuenta si dicta el curso.
     */
    public function test_la_matricula_solo_da_acceso_al_rol_estudiante(): void
    {
        $guia = $this->usuario('guia');
        $curso = $this->curso(null, true, [$guia]);
        $recurso = $this->enCurso($this->recurso('published'), $curso);

        $this->assertFalse($this->permite($guia, 'view', $recurso));
    }

    /**
     * Familia, cuentas inactivas y roles desconocidos quedan fuera del todo.
     */
    public function test_familia_y_cuentas_inactivas_no_ven_ningun_recurso(): void
    {
        $usuarios = $this->plantel();
        $general = $this->recurso('published');
        $deCurso = $this->enCurso($this->recurso('published'), $this->curso(null, true, [$usuarios['estudiante inactivo']]));

        foreach (['familia', 'administrador inactivo', 'guia inactiva', 'estudiante inactivo', 'rol desconocido'] as $quien) {
            $this->assertFalse($this->permite($usuarios[$quien], 'view', $general), "{$quien} / general");
            $this->assertFalse($this->permite($usuarios[$quien], 'view', $deCurso), "{$quien} / de curso");
        }
    }

    // ---------------------------------------------------------------
    // Recurso: permisos explícitos
    // ---------------------------------------------------------------

    /**
     * Un permiso por usuario abre un recurso publicado de un curso ajeno.
     */
    public function test_un_permiso_por_usuario_da_vista_de_un_recurso_de_curso_ajeno(): void
    {
        $estudiante = $this->usuario('estudiante');
        $otro = $this->usuario('estudiante');
        $recurso = $this->enCurso($this->recurso('published'), $this->curso());

        $this->assertFalse($this->permite($estudiante, 'view', $recurso));

        $this->permiso($recurso, $estudiante);

        $this->assertTrue($this->permite($estudiante, 'view', $recurso));
        $this->assertFalse($this->permite($otro, 'view', $recurso));
    }

    /**
     * Un permiso por rol vale para todos los usuarios de ese rol.
     */
    public function test_un_permiso_por_rol_da_vista_a_todo_ese_rol(): void
    {
        $estudiante = $this->usuario('estudiante');
        $otro = $this->usuario('estudiante');
        $guia = $this->usuario('guia');
        $recurso = $this->enCurso($this->recurso('published'), $this->curso());

        $this->permiso($recurso, User::ROLE_ESTUDIANTE);

        $this->assertTrue($this->permite($estudiante, 'view', $recurso));
        $this->assertTrue($this->permite($otro, 'view', $recurso));
        $this->assertFalse($this->permite($guia, 'view', $recurso));
    }

    /**
     * Las filas solo conceden: una fila con can_view en falso no quita ni da vista.
     */
    public function test_un_permiso_sin_can_view_no_da_vista(): void
    {
        $estudiante = $this->usuario('estudiante');
        $recurso = $this->enCurso($this->recurso('published'), $this->curso());

        $this->permiso($recurso, $estudiante, ver: false);

        $this->assertFalse($this->permite($estudiante, 'view', $recurso));
    }

    /**
     * Un permiso de otro rol o de otro recurso no aplica.
     */
    public function test_un_permiso_para_otro_usuario_rol_o_recurso_no_aplica(): void
    {
        $estudiante = $this->usuario('estudiante');
        $curso = $this->curso();
        $recurso = $this->enCurso($this->recurso('published'), $curso);
        $otroRecurso = $this->enCurso($this->recurso('published'), $curso);

        $this->permiso($recurso, User::ROLE_GUIA);
        $this->permiso($recurso, $this->usuario('estudiante'));
        $this->permiso($otroRecurso, $estudiante);

        $this->assertFalse($this->permite($estudiante, 'view', $recurso));
        $this->assertTrue($this->permite($estudiante, 'view', $otroRecurso));
    }

    /**
     * Un permiso explícito no publica lo que está en borrador o archivado.
     */
    public function test_un_permiso_no_expone_borradores_ni_archivados(): void
    {
        $estudiante = $this->usuario('estudiante');
        $curso = $this->curso();

        foreach (['draft', 'archived'] as $estado) {
            $recurso = $this->enCurso($this->recurso($estado), $curso);
            $this->permiso($recurso, $estudiante);

            $this->assertFalse($this->permite($estudiante, 'view', $recurso), $estado);
        }
    }

    /**
     * Un permiso por rol no abre la Biblioteca a la familia ni a cuentas inactivas.
     */
    public function test_un_permiso_no_abre_la_biblioteca_a_familia_ni_a_inactivos(): void
    {
        $familia = $this->usuario('familia');
        $inactivo = $this->usuario('estudiante', ['active' => false]);
        $recurso = $this->enCurso($this->recurso('published'), $this->curso());

        $this->permiso($recurso, User::ROLE_FAMILIA);
        $this->permiso($recurso, User::ROLE_ESTUDIANTE);
        $this->permiso($recurso, $inactivo);

        $this->assertFalse($this->permite($familia, 'view', $recurso));
        $this->assertFalse($this->permite($inactivo, 'view', $recurso));
    }

    // ---------------------------------------------------------------
    // Recurso: crear, editar, borrar y acciones editoriales
    // ---------------------------------------------------------------

    /**
     * Crear recursos: personal y guías.
     */
    public function test_crear_recursos_es_para_personal_y_guias_activos(): void
    {
        $this->assertMatriz([
            'administrador' => true,
            'coordinacion' => true,
            'guia' => true,
            'estudiante' => false,
            'familia' => false,
            'administrador inactivo' => false,
            'guia inactiva' => false,
            'estudiante inactivo' => false,
            'rol desconocido' => false,
        ], 'create', LibraryResource::class, $this->plantel());
    }

    /**
     * Editar, archivar, publicar y duplicar: personal, o la guía que lo creó.
     */
    public function test_editar_archivar_publicar_y_duplicar_son_del_personal_o_de_la_guia_creadora(): void
    {
        $usuarios = $this->plantel();
        $recurso = $this->recurso('draft', $usuarios['guia']);
        $otraGuia = $this->usuario('guia');
        $usuarios['otra guia'] = $otraGuia;

        $esperado = [
            'administrador' => true,
            'coordinacion' => true,
            'guia' => true,
            'otra guia' => false,
            'estudiante' => false,
            'familia' => false,
            'administrador inactivo' => false,
            'guia inactiva' => false,
            'estudiante inactivo' => false,
            'rol desconocido' => false,
        ];

        foreach (['update', 'archive', 'publish', 'duplicate'] as $habilidad) {
            $this->assertMatriz($esperado, $habilidad, $recurso, $usuarios);
        }
    }

    /**
     * La guía no toca lo que creó el personal ni lo de un recurso sin autor.
     */
    public function test_la_guia_no_edita_recursos_del_personal_ni_sin_autor(): void
    {
        $guia = $this->usuario('guia');
        $delPersonal = $this->recurso('published', $this->usuario('administrador'));
        $sinAutor = $this->recurso('published');

        foreach (['update', 'archive', 'publish', 'duplicate'] as $habilidad) {
            $this->assertFalse($this->permite($guia, $habilidad, $delPersonal), "{$habilidad} / del personal");
            $this->assertFalse($this->permite($guia, $habilidad, $sinAutor), "{$habilidad} / sin autor");
        }
    }

    /**
     * Un estudiante que figura como creador igual no edita.
     */
    public function test_el_estudiante_creador_no_puede_editar_su_recurso(): void
    {
        $estudiante = $this->usuario('estudiante');
        $recurso = $this->recurso('draft', $estudiante);

        foreach (['update', 'archive', 'publish', 'duplicate', 'delete'] as $habilidad) {
            $this->assertFalse($this->permite($estudiante, $habilidad, $recurso), $habilidad);
        }
    }

    /**
     * Borrar es solo del personal; la guía creadora puede archivar, no borrar.
     */
    public function test_borrar_es_solo_del_personal(): void
    {
        $usuarios = $this->plantel();
        $recurso = $this->recurso('draft', $usuarios['guia']);

        $this->assertMatriz([
            'administrador' => true,
            'coordinacion' => true,
            'guia' => false,
            'estudiante' => false,
            'familia' => false,
            'administrador inactivo' => false,
            'guia inactiva' => false,
            'estudiante inactivo' => false,
            'rol desconocido' => false,
        ], 'delete', $recurso, $usuarios);
    }

    // ---------------------------------------------------------------
    // Recurso: descargar
    // ---------------------------------------------------------------

    /**
     * Sin archivo guardado no hay nada que descargar, ni para el personal.
     */
    public function test_descargar_exige_un_archivo_guardado(): void
    {
        $admin = $this->usuario('administrador');

        $sinArchivo = $this->recurso('published', null, ['is_downloadable' => true]);
        $this->assertFalse($this->permite($admin, 'download', $sinArchivo));

        $this->conArchivo($sinArchivo);
        $this->assertTrue($this->permite($admin, 'download', $sinArchivo));
    }

    /**
     * Los enlaces y los videos por URL sin archivo nunca se descargan.
     */
    public function test_los_enlaces_y_videos_por_url_nunca_se_descargan(): void
    {
        $admin = $this->usuario('administrador');
        $estudiante = $this->usuario('estudiante');

        $enlace = $this->recurso('published', null, [
            'type' => ResourceType::Link,
            'external_url' => 'https://example.com/guia',
            'is_downloadable' => true,
        ]);
        $video = $this->recurso('published', null, [
            'type' => ResourceType::Video,
            'external_url' => 'https://example.com/video',
            'is_downloadable' => true,
        ]);

        foreach ([$enlace, $video] as $recurso) {
            $this->assertTrue($this->permite($estudiante, 'view', $recurso));
            $this->assertFalse($this->permite($estudiante, 'download', $recurso));
            $this->assertFalse($this->permite($admin, 'download', $recurso));
        }

        // Un video con archivo guardado sí se descarga.
        $this->conArchivo($video);
        $this->assertTrue($this->permite($estudiante, 'download', $video));
    }

    /**
     * Un enlace no es descargable aunque haya una fila de archivo suelta.
     */
    public function test_un_enlace_con_una_fila_de_archivo_suelta_sigue_sin_descargarse(): void
    {
        $admin = $this->usuario('administrador');
        $enlace = $this->conArchivo($this->recurso('published', null, [
            'type' => ResourceType::Link,
            'external_url' => 'https://example.com/guia',
            'is_downloadable' => true,
        ]));

        $this->assertFalse($this->permite($admin, 'download', $enlace));
    }

    /**
     * Sin is_downloadable ni permiso, solo el personal y el creador descargan.
     */
    public function test_el_recurso_no_descargable_solo_lo_descargan_el_personal_y_el_creador(): void
    {
        $usuarios = $this->plantel();
        $recurso = $this->conArchivo($this->recurso('published', $usuarios['guia'], ['is_downloadable' => false]));
        $usuarios['otra guia'] = $this->usuario('guia');

        $this->assertMatriz([
            'administrador' => true,
            'coordinacion' => true,
            'guia' => true,
            'otra guia' => false,
            'estudiante' => false,
            'familia' => false,
            'administrador inactivo' => false,
            'guia inactiva' => false,
            'estudiante inactivo' => false,
            'rol desconocido' => false,
        ], 'download', $recurso, $usuarios);
    }

    /**
     * Con is_downloadable, descarga todo el que puede ver el recurso.
     */
    public function test_el_recurso_descargable_lo_descarga_quien_puede_verlo(): void
    {
        $usuarios = $this->plantel();
        $usuarios['otra guia'] = $this->usuario('guia');
        $recurso = $this->conArchivo($this->recurso('published', $usuarios['guia'], ['is_downloadable' => true]));

        $this->assertMatriz([
            'administrador' => true,
            'coordinacion' => true,
            'guia' => true,
            'otra guia' => true,
            'estudiante' => true,
            'familia' => false,
            'administrador inactivo' => false,
            'guia inactiva' => false,
            'estudiante inactivo' => false,
            'rol desconocido' => false,
        ], 'download', $recurso, $usuarios);
    }

    /**
     * Descargar presupone poder ver: un descargable fuera de alcance se niega.
     */
    public function test_descargar_exige_poder_ver_el_recurso(): void
    {
        $estudiante = $this->usuario('estudiante');
        $autor = $this->usuario('guia');

        $borrador = $this->conArchivo($this->recurso('draft', $autor, ['is_downloadable' => true]));
        $deCursoAjeno = $this->conArchivo($this->enCurso(
            $this->recurso('published', $autor, ['is_downloadable' => true]),
            $this->curso(),
        ));

        $this->assertFalse($this->permite($estudiante, 'download', $borrador));
        $this->assertFalse($this->permite($estudiante, 'download', $deCursoAjeno));
    }

    /**
     * Un permiso con can_download habilita la descarga de un recurso no descargable.
     */
    public function test_un_permiso_con_can_download_habilita_la_descarga(): void
    {
        $estudiante = $this->usuario('estudiante');
        $otro = $this->usuario('estudiante');
        $guia = $this->usuario('guia');
        $recurso = $this->conArchivo($this->recurso('published', null, ['is_downloadable' => false]));

        $this->assertFalse($this->permite($estudiante, 'download', $recurso));

        $this->permiso($recurso, $estudiante, ver: true, descargar: true);
        $this->assertTrue($this->permite($estudiante, 'download', $recurso));
        $this->assertFalse($this->permite($otro, 'download', $recurso));

        $this->permiso($recurso, User::ROLE_GUIA, ver: true, descargar: true);
        $this->assertTrue($this->permite($guia, 'download', $recurso));
    }

    /**
     * Un permiso de solo vista no da descarga.
     */
    public function test_un_permiso_de_solo_vista_no_da_descarga(): void
    {
        $estudiante = $this->usuario('estudiante');
        $recurso = $this->conArchivo($this->enCurso($this->recurso('published'), $this->curso()));
        $this->permiso($recurso, $estudiante, ver: true, descargar: false);

        $this->assertTrue($this->permite($estudiante, 'view', $recurso));
        $this->assertFalse($this->permite($estudiante, 'download', $recurso));
    }

    /**
     * can_download sin poder ver el recurso (borrador) no alcanza.
     */
    public function test_un_permiso_can_download_no_abre_un_borrador(): void
    {
        $estudiante = $this->usuario('estudiante');
        $recurso = $this->conArchivo($this->recurso('draft'));
        $this->permiso($recurso, $estudiante, ver: true, descargar: true);

        $this->assertFalse($this->permite($estudiante, 'download', $recurso));
    }

    // ---------------------------------------------------------------
    // Categorías
    // ---------------------------------------------------------------

    /**
     * Cualquier usuario de la Biblioteca ve las categorías; solo el personal las gestiona.
     */
    public function test_las_categorias_se_ven_en_la_biblioteca_y_las_gestiona_el_personal(): void
    {
        $usuarios = $this->plantel();
        $categoria = ResourceCategory::factory()->create();

        $verla = [
            'administrador' => true,
            'coordinacion' => true,
            'guia' => true,
            'estudiante' => true,
            'familia' => false,
            'administrador inactivo' => false,
            'guia inactiva' => false,
            'estudiante inactivo' => false,
            'rol desconocido' => false,
        ];
        $gestionarla = [
            'administrador' => true,
            'coordinacion' => true,
            'guia' => false,
            'estudiante' => false,
            'familia' => false,
            'administrador inactivo' => false,
            'guia inactiva' => false,
            'estudiante inactivo' => false,
            'rol desconocido' => false,
        ];

        $this->assertMatriz($verla, 'view', $categoria, $usuarios);

        foreach (['update', 'delete'] as $habilidad) {
            $this->assertMatriz($gestionarla, $habilidad, $categoria, $usuarios);
        }

        $this->assertMatriz($gestionarla, 'create', ResourceCategory::class, $usuarios);
    }

    // ---------------------------------------------------------------
    // Cursos
    // ---------------------------------------------------------------

    /**
     * Ver un curso: personal, su docente y los estudiantes matriculados mientras esté activo.
     */
    public function test_ver_un_curso_es_del_personal_su_docente_y_estudiantes_matriculados(): void
    {
        $usuarios = $this->plantel();
        $docente = $usuarios['guia'];
        $usuarios['otra guia'] = $this->usuario('guia');
        $usuarios['no matriculado'] = $this->usuario('estudiante');

        $curso = $this->curso($docente, true, [$usuarios['estudiante'], $usuarios['estudiante inactivo']]);

        $this->assertMatriz([
            'administrador' => true,
            'coordinacion' => true,
            'guia' => true,
            'otra guia' => false,
            'estudiante' => true,
            'no matriculado' => false,
            'familia' => false,
            'administrador inactivo' => false,
            'guia inactiva' => false,
            'estudiante inactivo' => false,
            'rol desconocido' => false,
        ], 'view', $curso, $usuarios);
    }

    /**
     * Con el curso inactivo, el estudiante pierde la vista; docente y personal la conservan.
     */
    public function test_un_curso_inactivo_solo_lo_ven_el_personal_y_su_docente(): void
    {
        $docente = $this->usuario('guia');
        $estudiante = $this->usuario('estudiante');
        $curso = $this->curso($docente, false, [$estudiante]);

        $this->assertTrue($this->permite($this->usuario('administrador'), 'view', $curso));
        $this->assertTrue($this->permite($docente, 'view', $curso));
        $this->assertFalse($this->permite($estudiante, 'view', $curso));
    }

    /**
     * Crear, editar y borrar cursos es del personal, no de su docente.
     */
    public function test_crear_editar_y_borrar_cursos_es_solo_del_personal(): void
    {
        $usuarios = $this->plantel();
        $curso = $this->curso($usuarios['guia'], true, [$usuarios['estudiante']]);
        $esperado = [
            'administrador' => true,
            'coordinacion' => true,
            'guia' => false,
            'estudiante' => false,
            'familia' => false,
            'administrador inactivo' => false,
            'guia inactiva' => false,
            'estudiante inactivo' => false,
            'rol desconocido' => false,
        ];

        $this->assertMatriz($esperado, 'create', Course::class, $usuarios);
        $this->assertMatriz($esperado, 'update', $curso, $usuarios);
        $this->assertMatriz($esperado, 'delete', $curso, $usuarios);
    }

    /**
     * Gestionar los recursos de un curso: personal o la guía que lo dicta.
     */
    public function test_gestionar_recursos_de_un_curso_es_del_personal_o_de_su_docente(): void
    {
        $usuarios = $this->plantel();
        $usuarios['otra guia'] = $this->usuario('guia');
        $curso = $this->curso($usuarios['guia'], true, [$usuarios['estudiante']]);

        $this->assertMatriz([
            'administrador' => true,
            'coordinacion' => true,
            'guia' => true,
            'otra guia' => false,
            'estudiante' => false,
            'familia' => false,
            'administrador inactivo' => false,
            'guia inactiva' => false,
            'estudiante inactivo' => false,
            'rol desconocido' => false,
        ], 'manageResources', $curso, $usuarios);
    }

    /**
     * Un curso sin docente no le da gestión a nadie por accidente.
     */
    public function test_un_curso_sin_docente_no_da_gestion_ni_vista_a_las_guias(): void
    {
        $guia = $this->usuario('guia');
        $curso = $this->curso();

        $this->assertFalse($this->permite($guia, 'manageResources', $curso));
        $this->assertFalse($this->permite($guia, 'view', $curso));
    }

    // ---------------------------------------------------------------
    // Módulos
    // ---------------------------------------------------------------

    /**
     * Ver un módulo sigue la misma regla que ver su curso.
     */
    public function test_ver_un_modulo_sigue_la_regla_de_su_curso(): void
    {
        $usuarios = $this->plantel();
        $usuarios['otra guia'] = $this->usuario('guia');
        $usuarios['no matriculado'] = $this->usuario('estudiante');
        $curso = $this->curso($usuarios['guia'], true, [$usuarios['estudiante']]);
        $modulo = CourseModule::factory()->create(['course_id' => $curso->id]);

        $esperado = [
            'administrador' => true,
            'coordinacion' => true,
            'guia' => true,
            'otra guia' => false,
            'estudiante' => true,
            'no matriculado' => false,
            'familia' => false,
            'administrador inactivo' => false,
            'estudiante inactivo' => false,
            'rol desconocido' => false,
        ];

        $this->assertMatriz($esperado, 'view', $modulo, $usuarios);

        // Con el curso inactivo el estudiante pierde la vista del módulo.
        $curso->update(['is_active' => false]);
        $this->assertFalse($this->permite($usuarios['estudiante'], 'view', $modulo->fresh()));
        $this->assertTrue($this->permite($usuarios['guia'], 'view', $modulo->fresh()));
    }

    /**
     * Crear, editar y borrar módulos es del personal.
     */
    public function test_crear_editar_y_borrar_modulos_es_solo_del_personal(): void
    {
        $usuarios = $this->plantel();
        $curso = $this->curso($usuarios['guia'], true, [$usuarios['estudiante']]);
        $modulo = CourseModule::factory()->create(['course_id' => $curso->id]);
        $esperado = [
            'administrador' => true,
            'coordinacion' => true,
            'guia' => false,
            'estudiante' => false,
            'familia' => false,
            'administrador inactivo' => false,
            'guia inactiva' => false,
            'estudiante inactivo' => false,
            'rol desconocido' => false,
        ];

        $this->assertMatriz($esperado, 'create', CourseModule::class, $usuarios);
        $this->assertMatriz($esperado, 'update', $modulo, $usuarios);
        $this->assertMatriz($esperado, 'delete', $modulo, $usuarios);
    }

    // ---------------------------------------------------------------
    // Ayudantes de los modelos
    // ---------------------------------------------------------------

    /**
     * Un recurso es general mientras no esté en ningún curso.
     */
    public function test_un_recurso_es_general_si_no_esta_en_ningun_curso(): void
    {
        $recurso = $this->recurso();
        $this->assertTrue($recurso->isGeneral());

        $this->enCurso($recurso, $this->curso());
        $this->assertFalse($recurso->isGeneral());
    }

    /**
     * El dueño es quien lo creó; sin autor no es de nadie.
     */
    public function test_el_recurso_sabe_si_es_de_un_usuario(): void
    {
        $autor = $this->usuario('guia');
        $otro = $this->usuario('guia');

        $propio = $this->recurso('draft', $autor);
        $this->assertTrue($propio->isOwnedBy($autor));
        $this->assertFalse($propio->isOwnedBy($otro));
        $this->assertFalse($this->recurso()->isOwnedBy($autor));
    }

    /**
     * Solo hay archivo descargable si el tipo usa archivo y hay una fila guardada.
     */
    public function test_el_recurso_sabe_si_tiene_un_archivo_descargable(): void
    {
        $pdf = $this->recurso('published');
        $this->assertFalse($pdf->hasDownloadableFile());

        $this->conArchivo($pdf);
        $this->assertTrue($pdf->hasDownloadableFile());

        $enlace = $this->conArchivo($this->recurso('published', null, [
            'type' => ResourceType::Link,
            'external_url' => 'https://example.com',
        ]));
        $this->assertFalse($enlace->hasDownloadableFile());
    }

    /**
     * El usuario sabe si dicta o cursa un curso.
     */
    public function test_el_usuario_sabe_si_dicta_o_esta_matriculado_en_un_curso(): void
    {
        $docente = $this->usuario('guia');
        $estudiante = $this->usuario('estudiante');
        $curso = $this->curso($docente, true, [$estudiante]);
        $sinDocente = $this->curso();

        $this->assertTrue($docente->teachesCourse($curso));
        $this->assertFalse($estudiante->teachesCourse($curso));
        $this->assertFalse($docente->teachesCourse($sinDocente));

        $this->assertTrue($estudiante->isEnrolledIn($curso));
        $this->assertFalse($docente->isEnrolledIn($curso));
        $this->assertFalse($estudiante->isEnrolledIn($sinDocente));
    }

    // ---------------------------------------------------------------
    // Scope visibleTo de los recursos
    // ---------------------------------------------------------------

    /**
     * La política `view` y el scope `visibleTo` deben dar siempre el mismo
     * resultado: se cruzan usuarios y recursos de toda la matriz de reglas.
     */
    public function test_visible_to_coincide_con_la_politica_view_en_toda_la_matriz(): void
    {
        $administrador = $this->usuario('administrador');
        $coordinacion = $this->usuario('coordinacion');
        $duena = $this->usuario('guia');
        $docente = $this->usuario('guia');
        $otraGuia = $this->usuario('guia');
        $matriculado = $this->usuario('estudiante');
        $sinCurso = $this->usuario('estudiante');
        $soloCursoInactivo = $this->usuario('estudiante');
        $conPermiso = $this->usuario('estudiante');
        $familia = $this->usuario('familia');
        $usuarios = [
            $administrador, $coordinacion, $duena, $docente, $otraGuia, $matriculado, $sinCurso,
            $soloCursoInactivo, $conPermiso, $familia,
            $this->usuario('administrador', ['active' => false]),
            $this->usuario('guia', ['active' => false]),
            $this->usuario('estudiante', ['active' => false]),
            User::factory()->create(['role' => 'invitado', 'family_id' => null]),
        ];

        $cursoA = $this->curso($docente, true, [$matriculado]);
        $cursoB = $this->curso($docente, false, [$soloCursoInactivo, $matriculado]);
        $cursoC = $this->curso($otraGuia, true);
        $categoria = ResourceCategory::factory()->create();

        $colocaciones = [
            'general' => [],
            'curso A' => [$cursoA],
            'curso B inactivo' => [$cursoB],
            'curso C' => [$cursoC],
            'cursos B y C' => [$cursoB, $cursoC],
        ];

        $recursos = collect();
        foreach (['draft', 'published', 'archived'] as $estado) {
            foreach ($colocaciones as $cursos) {
                foreach ([$administrador, $duena] as $autor) {
                    $recurso = $this->recurso($estado, $autor, ['category_id' => $categoria->id]);
                    foreach ($cursos as $curso) {
                        $this->enCurso($recurso, $curso);
                    }
                    $recursos->push($recurso);
                }
            }
        }

        // Permisos explícitos sobre recursos del curso C (ajeno para los estudiantes).
        foreach ([
            fn (LibraryResource $r) => $this->permiso($r, $conPermiso),
            fn (LibraryResource $r) => $this->permiso($r, User::ROLE_ESTUDIANTE),
            fn (LibraryResource $r) => $this->permiso($r, User::ROLE_GUIA),
            fn (LibraryResource $r) => $this->permiso($r, User::ROLE_FAMILIA),
            fn (LibraryResource $r) => $this->permiso($r, $conPermiso, ver: false),
        ] as $conceder) {
            $recurso = $this->enCurso($this->recurso('published', $administrador, ['category_id' => $categoria->id]), $cursoC);
            $conceder($recurso);
            $recursos->push($recurso);
        }
        $borradorConPermiso = $this->enCurso($this->recurso('draft', $administrador, ['category_id' => $categoria->id]), $cursoC);
        $this->permiso($borradorConPermiso, $conPermiso);
        $recursos->push($borradorConPermiso);

        // Un borrador de un curso ajeno, creado por un estudiante: solo él lo ve.
        $propioDeEstudiante = $this->enCurso($this->recurso('draft', $sinCurso, ['category_id' => $categoria->id]), $cursoC);
        $recursos->push($propioDeEstudiante);

        foreach ($usuarios as $usuario) {
            $esperado = $recursos
                ->filter(fn (LibraryResource $r) => $this->permite($usuario, 'view', $r))
                ->pluck('id')->sort()->values()->all();
            $real = LibraryResource::visibleTo($usuario)->pluck('id')->sort()->values()->all();

            $this->assertSame($esperado, $real, "visibleTo no coincide con view para {$usuario->role} #{$usuario->id}");
        }

        // La matriz no es trivial: cada perfil ve algo distinto.
        $total = $recursos->count();
        $this->assertCount($total, LibraryResource::visibleTo($administrador)->pluck('id'));
        $this->assertCount($total, LibraryResource::visibleTo($coordinacion)->pluck('id'));
        $this->assertCount(0, LibraryResource::visibleTo($familia)->pluck('id'));
        $this->assertGreaterThan(0, LibraryResource::visibleTo($matriculado)->count());
        $this->assertLessThan($total, LibraryResource::visibleTo($matriculado)->count());
        $this->assertGreaterThan(LibraryResource::visibleTo($sinCurso)->count(), LibraryResource::visibleTo($matriculado)->count());
        $this->assertContains($propioDeEstudiante->id, LibraryResource::visibleTo($sinCurso)->pluck('id')->all());
    }

    /**
     * Las condiciones de acceso van agrupadas: combinarlas con otros filtros no filtra de más.
     */
    public function test_visible_to_no_filtra_recursos_al_combinarlo_con_otros_filtros(): void
    {
        $estudiante = $this->usuario('estudiante');
        $autor = $this->usuario('guia');
        $categoria = ResourceCategory::factory()->create();

        $propioVideo = $this->recurso('draft', $estudiante, ['type' => ResourceType::Video, 'external_url' => 'https://example.com/v']);
        $generalPdf = $this->recurso('published', $autor, ['category_id' => $categoria->id]);
        $generalVideo = $this->recurso('published', $autor, ['type' => ResourceType::Video, 'external_url' => 'https://example.com/g']);
        $borradorAjeno = $this->recurso('draft', $autor, ['category_id' => $categoria->id]);
        $cursoAjenoPdf = $this->enCurso($this->recurso('published', $autor, ['category_id' => $categoria->id]), $this->curso());

        $this->assertEqualsCanonicalizing(
            [$generalPdf->id],
            LibraryResource::visibleTo($estudiante)->where('category_id', $categoria->id)->pluck('id')->all(),
        );
        $this->assertEqualsCanonicalizing(
            [$propioVideo->id, $generalVideo->id],
            LibraryResource::visibleTo($estudiante)->ofType(ResourceType::Video)->pluck('id')->all(),
        );
        $this->assertSame(
            [],
            LibraryResource::visibleTo($estudiante)->whereKey([$borradorAjeno->id, $cursoAjenoPdf->id])->pluck('id')->all(),
        );
        $this->assertSame(
            [$generalPdf->id],
            LibraryResource::visibleTo($estudiante)->published()->where('category_id', $categoria->id)->pluck('id')->all(),
        );
    }

    /**
     * Quien no puede usar la Biblioteca recibe una consulta vacía, no todo el catálogo.
     */
    public function test_visible_to_devuelve_vacio_para_quien_no_usa_la_biblioteca(): void
    {
        $this->recurso('published');
        $usuarios = $this->plantel();

        foreach (['familia', 'administrador inactivo', 'guia inactiva', 'estudiante inactivo', 'rol desconocido'] as $quien) {
            $this->assertSame(0, LibraryResource::visibleTo($usuarios[$quien])->count(), $quien);
        }
    }

    /**
     * El personal no recibe restricciones: ve todos los estados.
     */
    public function test_visible_to_no_restringe_al_personal(): void
    {
        foreach (['draft', 'published', 'archived'] as $estado) {
            $this->recurso($estado);
        }

        $this->assertSame(3, LibraryResource::visibleTo($this->usuario('administrador'))->count());
        $this->assertSame(3, LibraryResource::visibleTo($this->usuario('coordinacion'))->count());
    }

    // ---------------------------------------------------------------
    // Scope visibleTo de los cursos
    // ---------------------------------------------------------------

    /**
     * El personal ve todos los cursos; la guía, los suyos; el estudiante, los activos donde cursa.
     */
    public function test_los_cursos_visibles_dependen_del_rol(): void
    {
        $docente = $this->usuario('guia');
        $otraGuia = $this->usuario('guia');
        $estudiante = $this->usuario('estudiante');

        $suyo = $this->curso($docente, true, [$estudiante]);
        $suyoInactivo = $this->curso($docente, false, [$estudiante]);
        $ajeno = $this->curso($otraGuia);
        $sinDocente = $this->curso();

        $this->assertEqualsCanonicalizing(
            [$suyo->id, $suyoInactivo->id, $ajeno->id, $sinDocente->id],
            Course::visibleTo($this->usuario('administrador'))->pluck('id')->all(),
        );
        $this->assertCount(4, Course::visibleTo($this->usuario('coordinacion'))->pluck('id'));
        $this->assertEqualsCanonicalizing([$suyo->id, $suyoInactivo->id], Course::visibleTo($docente)->pluck('id')->all());
        $this->assertSame([$suyo->id], Course::visibleTo($estudiante)->pluck('id')->all());
    }

    /**
     * Familia, cuentas inactivas y roles desconocidos no ven ningún curso.
     */
    public function test_los_cursos_visibles_estan_vacios_para_quien_no_usa_la_biblioteca(): void
    {
        $this->curso($this->usuario('guia'), true, [$this->usuario('estudiante')]);
        $usuarios = $this->plantel();

        foreach (['familia', 'administrador inactivo', 'guia inactiva', 'estudiante inactivo', 'rol desconocido'] as $quien) {
            $this->assertSame(0, Course::visibleTo($usuarios[$quien])->count(), $quien);
        }
    }

    /**
     * El scope de cursos y la política `view` deben coincidir.
     */
    public function test_course_visible_to_coincide_con_la_politica_view(): void
    {
        $docente = $this->usuario('guia');
        $otraGuia = $this->usuario('guia');
        $matriculado = $this->usuario('estudiante');
        $ajeno = $this->usuario('estudiante');
        $usuarios = [
            $this->usuario('administrador'), $this->usuario('coordinacion'), $docente, $otraGuia, $matriculado, $ajeno,
            $this->usuario('familia'),
            $this->usuario('guia', ['active' => false]),
            $this->usuario('estudiante', ['active' => false]),
            User::factory()->create(['role' => 'invitado', 'family_id' => null]),
        ];

        $cursos = [
            $this->curso($docente, true, [$matriculado]),
            $this->curso($docente, false, [$matriculado]),
            $this->curso($otraGuia, true, [$ajeno]),
            $this->curso(null, true),
            $this->curso(null, false),
        ];

        foreach ($usuarios as $usuario) {
            $esperado = collect($cursos)
                ->filter(fn (Course $c) => $this->permite($usuario, 'view', $c))
                ->pluck('id')->sort()->values()->all();
            $real = Course::visibleTo($usuario)->pluck('id')->sort()->values()->all();

            $this->assertSame($esperado, $real, "Course::visibleTo no coincide con view para {$usuario->role} #{$usuario->id}");
        }
    }

    // ---------------------------------------------------------------
    // Registro de accesos
    // ---------------------------------------------------------------

    /**
     * La ventana por defecto para no repetir consultas es de 30 minutos.
     */
    public function test_la_ventana_de_consultas_por_defecto_es_de_30_minutos(): void
    {
        $this->assertSame(30, config('biblioteca.view_log_window_minutes'));
    }

    /**
     * Una consulta repetida dentro de la ventana no genera otro registro.
     */
    public function test_consultar_dos_veces_seguidas_registra_una_sola_vez(): void
    {
        $logger = app(ResourceAccessLogger::class);
        $usuario = $this->usuario('estudiante');
        $recurso = $this->recurso();

        $primero = $logger->viewed($recurso, $usuario);
        $segundo = $logger->viewed($recurso, $usuario);

        $this->assertInstanceOf(ResourceAccessLog::class, $primero);
        $this->assertSame(ResourceAccessLog::ACTION_VIEWED, $primero->action);
        $this->assertSame($usuario->id, $primero->user_id);
        $this->assertSame($recurso->id, $primero->resource_id);
        $this->assertNull($segundo);
        $this->assertSame(1, ResourceAccessLog::count());
    }

    /**
     * Pasada la ventana, la misma consulta vuelve a registrarse.
     */
    public function test_la_consulta_se_registra_otra_vez_al_terminar_la_ventana(): void
    {
        $logger = app(ResourceAccessLogger::class);
        $usuario = $this->usuario('estudiante');
        $recurso = $this->recurso();

        $logger->viewed($recurso, $usuario);

        $this->travel(29)->minutes();
        $this->assertNull($logger->viewed($recurso, $usuario));

        $this->travel(2)->minutes();
        $this->assertNotNull($logger->viewed($recurso, $usuario));

        $this->assertSame(2, ResourceAccessLog::count());
    }

    /**
     * La deduplicación es por usuario y por recurso.
     */
    public function test_la_deduplicacion_es_por_usuario_y_por_recurso(): void
    {
        $logger = app(ResourceAccessLogger::class);
        $ana = $this->usuario('estudiante');
        $beto = $this->usuario('estudiante');
        $uno = $this->recurso();
        $dos = $this->recurso();

        $this->assertNotNull($logger->viewed($uno, $ana));
        $this->assertNotNull($logger->viewed($uno, $beto));
        $this->assertNotNull($logger->viewed($dos, $ana));

        $this->assertSame(3, ResourceAccessLog::count());
    }

    /**
     * La ventana sale de la configuración.
     */
    public function test_la_ventana_de_consultas_se_lee_de_la_configuracion(): void
    {
        $logger = app(ResourceAccessLogger::class);
        $usuario = $this->usuario('estudiante');
        $recurso = $this->recurso();

        config(['biblioteca.view_log_window_minutes' => 5]);
        $logger->viewed($recurso, $usuario);

        $this->travel(4)->minutes();
        $this->assertNull($logger->viewed($recurso, $usuario));

        $this->travel(2)->minutes();
        $this->assertNotNull($logger->viewed($recurso, $usuario));

        // Con la ventana en 0 cada consulta cuenta.
        config(['biblioteca.view_log_window_minutes' => 0]);
        $this->assertNotNull($logger->viewed($recurso, $usuario));
        $this->assertNotNull($logger->viewed($recurso, $usuario));
    }

    /**
     * Un visitante sin sesión no deja registro de consulta.
     */
    public function test_una_consulta_de_un_visitante_no_se_registra(): void
    {
        $this->assertNull(app(ResourceAccessLogger::class)->viewed($this->recurso(), null));
        $this->assertSame(0, ResourceAccessLog::count());
    }

    /**
     * Cada descarga queda registrada, sin deduplicar.
     */
    public function test_cada_descarga_se_registra(): void
    {
        $logger = app(ResourceAccessLogger::class);
        $usuario = $this->usuario('estudiante');
        $recurso = $this->recurso();

        $primera = $logger->downloaded($recurso, $usuario);
        $segunda = $logger->downloaded($recurso, $usuario);

        $this->assertNotSame($primera->id, $segunda->id);
        $this->assertSame(ResourceAccessLog::ACTION_DOWNLOADED, $primera->action);
        $this->assertSame($usuario->id, $primera->user_id);
        $this->assertSame(2, ResourceAccessLog::where('action', ResourceAccessLog::ACTION_DOWNLOADED)->count());
    }

    /**
     * Una descarga sin sesión igual se anota, sin usuario.
     */
    public function test_una_descarga_sin_usuario_se_registra_sin_usuario(): void
    {
        $registro = app(ResourceAccessLogger::class)->downloaded($this->recurso(), null);

        $this->assertNull($registro->user_id);
        $this->assertSame(1, ResourceAccessLog::count());
    }

    /**
     * Consultar y descargar son acciones distintas: una no oculta a la otra.
     */
    public function test_descargar_y_consultar_no_se_deduplican_entre_si(): void
    {
        $logger = app(ResourceAccessLogger::class);
        $usuario = $this->usuario('estudiante');
        $recurso = $this->recurso();

        $logger->downloaded($recurso, $usuario);
        $this->assertNotNull($logger->viewed($recurso, $usuario));
        $logger->downloaded($recurso, $usuario);

        $this->assertSame(1, ResourceAccessLog::where('action', ResourceAccessLog::ACTION_VIEWED)->count());
        $this->assertSame(2, ResourceAccessLog::where('action', ResourceAccessLog::ACTION_DOWNLOADED)->count());
    }
}
