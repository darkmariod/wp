<?php

namespace Tests\Feature;

use App\Filament\Pages\ReporteAsistencia;
use App\Livewire\MiEscuelita\Asistencia;
use App\Models\Attendance;
use App\Models\Child;
use App\Models\Environment;
use App\Models\Family;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Reporte mensual de asistencia por niño (PDF/Excel): es una
 * herramienta exclusiva del personal del colegio, nunca de las
 * familias, y respeta el mismo aislamiento por ambiente que el resto
 * del módulo de asistencia.
 */
class ReporteAsistenciaTest extends TestCase
{
    use RefreshDatabase;

    private function ambienteConNino(): array
    {
        $guia = User::factory()->guia()->create();
        $ambiente = Environment::factory()->create(['teacher_id' => $guia->id]);
        $familia = Family::factory()->create();
        $nino = Child::factory()->create([
            'family_id' => $familia->id,
            'environment_id' => $ambiente->id,
        ]);

        return compact('guia', 'ambiente', 'familia', 'nino');
    }

    public function test_solo_staff_y_guia_acceden_al_reporte(): void
    {
        $staff = User::factory()->administrador()->create();
        $this->actingAs($staff);
        $this->assertTrue(ReporteAsistencia::canAccess());

        $guia = User::factory()->guia()->create();
        $this->actingAs($guia);
        $this->assertTrue(ReporteAsistencia::canAccess());

        $familia = User::factory()->familia(Family::factory()->create())->create();
        $this->actingAs($familia);
        $this->assertFalse(ReporteAsistencia::canAccess());
    }

    public function test_la_guia_no_ve_ambientes_ajenos_en_el_selector(): void
    {
        ['guia' => $guia] = $this->ambienteConNino();
        $otraGuia = User::factory()->guia()->create();
        $ambienteAjeno = Environment::factory()->create(['teacher_id' => $otraGuia->id]);

        $this->actingAs($guia);

        $page = new ReporteAsistencia;
        $this->assertArrayNotHasKey($ambienteAjeno->id, $page->opcionesAmbientes());
    }

    public function test_la_guia_no_puede_pedir_el_reporte_de_un_nino_ajeno(): void
    {
        ['guia' => $guia] = $this->ambienteConNino();
        $otraGuia = User::factory()->guia()->create();
        $ambienteAjeno = Environment::factory()->create(['teacher_id' => $otraGuia->id]);
        $ninoAjeno = Child::factory()->create(['environment_id' => $ambienteAjeno->id]);

        $this->actingAs($guia);

        $page = new ReporteAsistencia;
        $page->child_id = $ninoAjeno->id;

        $this->assertNull($page->child());
    }

    public function test_el_reporte_marca_un_feriado_y_no_una_falta(): void
    {
        ['guia' => $guia, 'nino' => $nino] = $this->ambienteConNino();

        $this->actingAs($guia);

        $page = new ReporteAsistencia;
        $page->child_id = $nino->id;
        $page->mes = '2026-11';

        $reporte = $page->reporte();

        $this->assertSame(2, $reporte['resumen']['feriado']);
        $this->assertSame(0, $reporte['resumen'][Attendance::STATUS_FALTA_INJUSTIFICADA]);
    }

    public function test_descarga_el_pdf_del_mes_elegido(): void
    {
        ['guia' => $guia, 'ambiente' => $ambiente, 'nino' => $nino] = $this->ambienteConNino();

        Attendance::factory()->create([
            'child_id' => $nino->id,
            'date' => '2026-09-07',
            'status' => Attendance::STATUS_PRESENTE,
        ]);

        Livewire::actingAs($guia)
            ->test(ReporteAsistencia::class)
            ->set('environment_id', $ambiente->id)
            ->set('child_id', $nino->id)
            ->set('mes', '2026-09')
            ->call('descargarPdf')
            ->assertFileDownloaded('asistencia-'.Str::slug($nino->name).'-2026-09.pdf');
    }

    public function test_descarga_el_excel_del_mes_elegido(): void
    {
        ['guia' => $guia, 'ambiente' => $ambiente, 'nino' => $nino] = $this->ambienteConNino();

        Livewire::actingAs($guia)
            ->test(ReporteAsistencia::class)
            ->set('environment_id', $ambiente->id)
            ->set('child_id', $nino->id)
            ->set('mes', '2026-09')
            ->call('descargarExcel')
            ->assertFileDownloaded('asistencia-'.Str::slug($nino->name).'-2026-09.xlsx');
    }

