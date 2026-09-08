<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Bloque 0 — Smoke test del portal de familias tras la migración
 * a Tailwind v4 + Filament v5 + Livewire 4. Confirma que las rutas
 * principales del portal responden 200.
 */
class PortalV5SmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_portal_carga_para_familia(): void
    {
        $familia = User::factory()->familia()->create();

        $this->actingAs($familia)
            ->get('/mi-escuelita')
            ->assertOk();

        $this->actingAs($familia)
            ->get('/mi-escuelita/experiencias')
            ->assertOk();

        $this->actingAs($familia)
            ->get('/mi-escuelita/mis-experiencias')
            ->assertOk();
    }
}
