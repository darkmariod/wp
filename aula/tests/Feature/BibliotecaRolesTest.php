<?php

namespace Tests\Feature;

use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Models\Content;
use App\Models\Environment;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Base de la Biblioteca (T01): el rol `estudiante` nace sin acceso al
 * panel /admin ni al portal de familias, y solo entra a /biblioteca. Los
 * cuatro roles que ya existían se comportan exactamente igual que antes.
 */
class BibliotecaRolesTest extends TestCase
{
    use RefreshDatabase;

    // ---- Helpers y puerta del panel -------------------------------------

    public function test_estudiante_es_un_rol_valido_con_su_helper(): void
    {
        $estudiante = User::factory()->estudiante()->create();

        $this->assertContains(User::ROLE_ESTUDIANTE, User::ROLES);
        $this->assertSame('estudiante', $estudiante->role);
        $this->assertNull($estudiante->family_id);
        $this->assertTrue($estudiante->isEstudiante());
        $this->assertFalse($estudiante->isStaff());
        $this->assertFalse($estudiante->isFamilia());
        $this->assertTrue($estudiante->hasRole('estudiante'));
    }

    public function test_can_access_panel_es_una_lista_blanca_de_roles_de_personal(): void
    {
        $panel = Filament::getPanel('admin');

        $this->assertTrue(User::factory()->administrador()->create()->canAccessPanel($panel));
        $this->assertTrue(User::factory()->coordinacion()->create()->canAccessPanel($panel));
        $this->assertTrue(User::factory()->guia()->create()->canAccessPanel($panel));
        $this->assertFalse(User::factory()->estudiante()->create()->canAccessPanel($panel));
        $this->assertFalse(User::factory()->familia()->create()->canAccessPanel($panel));
    }

    public function test_un_usuario_inactivo_no_entra_al_panel(): void
    {
        $panel = Filament::getPanel('admin');

        $this->assertFalse(User::factory()->administrador()->create(['active' => false])->canAccessPanel($panel));
        $this->assertFalse(User::factory()->guia()->create(['active' => false])->canAccessPanel($panel));
    }

    public function test_un_rol_desconocido_no_entra_al_panel(): void
    {
        $desconocido = User::factory()->create(['role' => 'invitado']);

        $this->assertFalse($desconocido->canAccessPanel(Filament::getPanel('admin')));
        $this->assertFalse($desconocido->canUseBiblioteca());
    }

    public function test_estudiante_recibe_403_al_abrir_el_panel(): void
    {
        $estudiante = User::factory()->estudiante()->create();

        $this->actingAs($estudiante)->get('/admin')->assertForbidden();
        $this->actingAs($estudiante)->get('/admin/users')->assertForbidden();
    }

    public function test_el_personal_sigue_entrando_al_panel(): void
    {
        foreach (['administrador', 'coordinacion', 'guia'] as $rol) {
            $this->actingAs(User::factory()->{$rol}()->create())->get('/admin')->assertOk();
        }
    }

    public function test_can_use_biblioteca_solo_para_roles_permitidos_y_activos(): void
    {
        $this->assertTrue(User::factory()->administrador()->create()->canUseBiblioteca());
        $this->assertTrue(User::factory()->coordinacion()->create()->canUseBiblioteca());
        $this->assertTrue(User::factory()->guia()->create()->canUseBiblioteca());
        $this->assertTrue(User::factory()->estudiante()->create()->canUseBiblioteca());
        $this->assertFalse(User::factory()->familia()->create()->canUseBiblioteca());
        $this->assertFalse(User::factory()->estudiante()->create(['active' => false])->canUseBiblioteca());
    }

    // ---- Redirecciones --------------------------------------------------

