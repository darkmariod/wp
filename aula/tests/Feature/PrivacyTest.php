<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Bloque 4 — Privacidad y manejo de datos de menores.
 */
class PrivacyTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_politica_de_privacidad_esta_disponible_publicamente(): void
    {
        $this->get(route('privacidad'))->assertOk();
    }

    public function test_no_se_guardan_columnas_innecesarias_de_menores(): void
    {
        $this->assertFalse(
            Schema::hasColumn('children', 'dni') ||
            Schema::hasColumn('children', 'full_name') ||
            Schema::hasColumn('children', 'photo'),
            'Los niños no deben almacenar datos biométricos ni identificativos innecesarios.',
        );
    }

    public function test_las_evidencias_son_privadas_por_defecto(): void
    {
        $this->assertSame('local', config('filesystems.default'));
        $this->assertStringNotContainsString('public', config('filesystems.disks.local.root'));
    }
}
