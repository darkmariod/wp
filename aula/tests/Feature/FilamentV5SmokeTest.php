<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Bloque 0 — Smoke test de Filament v5. Verifica que el panel carga y
 * que cada resource responde 200, sin romperse tras el upgrade.
 */
class FilamentV5SmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_entra_al_panel_completo(): void
    {
        $admin = User::factory()->administrador()->create();

        $this->actingAs($admin)->get('/admin')->assertOk();
    }

    public function test_todos_los_resources_cargan(): void
    {
        $admin = User::factory()->administrador()->create();
        $this->actingAs($admin);

        foreach (['families', 'users', 'children', 'environments', 'areas', 'contents'] as $slug) {
            $response = $this->get("/admin/{$slug}");
            $response->assertOk();
        }
    }

    public function test_familia_sigue_bloqueada_del_panel(): void
    {
        $familia = User::factory()->familia()->create();

        $this->actingAs($familia)->get('/admin')->assertForbidden();
    }
}
