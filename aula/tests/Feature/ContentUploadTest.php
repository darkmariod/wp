<?php

namespace Tests\Feature;

use App\Filament\Resources\ContentResource\Pages\CreateContent;
use App\Filament\Resources\ContentResource\Pages\EditContent;
use App\Models\Area;
use App\Models\Child;
use App\Models\Content;
use App\Models\Environment;
use App\Models\Family;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Cubre el circuito completo de carga de contenido: la guía sube una
 * actividad desde el panel (con imagen de portada), la publica, y esa
 * actividad llega a la vista de las familias del ambiente correspondiente.
 *
 * Es el flujo central del producto y no estaba cubierto: los tests podían
 * quedar en verde mientras la pantalla de creación devolvía un 500.
 */
class ContentUploadTest extends TestCase
{
    use RefreshDatabase;

    private function ambienteConFamilia(): array
    {
        $guia = User::factory()->guia()->create();
        $ambiente = Environment::factory()->create(['teacher_id' => $guia->id]);
        $area = Area::create(['name' => 'Lectura', 'order' => 0]);

        $familia = Family::factory()->create();
        $nino = Child::factory()->create([
            'family_id' => $familia->id,
            'environment_id' => $ambiente->id,
        ]);
        $padre = User::factory()->familia($familia)->create();

        return compact('guia', 'ambiente', 'area', 'familia', 'nino', 'padre');
    }

