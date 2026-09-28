<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        // Familia (rol por defecto del factory) va a Mi Escuelita, no
        // a /dashboard — ese destino genérico de Breeze ya no se usa.
        $response->assertRedirect(route('mi-escuelita.home', absolute: false));
    }

    public function test_guia_es_redirigida_al_panel_al_autenticarse(): void
    {
        $guia = User::factory()->guia()->create();

        $response = $this->post('/login', [
            'email' => $guia->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/admin');
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_una_familia_ya_logueada_que_vuelve_a_login_va_directo_a_su_portal(): void
    {
        $familia = User::factory()->create();

        $response = $this->actingAs($familia)->get('/login');

        $response->assertRedirect(route('mi-escuelita.home', absolute: false));
    }

    public function test_personal_ya_logueado_que_vuelve_a_login_va_al_dashboard(): void
    {
        $guia = User::factory()->guia()->create();

        $response = $this->actingAs($guia)->get('/login');

        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
