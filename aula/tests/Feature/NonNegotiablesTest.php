<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Bloque 9 — No negociables del proyecto. Cada principio pedagógico se
 * blindó en el código y aquí se verifica con una sola ejecución.
 */
class NonNegotiablesTest extends TestCase
{
    use RefreshDatabase;

    private const TABLAS = ['evidence', 'observations', 'feedback', 'content', 'children'];

    private const COLUMNAS_PROHIBIDAS = ['grade', 'score', 'percentage', 'ranking', 'gpa', 'points'];

    public function test_no_existe_ninguna_columna_de_calificacion_o_ranking(): void
    {
        foreach (self::TABLAS as $tabla) {
            foreach (self::COLUMNAS_PROHIBIDAS as $columna) {
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
        $this->assertFalse(Schema::hasTable('attendance'));
    }

    public function test_no_existen_cuentas_para_ninos(): void
    {
        // Los niños quedan ligados a una familia; nunca tienen credenciales
        // de login propias.
        $this->assertFalse(Schema::hasColumn('children', 'email'));
        $this->assertFalse(Schema::hasColumn('children', 'password'));
        $this->assertFalse(Schema::hasColumn('children', 'user_id'));
    }

    public function test_los_archivos_privados_nunca_se_piden_por_url_publica(): void
    {
        $this->assertSame('local', config('filesystems.default'));
        $this->assertStringNotContainsString('public', config('filesystems.disks.local.root'));
    }
}
