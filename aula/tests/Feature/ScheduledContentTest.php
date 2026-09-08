<?php

namespace Tests\Feature;

use App\Models\Content;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Bloque 3 — Publicación programada (Fase 21). El scheduler solo toca
 * contenido marcado como 'scheduled' cuya fecha ya pasó; el resto queda
 * intacto.
 */
class ScheduledContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_publica_contenido_programado_cuya_fecha_ya_paso(): void
    {
        $content = Content::factory()->create([
            'status' => Content::STATUS_SCHEDULED,
            'published_at' => now()->subMinute(),
        ]);

        $this->artisan('content:publish-scheduled')->assertSuccessful();

        $this->assertSame(Content::STATUS_PUBLISHED, $content->fresh()->status);
    }

    public function test_no_toca_contenido_programado_cuya_fecha_aun_no_llega(): void
    {
        $content = Content::factory()->create([
            'status' => Content::STATUS_SCHEDULED,
            'published_at' => now()->addDay(),
        ]);

        $this->artisan('content:publish-scheduled')->assertSuccessful();

        $this->assertSame(Content::STATUS_SCHEDULED, $content->fresh()->status);
    }

    public function test_no_toca_borradores_ni_publicados(): void
    {
        $borrador = Content::factory()->create(['status' => Content::STATUS_DRAFT]);
        $publicado = Content::factory()->published()->create();

        $this->artisan('content:publish-scheduled')->assertSuccessful();

        $this->assertSame(Content::STATUS_DRAFT, $borrador->fresh()->status);
        $this->assertSame(Content::STATUS_PUBLISHED, $publicado->fresh()->status);
    }
}
