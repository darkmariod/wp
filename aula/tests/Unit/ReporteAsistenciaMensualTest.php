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

    public function test_la_semana_siempre_cubre_7_dias_de_lunes_a_domingo(): void
    {
        $nino = Child::factory()->create();

        // Un miércoles cualquiera: el reporte tiene que ubicar solo la
        // semana que lo contiene, no desde el 1 de mes ni desde hoy.
        $reporte = ReporteAsistenciaMensual::generarSemana($nino, CarbonImmutable::parse('2026-09-23'));

        $this->assertCount(7, $reporte['dias']);
        $this->assertSame('2026-09-21', $reporte['dias'][0]['fecha']->toDateString());
        $this->assertSame('2026-09-27', $reporte['dias'][6]['fecha']->toDateString());
    }

    public function test_dos_fechas_de_la_misma_semana_dan_el_mismo_reporte(): void
    {
        $nino = Child::factory()->create();

        // El lunes y el domingo de una misma semana civil tienen que
        // resolver a la MISMA semana (lun-dom), no a dos distintas.
        $desdeLunes = ReporteAsistenciaMensual::generarSemana($nino, CarbonImmutable::parse('2026-09-21'));
        $desdeDomingo = ReporteAsistenciaMensual::generarSemana($nino, CarbonImmutable::parse('2026-09-27'));

        $this->assertSame(
            $desdeLunes['dias'][0]['fecha']->toDateString(),
            $desdeDomingo['dias'][0]['fecha']->toDateString(),
        );
    }

    public function test_una_semana_que_cruza_de_mes_trae_dias_de_los_dos_meses(): void
    {
        $nino = Child::factory()->create();

        // La semana del 28 de sep. al 4 de oct. 2026 cruza el fin de mes.
        $reporte = ReporteAsistenciaMensual::generarSemana($nino, CarbonImmutable::parse('2026-09-30'));

        $this->assertSame('2026-09-28', $reporte['dias'][0]['fecha']->toDateString());
        $this->assertSame('2026-10-04', $reporte['dias'][6]['fecha']->toDateString());
    }

    public function test_el_reporte_anual_cubre_el_ciclo_completo(): void
    {
        $nino = Child::factory()->create();

        $reporte = ReporteAsistenciaMensual::generarAnual($nino, 2025);

        $this->assertSame('2025-09-01', $reporte['dias'][0]['fecha']->toDateString());
        $this->assertSame('2026-07-31', $reporte['dias'][array_key_last($reporte['dias'])]['fecha']->toDateString());
    }

    public function test_el_reporte_anual_cuenta_los_registros_reales_del_ciclo(): void
    {
        $nino = Child::factory()->create();

        Attendance::factory()->create([
            'child_id' => $nino->id,
            'date' => '2026-03-10',
            'status' => Attendance::STATUS_PRESENTE,
        ]);

        $reporte = ReporteAsistenciaMensual::generarAnual($nino, 2025);

        $this->assertSame(1, $reporte['resumen'][Attendance::STATUS_PRESENTE]);
    }
}
