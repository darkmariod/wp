<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\Content;
use App\Models\Environment;
use App\Models\Family;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Bloque 4 — Seguridad. La defensa no puede depender de esconder un
 * botón en React: todo límite se re-valida en el backend, aunque alguien
 * arme una petición a mano.
 */
class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_familia_no_puede_administrar_familias(): void
    {
        $user = User::factory()->familia()->create();

        $this->assertFalse($user->can('create', Family::class));
        $this->assertFalse($user->can('update', Family::factory()->create()));
    }

    public function test_guia_no_puede_gestionar_familias(): void
    {
        $familia = Family::factory()->create();
        $guia = User::factory()->guia()->create();

        $this->assertFalse($guia->can('view', $familia));
        $this->assertFalse($guia->can('update', $familia));
        $this->assertFalse($guia->can('delete', $familia));
    }

    public function test_solo_admin_puede_borrar_familias(): void
    {
        $familia = Family::factory()->create();
        $admin = User::factory()->administrador()->create();
        $coord = User::factory()->coordinacion()->create();

        $this->assertTrue($admin->can('delete', $familia));
        $this->assertFalse($coord->can('delete', $familia));
    }

    public function test_cambiar_de_nino_con_un_id_ajeno_rebota(): void
    {
        $familiaA = Family::factory()->create();
        $familiaB = Family::factory()->create();
        $userA = User::factory()->familia($familiaA)->create();
        $ninoDeB = Child::factory()->for($familiaB)->create();

        $this->actingAs($userA)
            ->post(route('mi-escuelita.nino.cambiar', $ninoDeB))
            ->assertForbidden();
    }

    public function test_abrir_vista_previa_con_firma_expirada_rebota(): void
    {
        $guia = User::factory()->guia()->create();
        $content = Content::factory()->create(['teacher_id' => $guia->id]);

        $url = URL::temporarySignedRoute(
            'mi-escuelita.preview.show',
            now()->subHour(),
            ['content' => $content->id],
        );

        $this->actingAs($guia)->get($url)->assertForbidden();
    }

    public function test_abrir_vista_previa_con_firma_valida_abre_la_vista_familia(): void
    {
        $guia = User::factory()->guia()->create();
        $ambiente = Environment::factory()->create(['teacher_id' => $guia->id]);
        $content = Content::factory()->create(['teacher_id' => $guia->id, 'environment_id' => $ambiente->id]);

        $url = URL::temporarySignedRoute(
            'mi-escuelita.preview.show',
            now()->addMinutes(30),
            ['content' => $content->id],
        );

        $this->actingAs($guia)->get($url)->assertOk();
    }

    public function test_vista_previa_de_contenido_de_otro_ambiente_rebota(): void
    {
        $guiaA = User::factory()->guia()->create();
        $ambienteA = Environment::factory()->create(['teacher_id' => $guiaA->id]);
        $otraGuia = User::factory()->guia()->create();
        $ambienteDeOtra = Environment::factory()->create(['teacher_id' => $otraGuia->id]);
        $contenidoDeOtroAmbiente = Content::factory()->create([
            'teacher_id' => $otraGuia->id,
            'environment_id' => $ambienteDeOtra->id,
        ]);

        $url = URL::temporarySignedRoute(
            'mi-escuelita.preview.show',
            now()->addMinutes(30),
            ['content' => $contenidoDeOtroAmbiente->id],
        );

        $this->actingAs($guiaA)->get($url)->assertForbidden();
    }
}
