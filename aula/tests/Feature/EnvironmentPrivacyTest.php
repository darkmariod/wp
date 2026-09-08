<?php

namespace Tests\Feature;

use App\Models\Environment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Fase 25 del prompt maestro, items 4-5.
 */
class EnvironmentPrivacyTest extends TestCase
{
    use RefreshDatabase;

    public function test_guia_a_puede_gestionar_su_propio_ambiente(): void
    {
        $guiaA = User::factory()->guia()->create();
        $ambienteDeA = Environment::factory()->create(['teacher_id' => $guiaA->id]);

        $this->assertTrue($guiaA->can('update', $ambienteDeA));
    }

    public function test_guia_a_no_puede_gestionar_el_ambiente_de_otra_guia(): void
    {
        $guiaA = User::factory()->guia()->create();
        $guiaB = User::factory()->guia()->create();
        $ambienteDeB = Environment::factory()->create(['teacher_id' => $guiaB->id]);

        $this->assertFalse($guiaA->can('update', $ambienteDeB));
    }

    public function test_staff_puede_gestionar_cualquier_ambiente(): void
    {
        $admin = User::factory()->administrador()->create();
        $ambiente = Environment::factory()->create();

        $this->assertTrue($admin->can('update', $ambiente));
    }
}
