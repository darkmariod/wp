<?php

namespace Tests\Feature;

use App\Filament\Resources\ContentResource;
use App\Filament\Resources\ContentResource\Pages\CreateContent;
use App\Models\Content;
use App\Models\Environment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * La regla de negocio más distintiva: en un ambiente "Mentes Razonadoras"
 * solo se permiten contenidos de tipo lectura y tarea. La validación de
 * servidor existe (ContentResource::esTipoPermitidoEnAmbiente + el método
 * del modelo), pero nada la cubría. Estos tests la fijan y prueban que un
 * tipo no permitido (ej. experience) se rechaza, y que los permitidos pasan.
 */
class ContentTipoRestrictionTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_modelo_rechaza_una_experiencia_en_razonadoras(): void
    {
        $ambiente = Environment::factory()->razonadoras()->create();

        $content = Content::factory()->create([
            'type' => Content::TYPE_EXPERIENCE,
            'environment_id' => $ambiente->id,
        ]);

        $this->assertFalse($content->tipoPermitidoEnSuAmbiente());
    }

    public function test_el_modelo_acepta_lectura_y_tarea_en_razonadoras(): void
    {
        $ambiente = Environment::factory()->razonadoras()->create();

        foreach ([Content::TYPE_READING, Content::TYPE_TASK] as $tipo) {
            $content = Content::factory()->create([
                'type' => $tipo,
                'environment_id' => $ambiente->id,
            ]);

            $this->assertTrue($content->tipoPermitidoEnSuAmbiente(), "Debería aceptar {$tipo} en razonadoras");
        }
    }

    public function test_el_modelo_acepta_cualquier_tipo_en_absorbentes_o_sin_ambiente(): void
    {
        $absorbentes = Environment::factory()->create();

        $enAbsorbentes = Content::factory()->create([
            'type' => Content::TYPE_EXPERIENCE,
            'environment_id' => $absorbentes->id,
        ]);

        $sinAmbiente = Content::factory()->create([
            'type' => Content::TYPE_VIDEO,
            'environment_id' => null,
        ]);

        $this->assertTrue($enAbsorbentes->tipoPermitidoEnSuAmbiente());
        $this->assertTrue($sinAmbiente->tipoPermitidoEnSuAmbiente());
    }

    public function test_es_tipo_permitido_en_ambiente_rechaza_la_experiencia(): void
    {
        $ambiente = Environment::factory()->razonadoras()->create();

        $this->assertFalse(
            ContentResource::esTipoPermitidoEnAmbiente($ambiente->id, Content::TYPE_EXPERIENCE)
        );
        $this->assertTrue(
            ContentResource::esTipoPermitidoEnAmbiente($ambiente->id, Content::TYPE_READING)
        );
        $this->assertTrue(
            ContentResource::esTipoPermitidoEnAmbiente($ambiente->id, Content::TYPE_TASK)
        );
    }

    public function test_la_pantalla_de_creacion_rechaza_guardar_una_experiencia_en_razonadoras(): void
    {
        $admin = User::factory()->administrador()->create();
        $guia = User::factory()->guia()->create();
        $ambiente = Environment::factory()->razonadoras()->create([
            'name' => 'Mentes Razonadoras',
        ]);

        Livewire::actingAs($admin)
            ->test(CreateContent::class)
            ->fillForm([
                'title' => 'Una experiencia que no debería poder crearse',
                'slug' => 'una-experiencia-que-no-deberia',
                'type' => Content::TYPE_EXPERIENCE,
                'environment_id' => $ambiente->id,
                'teacher_id' => $guia->id,
                'status' => Content::STATUS_DRAFT,
            ])
            ->call('create')
            ->assertHasFormErrors(['type']);
    }

    public function test_la_pantalla_de_creacion_acepta_lectura_en_razonadoras(): void
    {
        $admin = User::factory()->administrador()->create();
        $guia = User::factory()->guia()->create();
        $ambiente = Environment::factory()->razonadoras()->create([
            'name' => 'Mentes Razonadoras',
        ]);

        Livewire::actingAs($admin)
            ->test(CreateContent::class)
            ->fillForm([
                'title' => 'Una lectura válida',
                'slug' => 'una-lectura-valida',
                'type' => Content::TYPE_READING,
                'environment_id' => $ambiente->id,
                'teacher_id' => $guia->id,
                'status' => Content::STATUS_DRAFT,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('content', [
            'title' => 'Una lectura válida',
            'type' => Content::TYPE_READING,
        ]);
    }
}
