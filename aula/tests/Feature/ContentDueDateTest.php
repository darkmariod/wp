<?php

namespace Tests\Feature;

use App\Filament\Resources\ContentResource\Pages\CreateContent;
use App\Models\Content;
use App\Models\Environment;
use App\Models\Family;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * `due_date` es la fecha límite de una tarea/lectura — distinta de
 * `published_at` (cuándo se hace visible). Cubre: el cálculo de vencida,
 * que se pueda cargar desde el panel, y que la familia vea el aviso.
 */
class ContentDueDateTest extends TestCase
{
    use RefreshDatabase;

    public function test_sin_fecha_limite_nunca_esta_vencida(): void
    {
        $content = Content::factory()->create(['due_date' => null]);

        $this->assertFalse($content->isOverdue());
    }

    public function test_con_fecha_limite_pasada_esta_vencida(): void
    {
        $content = Content::factory()->create(['due_date' => now()->subDay()]);

        $this->assertTrue($content->isOverdue());
    }

    public function test_con_fecha_limite_futura_no_esta_vencida(): void
    {
        $content = Content::factory()->create(['due_date' => now()->addDay()]);

        $this->assertFalse($content->isOverdue());
    }

    public function test_hoy_todavia_no_cuenta_como_vencida(): void
    {
        // El vencimiento es por día, no por hora: si vence "hoy", la
        // familia todavía tiene el día para responder.
        $content = Content::factory()->create(['due_date' => now()->startOfDay()]);

        $this->assertFalse($content->isOverdue());
    }

    public function test_la_guia_carga_una_tarea_con_fecha_limite(): void
    {
        $guia = User::factory()->guia()->create();
        $ambiente = Environment::factory()->create();

        Livewire::actingAs($guia)
            ->test(CreateContent::class)
            ->fillForm([
                'title' => 'Sumas hasta el 20',
                'type' => Content::TYPE_TASK,
                'status' => Content::STATUS_DRAFT,
                'environment_id' => $ambiente->id,
                'teacher_id' => $guia->id,
                'due_date' => now()->addDays(5)->toDateString(),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $creado = Content::where('title', 'Sumas hasta el 20')->first();

        $this->assertNotNull($creado->due_date);
        $this->assertFalse($creado->isOverdue());
    }

    public function test_scope_vencidas_solo_trae_las_que_ya_pasaron(): void
    {
        Content::factory()->create(['due_date' => now()->subDays(2)]);
        Content::factory()->create(['due_date' => now()->addDays(2)]);
        Content::factory()->create(['due_date' => null]);

        $this->assertSame(1, Content::vencidas()->count());
    }

    public function test_la_familia_ve_el_aviso_de_vencida_en_el_detalle(): void
    {
        $familia = Family::factory()->create();
        $padre = User::factory()->familia($familia)->create();

        $content = Content::factory()->published()->create([
            'due_date' => now()->subDays(3),
        ]);

        $this->actingAs($padre)
            ->get(route('mi-escuelita.experiencias.show', $content))
            ->assertOk()
            ->assertSee('venció', escape: false);
    }

    public function test_la_familia_no_ve_aviso_de_vencida_si_no_hay_fecha_limite(): void
    {
        $familia = Family::factory()->create();
        $padre = User::factory()->familia($familia)->create();

        $content = Content::factory()->published()->create(['due_date' => null]);

        $html = $this->actingAs($padre)
            ->get(route('mi-escuelita.experiencias.show', $content))
            ->getContent();

        $this->assertStringNotContainsString('venció', $html);
    }
}
