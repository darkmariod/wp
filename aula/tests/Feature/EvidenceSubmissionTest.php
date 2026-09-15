<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\Content;
use App\Models\Environment;
use App\Models\Evidence;
use App\Models\Family;
use App\Models\Feedback;
use App\Models\Observation;
use App\Models\User;
use App\Notifications\EvidenceSubmittedNotification;
use App\Notifications\GuideRespondedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Fase 25 del prompt maestro, items 6-17. Todo contra las rutas reales,
 * no contra la Policy sola: si algún día se rompe un middleware o un
 * route model binding, esto lo agarra.
 */
class EvidenceSubmissionTest extends TestCase
{
    use RefreshDatabase;

    private function familiaConHijo(): array
    {
        $guia = User::factory()->guia()->create();
        $ambiente = Environment::factory()->create(['teacher_id' => $guia->id]);
        $familia = Family::factory()->create();
        $nino = Child::factory()->create(['family_id' => $familia->id, 'environment_id' => $ambiente->id]);
        $user = User::factory()->familia($familia)->create();

        return compact('guia', 'ambiente', 'familia', 'nino', 'user');
    }

    public function test_familia_puede_abrir_una_experiencia_publicada(): void
    {
        ['user' => $user, 'guia' => $guia, 'ambiente' => $ambiente] = $this->familiaConHijo();
        $content = Content::factory()->published()->create(['teacher_id' => $guia->id, 'environment_id' => $ambiente->id]);

        $this->actingAs($user)
            ->get(route('mi-escuelita.experiencias.show', $content->slug))
            ->assertOk();
    }

