<?php

namespace Tests\Feature;

use App\Filament\Resources\ContentResource;
use App\Filament\Resources\ContentResource\Pages\CreateContent;
use App\Filament\Resources\EvidenceResource\Pages\ListEvidence;
use App\Livewire\MiEscuelita\ExperienciaDetalle;
use App\Livewire\MiEscuelita\ExperienciaLista;
use App\Livewire\MiEscuelita\Historial;
use App\Models\Child;
use App\Models\Content;
use App\Models\Environment;
use App\Models\Evidence;
use App\Models\Family;
use App\Models\Feedback;
use App\Models\Media;
use App\Models\Observation;
use App\Models\User;
use App\Notifications\EvidenceSubmittedNotification;
use Database\Seeders\Demo\GrabacionTareasDemoSeeder as Demo;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Escenario para grabar el video del flujo de tareas: docentes que
 * publican, familias que entregan y docentes que responden. Todo contra el
 * comando real; los discos van falsos para no tocar storage/ de verdad.
 */
class PrepararGrabacionDemoTest extends TestCase
{
    use RefreshDatabase;

    private const AUTORRETRATO = 'demo-mi-autorretrato-con-materiales-reciclados';

    private const FIGURAS = 'demo-buscamos-figuras-geometricas-en-casa';

    private const CUENTO = 'demo-dibujo-el-cuento-de-mi-familia';

    private const FLOTA = 'demo-experimento-flota-o-se-hunde';

    private const RECETA = 'demo-mi-receta-favorita-en-5-pasos';