    public function test_la_guia_sube_una_actividad_con_imagen_de_portada(): void
    {
        Storage::fake('public');

        ['guia' => $guia, 'ambiente' => $ambiente, 'area' => $area] = $this->ambienteConFamilia();

        $portada = UploadedFile::fake()->image('portada.jpg', 800, 600);

        Livewire::actingAs($guia)
            ->test(CreateContent::class)
            ->fillForm([
                'title' => 'Sumamos con semillas',
                'type' => Content::TYPE_TASK,
                'status' => Content::STATUS_DRAFT,
                'environment_id' => $ambiente->id,
                'area_id' => $area->id,
                'teacher_id' => $guia->id,
                'cover_image' => [$portada],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $content = Content::where('title', 'Sumamos con semillas')->first();

        $this->assertNotNull($content, 'La actividad debería haberse guardado');
        $this->assertNotNull($content->cover_image, 'Debería haber quedado la portada');
        Storage::disk('public')->assertExists($content->cover_image);
    }

    public function test_la_guia_sube_una_actividad_sin_portada(): void
    {
        ['guia' => $guia, 'ambiente' => $ambiente, 'area' => $area] = $this->ambienteConFamilia();

        Livewire::actingAs($guia)
            ->test(CreateContent::class)
            ->fillForm([
                'title' => 'La tortuga y la liebre',
                'type' => Content::TYPE_READING,
                'status' => Content::STATUS_DRAFT,
                'environment_id' => $ambiente->id,
                'area_id' => $area->id,
                'teacher_id' => $guia->id,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('content', [
            'title' => 'La tortuga y la liebre',
            'type' => Content::TYPE_READING,
            'status' => Content::STATUS_DRAFT,
        ]);
    }

    public function test_el_slug_se_genera_solo_sin_que_la_guia_lo_escriba(): void
    {
        ['guia' => $guia, 'ambiente' => $ambiente, 'area' => $area] = $this->ambienteConFamilia();

        Livewire::actingAs($guia)
            ->test(CreateContent::class)
            ->fillForm([
                'title' => 'Contamos hasta diez',
                'type' => Content::TYPE_TASK,
                'status' => Content::STATUS_DRAFT,
                'environment_id' => $ambiente->id,
                'area_id' => $area->id,
                'teacher_id' => $guia->id,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $content = Content::where('title', 'Contamos hasta diez')->first();

        $this->assertNotNull($content);
        $this->assertStringStartsWith('contamos-hasta-diez-', $content->slug);
    }

    public function test_la_familia_ve_el_enlace_a_los_libros_cuando_la_guia_lo_carga(): void
    {
        ['ambiente' => $ambiente, 'area' => $area, 'guia' => $guia, 'nino' => $nino, 'padre' => $padre] = $this->ambienteConFamilia();

        $content = Content::factory()->published()->create([
            'environment_id' => $ambiente->id,
            'area_id' => $area->id,
            'teacher_id' => $guia->id,
            'books_url' => 'https://drive.google.com/carpeta-de-libros',
        ]);

        $this->actingAs($padre)
            ->get(route('mi-escuelita.experiencias.show', $content))
            ->assertOk()
            ->assertSee('https://drive.google.com/carpeta-de-libros')
            ->assertSee('Libros de lectura');
    }

    public function test_el_titulo_es_obligatorio(): void
    {
        ['guia' => $guia, 'ambiente' => $ambiente] = $this->ambienteConFamilia();

        Livewire::actingAs($guia)
            ->test(CreateContent::class)
            ->fillForm([
                'title' => '',
                'type' => Content::TYPE_READING,
                'status' => Content::STATUS_DRAFT,
                'environment_id' => $ambiente->id,
                'teacher_id' => $guia->id,
            ])
            ->call('create')
            ->assertHasFormErrors(['title']);
    }

    public function test_la_guia_edita_una_actividad_ya_cargada(): void
    {
        ['guia' => $guia, 'ambiente' => $ambiente, 'area' => $area] = $this->ambienteConFamilia();

        $content = Content::factory()->create([
            'title' => 'Título original',
            'type' => Content::TYPE_READING,
            'status' => Content::STATUS_DRAFT,
            'environment_id' => $ambiente->id,
            'area_id' => $area->id,
            'teacher_id' => $guia->id,
        ]);

        Livewire::actingAs($guia)
            ->test(EditContent::class, ['record' => $content->getKey()])
            ->fillForm(['title' => 'Título corregido'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Título corregido', $content->fresh()->title);
    }

    public function test_la_actividad_publicada_llega_a_la_familia_del_ambiente(): void
    {
        [
            'guia' => $guia,
            'ambiente' => $ambiente,
            'area' => $area,
            'padre' => $padre,
        ] = $this->ambienteConFamilia();

        $publicada = Content::factory()->published()->create([
            'title' => 'Contamos manzanas',
            'slug' => 'contamos-manzanas',
            'type' => Content::TYPE_TASK,
            'environment_id' => $ambiente->id,
            'area_id' => $area->id,
            'teacher_id' => $guia->id,
        ]);

        $borrador = Content::factory()->create([
            'title' => 'Todavía en borrador',
            'slug' => 'todavia-en-borrador',
            'type' => Content::TYPE_READING,
            'status' => Content::STATUS_DRAFT,
            'environment_id' => $ambiente->id,
            'area_id' => $area->id,
            'teacher_id' => $guia->id,
        ]);

        $this->actingAs($padre)
            ->get(route('mi-escuelita.experiencias.index'))
            ->assertOk()
            ->assertSee($publicada->title)
            ->assertDontSee($borrador->title);
    }

    public function test_la_actividad_de_otro_ambiente_no_se_filtra_a_la_familia(): void
    {
        ['guia' => $guia, 'padre' => $padre] = $this->ambienteConFamilia();

        $otroAmbiente = Environment::factory()->create(['teacher_id' => $guia->id]);

        $ajena = Content::factory()->published()->create([
            'title' => 'Actividad de otra aula',
            'slug' => 'actividad-de-otra-aula',
            'type' => Content::TYPE_READING,
            'environment_id' => $otroAmbiente->id,
            'teacher_id' => $guia->id,
        ]);

        $this->actingAs($padre)
            ->get(route('mi-escuelita.experiencias.index'))
            ->assertOk()
            ->assertDontSee($ajena->title);
    }
}
