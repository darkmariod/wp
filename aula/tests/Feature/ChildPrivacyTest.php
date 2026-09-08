<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\Environment;
use App\Models\Family;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Fase 25 del prompt maestro, items 1-3: la privacidad entre familias
 * no puede depender de React ocultando un botón — tiene que sostenerse
 * sola contra la Policy, aunque alguien arme el request a mano.
 */
class ChildPrivacyTest extends TestCase
{
    use RefreshDatabase;

    public function test_familia_a_puede_ver_a_su_propio_nino(): void
    {
        $familiaA = Family::factory()->create();
        $ninoA = Child::factory()->for($familiaA)->create();
        $userA = User::factory()->familia($familiaA)->create();

        $this->assertTrue($userA->can('view', $ninoA));
    }

    public function test_familia_a_no_puede_ver_al_nino_de_la_familia_b(): void
    {
        $familiaA = Family::factory()->create();
        $familiaB = Family::factory()->create();
        $ninoB = Child::factory()->for($familiaB)->create();
        $userA = User::factory()->familia($familiaA)->create();

        $this->assertFalse($userA->can('view', $ninoB));
    }

    public function test_familia_b_no_puede_ver_al_nino_de_la_familia_a(): void
    {
        $familiaA = Family::factory()->create();
        $familiaB = Family::factory()->create();
        $ninoA = Child::factory()->for($familiaA)->create();
        $userB = User::factory()->familia($familiaB)->create();

        $this->assertFalse($userB->can('view', $ninoA));
    }

    public function test_guia_a_puede_ver_ninos_de_su_ambiente(): void
    {
        $guiaA = User::factory()->guia()->create();
        $ambiente = Environment::factory()->create(['teacher_id' => $guiaA->id]);
        $nino = Child::factory()->create(['environment_id' => $ambiente->id]);

        $this->assertTrue($guiaA->can('view', $nino));
    }

    public function test_guia_a_no_puede_gestionar_ninos_de_otro_ambiente(): void
    {
        $guiaA = User::factory()->guia()->create();
        $ambienteDeOtraGuia = Environment::factory()->create();
        $nino = Child::factory()->create(['environment_id' => $ambienteDeOtraGuia->id]);

        $this->assertFalse($guiaA->can('view', $nino));
    }
}
