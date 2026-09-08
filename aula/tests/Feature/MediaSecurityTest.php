<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\Content;
use App\Models\Environment;
use App\Models\Evidence;
use App\Models\Family;
use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Bloque 4/5 — Acceso a archivos privados. El único camino es
 * /storage-privado/{medium} y pasa por la Policy del dueño del recurso.
 */
class MediaSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_familia_duena_puede_descargar_su_evidencia(): void
    {
        Storage::fake('local');
        ['user' => $user, 'nino' => $nino] = $this->familiaConHijo();

        $evidence = $this->crearEvidence($user->family_id, $nino->id);
        $file = UploadedFile::fake()->image('foto.jpg');
        $path = $file->store('evidence/'.$evidence->id, 'local');
        $media = $evidence->media()->create([
            'type' => Media::TYPE_IMAGE,
            'path' => $path,
            'original_name' => 'foto.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 100,
        ]);

        $this->actingAs($user)->get(route('media.show', $media))->assertOk();
    }

    public function test_otra_familia_no_puede_descargar_una_evidencia_ajena(): void
    {
        Storage::fake('local');
        ['nino' => $nino] = $this->familiaConHijo();
        $familiaDueno = Family::factory()->create();
        $otraFamilia = Family::factory()->create();
        $otroUser = User::factory()->familia($otraFamilia)->create();

        $evidence = $this->crearEvidence($familiaDueno->id, $nino->id);
        $file = UploadedFile::fake()->image('foto.jpg');
        $path = $file->store('evidence/'.$evidence->id, 'local');
        $media = $evidence->media()->create([
            'type' => Media::TYPE_IMAGE,
            'path' => $path,
            'original_name' => 'foto.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 100,
        ]);

        $this->actingAs($otroUser)->get(route('media.show', $media))->assertForbidden();
    }

    private function crearEvidence(int $familyId, int $childId): Evidence
    {
        return Evidence::create([
            'content_id' => Content::factory()->create()->id,
            'child_id' => $childId,
            'family_id' => $familyId,
            'comment' => 'Evidencia de prueba',
            'status' => Evidence::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);
    }

    private function familiaConHijo(): array
    {
        $guia = User::factory()->guia()->create();
        $ambiente = Environment::factory()->create(['teacher_id' => $guia->id]);
        $familia = Family::factory()->create();
        $nino = Child::factory()->create(['family_id' => $familia->id, 'environment_id' => $ambiente->id]);
        $user = User::factory()->familia($familia)->create();

        return compact('user', 'nino');
    }
}
