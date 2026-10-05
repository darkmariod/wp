<?php

namespace Tests\Feature;

use App\Enums\ResourceType;
use App\Models\LibraryResource;
use App\Models\ResourceFile;
use App\Services\Biblioteca\ResourceFileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Tests\TestCase;
use ZipArchive;

/**
 * Almacenamiento de la Biblioteca: los archivos subidos no son de fiar.
 * Se valida el contenido (no el nombre ni el tipo que declara el cliente),
 * se guardan en un disco privado con nombres generados y se limpian al borrar.
 *
 * Nota sobre los archivos: UploadedFile::fake() reporta el tipo MIME según la
 * extensión del nombre, así que NO sirve para probar la detección por
 * contenido. Esas pruebas usan archivos reales en el directorio temporal.
 */
class BibliotecaStorageTest extends TestCase
{
    use RefreshDatabase;

    private const PDF_MINIMO = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF\n";

    private const PNG_1X1 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

    private const CABECERA_OLE = "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1";

    private const UUID = '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}';

    /** @var list<string> */
    private array $temporales = [];

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('biblioteca');
        config(['biblioteca.disk' => 'biblioteca', 'biblioteca.max_upload_kb' => null]);
    }

    protected function tearDown(): void
    {
        foreach ($this->temporales as $ruta) {
            @unlink($ruta);
        }

        parent::tearDown();
    }

    private function servicio(): ResourceFileService
    {
        return app(ResourceFileService::class);
    }

    private function recurso(ResourceType $tipo = ResourceType::Pdf): LibraryResource
    {
        return LibraryResource::factory()->ofType($tipo)->create();
    }

    /**
     * Archivo REAL en disco: el tipo se detecta por su contenido, y el tipo
     * que "declara" el cliente es el que se indique (casi siempre mentira).
     */
    private function archivoReal(string $contenido, string $nombre, string $mimeDeclarado = 'application/pdf'): UploadedFile
    {
        $ruta = tempnam(sys_get_temp_dir(), 'bib');
        file_put_contents($ruta, $contenido);
        $this->temporales[] = $ruta;

        return new UploadedFile($ruta, $nombre, $mimeDeclarado, null, true);
    }

    private function pdfReal(string $nombre = 'guia.pdf'): UploadedFile
    {
        return $this->archivoReal(self::PDF_MINIMO, $nombre);
    }

    private function pngReal(string $nombre = 'portada.png'): UploadedFile
    {
        return $this->archivoReal(base64_decode(self::PNG_1X1), $nombre, 'image/png');
    }

    /**
     * Ejecuta la acción y exige que el archivo sea rechazado con un error
     * de validación sobre el campo `file`.
     */
    private function assertRechazado(callable $accion, ?string $fragmento = null): void
    {
        try {
            $accion();
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('file', $e->errors());

            if ($fragmento !== null) {
                $this->assertStringContainsString($fragmento, $e->errors()['file'][0]);
            }

            return;
        }

        $this->fail('Se esperaba que el archivo fuera rechazado.');
    }

    private function assertNadaGuardado(): void
    {
        $this->assertSame([], Storage::disk('biblioteca')->allFiles());
        $this->assertSame(0, ResourceFile::count());
    }

    /**
     * Crea un archivo huérfano (sin fila que lo referencie) con la antigüedad pedida.
     */
    private function huerfano(string $ruta, int $horas = 0): void
    {
        Storage::disk('biblioteca')->put($ruta, 'contenido');

        if ($horas > 0) {
            touch(Storage::disk('biblioteca')->path($ruta), now()->subHours($horas)->getTimestamp());
        }
    }

    // ---------------------------------------------------------------
    // Guardado
    // ---------------------------------------------------------------

    /**
     * El camino feliz: ruta generada, fila completa y objeto en el disco.
     */
    public function test_guarda_un_pdf_valido_con_ruta_segura_y_fila_en_la_base_de_datos(): void
    {
        $recurso = $this->recurso();

        $archivo = $this->servicio()->store($recurso, UploadedFile::fake()->create('Guía de cocina.pdf', 200, 'application/pdf'));

        $this->assertMatchesRegularExpression('#^resources/'.$recurso->id.'/'.self::UUID.'\.pdf$#', $archivo->path);
        Storage::disk('biblioteca')->assertExists($archivo->path);
        $this->assertSame($recurso->id, $archivo->resource_id);
        $this->assertSame('biblioteca', $archivo->disk);
        $this->assertSame('Guía de cocina.pdf', $archivo->original_name);
        $this->assertSame('application/pdf', $archivo->mime_type);
        $this->assertSame('pdf', $archivo->extension);
        $this->assertSame(200 * 1024, $archivo->size);
        $this->assertDatabaseHas('resource_files', ['id' => $archivo->id, 'path' => $archivo->path]);
    }

    /**
     * El disco nunca va escrito a mano: sale de config('biblioteca.disk').
     */
    public function test_usa_el_disco_indicado_en_la_configuracion(): void
    {
        Storage::fake('almacen_alterno');
        config(['biblioteca.disk' => 'almacen_alterno']);

        $archivo = $this->servicio()->store($this->recurso(), $this->pdfReal());

        $this->assertSame('almacen_alterno', $archivo->disk);
        Storage::disk('almacen_alterno')->assertExists($archivo->path);
        $this->assertSame([], Storage::disk('biblioteca')->allFiles());
    }

    /**
     * Un PDF verdadero se acepta aunque el cliente declare cualquier tipo.
     */
    public function test_acepta_un_pdf_real_aunque_el_cliente_declare_otro_tipo(): void
    {
        $archivo = $this->servicio()->store(
            $this->recurso(),
            $this->archivoReal(self::PDF_MINIMO, 'guia.pdf', 'application/octet-stream'),
        );

        $this->assertSame('application/pdf', $archivo->mime_type);
        Storage::disk('biblioteca')->assertExists($archivo->path);
    }

    /**
     * El nombre original solo es un dato para mostrar: jamás forma parte
     * de la ruta ni decide la extensión con la que se guarda.
     */
    public function test_el_nombre_original_nunca_llega_a_la_ruta_guardada(): void
    {
        $recurso = $this->recurso();

        foreach (['evil.php.pdf', '../../etc/passwd.pdf', '..\\..\\windows\\x.pdf', 'REPORTE FINAL.PDF'] as $nombre) {
            $archivo = $this->servicio()->store($recurso, $this->pdfReal($nombre));

            $this->assertMatchesRegularExpression('#^resources/'.$recurso->id.'/'.self::UUID.'\.pdf$#', $archivo->path, $nombre);
            $this->assertSame('pdf', $archivo->extension);
            $this->assertStringNotContainsString('..', $archivo->original_name);
            $this->assertStringNotContainsString('/', $archivo->original_name);
            $this->assertStringNotContainsString('\\', $archivo->original_name);
        }

        $this->assertSame(4, ResourceFile::count());
    }

    /**
     * El nombre original se limpia: sin caracteres de control, sin
     * caracteres que invierten el texto y con un máximo de 255.
     */
    public function test_sanea_el_nombre_original_antes_de_guardarlo(): void
    {
        $recurso = $this->recurso();

        $conControl = $this->servicio()->store($recurso, $this->pdfReal("gu\x07ia\n\x1Ffinal.pdf"));
        $this->assertSame('guiafinal.pdf', $conControl->original_name);

        // U+202E invierte el sentido del texto y sirve para disfrazar extensiones.
        $invertido = $this->servicio()->store($recurso, $this->pdfReal("recibo\u{202E}fdp.exe.pdf"));
        $this->assertSame('recibofdp.exe.pdf', $invertido->original_name);

        $largo = $this->servicio()->store($recurso, $this->pdfReal(str_repeat('a', 400).'.pdf'));
        $this->assertSame(255, mb_strlen($largo->original_name));
        $this->assertStringEndsWith('.pdf', $largo->original_name);

        $sinNombre = $this->servicio()->store($recurso, $this->pdfReal('.pdf'));
        $this->assertSame('archivo.pdf', $sinNombre->original_name);
    }

    // ---------------------------------------------------------------
    // Validación del contenido
    // ---------------------------------------------------------------

    /**
     * La extensión debe estar permitida para el TIPO del recurso, no solo
     * para la Biblioteca en general.
     */
    public function test_rechaza_una_extension_que_el_tipo_no_admite(): void
    {
        $this->assertRechazado(
            fn () => $this->servicio()->store($this->recurso(ResourceType::Pdf), UploadedFile::fake()->create('x.docx', 10)),
            'Solo se admiten archivos PDF',
        );
        $this->assertRechazado(
            fn () => $this->servicio()->store($this->recurso(ResourceType::Image), UploadedFile::fake()->create('x.pdf', 10, 'application/pdf')),
        );
        $this->assertRechazado(
            fn () => $this->servicio()->store($this->recurso(ResourceType::Pdf), UploadedFile::fake()->create('sin_extension', 10, 'application/pdf')),
        );
        $this->assertNadaGuardado();
    }

    /**
     * Un PHP con nombre .pdf y tipo "application/pdf" declarado debe caer:
     * lo que manda es el contenido.
     */
    public function test_rechaza_codigo_php_disfrazado_de_pdf_ignorando_el_tipo_declarado(): void
    {
        $this->assertRechazado(
            fn () => $this->servicio()->store(
                $this->recurso(),
                $this->archivoReal("<?php system(\$_GET['c']); ?>", 'x.pdf', 'application/pdf'),
            ),
            'no corresponde',
        );
        $this->assertNadaGuardado();
    }

    /**
     * HTML y SVG pueden ejecutar scripts en el navegador: no entran ni
     * con extensión de documento ni con extensión de imagen.
     */
    public function test_rechaza_html_y_svg_disfrazados_de_documentos_o_imagenes(): void
    {
        $html = '<!DOCTYPE html><html><body><script>alert(1)</script></body></html>';
        $svg = '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>';

        $this->assertRechazado(fn () => $this->servicio()->store($this->recurso(ResourceType::Pdf), $this->archivoReal($html, 'x.pdf')));
        $this->assertRechazado(fn () => $this->servicio()->store($this->recurso(ResourceType::Image), $this->archivoReal($svg, 'x.png', 'image/png')));
        $this->assertRechazado(fn () => $this->servicio()->store($this->recurso(ResourceType::Image), $this->archivoReal($svg, 'x.svg', 'image/svg+xml')));
        $this->assertNadaGuardado();
    }

    /**
     * Cada extensión acepta solo SUS tipos de contenido: un PNG verdadero
     * no sirve como .jpg ni como .pdf.
     */
    public function test_exige_que_el_contenido_corresponda_a_la_extension_declarada(): void
    {
        $this->assertRechazado(fn () => $this->servicio()->store($this->recurso(ResourceType::Pdf), $this->pngReal('x.pdf')));
        $this->assertRechazado(fn () => $this->servicio()->store($this->recurso(ResourceType::Image), $this->pngReal('x.jpg')));
        $this->assertRechazado(fn () => $this->servicio()->store($this->recurso(ResourceType::Image), $this->pdfReal('x.png')));
        $this->assertNadaGuardado();

        $imagen = $this->servicio()->store($this->recurso(ResourceType::Image), $this->pngReal('x.png'));
        $this->assertSame('image/png', $imagen->mime_type);
    }

    /**
     * Los .docx son un ZIP por dentro y finfo a veces los reporta como
     * application/zip: ambos se aceptan, pero un .docx no puede ser un PDF.
     */
    public function test_acepta_documentos_ooxml_aunque_finfo_los_reporte_como_zip(): void
    {
        $ruta = tempnam(sys_get_temp_dir(), 'bib');
        $this->temporales[] = $ruta;
        $zip = new ZipArchive;
        $zip->open($ruta, ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0"?><Types/>');
        $zip->addFromString('word/document.xml', '<?xml version="1.0"?><w:document/>');
        $zip->close();

        $docx = $this->servicio()->store(
            $this->recurso(ResourceType::Document),
            new UploadedFile($ruta, 'tarea.docx', 'application/octet-stream', null, true),
        );

        $this->assertContains($docx->mime_type, array_map('strtolower', config('biblioteca.extensions.docx')));
        Storage::disk('biblioteca')->assertExists($docx->path);

        $this->assertRechazado(fn () => $this->servicio()->store($this->recurso(ResourceType::Pdf), new UploadedFile($ruta, 'x.pdf', 'application/pdf', null, true)));
    }

    /**
     * Los .doc/.xls/.ppt antiguos son contenedores OLE; finfo los reporta
     * con un tipo genérico de contenedor, que se acepta solo para ellos.
     */
    public function test_acepta_documentos_antiguos_de_office_como_contenedor_ole(): void
    {
        $ole = self::CABECERA_OLE.str_repeat("\0", 600);

        $doc = $this->servicio()->store($this->recurso(ResourceType::Document), $this->archivoReal($ole, 'apunte.doc', 'application/msword'));
        $this->assertContains($doc->mime_type, array_map('strtolower', config('biblioteca.extensions.doc')));

        $this->assertRechazado(fn () => $this->servicio()->store($this->recurso(ResourceType::Document), $this->archivoReal($ole, 'apunte.docx')));
        $this->assertRechazado(fn () => $this->servicio()->store($this->recurso(ResourceType::Pdf), $this->archivoReal($ole, 'apunte.pdf')));
    }

    /**
     * Cada tipo tiene su propio máximo.
     */
    public function test_rechaza_archivos_que_superan_el_maximo_del_tipo(): void
    {
        config(['biblioteca.types.pdf.max_kb' => 100]);
        $recurso = $this->recurso();

        $this->assertRechazado(
            fn () => $this->servicio()->store($recurso, UploadedFile::fake()->create('grande.pdf', 101, 'application/pdf')),
            'no puede pesar más de',
        );
        $this->assertNadaGuardado();

        $this->servicio()->store($recurso, UploadedFile::fake()->create('justo.pdf', 100, 'application/pdf'));
        $this->assertSame(1, ResourceFile::count());
    }

    /**
     * BIBLIOTECA_MAX_UPLOAD_KB baja el máximo de todos los tipos, pero
     * nunca lo sube por encima del que tiene el tipo.
     */
    public function test_el_tope_global_reduce_el_maximo_de_todos_los_tipos_sin_subirlo(): void
    {
        config(['biblioteca.max_upload_kb' => 50]);

        $this->assertContains('max:50', $this->servicio()->rulesFor(ResourceType::Pdf));
        $this->assertContains('max:50', $this->servicio()->rulesFor(ResourceType::Video));
        $this->assertContains('max:50', $this->servicio()->thumbnailRules());
        $this->assertRechazado(fn () => $this->servicio()->store($this->recurso(), UploadedFile::fake()->create('x.pdf', 51, 'application/pdf')));

        config(['biblioteca.max_upload_kb' => 999999999]);
        $this->assertContains('max:'.config('biblioteca.types.pdf.max_kb'), $this->servicio()->rulesFor(ResourceType::Pdf));

        // Un valor vacío o inválido en el .env no debe dejar todo en cero.
        config(['biblioteca.max_upload_kb' => '']);
        $this->assertContains('max:'.config('biblioteca.types.pdf.max_kb'), $this->servicio()->rulesFor(ResourceType::Pdf));
    }

    public function test_rechaza_archivos_vacios(): void
    {
        $this->assertRechazado(
            fn () => $this->servicio()->store($this->recurso(), UploadedFile::fake()->create('vacio.pdf', 0, 'application/pdf')),
            'vacío',
        );
        $this->assertRechazado(fn () => $this->servicio()->store($this->recurso(), $this->archivoReal('', 'vacio.pdf')), 'vacío');
        $this->assertNadaGuardado();
    }

    /**
     * Un archivo que no viene de una subida HTTP real (por ejemplo, una
     * ruta local armada a mano) o que trae un error de subida se descarta.
     */
    public function test_rechaza_archivos_que_no_fueron_subidos_o_llegaron_con_error(): void
    {
        $ruta = tempnam(sys_get_temp_dir(), 'bib');
        file_put_contents($ruta, self::PDF_MINIMO);
        $this->temporales[] = $ruta;

        $noSubido = new UploadedFile($ruta, 'x.pdf', 'application/pdf', null, false);
        $conError = new UploadedFile($ruta, 'x.pdf', 'application/pdf', UPLOAD_ERR_PARTIAL, true);

        $this->assertRechazado(fn () => $this->servicio()->store($this->recurso(), $noSubido), 'No se pudo recibir');
        $this->assertRechazado(fn () => $this->servicio()->store($this->recurso(), $conError), 'No se pudo recibir');
        $this->assertNadaGuardado();
    }

    /**
     * Ningún tipo admite scripts ni ejecutables, sea cual sea el contenido.
     */
    public function test_rechaza_extensiones_activas_en_todos_los_tipos(): void
    {
        foreach (ResourceType::cases() as $tipo) {
            if (! $tipo->usesFile()) {
                continue;
            }

            $recurso = $this->recurso($tipo);

            foreach (['svg', 'html', 'htm', 'php', 'phtml', 'js', 'exe', 'sh', 'bat', 'jar'] as $extension) {
                $this->assertRechazado(
                    fn () => $this->servicio()->store($recurso, UploadedFile::fake()->create("x.{$extension}", 10)),
                );
            }
        }

        $this->assertNadaGuardado();
    }

    /**
     * La propia configuración no puede abrir esa puerta por descuido.
     */
    public function test_la_configuracion_no_admite_extensiones_activas_ni_tipos_genericos(): void
    {
        $prohibidas = ['php', 'phtml', 'phar', 'html', 'htm', 'svg', 'js', 'exe', 'sh', 'bat', 'jar'];
        $permitidas = array_keys(config('biblioteca.extensions'));

        $this->assertSame([], array_intersect($prohibidas, $permitidas));

        foreach (config('biblioteca.extensions') as $extension => $mimes) {
            $this->assertNotContains('application/octet-stream', $mimes, $extension);
            $this->assertNotEmpty($mimes, $extension);
        }

        $declaradas = [...config('biblioteca.thumbnail.extensions')];
        foreach (config('biblioteca.types') as $tipo => $politica) {
            $declaradas = [...$declaradas, ...$politica['extensions']];
        }

        $this->assertSame([], array_diff(array_unique($declaradas), $permitidas));
    }

    public function test_un_enlace_no_admite_archivos(): void
    {
        $enlace = $this->recurso(ResourceType::Link);

        $this->assertRechazado(
            fn () => $this->servicio()->store($enlace, $this->pdfReal()),
            'no llevan archivo',
        );
        $this->assertNadaGuardado();
        $this->assertSame(['prohibited'], $this->servicio()->rulesFor(ResourceType::Link));
    }

    public function test_los_tipos_con_archivo_tienen_politica_en_la_configuracion(): void
    {
        foreach (ResourceType::cases() as $tipo) {
            if ($tipo->usesFile()) {
                $this->assertNotEmpty(config("biblioteca.types.{$tipo->value}.extensions"), $tipo->value);
                $this->assertGreaterThan(0, config("biblioteca.types.{$tipo->value}.max_kb"), $tipo->value);
            }
        }

        $this->assertArrayNotHasKey('link', config('biblioteca.types'));
    }

    // ---------------------------------------------------------------
    // Reglas reutilizables
    // ---------------------------------------------------------------

    public function test_rules_for_arma_las_reglas_a_partir_de_la_configuracion(): void
    {
        $reglas = $this->servicio()->rulesFor(ResourceType::Pdf);

        $this->assertContains('required', $reglas);
        $this->assertContains('file', $reglas);
        $this->assertContains('extensions:pdf', $reglas);
        $this->assertContains('mimetypes:application/pdf', $reglas);
        $this->assertContains('max:51200', $reglas);

        $documento = collect($this->servicio()->rulesFor(ResourceType::Document))
            ->first(fn ($regla) => is_string($regla) && str_starts_with($regla, 'mimetypes:'));
        $this->assertStringContainsString('application/zip', $documento);
        $this->assertStringNotContainsString('octet-stream', $documento);

        $miniatura = $this->servicio()->thumbnailRules();
        $this->assertContains('extensions:jpg,jpeg,png,webp', $miniatura);
        $this->assertContains('max:5120', $miniatura);
    }

    /**
     * El video puede venir solo por URL, así que su archivo es opcional.
     */
    public function test_el_archivo_es_opcional_solo_en_los_tipos_que_no_lo_exigen(): void
    {
        $this->assertContains('nullable', $this->servicio()->rulesFor(ResourceType::Video));
        $this->assertNotContains('required', $this->servicio()->rulesFor(ResourceType::Video));
        $this->assertContains('required', $this->servicio()->rulesFor(ResourceType::Audio));
    }

    /**
     * Las mismas reglas sirven en un FormRequest, en Filament o en la API.
     */
    public function test_las_reglas_sirven_para_validar_fuera_del_servicio(): void
    {
        $reglas = ['archivo' => $this->servicio()->rulesFor(ResourceType::Pdf)];
        $mensajes = $this->servicio()->messagesFor(ResourceType::Pdf, 'archivo');

        $this->assertTrue(Validator::make(['archivo' => $this->pdfReal()], $reglas, $mensajes)->passes());

        $invalido = Validator::make(['archivo' => $this->archivoReal('<?php echo 1;', 'x.pdf')], $reglas, $mensajes);
        $this->assertTrue($invalido->fails());
        $this->assertStringContainsString('no corresponde', $invalido->errors()->first('archivo'));

        $this->assertTrue(Validator::make(['archivo' => null], $reglas, $mensajes)->fails());
    }

    // ---------------------------------------------------------------
    // Reemplazo
    // ---------------------------------------------------------------

    public function test_reemplazar_guarda_el_nuevo_y_borra_el_anterior(): void
    {
        $recurso = $this->recurso();
        $anterior = $this->servicio()->store($recurso, $this->pdfReal('v1.pdf'));

        $nuevo = $this->servicio()->replace($recurso, $this->pdfReal('v2.pdf'));

        $this->assertNotSame($anterior->path, $nuevo->path);
        Storage::disk('biblioteca')->assertExists($nuevo->path);
        Storage::disk('biblioteca')->assertMissing($anterior->path);
        $this->assertDatabaseMissing('resource_files', ['id' => $anterior->id]);
        $this->assertSame([$nuevo->id], $recurso->files()->pluck('id')->all());
        $this->assertSame($nuevo->id, $recurso->primaryFile()->id);
    }

    /**
     * Si el archivo nuevo no sirve, el anterior queda intacto.
     */
    public function test_reemplazar_conserva_el_archivo_anterior_si_el_nuevo_es_invalido(): void
    {
        $recurso = $this->recurso();
        $anterior = $this->servicio()->store($recurso, $this->pdfReal('v1.pdf'));

        $this->assertRechazado(fn () => $this->servicio()->replace($recurso, $this->archivoReal('<?php echo 1;', 'v2.pdf')));

        Storage::disk('biblioteca')->assertExists($anterior->path);
        $this->assertDatabaseHas('resource_files', ['id' => $anterior->id]);
        $this->assertCount(1, Storage::disk('biblioteca')->allFiles());
    }

    // ---------------------------------------------------------------
    // Miniaturas
    // ---------------------------------------------------------------

    public function test_la_miniatura_reemplaza_a_la_anterior(): void
    {
        $recurso = $this->recurso();

        $primera = $this->servicio()->storeThumbnail($recurso, $this->pngReal('uno.png'));
        $segunda = $this->servicio()->storeThumbnail($recurso, $this->pngReal('dos.png'));

        $this->assertMatchesRegularExpression('#^thumbnails/'.$recurso->id.'/'.self::UUID.'\.png$#', $segunda);
        Storage::disk('biblioteca')->assertMissing($primera);
        Storage::disk('biblioteca')->assertExists($segunda);
        $this->assertSame($segunda, $recurso->fresh()->thumbnail);
    }

    public function test_la_miniatura_rechaza_svg_contenido_falso_y_archivos_pesados(): void
    {
        $recurso = $this->recurso();
        $svg = '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>';

        $this->assertRechazado(fn () => $this->servicio()->storeThumbnail($recurso, $this->archivoReal($svg, 'x.svg', 'image/svg+xml')));
        $this->assertRechazado(fn () => $this->servicio()->storeThumbnail($recurso, $this->archivoReal($svg, 'x.png', 'image/png')));
        $this->assertRechazado(fn () => $this->servicio()->storeThumbnail($recurso, $this->pdfReal('x.png')));

        config(['biblioteca.thumbnail.max_kb' => 10]);
        $this->assertRechazado(fn () => $this->servicio()->storeThumbnail($recurso, UploadedFile::fake()->create('grande.png', 11, 'image/png')));

        $this->assertNull($recurso->fresh()->thumbnail);
        $this->assertSame([], Storage::disk('biblioteca')->allFiles());
    }

    public function test_borrar_la_miniatura_elimina_el_objeto_y_limpia_la_columna(): void
    {
        $recurso = $this->recurso();
        $ruta = $this->servicio()->storeThumbnail($recurso, $this->pngReal());

        $this->servicio()->deleteThumbnail($recurso);

        Storage::disk('biblioteca')->assertMissing($ruta);
        $this->assertNull($recurso->fresh()->thumbnail);
        $this->servicio()->deleteThumbnail($recurso);
    }

    // ---------------------------------------------------------------
    // Borrado
    // ---------------------------------------------------------------

    /**
     * Las filas de resource_files las borra la base de datos en cascada
     * (sin eventos de Eloquent): el observer debe limpiar los objetos.
     */
    public function test_borrar_un_recurso_elimina_sus_archivos_fisicos_y_su_miniatura(): void
    {
        $recurso = $this->recurso();
        $uno = $this->servicio()->store($recurso, $this->pdfReal('uno.pdf'));
        $dos = $this->servicio()->store($recurso, $this->pdfReal('dos.pdf'));
        $miniatura = $this->servicio()->storeThumbnail($recurso, $this->pngReal());
        $ajeno = $this->servicio()->store($this->recurso(), $this->pdfReal('ajeno.pdf'));

        $recurso->delete();

        foreach ([$uno->path, $dos->path, $miniatura] as $ruta) {
            Storage::disk('biblioteca')->assertMissing($ruta);
        }
        Storage::disk('biblioteca')->assertExists($ajeno->path);
        $this->assertSame(1, ResourceFile::count());
    }

    /**
     * Si el borrado se deshace (rollback), los archivos NO se pierden.
     * Esta prueba confirma además que DB::afterCommit corre bajo RefreshDatabase.
     */
    public function test_si_el_borrado_del_recurso_se_deshace_los_archivos_se_conservan(): void
    {
        $recurso = $this->recurso();
        $archivo = $this->servicio()->store($recurso, $this->pdfReal());

        try {
            DB::transaction(function () use ($recurso) {
                $recurso->delete();

                throw new RuntimeException('Falla posterior al borrado.');
            });
        } catch (RuntimeException) {
            // Se esperaba: la transacción se revierte.
        }

        $this->assertDatabaseHas('resources', ['id' => $recurso->id]);
        $this->assertDatabaseHas('resource_files', ['id' => $archivo->id]);
        Storage::disk('biblioteca')->assertExists($archivo->path);

        // El modelo en memoria quedó marcado como borrado: se vuelve a cargar.
        DB::transaction(fn () => LibraryResource::findOrFail($recurso->id)->delete());

        Storage::disk('biblioteca')->assertMissing($archivo->path);
    }

    public function test_borrar_un_archivo_elimina_el_objeto_fisico(): void
    {
        $recurso = $this->recurso();
        $archivo = $this->servicio()->store($recurso, $this->pdfReal());

        $archivo->delete();

        Storage::disk('biblioteca')->assertMissing($archivo->path);
        $this->assertDatabaseMissing('resource_files', ['id' => $archivo->id]);
    }

    public function test_el_servicio_borra_un_archivo_y_todos_los_de_un_recurso(): void
    {
        $recurso = $this->recurso();
        $uno = $this->servicio()->store($recurso, $this->pdfReal('uno.pdf'));
        $dos = $this->servicio()->store($recurso, $this->pdfReal('dos.pdf'));

        $this->servicio()->deleteFile($uno);
        Storage::disk('biblioteca')->assertMissing($uno->path);
        Storage::disk('biblioteca')->assertExists($dos->path);

        $miniatura = $this->servicio()->storeThumbnail($recurso, $this->pngReal());
        $this->servicio()->deleteAllFor($recurso->fresh());

        Storage::disk('biblioteca')->assertMissing($dos->path);
        Storage::disk('biblioteca')->assertMissing($miniatura);
        $this->assertSame(0, ResourceFile::count());
        $this->assertNull($recurso->fresh()->thumbnail);
        $this->assertTrue($recurso->exists);
    }

    /**
     * Borrar algo que ya no está en el disco no es un error: el borrado de
     * la fila debe terminar igual.
     */
    public function test_borrar_un_objeto_fisico_inexistente_no_lanza_errores(): void
    {
        $recurso = $this->recurso();
        $archivo = ResourceFile::factory()->create([
            'resource_id' => $recurso->id,
            'disk' => 'biblioteca',
            'path' => 'resources/'.$recurso->id.'/fantasma.pdf',
        ]);
        $recurso->update(['thumbnail' => 'thumbnails/'.$recurso->id.'/fantasma.png']);

        $this->servicio()->deleteFile($archivo);
        $this->servicio()->deleteThumbnail($recurso);
        $this->servicio()->deleteObject('biblioteca', 'resources/0/no-existe.pdf');

        $recurso->update(['thumbnail' => 'thumbnails/'.$recurso->id.'/otro-fantasma.png']);
        ResourceFile::factory()->create(['resource_id' => $recurso->id, 'disk' => 'biblioteca', 'path' => 'resources/x/y.pdf']);
        $recurso->delete();

        $this->assertDatabaseMissing('resources', ['id' => $recurso->id]);
        $this->assertSame(0, ResourceFile::count());
    }

    // ---------------------------------------------------------------
    // Comando biblioteca:limpiar-huerfanos
    // ---------------------------------------------------------------

    /**
     * Sin --force solo informa: no borra nada. Lo referenciado (archivos y
     * miniaturas) ni siquiera aparece en la lista.
     */
    public function test_el_comando_en_simulacion_lista_huerfanos_y_no_borra_nada(): void
    {
        $recurso = $this->recurso();
        $referenciado = $this->servicio()->store($recurso, $this->pdfReal());
        $miniatura = $this->servicio()->storeThumbnail($recurso, $this->pngReal());
        $this->huerfano('resources/999/viejo.pdf', 48);
        $this->huerfano('resources/999/nuevo.pdf');
        $this->huerfano('thumbnails/999/viejo.png', 48);

        $this->artisan('biblioteca:limpiar-huerfanos')
            ->expectsOutputToContain('Archivos huérfanos: 3')
            ->expectsOutputToContain('resources/999/viejo.pdf')
            ->expectsOutputToContain('resources/999/nuevo.pdf')
            ->expectsOutputToContain('thumbnails/999/viejo.png')
            ->expectsOutputToContain('Simulación')
            ->doesntExpectOutputToContain($referenciado->path)
            ->doesntExpectOutputToContain($miniatura)
            ->assertSuccessful();

        foreach (['resources/999/viejo.pdf', 'resources/999/nuevo.pdf', 'thumbnails/999/viejo.png', $referenciado->path, $miniatura] as $ruta) {
            Storage::disk('biblioteca')->assertExists($ruta);
        }
    }

    /**
     * Con --force se borran solo los huérfanos con más antigüedad que el
     * umbral: una subida en curso nunca se elimina.
     */
    public function test_el_comando_con_force_borra_solo_huerfanos_mas_viejos_que_el_umbral(): void
    {
        $recurso = $this->recurso();
        $referenciado = $this->servicio()->store($recurso, $this->pdfReal());
        $miniatura = $this->servicio()->storeThumbnail($recurso, $this->pngReal());
        // Un archivo referenciado muy viejo tampoco se toca.
        touch(Storage::disk('biblioteca')->path($referenciado->path), now()->subDays(30)->getTimestamp());
        $this->huerfano('resources/999/viejo.pdf', 48);
        $this->huerfano('resources/999/reciente.pdf', 2);
        $this->huerfano('thumbnails/999/viejo.png', 30);

        $this->artisan('biblioteca:limpiar-huerfanos', ['--force' => true])
            ->expectsOutputToContain('Eliminados: 2 de 3')
            ->assertSuccessful();

        Storage::disk('biblioteca')->assertMissing('resources/999/viejo.pdf');
        Storage::disk('biblioteca')->assertMissing('thumbnails/999/viejo.png');
        Storage::disk('biblioteca')->assertExists('resources/999/reciente.pdf');
        Storage::disk('biblioteca')->assertExists($referenciado->path);
        Storage::disk('biblioteca')->assertExists($miniatura);

        $this->artisan('biblioteca:limpiar-huerfanos', ['--force' => true, '--horas' => 1])->assertSuccessful();
        Storage::disk('biblioteca')->assertMissing('resources/999/reciente.pdf');
        Storage::disk('biblioteca')->assertExists($referenciado->path);
    }

    /**
     * El comando solo mira resources/ y thumbnails/: el resto del disco
     * puede tener otros usos y nunca se toca.
     */
    public function test_el_comando_ignora_lo_que_esta_fuera_de_resources_y_thumbnails(): void
    {
        $this->huerfano('otros/ajeno.txt', 500);
        $this->huerfano('resources/999/viejo.pdf', 48);

        $this->artisan('biblioteca:limpiar-huerfanos', ['--force' => true])
            ->doesntExpectOutputToContain('otros/ajeno.txt')
            ->assertSuccessful();

        Storage::disk('biblioteca')->assertExists('otros/ajeno.txt');
        Storage::disk('biblioteca')->assertMissing('resources/999/viejo.pdf');
    }

    /**
     * Los registros sin archivo físico solo se reportan: borrarlos o
     * repararlos es una decisión de una persona.
     */
    public function test_el_comando_reporta_registros_sin_archivo_fisico_pero_no_los_borra(): void
    {
        $recurso = $this->recurso();
        $sinArchivo = ResourceFile::factory()->create([
            'resource_id' => $recurso->id,
            'disk' => 'biblioteca',
            'path' => 'resources/'.$recurso->id.'/perdido.pdf',
        ]);
        $recurso->update(['thumbnail' => 'thumbnails/'.$recurso->id.'/perdida.png']);

        $this->artisan('biblioteca:limpiar-huerfanos', ['--force' => true])
            ->expectsOutputToContain('Registros sin archivo físico')
            ->expectsOutputToContain($sinArchivo->path)
            ->expectsOutputToContain('thumbnails/'.$recurso->id.'/perdida.png')
            ->assertSuccessful();

        $this->assertDatabaseHas('resource_files', ['id' => $sinArchivo->id]);
        $this->assertSame('thumbnails/'.$recurso->id.'/perdida.png', $recurso->fresh()->thumbnail);
    }

    public function test_el_comando_informa_cuando_no_hay_nada_que_limpiar(): void
    {
        $this->servicio()->store($this->recurso(), $this->pdfReal());

        $this->artisan('biblioteca:limpiar-huerfanos')
            ->expectsOutputToContain('No hay archivos huérfanos')
            ->assertSuccessful();
    }

    public function test_el_comando_rechaza_una_antiguedad_invalida(): void
    {
        $this->huerfano('resources/999/viejo.pdf', 48);

        $this->artisan('biblioteca:limpiar-huerfanos', ['--force' => true, '--horas' => 'mucho'])
            ->expectsOutputToContain('--horas')
            ->assertFailed();

        Storage::disk('biblioteca')->assertExists('resources/999/viejo.pdf');
    }
}