    public function test_login_de_estudiante_redirige_a_la_biblioteca(): void
    {
        $estudiante = User::factory()->estudiante()->create();

        $response = $this->post('/login', [
            'email' => $estudiante->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($estudiante);
        $response->assertRedirect(route('biblioteca.index', absolute: false));
        $this->assertSame('/biblioteca', route('biblioteca.index', absolute: false));
    }

    public function test_login_de_familia_y_de_personal_no_cambia(): void
    {
        $familia = User::factory()->familia()->create();
        $this->post('/login', ['email' => $familia->email, 'password' => 'password'])
            ->assertRedirect(route('mi-escuelita.home', absolute: false));
        $this->post('/logout');

        foreach (['administrador', 'coordinacion', 'guia'] as $rol) {
            $usuario = User::factory()->{$rol}()->create();
            $this->post('/login', ['email' => $usuario->email, 'password' => 'password'])
                ->assertRedirect('/admin');
            $this->post('/logout');
        }
    }

    public function test_estudiante_ya_logueado_que_vuelve_a_login_cae_en_la_biblioteca(): void
    {
        $estudiante = User::factory()->estudiante()->create();

        $this->actingAs($estudiante)
            ->get('/login')
            ->assertRedirect(route('biblioteca.index'));
    }

    public function test_familia_y_personal_ya_logueados_mantienen_su_redireccion(): void
    {
        $this->actingAs(User::factory()->familia()->create())
            ->get('/login')
            ->assertRedirect(route('mi-escuelita.home'));

        foreach (['administrador', 'coordinacion', 'guia'] as $rol) {
            $this->actingAs(User::factory()->{$rol}()->create())
                ->get('/login')
                ->assertRedirect(route('dashboard'));
        }
    }

    // ---- Puerta /biblioteca ---------------------------------------------

    public function test_biblioteca_responde_200_a_estudiante_guia_coordinacion_y_administrador(): void
    {
        foreach (['estudiante', 'guia', 'coordinacion', 'administrador'] as $rol) {
            $this->actingAs(User::factory()->{$rol}()->create())
                ->get('/biblioteca')
                ->assertOk();
        }
    }

    public function test_biblioteca_rechaza_a_familia_con_403(): void
    {
        $this->actingAs(User::factory()->familia()->create())
            ->get('/biblioteca')
            ->assertForbidden();
    }

    public function test_biblioteca_rechaza_a_un_estudiante_inactivo_con_403(): void
    {
        $this->actingAs(User::factory()->estudiante()->create(['active' => false]))
            ->get('/biblioteca')
            ->assertForbidden();
    }

    public function test_biblioteca_manda_a_los_visitantes_a_iniciar_sesion(): void
    {
        $this->get('/biblioteca')->assertRedirect(route('login'));
    }

    // ---- Políticas: el estudiante no hereda acceso de personal ----------

    public function test_estudiante_no_puede_ver_el_listado_de_contenidos_ni_de_ambientes(): void
    {
        $estudiante = User::factory()->estudiante()->create();

        $this->assertFalse($estudiante->can('viewAny', Content::class));
        $this->assertFalse($estudiante->can('viewAny', Environment::class));
        $this->assertFalse($estudiante->can('create', Content::class));
    }

    public function test_personal_conserva_el_acceso_a_contenidos_y_ambientes(): void
    {
        foreach (['administrador', 'coordinacion', 'guia'] as $rol) {
            $usuario = User::factory()->{$rol}()->create();

            $this->assertTrue($usuario->can('viewAny', Content::class), $rol);
            $this->assertTrue($usuario->can('viewAny', Environment::class), $rol);
        }

        $this->assertFalse(User::factory()->familia()->create()->can('viewAny', Content::class));
        $this->assertFalse(User::factory()->familia()->create()->can('viewAny', Environment::class));
        $this->assertFalse(User::factory()->guia()->create(['active' => false])->can('viewAny', Content::class));
    }

    public function test_el_middleware_staff_rechaza_al_estudiante_y_a_la_familia(): void
    {
        Route::middleware(['web', 'auth', 'staff'])
            ->get('/__prueba-staff', fn () => 'ok');

        $this->actingAs(User::factory()->estudiante()->create())->get('/__prueba-staff')->assertForbidden();
        $this->actingAs(User::factory()->familia()->create())->get('/__prueba-staff')->assertForbidden();
        $this->actingAs(User::factory()->guia()->create())->get('/__prueba-staff')->assertOk();
        $this->actingAs(User::factory()->administrador()->create())->get('/__prueba-staff')->assertOk();
    }

    // ---- Filament UserResource ------------------------------------------

    public function test_el_administrador_crea_un_estudiante_sin_familia(): void
    {
        $admin = User::factory()->administrador()->create();

        Livewire::actingAs($admin)
            ->test(CreateUser::class)
            ->fillForm([
                'name' => 'Estudiante Prueba',
                'email' => 'estudiante.prueba@example.com',
                'role' => User::ROLE_ESTUDIANTE,
                'active' => true,
                'password' => 'secreto-1234',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('users', [
            'email' => 'estudiante.prueba@example.com',
            'role' => 'estudiante',
            'family_id' => null,
        ]);
    }

    public function test_el_administrador_edita_un_estudiante(): void
    {
        $admin = User::factory()->administrador()->create();
        $estudiante = User::factory()->estudiante()->create(['name' => 'Nombre viejo']);

        Livewire::actingAs($admin)
            ->test(EditUser::class, ['record' => $estudiante->getKey()])
            ->fillForm(['name' => 'Nombre nuevo'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Nombre nuevo', $estudiante->fresh()->name);
        $this->assertSame('estudiante', $estudiante->fresh()->role);
    }

    public function test_el_listado_de_usuarios_muestra_al_estudiante_sin_romperse(): void
    {
        $admin = User::factory()->administrador()->create();
        $estudiante = User::factory()->estudiante()->create();

        Livewire::actingAs($admin)
            ->test(ListUsers::class)
            ->assertCanSeeTableRecords([$estudiante])
            ->assertSee('Estudiante');

        $this->actingAs($admin)->get('/admin/users')->assertOk();
    }
}
