<?php

namespace Tests\Unit;

use App\Models\Attendance;
use App\Models\Child;
use App\Support\ReporteAsistenciaMensual;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReporteAsistenciaMensualTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_feriado_se_cuenta_como_feriado_y_no_como_falta(): void
    {
        $nino = Child::factory()->create();

        // 2026-11-02 es feriado (Día de Difuntos) y no tiene registro:
        // si el reporte lo tratara como "sin registro" o falta, estaría
        // mal — el colegio simplemente no tuvo clases ese día.
        $reporte = ReporteAsistenciaMensual::generar($nino, CarbonImmutable::parse('2026-11-01'));

        $this->assertSame(2, $reporte['resumen'][ReporteAsistenciaMensual::FERIADO]);

        $dia2 = collect($reporte['dias'])->firstWhere(fn ($d) => $d['fecha']->toDateString() === '2026-11-02');
        $this->assertSame('Día de Difuntos / Independencia de Cuenca', $dia2['feriado']);
        $this->assertNull($dia2['status']);
    }

    public function test_un_dia_de_clases_sin_registro_no_se_cuenta_como_falta(): void
    {
        $nino = Child::factory()->create();

        // Setiembre 2026: sin feriados, un mes de clases común.
        $reporte = ReporteAsistenciaMensual::generar($nino, CarbonImmutable::parse('2026-09-01'));

        $this->assertSame(0, $reporte['resumen'][Attendance::STATUS_FALTA_INJUSTIFICADA]);
        $this->assertGreaterThan(0, $reporte['resumen'][ReporteAsistenciaMensual::SIN_REGISTRO]);
    }

    public function test_cuenta_los_registros_reales_por_estado(): void
    {
        $nino = Child::factory()->create();

        Attendance::factory()->create([
            'child_id' => $nino->id,
            'date' => '2026-09-07',
            'status' => Attendance::STATUS_PRESENTE,
        ]);
        Attendance::factory()->create([
            'child_id' => $nino->id,
            'date' => '2026-09-08',
            'status' => Attendance::STATUS_FALTA_INJUSTIFICADA,
        ]);

        $reporte = ReporteAsistenciaMensual::generar($nino, CarbonImmutable::parse('2026-09-01'));

        $this->assertSame(1, $reporte['resumen'][Attendance::STATUS_PRESENTE]);
        $this->assertSame(1, $reporte['resumen'][Attendance::STATUS_FALTA_INJUSTIFICADA]);
    }

    public function test_el_calendario_cubre_todos_los_dias_del_mes(): void
    {
        $nino = Child::factory()->create();

        $reporte = ReporteAsistenciaMensual::generar($nino, CarbonImmutable::parse('2026-09-15'));

        $this->assertCount(30, $reporte['dias']);
    }
}