    public function test_experiencia_sin_requires_evidence_rechaza_el_envio_en_el_backend(): void
    {
        Storage::fake('local');
        ['user' => $user, 'guia' => $guia, 'ambiente' => $ambiente, 'nino' => $nino] = $this->familiaConHijo();
        $content = Content::factory()->published()->create([
            'teacher_id' => $guia->id,
            'environment_id' => $ambiente->id,
            'requires_evidence' => false,
        ]);

        // Aunque el botón no se muestre en React, si alguien arma el
        // POST a mano tiene que rebotar igual: no alcanza con esconder
        // el botón del lado del frontend.
        $this->actingAs($user)
            ->post(route('mi-escuelita.evidencias.store', $content->slug), [
                'child_id' => $nino->id,
                'comment' => 'Intento sin permiso',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('evidence', 0);
    }

    public function test_familia_puede_compartir_fotos_video_y_comentario(): void
    {
        Storage::fake('local');
        Notification::fake();
        ['user' => $user, 'guia' => $guia, 'ambiente' => $ambiente, 'nino' => $nino] = $this->familiaConHijo();
        $content = Content::factory()->published()->requiresEvidence()->create([
            'teacher_id' => $guia->id,
            'environment_id' => $ambiente->id,
        ]);

        $response = $this->actingAs($user)->post(
            route('mi-escuelita.evidencias.store', $content->slug),
            [
                'child_id' => $nino->id,
                'comment' => 'Le encantó buscar hojas de distintos tamaños.',
                'photos' => [
                    UploadedFile::fake()->image('foto1.jpg'),
                    UploadedFile::fake()->image('foto2.jpg'),
                ],
                'video' => UploadedFile::fake()->create('video.mp4', 2000, 'video/mp4'),
            ],
        );

        $response->assertRedirect();

        $this->assertDatabaseHas('evidence', [
            'content_id' => $content->id,
            'child_id' => $nino->id,
            'family_id' => $user->family_id,
            'comment' => 'Le encantó buscar hojas de distintos tamaños.',
            'status' => Evidence::STATUS_SUBMITTED,
        ]);

        $evidence = Evidence::first();
        $this->assertCount(3, $evidence->media); // 2 fotos + 1 video
        foreach ($evidence->media as $media) {
            Storage::disk('local')->assertExists($media->path);
        }

        Notification::assertSentTo($guia, EvidenceSubmittedNotification::class);
    }

    public function test_familia_no_puede_compartir_mas_de_5_fotos(): void
    {
        Storage::fake('local');
        ['user' => $user, 'guia' => $guia, 'ambiente' => $ambiente, 'nino' => $nino] = $this->familiaConHijo();
        $content = Content::factory()->published()->requiresEvidence()->create([
            'teacher_id' => $guia->id,
            'environment_id' => $ambiente->id,
        ]);

        $this->actingAs($user)->post(
            route('mi-escuelita.evidencias.store', $content->slug),
            [
                'child_id' => $nino->id,
                'photos' => array_map(fn ($i) => UploadedFile::fake()->image("foto{$i}.jpg"), range(1, 6)),
            ],
        )->assertSessionHasErrors('photos');
    }

    /**
     * La UI real ya no pega al controller de arriba: el botón "Compartir
     * experiencia" corre por App\Livewire\MiEscuelita\ExperienciaDetalle
     * (ver experiencias-show.blade.php). Ese componente tiene su propio
     * rules()/messages() — deben coincidir con su propiedad pública
     * `$fotos`, no con el `photos` en inglés del controller viejo.
     */
    public function test_familia_comparte_fotos_desde_el_componente_livewire_real(): void
    {
        Storage::fake('local');
        Notification::fake();
        ['user' => $user, 'guia' => $guia, 'ambiente' => $ambiente, 'nino' => $nino] = $this->familiaConHijo();
        $content = Content::factory()->published()->requiresEvidence()->create([
            'teacher_id' => $guia->id,
            'environment_id' => $ambiente->id,
        ]);

        \Livewire\Livewire::actingAs($user)
            ->test(\App\Livewire\MiEscuelita\ExperienciaDetalle::class, [
                'content' => $content,
                'yaEnviada' => false,
            ])
            ->set('child_id', $nino->id)
            ->set('comment', 'Le encantó buscar hojas de distintos tamaños.')
            ->set('fotos', [
                UploadedFile::fake()->image('foto1.jpg'),
                UploadedFile::fake()->image('foto2.jpg'),
            ])
            ->call('submit')
            ->assertHasNoErrors();

        $evidence = Evidence::first();
        $this->assertNotNull($evidence);
        $this->assertCount(2, $evidence->media);
        Notification::assertSentTo($guia, EvidenceSubmittedNotification::class);
    }

    public function test_guia_puede_responder_y_la_familia_recibe_notificacion(): void
    {
        Notification::fake();
        ['user' => $user, 'guia' => $guia, 'nino' => $nino] = $this->familiaConHijo();
        $content = Content::factory()->create(['teacher_id' => $guia->id]);

        $observation = Observation::create([
            'child_id' => $nino->id,
            'content_id' => $content->id,
            'teacher_id' => $guia->id,
            'observation' => 'Mostró concentración sostenida durante la actividad.',
        ]);

        Feedback::create([
            'observation_id' => $observation->id,
            'teacher_id' => $guia->id,
            'message' => 'Sigamos practicando en casa con actividades similares.',
        ]);

        Notification::assertSentTo($user, GuideRespondedNotification::class);
    }

    public function test_archivos_privados_no_se_sirven_por_una_url_publica(): void
    {
        // El disco 'local' apunta a storage/app/private, sin symlink a
        // public/ — no existe forma de pedirlo por una URL directa.
        $this->assertSame('local', config('filesystems.default'));
        $this->assertStringNotContainsString('public', config('filesystems.disks.local.root'));
    }

    public function test_no_existen_columnas_de_calificacion_porcentaje_ni_ranking(): void
    {
        foreach (['evidence', 'observations', 'feedback', 'content'] as $tabla) {
            foreach (['grade', 'score', 'percentage', 'ranking'] as $columna) {
                $this->assertFalse(
                    Schema::hasColumn($tabla, $columna),
                    "La tabla {$tabla} no debería tener la columna {$columna}",
                );
            }
        }
    }

    public function test_existe_tabla_de_asistencia(): void
    {
        $this->assertTrue(Schema::hasTable('attendances'));

        // La asistencia tampoco compite: solo estados, nunca notas
        // ni ranking.
        foreach (['grade', 'score', 'percentage', 'ranking'] as $columna) {
            $this->assertFalse(
                Schema::hasColumn('attendances', $columna),
                "La tabla attendances no debería tener la columna {$columna}",
            );
        }
    }
}
