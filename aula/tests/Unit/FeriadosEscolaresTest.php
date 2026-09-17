<?php

namespace Tests\Unit;

use App\Support\FeriadosEscolares;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class FeriadosEscolaresTest extends TestCase
{
    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function feriadosDelCiclo(): array
    {
        return [
            'independencia de Guayaquil' => ['2026-10-09', 'Independencia de Guayaquil'],
            'día de Difuntos' => ['2026-11-02', 'Día de Difuntos / Independencia de Cuenca'],
            'independencia de Cuenca' => ['2026-11-03', 'Día de Difuntos / Independencia de Cuenca'],
            'medio de las vacaciones de fin de año' => ['2026-12-28', 'Vacaciones de fin de año'],
            'año nuevo (dentro del receso)' => ['2027-01-01', 'Vacaciones de fin de año'],
            'carnaval lunes' => ['2027-02-08', 'Carnaval'],
            'carnaval martes' => ['2027-02-09', 'Carnaval'],
            'viernes Santo' => ['2027-03-26', 'Viernes Santo'],
            'día del Trabajo trasladado' => ['2027-04-30', 'Día del Trabajo (trasladado)'],
            'batalla de Pichincha' => ['2027-05-24', 'Batalla de Pichincha'],
        ];
    }

    #[DataProvider('feriadosDelCiclo')]
    public function test_reconoce_los_feriados_oficiales_del_ciclo(string $fecha, string $nombreEsperado): void
    {
        $this->assertTrue(FeriadosEscolares::esFeriado(CarbonImmutable::parse($fecha)));
        $this->assertSame($nombreEsperado, FeriadosEscolares::nombre(CarbonImmutable::parse($fecha)));
    }

    public function test_un_dia_de_clases_normal_no_es_feriado(): void
    {
        $this->assertFalse(FeriadosEscolares::esFeriado(CarbonImmutable::parse('2026-09-15')));
        $this->assertNull(FeriadosEscolares::nombre(CarbonImmutable::parse('2026-09-15')));
    }

    public function test_un_ciclo_sin_feriados_cargados_no_revienta(): void
    {
        $this->assertFalse(FeriadosEscolares::esFeriado(CarbonImmutable::parse('2030-10-09')));
    }
}
