<?php

namespace Tests\Feature;

use App\Filament\Resources\ChildResource\Pages\CreateChild;
use App\Models\Child;
use App\Models\Environment;
use App\Models\Family;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * La guía es quien recibe a los niños nuevos día a día, así que también
 * puede darlos de alta (antes solo podía el staff). Lo que sí se cuida:
 * que solo pueda anotarlos en SU PROPIO ambiente, no en el de otra guía.
 */
class ChildResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_guia_puede_crear_un_nino_en_su_propio_ambiente(): void
    {
        $guia = User::factory()->guia()->create();
        $ambiente = Environment::factory()->create(['teacher_id' => $guia->id]);
        $familia = Family::factory()->create();

        Livewire::actingAs($guia)
            ->test(CreateChild::class)
            ->fillForm([
                'name' => 'Prueba 2',
                'family_id' => $familia->id,
                'environment_id' => $ambiente->id,
                'status' => 'active',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('children', [
            'name' => 'Prueba 2',
            'family_id' => $familia->id,
            'environment_id' => $ambiente->id,
        ]);
    }

    public function test_la_guia_no_puede_anotar_un_nino_en_el_ambiente_de_otra_guia(): void
    {
        $guia = User::factory()->guia()->create();
        Environment::factory()->create(['teacher_id' => $guia->id]);

        $otraGuia = User::factory()->guia()->create();
        $ambienteAjeno = Environment::factory()->create(['teacher_id' => $otraGuia->id]);
        $familia = Family::factory()->create();

        Livewire::actingAs($guia)
            ->test(CreateChild::class)
            ->fillForm([
                'name' => 'Intento ajeno',
                'family_id' => $familia->id,
                'environment_id' => $ambienteAjeno->id,
                'status' => 'active',
            ])
            ->call('create')
            ->assertHasFormErrors(['environment_id']);

        $this->assertDatabaseMissing('children', ['name' => 'Intento ajeno']);
    }

    public function test_la_familia_no_puede_crear_ninos(): void
    {
        $familia = Family::factory()->create();
        $user = User::factory()->familia($familia)->create();

        $this->assertFalse($user->can('create', Child::class));
    }
}