    private const MAQUETA = 'demo-maqueta-del-sistema-solar';

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Storage::fake('public');
    }

    private function preparar(): void
    {
        [$codigo, $salida] = $this->correr();

        $this->assertSame(0, $codigo, $salida);
    }

    private function limpiar(): void
    {
        [$codigo, $salida] = $this->correr(['--limpiar' => true]);

        $this->assertSame(0, $codigo, $salida);
    }

    /**
     * @param  array<string, mixed>  $opciones
     * @return array{0: int, 1: string}
     */
    private function correr(array $opciones = []): array
    {
        $codigo = Artisan::call('aula:preparar-grabacion', $opciones);

        return [$codigo, Artisan::output()];
    }

    private function usuario(string $correo): User
    {
        return User::query()->where('email', $correo)->firstOrFail();
    }

    private function tarea(string $slug): Content
    {
        return Content::query()->where('slug', $slug)->firstOrFail();
    }

    private function nino(string $nombre): Child
    {
        return Child::query()->where('name', $nombre)->firstOrFail();
    }

    private function ambiente(string $nombre): Environment
    {
        return Environment::query()->where('name', $nombre)->firstOrFail();
    }

    /**
     * @return array<string, int>
     */
    private function conteos(): array
    {
        return [
            'users' => User::count(),
            'families' => Family::count(),
            'children' => Child::count(),
            'environments' => Environment::count(),
            'content' => Content::withTrashed()->count(),
            'evidence' => Evidence::withTrashed()->count(),
        ];
    }

    /**
     * La familia demo entrega una tarea desde el portal, como en el video.
     */
    private function entregar(string $correo, string $slug, string $nino): Evidence
    {
        $tarea = $this->tarea($slug);

        Livewire::actingAs($this->usuario($correo))
            ->test(ExperienciaDetalle::class, ['content' => $tarea, 'yaEnviada' => false])
            ->set('child_id', $this->nino($nino)->id)
            ->set('comment', 'Lo hicimos con tapas y cartón.')
            ->set('fotos', [UploadedFile::fake()->image('entrega.jpg')])
            ->call('submit')
            ->assertHasNoErrors();

        return Evidence::query()->where('content_id', $tarea->id)->firstOrFail();
    }

    /**
     * Una entrega con todo lo que deja una toma: archivo, respuesta, aviso.
     *
     * @return array{evidencia: Evidence, observacion: Observation, ruta: string}
     */
    private function entregaConRespuesta(string $slug, string $nino): array
    {
        $tarea = $this->tarea($slug);
        $hijo = $this->nino($nino);
        $docente = User::findOrFail($tarea->teacher_id);

        $evidencia = Evidence::create([
            'content_id' => $tarea->id,
            'child_id' => $hijo->id,
            'family_id' => $hijo->family_id,
            'comment' => 'Mira lo que hicimos.',
            'status' => Evidence::STATUS_RESPONDED,
            'submitted_at' => now(),
            'reviewed_at' => now(),
        ]);

        $ruta = "evidence/{$evidencia->id}/foto.jpg";
        Storage::disk('local')->put($ruta, 'foto');
        $evidencia->media()->create([
            'type' => Media::TYPE_IMAGE, 'path' => $ruta, 'original_name' => 'foto.jpg', 'mime_type' => 'image/jpeg', 'size' => 4,
        ]);

        $observacion = Observation::create([
            'child_id' => $hijo->id, 'content_id' => $tarea->id, 'teacher_id' => $docente->id, 'observation' => 'Excelente trabajo.',
        ]);
        Feedback::create(['observation_id' => $observacion->id, 'teacher_id' => $docente->id, 'message' => 'Sigan así.']);

        return compact('evidencia', 'observacion', 'ruta');
    }

    private function avisoPara(User $usuario): string
    {
        $id = (string) Str::uuid();

        DB::table('notifications')->insert([
            'id' => $id,
            'type' => EvidenceSubmittedNotification::class,
            'notifiable_type' => $usuario->getMorphClass(),
            'notifiable_id' => $usuario->id,
            'data' => '{"message":"aviso"}',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $id;
    }

    /**
     * Prueba que el seeder declara qué datos son de la demo, para identificarlos.
     */
    public function test_el_seeder_expone_que_datos_pertenecen_a_la_demo(): void
    {
        $this->assertEqualsCanonicalizing(
            [self::AUTORRETRATO, self::FIGURAS, self::CUENTO, self::FLOTA, self::RECETA, self::MAQUETA],
            Demo::slugsTareas(),
        );
        $this->assertEqualsCanonicalizing(
            [Demo::DOCENTE_GIRASOLES, Demo::DOCENTE_ESTRELLAS, Demo::FAMILIA_DEMO, Demo::FAMILIA_PRUEBA],
            Demo::correos(),
        );
        $this->assertEqualsCanonicalizing([Demo::DOCENTE_GIRASOLES, Demo::DOCENTE_ESTRELLAS], Demo::correosDocentes());
        $this->assertEqualsCanonicalizing(['Familia Mena', 'Familia Cruz'], Demo::nombresFamilias());
        $this->assertEqualsCanonicalizing(['Girasoles', 'Estrellas'], Demo::nombresAmbientes());

        foreach (Demo::correos() as $correo) {
            $this->assertStringEndsWith('@demo.pestalozzi.test', $correo);
        }
    }

    /**
     * Prueba que el comando crea las cuatro cuentas con su rol y la clave conocida.
     */
    public function test_crea_las_cuatro_cuentas_con_su_rol_y_una_clave_conocida(): void
    {
        $this->preparar();

        $this->assertSame(4, User::count());

        $esperadas = [
            [Demo::DOCENTE_GIRASOLES, 'Mónica Paredes', User::ROLE_GUIA, null],
            [Demo::DOCENTE_ESTRELLAS, 'Luis Andrade', User::ROLE_GUIA, null],
            [Demo::FAMILIA_DEMO, 'Carlos Mena', User::ROLE_FAMILIA, 'Familia Mena'],
            [Demo::FAMILIA_PRUEBA, 'Daniela Cruz', User::ROLE_FAMILIA, 'Familia Cruz'],
        ];

        foreach ($esperadas as [$correo, $nombre, $rol, $familia]) {
            $usuario = $this->usuario($correo);

            $this->assertSame($nombre, $usuario->name);
            $this->assertSame($rol, $usuario->role);
            $this->assertTrue($usuario->hasRole($rol), "{$correo} debería tener el rol {$rol} también en Spatie");
            $this->assertTrue($usuario->active);
            $this->assertTrue(Hash::check('password', $usuario->password));
            $this->assertSame($familia, $usuario->family?->name);
        }

        $this->assertSame(2, Family::count());
    }

    /**
     * Prueba que los docentes entran al panel y las familias no.
     */
    public function test_los_docentes_entran_al_panel_y_las_familias_al_portal(): void
    {
        $this->preparar();

        $panel = Filament::getPanel('admin');

        $this->assertTrue($this->usuario(Demo::DOCENTE_GIRASOLES)->canAccessPanel($panel));
        $this->assertTrue($this->usuario(Demo::DOCENTE_ESTRELLAS)->canAccessPanel($panel));
        $this->assertFalse($this->usuario(Demo::FAMILIA_DEMO)->canAccessPanel($panel));

        $this->actingAs($this->usuario(Demo::FAMILIA_DEMO))
            ->get(route('mi-escuelita.experiencias.index'))
            ->assertOk();
    }

    /**
     * Prueba que cada docente es titular de su ambiente y que ahí se permiten tareas.
     */
    public function test_cada_docente_es_titular_de_su_ambiente_y_ahi_se_permiten_tareas(): void
    {
        $this->preparar();

        $this->assertSame(2, Environment::count());

        $girasoles = $this->ambiente('Girasoles');
        $estrellas = $this->ambiente('Estrellas');

        $this->assertSame($this->usuario(Demo::DOCENTE_GIRASOLES)->id, (int) $girasoles->teacher_id);
        $this->assertSame($this->usuario(Demo::DOCENTE_ESTRELLAS)->id, (int) $estrellas->teacher_id);

        foreach ([$girasoles, $estrellas] as $ambiente) {
            $this->assertTrue($ambiente->active);
            $this->assertArrayHasKey(Content::TYPE_TASK, ContentResource::tipoOptions($ambiente->id));
            $this->assertTrue(ContentResource::esTipoPermitidoEnAmbiente($ambiente->id, Content::TYPE_TASK));
        }
    }

    /**
     * Prueba que los niños quedan en su ambiente y familia, con edad de aula.
     */
    public function test_crea_a_los_ninos_en_su_ambiente_y_familia(): void
    {
        $this->preparar();

        $this->assertSame(3, Child::count());

        $esperados = [
            ['Sofía Mena', 'Girasoles', 'Familia Mena'],
            ['Mateo Mena', 'Estrellas', 'Familia Mena'],
            ['Emilia Cruz', 'Girasoles', 'Familia Cruz'],
        ];

        foreach ($esperados as [$nombre, $ambiente, $familia]) {
            $nino = $this->nino($nombre);

            $this->assertSame($ambiente, $nino->environment->name);
            $this->assertSame($familia, $nino->family->name);
            $this->assertSame('active', $nino->status);
            $this->assertGreaterThanOrEqual(3, $nino->birth_date->age);
            $this->assertLessThanOrEqual(9, $nino->birth_date->age);
        }
    }

    /**
     * Prueba que hay seis tareas publicadas por ambiente que piden entrega y no hay entregas.
     */
    public function test_crea_seis_tareas_publicadas_que_piden_entrega(): void
    {
        $this->preparar();

        $esperadas = [
            self::AUTORRETRATO => ['Mi autorretrato con materiales reciclados', 'Girasoles'],
            self::FIGURAS => ['Buscamos figuras geométricas en casa', 'Girasoles'],
            self::CUENTO => ['Dibujo: el cuento de mi familia', 'Girasoles'],
            self::FLOTA => ['Experimento: ¿flota o se hunde?', 'Estrellas'],
            self::RECETA => ['Mi receta favorita en 5 pasos', 'Estrellas'],
            self::MAQUETA => ['Maqueta del sistema solar', 'Estrellas'],
        ];

        $this->assertSame(6, Content::count());
        $this->assertSame(0, Evidence::count());

        foreach ($esperadas as $slug => [$titulo, $ambiente]) {
            $tarea = $this->tarea($slug);

            $this->assertSame($titulo, $tarea->title);
            $this->assertSame(Content::TYPE_TASK, $tarea->type);
            $this->assertSame(Content::STATUS_PUBLISHED, $tarea->status);
            $this->assertTrue($tarea->requires_evidence);
            $this->assertTrue($tarea->published_at->isPast());
            $this->assertSame($ambiente, $tarea->environment->name);
            $this->assertSame($tarea->environment->teacher_id, $tarea->teacher_id);
            $this->assertNotEmpty($tarea->description);
            $this->assertStringContainsString('<ol>', $tarea->body);
            $this->assertStringContainsString('<li>', $tarea->body);
        }
    }

    /**
     * Prueba que cada ambiente tiene una tarea abierta, una que vence mañana y una vencida.
     */
    public function test_las_fechas_limite_son_abierta_manana_y_vencida(): void
    {
        $this->preparar();

        $esperadas = [
            self::AUTORRETRATO => 7, self::FIGURAS => 1, self::CUENTO => -1,
            self::FLOTA => 7, self::RECETA => 1, self::MAQUETA => -1,
        ];

        foreach ($esperadas as $slug => $dias) {
            $tarea = $this->tarea($slug);

            $this->assertSame(today()->addDays($dias)->toDateString(), $tarea->due_date->toDateString(), $slug);
            $this->assertSame($dias < 0, $tarea->isOverdue(), $slug);
        }
    }

    /**
     * Prueba que cada familia ve solo las tareas de los ambientes de sus hijos, también la vencida.
     */
    public function test_cada_familia_ve_solo_las_tareas_de_los_ambientes_de_sus_hijos(): void
    {
        $this->preparar();

        $girasoles = [self::AUTORRETRATO, self::FIGURAS, self::CUENTO];
        $estrellas = [self::FLOTA, self::RECETA, self::MAQUETA];

        $cruz = $this->usuario(Demo::FAMILIA_PRUEBA);
        $mena = $this->usuario(Demo::FAMILIA_DEMO);

        foreach ($girasoles as $slug) {
            $this->assertTrue($cruz->can('view', $this->tarea($slug)), "Familia Cruz debería ver {$slug}");
            $this->assertTrue($mena->can('view', $this->tarea($slug)), "Familia Mena debería ver {$slug}");
        }

        foreach ($estrellas as $slug) {
            $this->assertFalse($cruz->can('view', $this->tarea($slug)), "Familia Cruz no debería ver {$slug}");
            $this->assertTrue($mena->can('view', $this->tarea($slug)), "Familia Mena debería ver {$slug}");
        }

        $this->assertTrue($this->tarea(self::CUENTO)->isOverdue());

        Livewire::actingAs($cruz)->test(ExperienciaLista::class)
            ->assertSee('Mi autorretrato con materiales reciclados')
            ->assertSee('Dibujo: el cuento de mi familia')
            ->assertDontSee('Maqueta del sistema solar');
    }

    /**
     * Prueba que correrlo dos veces no duplica nada y vuelve a ubicar las fechas respecto a hoy.
     */
    public function test_correrlo_dos_veces_no_duplica_nada_y_reajusta_las_fechas(): void
    {
        $this->preparar();
        $conteos = $this->conteos();

        // Pasan diez días y alguien dejó las tareas en mal estado.
        Content::query()->whereIn('slug', Demo::slugsTareas())->update([
            'status' => Content::STATUS_DRAFT,
            'published_at' => null,
            'requires_evidence' => false,
            'title' => 'Editada',
        ]);
        $this->travel(10)->days();

        $this->preparar();

        $this->assertSame($conteos, $this->conteos());

        foreach ([self::AUTORRETRATO, self::FLOTA] as $slug) {
            $this->assertTrue($this->tarea($slug)->due_date->greaterThan(today()), "{$slug} debería estar abierta");
            $this->assertSame(today()->addDays(7)->toDateString(), $this->tarea($slug)->due_date->toDateString());
        }

        foreach ([self::FIGURAS, self::RECETA] as $slug) {
            $this->assertSame(today()->addDay()->toDateString(), $this->tarea($slug)->due_date->toDateString());
        }

        foreach ([self::CUENTO, self::MAQUETA] as $slug) {
            $this->assertTrue($this->tarea($slug)->due_date->lessThan(today()), "{$slug} debería estar vencida");
            $this->assertTrue($this->tarea($slug)->isOverdue());
        }

        foreach (Demo::slugsTareas() as $slug) {
            $tarea = $this->tarea($slug);

            $this->assertSame(Content::STATUS_PUBLISHED, $tarea->status);
            $this->assertTrue($tarea->published_at->lessThanOrEqualTo(now()));
            $this->assertTrue($tarea->requires_evidence);
            $this->assertNotSame('Editada', $tarea->title);
        }
    }

    /**
     * Prueba que reponer una tarea publicada no manda avisos a las familias.
     */
    public function test_reponer_las_tareas_no_manda_avisos_de_publicacion(): void
    {
        $this->preparar();
        Content::query()->whereIn('slug', Demo::slugsTareas())->update(['status' => Content::STATUS_DRAFT]);

        Notification::fake();

        $this->preparar();

        Notification::assertNothingSent();
    }

    /**
     * Prueba que una tarea de la demo borrada en el panel se repone sin chocar con el slug.
     */
    public function test_repone_una_tarea_de_la_demo_que_se_borro_en_el_panel(): void
    {
        $this->preparar();
        $id = $this->tarea(self::FIGURAS)->id;
        Content::query()->whereKey($id)->first()->delete();

        $this->preparar();

        $this->assertSame($id, $this->tarea(self::FIGURAS)->id);
        $this->assertNull($this->tarea(self::FIGURAS)->deleted_at);
        $this->assertSame(6, Content::count());
    }

    /**
     * Prueba que preparar la demo no modifica ninguna fila que no sea suya.
     */
    public function test_preparar_no_modifica_filas_que_no_son_de_la_demo(): void
    {
        $docente = User::factory()->guia()->create(['email' => 'docente.real@colegio.test']);
        $ambiente = Environment::factory()->create(['name' => 'Mentes Absorbentes', 'teacher_id' => $docente->id]);
        $familia = Family::factory()->create(['name' => 'Familia Real']);
        $padre = User::factory()->familia($familia)->create(['email' => 'padre.real@colegio.test']);
        $nino = Child::factory()->create(['family_id' => $familia->id, 'environment_id' => $ambiente->id]);
        $contenido = Content::factory()->published()->create(['teacher_id' => $docente->id, 'environment_id' => $ambiente->id]);
        $evidencia = Evidence::factory()->create(['content_id' => $contenido->id, 'child_id' => $nino->id, 'family_id' => $familia->id]);

        $filas = [$docente, $ambiente, $familia, $padre, $nino, $contenido, $evidencia];
        $antes = array_map(fn ($fila) => $fila->fresh()->getAttributes(), $filas);

        $this->preparar();
        $this->preparar();

        foreach ($filas as $i => $fila) {
            $this->assertSame($antes[$i], $fila->fresh()->getAttributes(), 'Cambió '.$fila::class);
        }
    }

    /**
     * Prueba que en producción no escribe nada y avisa con un error.
     */
    public function test_se_niega_a_correr_en_produccion_sin_escribir_nada(): void
    {
        $this->app['env'] = 'production';

        [$codigo, $salida] = $this->correr();

        $this->assertSame(1, $codigo);
        $this->assertStringContainsString('producción', $salida);
        $this->assertSame(array_fill_keys(array_keys($this->conteos()), 0), $this->conteos());

        // El seeder por su cuenta también se niega.
        $this->app->make(Demo::class)->run();

        $this->assertSame(array_fill_keys(array_keys($this->conteos()), 0), $this->conteos());
    }

    /**
     * Prueba que en producción tampoco borra nada con --limpiar.
     */
    public function test_limpiar_se_niega_en_produccion_y_no_borra_nada(): void
    {
        $this->preparar();
        $entrega = $this->entregaConRespuesta(self::FIGURAS, 'Emilia Cruz');

        $this->app['env'] = 'production';

        [$codigo, $salida] = $this->correr(['--limpiar' => true]);

        $this->assertSame(1, $codigo);
        $this->assertStringContainsString('producción', $salida);
        $this->assertDatabaseHas('evidence', ['id' => $entrega['evidencia']->id]);
        $this->assertDatabaseHas('observations', ['id' => $entrega['observacion']->id]);
        Storage::disk('local')->assertExists($entrega['ruta']);
    }

    /**
     * Prueba que un ambiente de la demo con otro titular aborta sin cambiar nada.
     */
    #[DataProvider('ambientesDeLaDemo')]
    public function test_aborta_si_un_ambiente_de_la_demo_pertenece_a_otra_persona(string $nombre): void
    {
        $otra = User::factory()->guia()->create();
        $ambiente = Environment::factory()->create(['name' => $nombre, 'teacher_id' => $otra->id]);
        $antes = $this->conteos();

        [$codigo, $salida] = $this->correr();

        $this->assertSame(1, $codigo);
        $this->assertStringContainsString($nombre, $salida);
        $this->assertSame($antes, $this->conteos());
        $this->assertSame($otra->id, (int) $ambiente->fresh()->teacher_id);
        $this->assertDatabaseMissing('users', ['email' => Demo::DOCENTE_GIRASOLES]);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function ambientesDeLaDemo(): array
    {
        return ['Girasoles' => ['Girasoles'], 'Estrellas' => ['Estrellas']];
    }

    /**
     * Prueba que un ambiente de la demo sin docente tampoco se adopta.
     */
    public function test_aborta_si_un_ambiente_de_la_demo_no_tiene_docente(): void
    {
        $ambiente = Environment::factory()->create(['name' => 'Girasoles', 'teacher_id' => null]);
        $antes = $this->conteos();

        [$codigo, $salida] = $this->correr();

        $this->assertSame(1, $codigo);
        $this->assertStringContainsString('Girasoles', $salida);
        $this->assertSame($antes, $this->conteos());
        $this->assertNull($ambiente->fresh()->teacher_id);
    }

    /**
     * Prueba que una familia real con el nombre de la demo no se adopta.
     */
    public function test_aborta_si_ya_existe_una_familia_real_con_el_nombre_de_la_demo(): void
    {
        $familia = Family::factory()->create(['name' => 'Familia Mena']);
        $antes = $this->conteos();

        [$codigo, $salida] = $this->correr();

        $this->assertSame(1, $codigo);
        $this->assertStringContainsString('Familia Mena', $salida);
        $this->assertSame($antes, $this->conteos());
        $this->assertSame(0, $familia->children()->count());
    }

    /**
     * Prueba que una cuenta con el correo de la demo y otro rol no se reutiliza.
     */
    public function test_aborta_si_un_correo_de_la_demo_ya_existe_con_otro_rol(): void
    {
        User::factory()->administrador()->create(['email' => Demo::DOCENTE_GIRASOLES]);
        $antes = $this->conteos();

        [$codigo, $salida] = $this->correr();

        $this->assertSame(1, $codigo);
        $this->assertStringContainsString(Demo::DOCENTE_GIRASOLES, $salida);
        $this->assertSame($antes, $this->conteos());
    }

    /**
     * Prueba que con un conflicto --limpiar tampoco borra nada.
     */
    public function test_limpiar_tambien_aborta_ante_un_conflicto_sin_borrar_nada(): void
    {
        $this->preparar();
        $entrega = $this->entregaConRespuesta(self::FIGURAS, 'Emilia Cruz');
        $otra = User::factory()->guia()->create();
        $this->ambiente('Girasoles')->update(['teacher_id' => $otra->id]);

        [$codigo, $salida] = $this->correr(['--limpiar' => true]);

        $this->assertSame(1, $codigo);
        $this->assertStringContainsString('Girasoles', $salida);
        $this->assertDatabaseHas('evidence', ['id' => $entrega['evidencia']->id]);
        Storage::disk('local')->assertExists($entrega['ruta']);
    }

    /**
     * Prueba que --limpiar borra las entregas con sus archivos, respuestas y avisos.
     */
    public function test_limpiar_borra_las_entregas_con_sus_archivos_respuestas_y_avisos(): void
    {
        $this->preparar();

        $uno = $this->entregaConRespuesta(self::FIGURAS, 'Emilia Cruz');
        $dos = $this->entregaConRespuesta(self::FLOTA, 'Mateo Mena');
        $avisos = [
            $this->avisoPara($this->usuario(Demo::DOCENTE_GIRASOLES)),
            $this->avisoPara($this->usuario(Demo::FAMILIA_DEMO)),
        ];
        $mediaIds = Media::query()->pluck('id')->all();

        $this->limpiar();

        foreach ([$uno, $dos] as $entrega) {
            $this->assertDatabaseMissing('evidence', ['id' => $entrega['evidencia']->id]);
            $this->assertDatabaseMissing('observations', ['id' => $entrega['observacion']->id]);
            Storage::disk('local')->assertMissing($entrega['ruta']);
        }

        $this->assertSame(0, Evidence::withTrashed()->count());
        $this->assertSame(0, Observation::withTrashed()->count());
        $this->assertSame(0, Feedback::withTrashed()->count());
        $this->assertSame(0, Media::query()->whereIn('id', $mediaIds)->count());
        $this->assertSame(0, DB::table('notifications')->whereIn('id', $avisos)->count());
        $this->assertSame([], Storage::disk('local')->allFiles());

        // Vuelve al inicio: las seis tareas siguen publicadas y sin entregas.
        $this->assertSame(6, Content::count());
        $this->assertSame(3, Child::count());
        $this->assertSame(4, User::count());
    }

    /**
     * Prueba que --limpiar borra las tareas creadas en la toma y deja las seis de la demo.
     */
    public function test_limpiar_borra_las_tareas_creadas_en_la_toma_y_repone_las_de_la_demo(): void
    {
        $this->preparar();

        $docente = $this->usuario(Demo::DOCENTE_GIRASOLES);
        $idsSembradas = Content::query()->whereIn('slug', Demo::slugsTareas())->pluck('id', 'slug')->all();

        $extra = Content::factory()->published()->requiresEvidence()->create([
            'type' => Content::TYPE_TASK,
            'teacher_id' => $docente->id,
            'environment_id' => $this->ambiente('Girasoles')->id,
            'cover_image' => 'content/portada-extra.jpg',
        ]);
        $borrada = Content::factory()->create(['teacher_id' => $docente->id]);
        $borrada->delete();

        Storage::disk('public')->put('content/portada-extra.jpg', 'portada');
        Storage::disk('local')->put('content/adjunto-extra.pdf', 'adjunto');
        $extra->media()->create([
            'type' => Media::TYPE_PDF, 'path' => 'content/adjunto-extra.pdf', 'original_name' => 'adjunto.pdf', 'mime_type' => 'application/pdf', 'size' => 7,
        ]);

        $entrega = $this->entregaConRespuesta(self::FIGURAS, 'Emilia Cruz');
        $sobreExtra = Evidence::create([
            'content_id' => $extra->id,
            'child_id' => $this->nino('Emilia Cruz')->id,
            'family_id' => $this->nino('Emilia Cruz')->family_id,
            'status' => Evidence::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);
        Storage::disk('local')->put("evidence/{$sobreExtra->id}/hoja.pdf", 'hoja');
        $sobreExtra->media()->create([
            'type' => Media::TYPE_PDF, 'path' => "evidence/{$sobreExtra->id}/hoja.pdf", 'original_name' => 'hoja.pdf', 'mime_type' => 'application/pdf', 'size' => 4,
        ]);

        // Una tarea de la demo quedó editada y despublicada durante la toma.
        $this->tarea(self::AUTORRETRATO)->update(['title' => 'Título editado', 'status' => Content::STATUS_DRAFT]);

        $this->limpiar();

        $this->assertDatabaseMissing('content', ['id' => $extra->id]);
        $this->assertDatabaseMissing('content', ['id' => $borrada->id]);
        $this->assertDatabaseMissing('evidence', ['id' => $sobreExtra->id]);
        $this->assertDatabaseMissing('evidence', ['id' => $entrega['evidencia']->id]);
        $this->assertSame(0, Media::count());
        Storage::disk('public')->assertMissing('content/portada-extra.jpg');
        Storage::disk('local')->assertMissing('content/adjunto-extra.pdf');
        $this->assertSame([], Storage::disk('local')->allFiles());

        $this->assertSame(6, Content::withTrashed()->count());
        $this->assertSame($idsSembradas, Content::query()->whereIn('slug', Demo::slugsTareas())->pluck('id', 'slug')->all());

        $repuesta = $this->tarea(self::AUTORRETRATO);
        $this->assertSame('Mi autorretrato con materiales reciclados', $repuesta->title);
        $this->assertSame(Content::STATUS_PUBLISHED, $repuesta->status);
    }

    /**
     * Prueba que --limpiar no toca nada que no sea de la demo.
     */
    public function test_limpiar_no_toca_nada_que_no_sea_de_la_demo(): void
    {
        $this->preparar();

        $admin = User::factory()->administrador()->create();
        $docente = User::factory()->guia()->create();
        $ambiente = Environment::factory()->create(['name' => 'Mentes Absorbentes', 'teacher_id' => $docente->id]);
        $familia = Family::factory()->create(['name' => 'Familia Real']);
        $padre = User::factory()->familia($familia)->create();
        $nino = Child::factory()->create(['family_id' => $familia->id, 'environment_id' => $ambiente->id]);
        $contenido = Content::factory()->published()->requiresEvidence()->create(['teacher_id' => $docente->id, 'environment_id' => $ambiente->id]);
        $evidencia = Evidence::factory()->create(['content_id' => $contenido->id, 'child_id' => $nino->id, 'family_id' => $familia->id]);
        $observacion = Observation::factory()->create(['child_id' => $nino->id, 'content_id' => $contenido->id, 'teacher_id' => $docente->id]);

        Storage::disk('local')->put("evidence/{$evidencia->id}/real.jpg", 'real');
        $media = $evidencia->media()->create([
            'type' => Media::TYPE_IMAGE, 'path' => "evidence/{$evidencia->id}/real.jpg", 'original_name' => 'real.jpg', 'mime_type' => 'image/jpeg', 'size' => 4,
        ]);
        Storage::disk('public')->put('content/portada-real.jpg', 'real');
        $contenido->update(['cover_image' => 'content/portada-real.jpg']);
        $aviso = $this->avisoPara($padre);

        // Mezclas dentro del ambiente de la demo: nada de esto es de la toma.
        $deAdmin = Content::factory()->published()->create(['teacher_id' => $admin->id, 'environment_id' => $this->ambiente('Girasoles')->id]);
        $ninoAjeno = Child::factory()->create(['family_id' => $familia->id, 'environment_id' => $this->ambiente('Girasoles')->id]);
        $entregaAjena = Evidence::create([
            'content_id' => $this->tarea(self::FIGURAS)->id,
            'child_id' => $ninoAjeno->id,
            'family_id' => $familia->id,
            'status' => Evidence::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);

        // Y una toma completa de la demo para que haya algo que limpiar.
        $this->entregaConRespuesta(self::FIGURAS, 'Emilia Cruz');
        $this->avisoPara($this->usuario(Demo::FAMILIA_PRUEBA));

        $this->limpiar();

        foreach ([$admin, $docente, $ambiente, $familia, $padre, $nino, $contenido, $evidencia, $observacion, $media, $deAdmin, $ninoAjeno, $entregaAjena] as $fila) {
            $this->assertDatabaseHas($fila->getTable(), ['id' => $fila->id]);
        }

        $this->assertDatabaseHas('notifications', ['id' => $aviso]);
        Storage::disk('local')->assertExists("evidence/{$evidencia->id}/real.jpg");
        Storage::disk('public')->assertExists('content/portada-real.jpg');
        $this->assertSame(1, Evidence::query()->where('child_id', $nino->id)->count());
    }

    /**
     * Prueba que la familia demo entrega una tarea desde el portal y queda enviada para el niño correcto.
     */
    public function test_la_familia_demo_entrega_una_tarea_desde_el_portal(): void
    {
        Notification::fake();
        $this->preparar();

        $tarea = $this->tarea(self::FIGURAS);
        $padre = $this->usuario(Demo::FAMILIA_DEMO);
        $sofia = $this->nino('Sofía Mena');

        Livewire::actingAs($padre)
            ->test(ExperienciaDetalle::class, ['content' => $tarea, 'yaEnviada' => false])
            ->assertSee('Compartir experiencia')
            ->set('child_id', $sofia->id)
            ->set('comment', 'Encontramos un círculo en cada cuarto.')
            ->set('fotos', [UploadedFile::fake()->image('figuras.jpg'), UploadedFile::fake()->image('figuras2.jpg')])
            ->call('submit')
            ->assertHasNoErrors();

        $evidencia = Evidence::query()->firstOrFail();

        $this->assertSame($tarea->id, $evidencia->content_id);
        $this->assertSame($sofia->id, $evidencia->child_id);
        $this->assertSame($padre->family_id, $evidencia->family_id);
        $this->assertSame(Evidence::STATUS_SUBMITTED, $evidencia->status);
        $this->assertCount(2, $evidencia->media);

        foreach ($evidencia->media as $media) {
            Storage::disk('local')->assertExists($media->path);
        }

        Notification::assertSentTo($this->usuario(Demo::DOCENTE_GIRASOLES), EvidenceSubmittedNotification::class);
    }

    /**
     * Prueba que la familia puede entregar también la tarea vencida.
     */
    public function test_la_familia_puede_entregar_la_tarea_vencida(): void
    {
        Notification::fake();
        $this->preparar();

        $evidencia = $this->entregar(Demo::FAMILIA_PRUEBA, self::CUENTO, 'Emilia Cruz');

        $this->assertSame(Evidence::STATUS_SUBMITTED, $evidencia->status);
        $this->assertTrue($evidencia->content->isOverdue());
    }

    /**
     * Prueba que la docente responde desde Evidencias y la familia ve la respuesta en su historial.
     *
     * Se maneja la acción real "Responder" de EvidenceResource con Livewire: la
     * docente ve la entrega, escribe su respuesta y la evidencia pasa a respondida.
     */
    public function test_la_docente_responde_la_entrega_desde_el_panel_y_la_familia_la_ve(): void
    {
        Notification::fake();
        $this->preparar();

        $evidencia = $this->entregar(Demo::FAMILIA_DEMO, self::FIGURAS, 'Sofía Mena');
        $monica = $this->usuario(Demo::DOCENTE_GIRASOLES);
        $respuesta = 'Qué lindo trabajo, Sofía. Encontraste figuras muy variadas.';

        // La docente del otro ambiente no ve la entrega.
        Livewire::actingAs($this->usuario(Demo::DOCENTE_ESTRELLAS))
            ->test(ListEvidence::class)
            ->assertCanNotSeeTableRecords([$evidencia]);

        Livewire::actingAs($monica)
            ->test(ListEvidence::class)
            ->assertCanSeeTableRecords([$evidencia])
            ->callTableAction('responder', $evidencia, data: ['respuesta' => $respuesta])
            ->assertHasNoTableActionErrors();

        $evidencia->refresh();

        $this->assertSame(Evidence::STATUS_RESPONDED, $evidencia->status);
        $this->assertNotNull($evidencia->reviewed_at);
        $this->assertDatabaseHas('observations', [
            'child_id' => $evidencia->child_id,
            'content_id' => $evidencia->content_id,
            'teacher_id' => $monica->id,
            'observation' => $respuesta,
        ]);

        Livewire::actingAs($this->usuario(Demo::FAMILIA_DEMO))
            ->test(Historial::class)
            ->assertSee('Buscamos figuras geométricas en casa')
            ->assertSee('Con respuesta')
            ->assertSee($respuesta);
    }

    /**
     * Prueba que cada docente crea una tarea para su ambiente y le llega a las familias correctas.
     */
    public function test_cada_docente_crea_una_tarea_para_su_ambiente_y_llega_a_las_familias_correctas(): void
    {
        $this->preparar();

        $cruz = $this->usuario(Demo::FAMILIA_PRUEBA);
        $mena = $this->usuario(Demo::FAMILIA_DEMO);

        $casos = [
            [Demo::DOCENTE_GIRASOLES, 'Girasoles', 'Tarea nueva de Girasoles', true],
            [Demo::DOCENTE_ESTRELLAS, 'Estrellas', 'Tarea nueva de Estrellas', false],
        ];

        foreach ($casos as [$correo, $nombreAmbiente, $titulo, $laVeCruz]) {
            $docente = $this->usuario($correo);
            $ambiente = $this->ambiente($nombreAmbiente);

            Livewire::actingAs($docente)
                ->test(CreateContent::class)
                ->fillForm([
                    'title' => $titulo,
                    'type' => Content::TYPE_TASK,
                    'status' => Content::STATUS_PUBLISHED,
                    'environment_id' => $ambiente->id,
                    'teacher_id' => $docente->id,
                    'requires_evidence' => true,
                    'published_at' => now()->subMinute(),
                    'due_date' => today()->addDays(3)->toDateString(),
                ])
                ->call('create')
                ->assertHasNoFormErrors();

            $creada = Content::query()->where('title', $titulo)->firstOrFail();

            $this->assertSame(Content::TYPE_TASK, $creada->type);
            $this->assertSame($docente->id, $creada->teacher_id);
            $this->assertSame($ambiente->id, $creada->environment_id);
            $this->assertTrue($creada->requires_evidence);
            $this->assertTrue($mena->can('view', $creada));
            $this->assertSame($laVeCruz, $cruz->can('view', $creada));
        }
    }

    /**
     * Prueba que el resumen lista cuentas, direcciones, tareas y cómo levantar el servidor.
     */
    public function test_el_resumen_indica_cuentas_direcciones_tareas_y_como_levantar_el_servidor(): void
    {
        [$codigo, $salida] = $this->correr();

        $this->assertSame(0, $codigo);

        foreach (Demo::correos() as $correo) {
            $this->assertStringContainsString($correo, $salida);
        }

        foreach (['/login', '/admin', 'password', 'Girasoles', 'Estrellas'] as $texto) {
            $this->assertStringContainsString($texto, $salida);
        }

        foreach (['Mi autorretrato con materiales reciclados', 'Maqueta del sistema solar'] as $titulo) {
            $this->assertStringContainsString($titulo, $salida);
        }

        $this->assertStringContainsString(today()->addDay()->format('d/m/Y'), $salida);
        $this->assertStringContainsString('upload_max_filesize=64M', $salida);
        $this->assertStringContainsString('post_max_size=70M', $salida);
    }
}
