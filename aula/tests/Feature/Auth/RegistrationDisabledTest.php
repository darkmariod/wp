<?php

namespace Tests\Feature\Auth;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * El colegio pidió que no exista un "público en general" con acceso
 * propio: solo personal y familias que da de alta el panel. El
 * auto-registro venía de Breeze, sin usarlo — esto confirma que
 * queda cerrado de verdad, no solo oculto en la vista.
 */
class RegistrationDisabledTest extends TestCase
{
    public function test_no_existe_ruta_de_registro(): void
    {
        $this->assertFalse(Route::has('register'));
    }

    public function test_get_register_no_esta_disponible(): void
    {
        $this->get('/register')->assertNotFound();
    }

    public function test_post_register_no_esta_disponible(): void
    {
        $this->post('/register', [
            'name' => 'Alguien',
            'email' => 'alguien@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertNotFound();

        $this->assertGuest();
    }

    public function test_el_login_no_muestra_crear_cuenta(): void
    {
        $this->get('/login')->assertDontSee('Crear cuenta');
    }
}
