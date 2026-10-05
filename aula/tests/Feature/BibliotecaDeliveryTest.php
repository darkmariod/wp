<?php

namespace Tests\Feature;

use App\Enums\ResourceType;
use App\Models\Child;
use App\Models\Content;
use App\Models\Course;
use App\Models\Environment;
use App\Models\Evidence;
use App\Models\Family;
use App\Models\LibraryResource;
use App\Models\Media;
use App\Models\ResourceAccessLog;
use App\Models\ResourceFile;
use App\Models\ResourcePermission;
use App\Models\User;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use League\Flysystem\Filesystem as Flysystem;
use League\Flysystem\FilesystemAdapter as FlysystemAdapter;
use Mockery;
use Tests\TestCase;

/**
 * Entrega protegida de archivos de la Biblioteca (T05): `archivo` (en línea)
 * y `descargar` (adjunto). Quien conoce o adivina una URL nunca recibe un
 * archivo al que no tiene derecho, y la ruta de almacenamiento no se expone.
 * Se usan usuarios planos de la factory, nunca `super_admin`.
 */
class BibliotecaDeliveryTest extends TestCase
{
    use RefreshDatabase;

    private const PDF = "%PDF-1.4\n%contenido de prueba para la Biblioteca\n%%EOF\n";

    /** @var array<string, array{0: string, 1: string}> tipo => [extensión, tipo MIME guardado] */
    private const FORMATOS = [
        'pdf' => ['pdf', 'application/pdf'],
        'video' => ['mp4', 'video/mp4'],
        'audio' => ['mp3', 'audio/mpeg'],
        'image' => ['png', 'image/png'],
        'document' => ['docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
    ];

    // ---------------------------------------------------------------
    // Ayudantes
    // ---------------------------------------------------------------

    private function usuario(string $rol, array $atributos = []): User
    {
        return User::factory()->{$rol}()->create($atributos);
    }

    private function recurso(string $tipo = 'pdf', string $estado = 'published', array $atributos = [], ?User $creador = null): LibraryResource
    {
        return LibraryResource::factory()
            ->ofType(ResourceType::from($tipo))
            ->{$estado}()
            ->create([
                'created_by' => $creador?->id,
                ...$atributos,
            ]);
    }

    /**
     * Guarda un archivo real en el disco y su fila de resource_files.
     *
     * @param  array<string, mixed>  $atributos  sobrescribe columnas de la fila (disk, path, mime_type, original_name...)
     */
    private function conArchivo(LibraryResource $recurso, array $atributos = [], ?string $contenido = self::PDF): ResourceFile
    {
        [$extension, $mime] = self::FORMATOS[$recurso->type->value] ?? self::FORMATOS['pdf'];
        $disco = $atributos['disk'] ?? config('biblioteca.disk');
        $ruta = $atributos['path'] ?? "resources/{$recurso->id}/".Str::uuid().".{$extension}";

        // Con null solo se crea la fila (discos remotos simulados).
        if ($contenido !== null) {
            Storage::disk($disco)->put($ruta, $contenido);
        }

        return ResourceFile::factory()->create([
            'resource_id' => $recurso->id,
            'disk' => $disco,
            'path' => $ruta,
            'original_name' => "manual.{$extension}",
            'mime_type' => $mime,
            'size' => strlen($contenido ?? ''),
            'extension' => $extension,
            ...$atributos,
        ]);
    }

    /**
     * Un recurso publicado, con archivo y en un curso del estudiante.
     */
    private function recursoDelEstudiante(User $estudiante, string $tipo = 'pdf', array $atributos = []): LibraryResource
    {
        $recurso = $this->recurso($tipo, 'published', $atributos);
        $this->conArchivo($recurso);
        $this->matricular($estudiante, $recurso);

        return $recurso;
    }

    private function matricular(User $estudiante, LibraryResource $recurso, bool $cursoActivo = true): void
    {
        $curso = Course::factory()->create(['is_active' => $cursoActivo]);
        $curso->students()->attach($estudiante->id);
        $recurso->courses()->attach($curso->id);
    }

    private function urlArchivo(LibraryResource $recurso): string
    {
        return route('biblioteca.archivo', ['resource' => $recurso->slug]);
    }

    private function urlDescarga(LibraryResource $recurso): string
    {
        return route('biblioteca.descargar', ['resource' => $recurso->slug]);
    }

    /**
     * @return array<string, string>
     */
    private function rutas(LibraryResource $recurso): array
    {
        return [
            'archivo' => $this->urlArchivo($recurso),
            'descargar' => $this->urlDescarga($recurso),
        ];
    }

    private function abrir(User $usuario, string $url, array $cabeceras = []): TestResponse
    {
        return $this->actingAs($usuario)->get($url, $cabeceras);
    }

    private function assertSinRutaDeAlmacenamiento(TestResponse $respuesta, ResourceFile $archivo): void
    {
        $visible = json_encode($respuesta->headers->all()).(string) $respuesta->baseResponse->getContent();

        $this->assertStringNotContainsString($archivo->path, $visible);
        $this->assertStringNotContainsString(basename($archivo->path), $visible);
        $this->assertStringNotContainsString(storage_path(), $visible);
    }

    /**
     * Registra un disco "nube" con un adaptador que NO es local (simula
     * s3 / R2) cuyas URL firmadas se capturan sin salir a la red.
     *
     * @param  array<int, array{ruta: string, expira: mixed, opciones: array<string, string>}>  $capturas
     */
    private function discoRemoto(bool $existe, array &$capturas): void
    {
        $adaptador = Mockery::mock(FlysystemAdapter::class);
        $adaptador->shouldReceive('fileExists')->andReturn($existe);
        $adaptador->shouldReceive('directoryExists')->andReturn(false);

        $disco = new FilesystemAdapter(new Flysystem($adaptador), $adaptador, ['driver' => 's3']);
        $disco->buildTemporaryUrlsUsing(function (string $ruta, $expira, array $opciones) use (&$capturas): string {
            $capturas[] = compact('ruta', 'expira', 'opciones');

            return 'https://nube.test/firmado?firma=abc';
        });

        Storage::set('nube', $disco);
    }

    // ---------------------------------------------------------------
    // Puerta de entrada
    // ---------------------------------------------------------------

    public function test_un_visitante_es_enviado_al_login_en_ambas_rutas(): void
    {
        $recurso = $this->recurso();
        $this->conArchivo($recurso);

        foreach ($this->rutas($recurso) as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }
    }

    public function test_una_familia_recibe_403_en_ambas_rutas(): void
    {
        $recurso = $this->recurso();
        $this->conArchivo($recurso);

        foreach ($this->rutas($recurso) as $url) {
            $this->abrir($this->usuario('familia'), $url)->assertForbidden();
        }
    }

    public function test_un_estudiante_inactivo_recibe_403_en_ambas_rutas(): void
    {
        $estudiante = $this->usuario('estudiante', ['active' => false]);
        $recurso = $this->recursoDelEstudiante($estudiante, 'pdf', ['is_downloadable' => true]);

        foreach ($this->rutas($recurso) as $url) {
            $this->abrir($estudiante, $url)->assertForbidden();
        }
    }

    // ---------------------------------------------------------------
    // Entrega en línea
    // ---------------------------------------------------------------

    public function test_el_estudiante_matriculado_ve_el_pdf_en_linea_con_cabeceras_seguras(): void
    {
        $estudiante = $this->usuario('estudiante');
        $recurso = $this->recursoDelEstudiante($estudiante);

        $respuesta = $this->abrir($estudiante, $this->urlArchivo($recurso))->assertOk();

        $respuesta->assertHeader('Content-Type', 'application/pdf');
        $respuesta->assertHeader('Content-Disposition', 'inline');
        $respuesta->assertHeader('X-Content-Type-Options', 'nosniff');
        $respuesta->assertHeader('Referrer-Policy', 'no-referrer');
        $respuesta->assertHeader('Cross-Origin-Resource-Policy', 'same-origin');
        $this->assertStringContainsString('no-store', $respuesta->headers->get('Cache-Control'));
        $this->assertStringContainsString('private', $respuesta->headers->get('Cache-Control'));
        $this->assertSame(self::PDF, $respuesta->streamedContent());
    }

    public function test_la_respuesta_no_revela_la_ruta_de_almacenamiento(): void
    {
        $estudiante = $this->usuario('estudiante', ['name' => 'Ana']);
        $recurso = $this->recursoDelEstudiante($estudiante, 'pdf', ['is_downloadable' => true]);
        $archivo = $recurso->primaryFile();

        foreach ($this->rutas($recurso) as $url) {
            $this->assertSinRutaDeAlmacenamiento($this->abrir($estudiante, $url)->assertOk(), $archivo);
        }
    }

    public function test_un_recurso_que_el_usuario_no_puede_ver_responde_404_y_no_403(): void
    {
        $estudiante = $this->usuario('estudiante');

        // Matriculado solo en un curso inactivo.
        $enCursoInactivo = $this->recurso('pdf', 'published', ['is_downloadable' => true]);
        $this->conArchivo($enCursoInactivo);
        $this->matricular($estudiante, $enCursoInactivo, cursoActivo: false);

        // En un curso activo, pero al que el estudiante no pertenece.
        $enCursoAjeno = $this->recurso('pdf', 'published', ['is_downloadable' => true]);
        $this->conArchivo($enCursoAjeno);
        $enCursoAjeno->courses()->attach(Course::factory()->create()->id);

        // Aun siendo general y estando matriculado, un borrador y un archivado no se ven.
        $borrador = $this->recursoDelEstudiante($estudiante);
        $borrador->update(['status' => 'draft']);
        $archivado = $this->recursoDelEstudiante($estudiante);
        $archivado->update(['status' => 'archived']);

        foreach ([$enCursoInactivo, $enCursoAjeno, $borrador, $archivado] as $recurso) {
            foreach ($this->rutas($recurso) as $url) {
                $this->abrir($estudiante, $url)->assertNotFound();
            }
        }
    }

    public function test_un_slug_inexistente_responde_404(): void
    {
        $estudiante = $this->usuario('estudiante');

        $this->abrir($estudiante, route('biblioteca.archivo', ['resource' => 'no-existe']))->assertNotFound();
        $this->abrir($estudiante, route('biblioteca.descargar', ['resource' => 'no-existe']))->assertNotFound();
    }

    public function test_un_recurso_general_publicado_se_ve_en_linea(): void
    {
        $recurso = $this->recurso();
        $this->conArchivo($recurso);

        $this->abrir($this->usuario('estudiante'), $this->urlArchivo($recurso))->assertOk();
    }

    public function test_un_permiso_explicito_de_ver_abre_el_archivo(): void
    {
        $estudiante = $this->usuario('estudiante');
        $recurso = $this->recurso();
        $this->conArchivo($recurso);
        // El curso lo saca de la biblioteca general: sin permiso no se vería.
        $recurso->courses()->attach(Course::factory()->create()->id);
        $this->abrir($estudiante, $this->urlArchivo($recurso))->assertNotFound();

        ResourcePermission::factory()->forUser($estudiante)->create(['resource_id' => $recurso->id]);

        $this->abrir($estudiante, $this->urlArchivo($recurso))->assertOk();
    }

    public function test_una_guia_ajena_no_ve_un_borrador_pero_si_el_creador(): void
    {
        $creadora = $this->usuario('guia');
        $recurso = $this->recurso('pdf', 'draft', creador: $creadora);
        $this->conArchivo($recurso);

        $this->abrir($this->usuario('guia'), $this->urlArchivo($recurso))->assertNotFound();
        $this->abrir($creadora, $this->urlArchivo($recurso))->assertOk();
    }

    public function test_un_documento_no_se_ve_en_linea_pero_si_se_descarga(): void
    {
        $estudiante = $this->usuario('estudiante');
        $recurso = $this->recursoDelEstudiante($estudiante, 'document', ['is_downloadable' => true]);

        $this->abrir($estudiante, $this->urlArchivo($recurso))->assertNotFound();

        $this->abrir($estudiante, $this->urlDescarga($recurso))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    }

    public function test_video_audio_e_imagen_se_sirven_en_linea_con_su_tipo_guardado(): void
    {
        $estudiante = $this->usuario('estudiante');

        foreach (['video' => 'video/mp4', 'audio' => 'audio/mpeg', 'image' => 'image/png'] as $tipo => $mime) {
            $recurso = $this->recursoDelEstudiante($estudiante, $tipo);

            $this->abrir($estudiante, $this->urlArchivo($recurso))
                ->assertOk()
                ->assertHeader('Content-Type', $mime)
                ->assertHeader('Content-Disposition', 'inline');
        }
    }

    public function test_un_tipo_activo_guardado_nunca_se_sirve_en_linea(): void
    {
        $estudiante = $this->usuario('estudiante');
        $recurso = $this->recurso();
        $this->conArchivo($recurso, ['mime_type' => 'text/html'], '<script>alert(1)</script>');
        $this->matricular($estudiante, $recurso);

        $this->abrir($estudiante, $this->urlArchivo($recurso))->assertNotFound();
    }

    public function test_el_tipo_de_contenido_sale_del_guardado_y_no_de_la_peticion(): void
    {
        $estudiante = $this->usuario('estudiante');
        $recurso = $this->recursoDelEstudiante($estudiante);

        $this->abrir($estudiante, $this->urlArchivo($recurso).'?mime=text/html&type=text/html', [
            'Accept' => 'text/html',
            'Content-Type' => 'text/html',
        ])->assertOk()->assertHeader('Content-Type', 'application/pdf');
    }

    // ---------------------------------------------------------------
    // Descarga
    // ---------------------------------------------------------------

    public function test_un_recurso_no_descargable_da_403_al_estudiante_y_200_a_la_creadora_y_al_personal(): void
    {
        $creadora = $this->usuario('guia');
        $estudiante = $this->usuario('estudiante');
        $recurso = $this->recurso('pdf', 'published', ['is_downloadable' => false], $creadora);
        $this->conArchivo($recurso);
        $this->matricular($estudiante, $recurso);

        $this->abrir($estudiante, $this->urlDescarga($recurso))->assertForbidden();
        $this->abrir($creadora, $this->urlDescarga($recurso))->assertOk();
        $this->abrir($this->usuario('administrador'), $this->urlDescarga($recurso))->assertOk();
        $this->abrir($this->usuario('coordinacion'), $this->urlDescarga($recurso))->assertOk();
    }

    public function test_un_recurso_descargable_se_entrega_como_adjunto_con_nombre_saneado(): void
    {
        $estudiante = $this->usuario('estudiante');
        $recurso = $this->recurso('pdf', 'published', ['is_downloadable' => true]);
        $this->conArchivo($recurso, ['original_name' => 'Guía de cocina – año 2026.pdf']);
        $this->matricular($estudiante, $recurso);

        $respuesta = $this->abrir($estudiante, $this->urlDescarga($recurso))->assertOk();
        $disposicion = $respuesta->headers->get('Content-Disposition');

        $this->assertStringStartsWith('attachment;', $disposicion);
        $this->assertStringContainsString("filename*=utf-8''Gu%C3%ADa%20de%20cocina", $disposicion);
        $this->assertStringContainsString('filename="Guia de cocina - ano 2026.pdf"', $disposicion);
        $this->assertSame(1, preg_match('/^[\x20-\x7E]+$/', $disposicion), 'La cabecera solo lleva ASCII imprimible.');
        $respuesta->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertSame(self::PDF, $respuesta->streamedContent());
    }

    public function test_un_nombre_original_hostil_no_rompe_ni_inyecta_cabeceras(): void
    {
        $estudiante = $this->usuario('estudiante');
        $recurso = $this->recurso('pdf', 'published', ['is_downloadable' => true]);
        $this->conArchivo($recurso, ['original_name' => "../../etc/pas\\swd\"\r\nX-Inyectada: 1.pdf"]);
        $this->matricular($estudiante, $recurso);

        $respuesta = $this->abrir($estudiante, $this->urlDescarga($recurso))->assertOk();
        $disposicion = $respuesta->headers->get('Content-Disposition');

        $this->assertFalse($respuesta->headers->has('X-Inyectada'));
        $this->assertStringStartsWith('attachment;', $disposicion);
        $this->assertDoesNotMatchRegularExpression('/[\r\n]/', $disposicion);
        $this->assertStringNotContainsString('etc', $disposicion);
        $this->assertStringNotContainsString('..', $disposicion);
    }

    public function test_un_permiso_explicito_de_descarga_abre_la_descarga(): void
    {
        $estudiante = $this->usuario('estudiante');
        $recurso = $this->recursoDelEstudiante($estudiante, 'pdf', ['is_downloadable' => false]);

        $this->abrir($estudiante, $this->urlDescarga($recurso))->assertForbidden();

        ResourcePermission::factory()->forUser($estudiante)->canDownload()->create(['resource_id' => $recurso->id]);

        $this->abrir($estudiante, $this->urlDescarga($recurso))->assertOk();
    }

    public function test_un_enlace_y_un_recurso_sin_archivo_dan_404(): void
    {
        $estudiante = $this->usuario('estudiante');
        $administrador = $this->usuario('administrador');

        $enlace = $this->recurso('link', 'published', ['is_downloadable' => true]);
        $videoPorUrl = $this->recurso('video', 'published', ['is_downloadable' => true]);
        $pdfSinArchivo = $this->recurso('pdf', 'published', ['is_downloadable' => true]);

        foreach ([$enlace, $videoPorUrl, $pdfSinArchivo] as $recurso) {
            foreach ($this->rutas($recurso) as $url) {
                $this->abrir($estudiante, $url)->assertNotFound();
                $this->abrir($administrador, $url)->assertNotFound();
            }
        }
    }

    // ---------------------------------------------------------------
    // Registro de accesos
    // ---------------------------------------------------------------

    public function test_la_descarga_registra_un_evento_y_el_archivo_en_linea_no(): void
    {
        $estudiante = $this->usuario('estudiante');
        $recurso = $this->recursoDelEstudiante($estudiante, 'pdf', ['is_downloadable' => true]);

        $this->abrir($estudiante, $this->urlArchivo($recurso))->assertOk();
        $this->abrir($estudiante, $this->urlArchivo($recurso), ['Range' => 'bytes=0-9'])->assertStatus(206);
        $this->assertSame(0, ResourceAccessLog::count());

        $this->abrir($estudiante, $this->urlDescarga($recurso))->assertOk();

        $this->assertSame(1, ResourceAccessLog::count());
        $log = ResourceAccessLog::first();
        $this->assertSame(ResourceAccessLog::ACTION_DOWNLOADED, $log->action);
        $this->assertSame($estudiante->id, $log->user_id);
        $this->assertSame($recurso->id, $log->resource_id);
    }

    public function test_una_descarga_denegada_o_fallida_no_registra_nada(): void
    {
        $estudiante = $this->usuario('estudiante');
        $noDescargable = $this->recursoDelEstudiante($estudiante, 'pdf', ['is_downloadable' => false]);
        $sinArchivo = $this->recurso('pdf', 'published', ['is_downloadable' => true]);
        $this->matricular($estudiante, $sinArchivo);
        $conObjetoPerdido = $this->recursoDelEstudiante($estudiante, 'pdf', ['is_downloadable' => true]);
        Storage::disk(config('biblioteca.disk'))->delete($conObjetoPerdido->primaryFile()->path);
        Exceptions::fake();

        $this->abrir($estudiante, $this->urlDescarga($noDescargable))->assertForbidden();
        $this->abrir($estudiante, $this->urlDescarga($sinArchivo))->assertNotFound();
        $this->abrir($estudiante, $this->urlDescarga($conObjetoPerdido))->assertNotFound();

        $this->assertSame(0, ResourceAccessLog::count());
    }

    // ---------------------------------------------------------------
    // Discos y Range
    // ---------------------------------------------------------------

    public function test_el_archivo_se_sirve_desde_el_disco_guardado_en_la_fila(): void
    {
        Storage::fake('biblioteca-anterior');
        $estudiante = $this->usuario('estudiante');
        $recurso = $this->recurso();
        $this->conArchivo($recurso, ['disk' => 'biblioteca-anterior'], "%PDF-1.4\nsolo existe en el disco anterior\n");
        $this->matricular($estudiante, $recurso);

        // El disco configurado hoy es otro y no tiene el objeto.
        $this->assertNotSame('biblioteca-anterior', config('biblioteca.disk'));

        $this->assertSame(
            "%PDF-1.4\nsolo existe en el disco anterior\n",
            $this->abrir($estudiante, $this->urlArchivo($recurso))->assertOk()->streamedContent(),
        );
    }

    public function test_el_video_admite_peticiones_de_rango(): void
    {
        $estudiante = $this->usuario('estudiante');
        $recurso = $this->recurso('video');
        $contenido = implode('', range('a', 'z'));
        $this->conArchivo($recurso, [], $contenido);
        $this->matricular($estudiante, $recurso);

        $completo = $this->abrir($estudiante, $this->urlArchivo($recurso))->assertOk();
        $completo->assertHeader('Accept-Ranges', 'bytes');

        $parcial = $this->abrir($estudiante, $this->urlArchivo($recurso), ['Range' => 'bytes=2-5'])
            ->assertStatus(206);

        $parcial->assertHeader('Content-Range', 'bytes 2-5/26');
        $parcial->assertHeader('Content-Length', '4');
        $parcial->assertHeader('Content-Type', 'video/mp4');
        $parcial->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertSame('cdef', $parcial->streamedContent());

        $this->abrir($estudiante, $this->urlArchivo($recurso), ['Range' => 'bytes=500-600'])->assertStatus(416);
    }

    public function test_una_peticion_condicional_recibe_304(): void
    {
        $estudiante = $this->usuario('estudiante');
        $recurso = $this->recursoDelEstudiante($estudiante);

        $etag = $this->abrir($estudiante, $this->urlArchivo($recurso))->assertOk()->headers->get('ETag');
        $this->assertNotEmpty($etag);

        $this->abrir($estudiante, $this->urlArchivo($recurso), ['If-None-Match' => $etag])->assertStatus(304);
        $this->abrir($estudiante, $this->urlArchivo($recurso), ['If-None-Match' => '"otro"'])->assertOk();
    }

    public function test_un_disco_remoto_redirige_a_una_url_temporal_acotada(): void
    {
        config(['biblioteca.temporary_url_minutes' => 3]);
        $capturas = [];
        $this->discoRemoto(existe: true, capturas: $capturas);

        $estudiante = $this->usuario('estudiante');
        $recurso = $this->recurso('pdf', 'published', ['is_downloadable' => true]);
        $this->conArchivo($recurso, ['disk' => 'nube', 'path' => 'resources/9/archivo.pdf', 'original_name' => 'Manual.pdf'], null);
        $this->matricular($estudiante, $recurso);

        $descarga = $this->abrir($estudiante, $this->urlDescarga($recurso));
        $enLinea = $this->abrir($estudiante, $this->urlArchivo($recurso));

        $descarga->assertRedirect('https://nube.test/firmado?firma=abc');
        $enLinea->assertRedirect('https://nube.test/firmado?firma=abc');
        $this->assertStringContainsString('no-store', $descarga->headers->get('Cache-Control'));
        $this->assertStringContainsString('no-store', $enLinea->headers->get('Cache-Control'));
        $descarga->assertHeader('Referrer-Policy', 'no-referrer');

        $this->assertCount(2, $capturas);
        [$adjunto, $inline] = $capturas;
        $this->assertSame('resources/9/archivo.pdf', $adjunto['ruta']);
        $this->assertEqualsWithDelta(180, now()->diffInSeconds($adjunto['expira']), 5);
        $this->assertSame('application/pdf', $adjunto['opciones']['ResponseContentType']);
        $this->assertStringStartsWith('attachment;', $adjunto['opciones']['ResponseContentDisposition']);
        $this->assertStringContainsString('Manual.pdf', $adjunto['opciones']['ResponseContentDisposition']);
        $this->assertSame('inline', $inline['opciones']['ResponseContentDisposition']);
        $this->assertSame(1, ResourceAccessLog::where('action', ResourceAccessLog::ACTION_DOWNLOADED)->count());
    }

    public function test_un_objeto_remoto_inexistente_responde_404_y_se_reporta(): void
    {
        $capturas = [];
        $this->discoRemoto(existe: false, capturas: $capturas);
        Exceptions::fake();

        $estudiante = $this->usuario('estudiante');
        $recurso = $this->recurso();
        $this->conArchivo($recurso, ['disk' => 'nube', 'path' => 'resources/9/perdido.pdf'], null);
        $this->matricular($estudiante, $recurso);

        $this->abrir($estudiante, $this->urlArchivo($recurso))->assertNotFound();

        $this->assertSame([], $capturas, 'No debe firmarse una URL de un objeto que no existe.');
        Exceptions::assertReported(FileNotFoundException::class);
    }

    public function test_un_archivo_fisico_inexistente_responde_404_se_reporta_y_no_filtra_la_ruta(): void
    {
        $estudiante = $this->usuario('estudiante');
        $recurso = $this->recursoDelEstudiante($estudiante, 'pdf', ['is_downloadable' => true]);
        $archivo = $recurso->primaryFile();
        Storage::disk($archivo->disk)->delete($archivo->path);
        Exceptions::fake();

        foreach ($this->rutas($recurso) as $url) {
            $respuesta = $this->abrir($estudiante, $url)->assertNotFound();
            $this->assertSinRutaDeAlmacenamiento($respuesta, $archivo);
        }

        Exceptions::assertReportedCount(2);
        Exceptions::assertReported(FileNotFoundException::class);
    }

    // ---------------------------------------------------------------
    // Límites de uso
    // ---------------------------------------------------------------

    public function test_los_limitadores_existen_y_estan_en_las_rutas(): void
    {
        $usuario = $this->usuario('estudiante');
        $peticion = Request::create('/biblioteca');
        $peticion->setUserResolver(fn () => $usuario);

        foreach (['biblioteca-descargas' => 'descargas', 'biblioteca-archivos' => 'archivos'] as $nombre => $clave) {
            $limitador = RateLimiter::limiter($nombre);

            $this->assertNotNull($limitador, "Falta el limitador {$nombre}.");
            $limite = $limitador($peticion);
            $this->assertSame((int) config("biblioteca.rate_limit.{$clave}"), $limite->maxAttempts);
            $this->assertStringContainsString((string) $usuario->id, (string) $limite->key);
        }

        $this->assertSame(240, config('biblioteca.rate_limit.archivos'));
        $this->assertSame(30, config('biblioteca.rate_limit.descargas'));

        $rutas = Route::getRoutes();
        $this->assertContains('throttle:biblioteca-descargas', $rutas->getByName('biblioteca.descargar')->gatherMiddleware());
        $this->assertContains('throttle:biblioteca-archivos', $rutas->getByName('biblioteca.archivo')->gatherMiddleware());

        foreach (['biblioteca.descargar', 'biblioteca.archivo'] as $nombre) {
            $this->assertContains('auth', $rutas->getByName($nombre)->gatherMiddleware());
            $this->assertContains('biblioteca', $rutas->getByName($nombre)->gatherMiddleware());
        }
    }

    public function test_el_limite_de_descargas_responde_429(): void
    {
        config(['biblioteca.rate_limit.descargas' => 2]);
        $estudiante = $this->usuario('estudiante');
        $recurso = $this->recursoDelEstudiante($estudiante, 'pdf', ['is_downloadable' => true]);

        $this->abrir($estudiante, $this->urlDescarga($recurso))->assertOk();
        $this->abrir($estudiante, $this->urlDescarga($recurso))->assertOk();
        $this->abrir($estudiante, $this->urlDescarga($recurso))->assertStatus(429);

        // El límite es por usuario: otra persona no lo comparte.
        $otro = $this->usuario('estudiante');
        $this->matricular($otro, $recurso);
        $this->abrir($otro, $this->urlDescarga($recurso))->assertOk();
    }

    public function test_el_limite_de_archivos_en_linea_responde_429(): void
    {
        config(['biblioteca.rate_limit.archivos' => 2]);
        $estudiante = $this->usuario('estudiante');
        $recurso = $this->recursoDelEstudiante($estudiante);

        $this->abrir($estudiante, $this->urlArchivo($recurso))->assertOk();
        $this->abrir($estudiante, $this->urlArchivo($recurso))->assertOk();
        $this->abrir($estudiante, $this->urlArchivo($recurso))->assertStatus(429);
    }

    // ---------------------------------------------------------------
    // Fugas en las otras puertas a archivos privados
    // ---------------------------------------------------------------

    public function test_un_estudiante_no_abre_los_archivos_privados_de_contenido_ni_de_evidencia(): void
    {
        Storage::fake('local');
        $estudiante = $this->usuario('estudiante');

        $contenido = Content::factory()->published()->create();
        Storage::disk('local')->put('content/guia.pdf', self::PDF);
        $medioDeContenido = $contenido->media()->create([
            'type' => Media::TYPE_PDF,
            'path' => 'content/guia.pdf',
            'original_name' => 'guia.pdf',
            'mime_type' => 'application/pdf',
            'size' => strlen(self::PDF),
        ]);

        $guia = $this->usuario('guia');
        $familia = Family::factory()->create();
        $nino = Child::factory()->create([
            'family_id' => $familia->id,
            'environment_id' => Environment::factory()->create(['teacher_id' => $guia->id])->id,
        ]);
        $evidencia = Evidence::create([
            'content_id' => $contenido->id,
            'child_id' => $nino->id,
            'family_id' => $familia->id,
            'comment' => 'Evidencia de prueba',
            'status' => Evidence::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);
        Storage::disk('local')->put('evidence/foto.jpg', 'imagen');
        $medioDeEvidencia = $evidencia->media()->create([
            'type' => Media::TYPE_IMAGE,
            'path' => 'evidence/foto.jpg',
            'original_name' => 'foto.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 6,
        ]);

        $this->abrir($estudiante, route('media.show', $medioDeContenido))->assertForbidden();
        $this->abrir($estudiante, route('media.show', $medioDeEvidencia))->assertForbidden();
    }

    public function test_un_estudiante_no_abre_la_vista_previa_firmada_de_un_contenido(): void
    {
        $estudiante = $this->usuario('estudiante');
        $contenido = Content::factory()->published()->create();

        $url = URL::temporarySignedRoute('mi-escuelita.preview.show', now()->addMinutes(30), ['content' => $contenido->id]);

        $this->abrir($estudiante, $url)->assertForbidden();
    }

    public function test_un_estudiante_no_entra_al_portal_de_las_familias(): void
    {
        $this->abrir($this->usuario('estudiante'), route('mi-escuelita.home'))->assertForbidden();
    }

    public function test_quien_no_puede_usar_la_biblioteca_no_distingue_un_recurso_inexistente_de_uno_real(): void
    {
        // La puerta de la Biblioteca debe cerrarse ANTES de resolver el slug:
        // si no, el 404 del binding delata qué recursos existen.
        $borrador = $this->recurso('pdf', 'draft');
        $this->conArchivo($borrador);

        foreach ([$this->usuario('familia'), $this->usuario('estudiante', ['active' => false])] as $usuario) {
            foreach (['archivo', 'descargar'] as $ruta) {
                $this->abrir($usuario, "/biblioteca/slug-que-no-existe/{$ruta}")->assertForbidden();
                $this->abrir($usuario, "/biblioteca/{$borrador->slug}/{$ruta}")->assertForbidden();
            }
        }
    }

    public function test_solo_una_descarga_completa_se_anota_en_el_registro(): void
    {
        $estudiante = $this->usuario('estudiante');
        $recurso = $this->recursoDelEstudiante($estudiante, 'pdf', ['is_downloadable' => true]);
        $url = $this->urlDescarga($recurso);

        // Ni HEAD ni una petición de rango son una descarga: no inflan las estadísticas.
        $this->actingAs($estudiante)->call('HEAD', $url)->assertOk();
        $this->abrir($estudiante, $url, ['Range' => 'bytes=0-9'])->assertStatus(206);
        $this->assertSame(0, ResourceAccessLog::count());

        $completa = $this->abrir($estudiante, $url)->assertOk();
        $this->assertSame(1, ResourceAccessLog::count());

        // Tampoco una petición condicional que termina en 304.
        $this->abrir($estudiante, $url, ['If-None-Match' => $completa->headers->get('ETag')])->assertStatus(304);
        $this->assertSame(1, ResourceAccessLog::count());
    }
}
