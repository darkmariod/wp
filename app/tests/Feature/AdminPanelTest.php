<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'admin@constructora.com',
        ]);
    }

    protected function loginAsAdmin(): void
    {
        Auth::login($this->user);
        $this->session(['_auth_password_timeout_' . Auth::getDefaultDriver() => now()->addMinutes(120)->getTimestamp()]);
    }

    // ── Login ──────────────────────────────────────────────

    public function test_login_page_loads(): void
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
    }

    public function test_dashboard_requires_auth(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/admin/login');
    }

    public function test_dashboard_loads_for_authenticated_user(): void
    {
        $this->loginAsAdmin();

        $response = $this->get('/admin');

        $response->assertStatus(200);
    }

    // ── Resources index pages ──────────────────────────────

    public function test_all_resource_index_pages_load(): void
    {
        $this->loginAsAdmin();

        $resources = [
            '/admin/clientes',
            '/admin/proveedors',
            '/admin/trabajadors',
            '/admin/obras',
            '/admin/plan-cuentas',
            '/admin/presupuestos',
            '/admin/anticipo-clientes',
            '/admin/cuenta-por-cobrars',
            '/admin/cuenta-por-pagars',
            '/admin/flujo-cajas',
            '/admin/asiento-contables',
            '/admin/asistencia-obras',
        ];

        foreach ($resources as $uri) {
            $response = $this->get($uri);

            $response->assertStatus(200, "Failed loading: {$uri}");
        }
    }

    // ── Report pages ───────────────────────────────────────

    public function test_all_report_pages_load(): void
    {
        $this->loginAsAdmin();

        $reports = [
            '/admin/balance-general-page',
            '/admin/estado-resultados-page',
            '/admin/libro-diario-page',
            '/admin/libro-mayor-page',
            '/admin/desviacion-presupuestaria-page',
        ];

        foreach ($reports as $uri) {
            $response = $this->get($uri);

            $response->assertStatus(200, "Failed loading report: {$uri}");
        }
    }

    // ── Resource create pages ──────────────────────────────

    public function test_all_resource_create_pages_load(): void
    {
        $this->loginAsAdmin();

        $createPages = [
            '/admin/clientes/create',
            '/admin/proveedors/create',
            '/admin/trabajadors/create',
            '/admin/obras/create',
            '/admin/plan-cuentas/create',
            '/admin/presupuestos/create',
            '/admin/anticipo-clientes/create',
            '/admin/cuenta-por-cobrars/create',
            '/admin/cuenta-por-pagars/create',
            '/admin/flujo-cajas/create',
            '/admin/asiento-contables/create',
            '/admin/asistencia-obras/create',
        ];

        foreach ($createPages as $uri) {
            $response = $this->get($uri);

            $response->assertStatus(200, "Failed loading create page: {$uri}");
        }
    }

    // ── Logout ─────────────────────────────────────────────

    public function test_logout_works(): void
    {
        $this->loginAsAdmin();

        $response = $this->post('/admin/logout');

        $response->assertRedirect();
    }
}
