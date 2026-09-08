<?php

namespace Tests\Feature;

use App\Livewire\MiEscuelita\ExperienciaLista;
use App\Models\Area;
use App\Models\Child;
use App\Models\Content;
use App\Models\Environment;
use App\Models\Family;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PortalExperienciasTableTest extends TestCase
{
    use RefreshDatabase;

    private function familiaConHijoEnAmbiente(string $nombreAmbiente = 'Ambiente A'): array
    {
        $guia = User::factory()->guia()->create();
        $ambiente = Environment::factory()->create(['teacher_id' => $guia->id, 'name' => $nombreAmbiente]);
        $familia = Family::factory()->create();
        $nino = Child::factory()->create(['family_id' => $familia->id, 'environment_id' => $ambiente->id]);
        $user = User::factory()->familia($familia)->create();

        return compact('guia', 'ambiente', 'familia', 'nino', 'user');
    }

    public function test_familia_ve_solo_las_experiencias_de_su_ambiente(): void
    {
        ['user' => $user, 'guia' => $guia, 'ambiente' => $ambiente] = $this->familiaConHijoEnAmbiente();
        $otroAmbiente = Environment::factory()->create(['teacher_id' => $guia->id, 'name' => 'Otro ambiente']);

        Content::factory()->published()->create([
            'teacher_id' => $guia->id,
            'environment_id' => $ambiente->id,
            'title' => 'Mi experiencia del ambiente',
        ]);
        Content::factory()->published()->create([
            'teacher_id' => $guia->id,
            'environment_id' => $otroAmbiente->id,
            'title' => 'Experiencia de otro ambiente',
        ]);

        Livewire::actingAs($user)
            ->test(ExperienciaLista::class)
            ->assertSee('Mi experiencia del ambiente')
            ->assertDontSee('Experiencia de otro ambiente');
    }

    public function test_busqueda_por_titulo_filtra_la_tabla(): void
    {
        ['user' => $user] = $this->familiaConHijoEnAmbiente();
        $guia = User::factory()->guia()->create();

        Content::factory()->published()->create([
            'teacher_id' => $guia->id,
            'title' => 'Las vocales y sus sonidos',
        ]);
        Content::factory()->published()->create([
            'teacher_id' => $guia->id,
            'title' => 'Los números del uno al diez',
        ]);

        Livewire::actingAs($user)->test(ExperienciaLista::class)
            ->set('search', 'Vocales')
            ->assertSee('Las vocales y sus sonidos')
            ->assertDontSee('Los números del uno al diez');
    }

    public function test_filtro_por_area_filtra_la_tabla(): void
    {
        ['user' => $user] = $this->familiaConHijoEnAmbiente();
        $guia = User::factory()->guia()->create();
        $areaLectura = Area::factory()->create(['name' => 'Lectoescritura']);
        $areaMatematica = Area::factory()->create(['name' => 'Matemática']);

        Content::factory()->published()->create([
            'teacher_id' => $guia->id,
            'area_id' => $areaLectura->id,
            'title' => 'Taller de lectura',
        ]);
        Content::factory()->published()->create([
            'teacher_id' => $guia->id,
            'area_id' => $areaMatematica->id,
            'title' => 'Taller de matemática',
        ]);

        Livewire::actingAs($user)->test(ExperienciaLista::class)
            ->set('filtroArea', $areaLectura->id)
            ->assertSee('Taller de lectura')
            ->assertDontSee('Taller de matemática');
    }

    public function test_los_borradores_no_aparecen_en_la_tabla(): void
    {
        ['user' => $user] = $this->familiaConHijoEnAmbiente();
        $guia = User::factory()->guia()->create();

        Content::factory()->published()->create([
            'teacher_id' => $guia->id,
            'title' => 'Experiencia publicada',
        ]);
        Content::factory()->create([
            'teacher_id' => $guia->id,
            'title' => 'Borrador que no debe verse',
        ]);

        Livewire::actingAs($user)->test(ExperienciaLista::class)
            ->assertSee('Experiencia publicada')
            ->assertDontSee('Borrador que no debe verse');
    }
}
