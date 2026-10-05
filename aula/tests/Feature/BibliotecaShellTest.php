<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\HtmlString;
use Tests\TestCase;

/**
 * Cascarón de la Biblioteca: layout, navegación, marca configurable,
 * redirecciones por rol y perfil del estudiante.
 */
class BibliotecaShellTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Textos de los enlaces marcados como página actual (sidebar y menú móvil).
     *
     * @return list<string>
     */
    private function enlacesActuales(string $html): array
    {
        preg_match_all('/<a\b[^>]*aria-current="page"[^>]*>(.*?)<\/a>/s', $html, $m);

        return array_values(array_unique(array_map(
            fn (string $texto): string => trim(preg_replace('/\s+/', ' ', strip_tags($texto))),
            $m[1],
        )));
    }

    /**
     * Cada estudiante y docente recibe 200 con los puntos de referencia accesibles.
     */
    public function test_estudiante_y_docente_ven_la_biblioteca_con_sus_puntos_de_referencia(): void
    {
        foreach ([User::factory()->estudiante()->create(), User::factory()->guia()->create()] as $usuario) {
            $this->actingAs($usuario)
                ->get(route('biblioteca.index'))
                ->assertOk()
                ->assertSee('<html lang="es" data-theme="system"', false)
                ->assertSee('Saltar al contenido')
                ->assertSee('href="#contenido"', false)
                ->assertSee('<nav aria-label="Navegación principal"', false)
                ->assertSee('<main id="contenido" tabindex="-1"', false)
                ->assertSee('name="color-scheme"', false)
                ->assertSee('name="theme-color"', false)
                ->assertSee('bib-theme', false)
                ->assertSee('action="'.route('logout').'"', false)
                ->assertSee('Salir')
                ->assertSee($usuario->name);
        }
    }

    /**
     * El script del <head> usa localStorage protegido para que no falle sin él.
     */
    public function test_el_tema_se_aplica_antes_del_primer_pintado_con_localstorage_protegido(): void
    {
        $html = $this->actingAs(User::factory()->estudiante()->create())
            ->get(route('biblioteca.index'))
            ->assertOk()
            ->getContent();

        $this->assertMatchesRegularExpression('/<head>.*<script>[^<]*bib-theme[^<]*<\/script>.*<\/head>/s', $html);
        $this->assertMatchesRegularExpression('/<script>[^<]*try\s*\{[^<]*localStorage[^<]*\}\s*catch/s', $html);
    }

    /**
     * El selector de tema ofrece claro, oscuro y automático como botones.
     */
    public function test_el_selector_de_tema_ofrece_claro_oscuro_y_automatico(): void
    {
        $this->actingAs(User::factory()->estudiante()->create())
            ->get(route('biblioteca.index'))
            ->assertSee('role="group"', false)
            ->assertSee('aria-label="Tema de color"', false)
            ->assertSee('aria-label="Tema claro"', false)
            ->assertSee('aria-label="Tema oscuro"', false)
            ->assertSee('aria-label="Tema automático"', false)
            ->assertSee('aria-pressed', false);
    }

    /**
     * El menú móvil expone su estado y a qué panel controla.
     */
    public function test_el_boton_del_menu_movil_expone_su_estado(): void
    {
        $this->actingAs(User::factory()->estudiante()->create())
            ->get(route('biblioteca.index'))
            ->assertSee('aria-label="Abrir menú"', false)
            ->assertSee('aria-controls="bib-drawer"', false)
            ->assertSee(':aria-expanded', false)
            ->assertSee('id="bib-drawer"', false);
    }

    /**
     * "Administrar" solo existe para quien puede entrar al panel.
     */
    public function test_administrar_aparece_para_docentes_y_personal_pero_no_para_estudiantes(): void
    {
        foreach (['guia', 'administrador', 'coordinacion'] as $rol) {
            $this->actingAs(User::factory()->{$rol}()->create())
                ->get(route('biblioteca.index'))
                ->assertOk()
                ->assertSee('href="'.url('/admin').'"', false)
                ->assertSee('Administrar');
        }

        $this->actingAs(User::factory()->estudiante()->create())
            ->get(route('biblioteca.index'))
            ->assertOk()
            ->assertDontSee('/admin', false)
            ->assertDontSee('Administrar');
    }

    /**
     * "Mis cursos" se muestra recién cuando exista la ruta de cursos.
     */
    public function test_mis_cursos_solo_aparece_cuando_existe_la_ruta(): void
    {
        $estudiante = User::factory()->estudiante()->create();

        $this->assertFalse(Route::has('biblioteca.cursos'));
        $this->actingAs($estudiante)->get(route('biblioteca.index'))->assertDontSee('Mis cursos');

        Route::get('/biblioteca-cursos-de-prueba', fn () => 'cursos')->name('biblioteca.cursos');
        Route::getRoutes()->refreshNameLookups();

        $this->actingAs($estudiante)
            ->get(route('biblioteca.index'))
            ->assertSee('Mis cursos')
            ->assertSee('href="'.route('biblioteca.cursos').'"', false);
    }

    /**
     * Cuando hay una sola página actual, solo su enlace lleva aria-current.
     */
    public function test_el_enlace_de_la_pagina_actual_lleva_aria_current(): void
    {
        $html = $this->actingAs(User::factory()->estudiante()->create())
            ->get(route('biblioteca.index'))
            ->getContent();

        $this->assertSame(['Biblioteca'], $this->enlacesActuales($html));

        $html = $this->actingAs(User::factory()->estudiante()->create())
            ->get(route('profile.edit'))
            ->getContent();

        $this->assertSame(['Perfil'], $this->enlacesActuales($html));
    }

    /**
     * Nombre, logo, icono y color salen de config('biblioteca.brand').
     */
    public function test_la_marca_sale_de_la_configuracion(): void
    {
        $estudiante = User::factory()->estudiante()->create();

        $this->actingAs($estudiante)
            ->get(route('biblioteca.index'))
            ->assertSee('<title>Biblioteca · Pestalozzi</title>', false)
            ->assertSee(asset('images/pestalozzi-logo.svg'), false)
            ->assertSee('href="/favicon.svg"', false)
            ->assertSee('--bib-primary: #126333;', false)
            ->assertDontSee('html:root[data-theme="dark"]', false);

        config(['biblioteca.brand' => [
            'name' => 'Colegio Demo',
            'logo' => 'images/demo-logo.png',
            'favicon' => 'images/demo.ico',
            'primary' => '#1d4ed8',
        ]]);

        $this->actingAs($estudiante)
            ->get(route('biblioteca.index'))
            ->assertSee('<title>Biblioteca · Colegio Demo</title>', false)
            ->assertSee('Colegio Demo')
            ->assertDontSee('Pestalozzi')
            ->assertDontSee(asset('images/pestalozzi-logo.svg'), false)
            ->assertSee('src="'.asset('images/demo-logo.png').'"', false)
            ->assertSee('href="'.asset('images/demo.ico').'"', false)
            ->assertSee('--bib-primary: #1D4ED8;', false)
            ->assertSee('html:root[data-theme="dark"] { --bib-primary: color-mix(in srgb, #1D4ED8 55%, white);', false);
    }

    /**
     * Un color de marca mal escrito no puede inyectar CSS ni HTML: se ignora.
     */
    public function test_un_color_de_marca_invalido_se_ignora_y_queda_el_del_css(): void
    {
        config(['biblioteca.brand.primary' => 'red;}</style><script>alert(1)</script>']);

        $this->actingAs(User::factory()->estudiante()->create())
            ->get(route('biblioteca.index'))
            ->assertOk()
            ->assertDontSee('<script>alert(1)', false)
            ->assertDontSee('--bib-primary:', false);
    }

    /**
     * El título sigue el formato "{página} · Biblioteca · {marca}".
     */
    public function test_el_titulo_de_las_paginas_incluye_la_biblioteca_y_la_marca(): void
    {
        $this->actingAs(User::factory()->estudiante()->create())
            ->get(route('profile.edit'))
            ->assertSee('<title>Perfil · Biblioteca · Pestalozzi</title>', false);
    }

    /**
     * El layout también se usa desde componentes Livewire de página completa.
     */
    public function test_el_layout_acepta_contenido_por_slot(): void
    {
        $this->actingAs(User::factory()->estudiante()->create());

        $html = view('layouts.biblioteca', [
            'slot' => new HtmlString('<p>Contenido por slot</p>'),
            'title' => 'Mis recursos',
        ])->render();

        $this->assertStringContainsString('<p>Contenido por slot</p>', $html);
        $this->assertStringContainsString('<title>Mis recursos · Biblioteca · Pestalozzi</title>', $html);
        $this->assertMatchesRegularExpression('/<main id="contenido"[^>]*>.*Contenido por slot.*<\/main>/s', $html);
    }

    /**
     * La página provisional usa el layout con su encabezado y estado vacío.
     */
    public function test_la_pagina_de_biblioteca_muestra_encabezado_y_estado_vacio(): void
    {
        $this->actingAs(User::factory()->estudiante()->create())
            ->get(route('biblioteca.index'))
            ->assertSee('<h1', false)
            ->assertSee('Biblioteca')
            ->assertSee('Encuentra materiales, documentos y recursos para tus clases.');
    }

    /**
     * Ningún emoji en el HTML del layout: los iconos son SVG.
     */
    public function test_el_layout_no_usa_emojis(): void
    {
        $html = $this->actingAs(User::factory()->guia()->create())
            ->get(route('biblioteca.index'))
            ->getContent();

        $this->assertSame(0, preg_match('/[\x{1F000}-\x{1FAFF}\x{2600}-\x{27BF}\x{2B00}-\x{2BFF}\x{FE0F}]/u', $html));
        $this->assertStringContainsString('aria-hidden="true"', $html);
        $this->assertStringContainsString('<svg', $html);
    }

    /**
     * Quien no inició sesión va al login.
     */
    public function test_un_invitado_va_al_login(): void
    {
        $this->get(route('biblioteca.index'))->assertRedirect(route('login'));
    }

    /**
     * El estudiante no tiene panel: /dashboard lo manda a la Biblioteca.
     */
    public function test_dashboard_redirige_al_estudiante_a_la_biblioteca(): void
    {
        $this->actingAs(User::factory()->estudiante()->create())
            ->get('/dashboard')
            ->assertRedirect(route('biblioteca.index'));
    }

    /**
     * El personal y las familias conservan el comportamiento anterior.
     */
    public function test_dashboard_sigue_igual_para_personal_y_familias(): void
    {
        $this->actingAs(User::factory()->administrador()->create())
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Panel')
            ->assertSee('href="/admin"', false);

        $this->actingAs(User::factory()->familia()->create())
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Ir al portal de mi familia');
    }

    /**
     * El perfil del estudiante usa el layout de la Biblioteca, sin /admin ni migas.
     */
    public function test_el_perfil_del_estudiante_usa_el_layout_de_la_biblioteca(): void
    {
        $this->actingAs(User::factory()->estudiante()->create())
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Saltar al contenido')
            ->assertSee('<main id="contenido"', false)
            ->assertSee('Información del perfil')
            ->assertDontSee('/admin', false)
            ->assertDontSee('aria-label="Breadcrumb"', false)
            ->assertDontSee('Inicio');
    }

    /**
     * Familias y personal siguen viendo el perfil dentro del portal de siempre.
     */
    public function test_el_perfil_de_familias_y_personal_conserva_el_layout_del_portal(): void
    {
        foreach (['familia', 'administrador', 'guia', 'coordinacion'] as $rol) {
            $this->actingAs(User::factory()->{$rol}()->create())
                ->get(route('profile.edit'))
                ->assertOk()
                ->assertSee('<title>Perfil — Mi Escuelita</title>', false)
                ->assertSee('aria-label="Breadcrumb"', false)
                ->assertSee('Información del perfil')
                ->assertDontSee('Saltar al contenido')
                ->assertDontSee('bib-theme', false);
        }
    }
}