    public function test_descarga_el_pdf_de_la_semana_elegida(): void
    {
        ['guia' => $guia, 'ambiente' => $ambiente, 'nino' => $nino] = $this->ambienteConNino();

        Livewire::actingAs($guia)
            ->test(ReporteAsistencia::class)
            ->set('environment_id', $ambiente->id)
            ->set('child_id', $nino->id)
            ->set('periodo', 'semana')
            ->set('semana', '2026-09-21')
            ->call('descargarPdf')
            ->assertFileDownloaded('asistencia-'.Str::slug($nino->name).'-semana-2026-09-21.pdf');
    }

    public function test_descarga_el_excel_de_la_semana_elegida(): void
    {
        ['guia' => $guia, 'ambiente' => $ambiente, 'nino' => $nino] = $this->ambienteConNino();

        Livewire::actingAs($guia)
            ->test(ReporteAsistencia::class)
            ->set('environment_id', $ambiente->id)
            ->set('child_id', $nino->id)
            ->set('periodo', 'semana')
            ->set('semana', '2026-09-21')
            ->call('descargarExcel')
            ->assertFileDownloaded('asistencia-'.Str::slug($nino->name).'-semana-2026-09-21.xlsx');
    }

    public function test_descarga_el_pdf_del_ultimo_ciclo_cerrado(): void
    {
        ['guia' => $guia, 'ambiente' => $ambiente, 'nino' => $nino] = $this->ambienteConNino();

        Livewire::actingAs($guia)
            ->test(ReporteAsistencia::class)
            ->set('environment_id', $ambiente->id)
            ->set('child_id', $nino->id)
            ->set('periodo', 'anio')
            ->call('descargarPdf')
            ->assertFileDownloaded('asistencia-'.Str::slug($nino->name).'-ciclo-2025-2026.pdf');
    }

    public function test_sin_ciclo_cerrado_el_reporte_anual_no_rompe_avisa_y_no_descarga(): void
    {
        // "now" en 2025-01-01: el ciclo vigente es 2024-2025 (todavía no
        // cerrado) y el anterior es 2023, previo al piso de 2025 que
        // usa ultimoCerrado() — no hay ningún ciclo cerrado para reportar.
        CarbonImmutable::setTestNow('2025-01-01');

        ['guia' => $guia, 'ambiente' => $ambiente, 'nino' => $nino] = $this->ambienteConNino();

        Livewire::actingAs($guia)
            ->test(ReporteAsistencia::class)
            ->set('environment_id', $ambiente->id)
            ->set('child_id', $nino->id)
            ->set('periodo', 'anio')
            ->call('descargarPdf')
            ->assertNoFileDownloaded()
            ->assertNotified('Todavía no hay ningún ciclo lectivo cerrado para reportar');

        CarbonImmutable::setTestNow();
    }

    public function test_la_guia_no_puede_pedir_la_semana_de_un_nino_ajeno(): void
    {
        ['guia' => $guia, 'ambiente' => $ambiente] = $this->ambienteConNino();
        $otraGuia = User::factory()->guia()->create();
        $ambienteAjeno = Environment::factory()->create(['teacher_id' => $otraGuia->id]);
        $ninoAjeno = Child::factory()->create(['environment_id' => $ambienteAjeno->id]);

        Livewire::actingAs($guia)
            ->test(ReporteAsistencia::class)
            ->set('environment_id', $ambiente->id)
            ->set('child_id', $ninoAjeno->id)
            ->set('periodo', 'semana')
            ->set('semana', '2026-09-21')
            ->call('descargarPdf')
            ->assertNoFileDownloaded();
    }

    public function test_el_resumen_semanal_del_panel_coincide_con_el_del_portal_de_familias(): void
    {
        ['guia' => $guia, 'ambiente' => $ambiente, 'nino' => $nino, 'familia' => $familia] = $this->ambienteConNino();

        $padre = User::factory()->familia($familia)->create();

        Attendance::factory()->create([
            'child_id' => $nino->id,
            'date' => '2026-09-21',
            'status' => Attendance::STATUS_PRESENTE,
        ]);
        Attendance::factory()->create([
            'child_id' => $nino->id,
            'date' => '2026-09-22',
            'status' => Attendance::STATUS_ATRASO,
        ]);

        $reportePanel = Livewire::actingAs($guia)
            ->test(ReporteAsistencia::class)
            ->set('environment_id', $ambiente->id)
            ->set('child_id', $nino->id)
            ->set('periodo', 'semana')
            ->set('semana', '2026-09-21')
            ->instance()
            ->reporte();

        $resumenFamilia = Livewire::actingAs($padre)
            ->test(Asistencia::class)
            ->set('semana', '2026-09-21')
            ->instance()
            ->resumenSemana();

        $this->assertSame($reportePanel['resumen'][Attendance::STATUS_PRESENTE], $resumenFamilia[Attendance::STATUS_PRESENTE]);
        $this->assertSame($reportePanel['resumen'][Attendance::STATUS_ATRASO], $resumenFamilia[Attendance::STATUS_ATRASO]);
    }
}
