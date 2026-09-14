<?php

namespace Tests\Feature;

use App\Filament\Pages\RegistrarAsistencia;
use App\Livewire\MiEscuelita\Asistencia;
use App\Models\Attendance;
use App\Models\Child;
use App\Models\Environment;
use App\Models\Family;
use App\Models\User;
use App\Support\CicloEscolar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Módulo de asistencia: registro diario por la guía, resumen para
 * docentes y reporte individual por niño para las familias.
 */
class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Guía con su ambiente y una familia con un niño dentro.
     */
    private function ambienteConFamilia(): array
    {
        $guia = User::factory()->guia()->create();
        $ambiente = Environment::factory()->create(['teacher_id' => $guia->id]);
        $familia = Family::factory()->create();
        $nino = Child::factory()->create([
            'family_id' => $familia->id,
            'environment_id' => $ambiente->id,
        ]);
        $user = User::factory()->familia($familia)->create();

        return compact('guia', 'ambiente', 'familia', 'nino', 'user');
    }

    public function test_la_guia_registra_asistencia_de_un_dia(): void
    {
        ['guia' => $guia, 'ambiente' => $ambiente, 'nino' => $nino] = $this->ambienteConFamilia();

        $fecha = now()->toDateString();

        Livewire::actingAs($guia)
            ->test(RegistrarAsistencia::class)
            ->set('environment_id', $ambiente->id)
            ->set('fecha', $fecha)
            ->set('estados', [$nino->id => Attendance::STATUS_PRESENTE])
            ->call('guardar')
            ->assertHasNoFormErrors();

        $registro = Attendance::where('child_id', $nino->id)->first();

        $this->assertNotNull($registro);
        $this->assertSame($fecha, $registro->date->toDateString());
        $this->assertSame(Attendance::STATUS_PRESENTE, $registro->status);
        $this->assertSame($guia->id, $registro->recorded_by);
    }

    public function test_la_guia_no_registra_en_un_ambiente_ajeno(): void
    {
        ['guia' => $guia] = $this->ambienteConFamilia();
        $otraGuia = User::factory()->guia()->create();
        $ambienteAjeno = Environment::factory()->create(['teacher_id' => $otraGuia->id]);
        $ninoAjeno = Child::factory()->create(['environment_id' => $ambienteAjeno->id]);

        $this->actingAs($guia);

        // El Select no le ofrece ambientes ajenos.
        $page = new RegistrarAsistencia();
        $this->assertArrayNotHasKey($ambienteAjeno->id, $page->opcionesAmbientes());

        // Y aunque la request fuera manipulada, el Select valida que el
        // ambiente esté dentro de sus opciones: rebota con errores.
        Livewire::actingAs($guia)
            ->test(RegistrarAsistencia::class)
            ->set('environment_id', $ambienteAjeno->id)
            ->set('fecha', now()->toDateString())
            ->set('estados', [$ninoAjeno->id => Attendance::STATUS_PRESENTE])
            ->call('guardar')
            ->assertHasFormErrors(['environment_id' => 'in']);

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_marcar_de_nuevo_en_el_mismo_dia_no_duplica(): void
    {
        ['guia' => $guia, 'ambiente' => $ambiente, 'nino' => $nino] = $this->ambienteConFamilia();
        $fecha = now()->toDateString();

        Livewire::actingAs($guia)
            ->test(RegistrarAsistencia::class)
            ->set('environment_id', $ambiente->id)
            ->set('fecha', $fecha)
            ->set('estados', [$nino->id => Attendance::STATUS_ATRASO])
            ->call('guardar');

        Livewire::actingAs($guia)
            ->test(RegistrarAsistencia::class)
            ->set('environment_id', $ambiente->id)
            ->set('fecha', $fecha)
            ->set('estados', [$nino->id => Attendance::STATUS_PRESENTE])
            ->call('guardar');

        $this->assertDatabaseCount('attendances', 1);

        $registro = Attendance::where('child_id', $nino->id)->first();
        $this->assertSame(Attendance::STATUS_PRESENTE, $registro->status);
    }

    public function test_resumen_mensual_cuenta_cada_estado(): void
    {
        ['user' => $user, 'nino' => $nino] = $this->ambienteConFamilia();
        $mes = now()->startOfMonth();

        Attendance::factory()->create([
            'child_id' => $nino->id,
            'date' => $mes->copy()->day(2),
            'status' => Attendance::STATUS_PRESENTE,
        ]);
        Attendance::factory()->create([
            'child_id' => $nino->id,
            'date' => $mes->copy()->day(4),
            'status' => Attendance::STATUS_ATRASO,
        ]);
        Attendance::factory()->create([
            'child_id' => $nino->id,
            'date' => $mes->copy()->day(9),
            'status' => Attendance::STATUS_FALTA_JUSTIFICADA,
        ]);
        Attendance::factory()->create([
            'child_id' => $nino->id,
            'date' => $mes->copy()->day(11),
            'status' => Attendance::STATUS_FALTA_INJUSTIFICADA,
        ]);

        Livewire::actingAs($user)
            ->test(Asistencia::class)
            ->assertSet('resumenMes', [
                Attendance::STATUS_PRESENTE => 1,
                Attendance::STATUS_ATRASO => 1,
                Attendance::STATUS_FALTA_JUSTIFICADA => 1,
                Attendance::STATUS_FALTA_INJUSTIFICADA => 1,
            ])
            ->assertSet('detalleDias', fn (iterable $dias) => collect($dias)->pluck('status')->all() === [
                Attendance::STATUS_PRESENTE,
                Attendance::STATUS_ATRASO,
                Attendance::STATUS_FALTA_JUSTIFICADA,
                Attendance::STATUS_FALTA_INJUSTIFICADA,
            ]);
    }

    public function test_familia_solo_ve_la_asistencia_de_su_propio_hijo(): void
    {
        ['user' => $user, 'ambiente' => $ambiente, 'nino' => $nino] = $this->ambienteConFamilia();
        $otraFamilia = Family::factory()->create();
        $otroNino = Child::factory()->create([
            'family_id' => $otraFamilia->id,
            'environment_id' => $ambiente->id,
        ]);
        $mes = now()->startOfMonth();

        Attendance::factory()->create([
            'child_id' => $nino->id,
            'date' => $mes->copy()->day(3),
            'status' => Attendance::STATUS_PRESENTE,
        ]);
        Attendance::factory()->create([
            'child_id' => $otroNino->id,
            'date' => $mes->copy()->day(5),
            'status' => Attendance::STATUS_PRESENTE,
        ]);

        Livewire::actingAs($user)
            ->test(Asistencia::class)
            ->assertSet('resumenMes', [
                Attendance::STATUS_PRESENTE => 1,
                Attendance::STATUS_ATRASO => 0,
                Attendance::STATUS_FALTA_JUSTIFICADA => 0,
                Attendance::STATUS_FALTA_INJUSTIFICADA => 0,
            ])
            ->assertSet('detalleDias', fn (iterable $dias) => collect($dias)->pluck('child_id')->all() === [$nino->id]);
    }

    public function test_resumen_anual_suma_los_meses_del_ciclo_cerrado(): void
    {
        ['user' => $user, 'nino' => $nino] = $this->ambienteConFamilia();
        $anioCerrado = CicloEscolar::ultimoCerrado();

        $this->assertNotNull($anioCerrado);

        // Un registro en octubre (si el ciclo es 2025-2026, octubre 2025
        // pertenece al MISMO ciclo: septiembre arranca el ciclo).
        Attendance::factory()->create([
            'child_id' => $nino->id,
            'date' => CicloEscolar::inicio($anioCerrado)->addMonths(1)->day(10),
            'status' => Attendance::STATUS_PRESENTE,
        ]);
        Attendance::factory()->create([
            'child_id' => $nino->id,
            'date' => CicloEscolar::fin($anioCerrado)->subDays(5),
            'status' => Attendance::STATUS_ATRASO,
        ]);

        Livewire::actingAs($user)
            ->test(Asistencia::class)
            ->assertSet('resumenAnual', [
                'anio' => $anioCerrado,
                'etiqueta' => CicloEscolar::etiqueta($anioCerrado),
                'counts' => [
                    Attendance::STATUS_PRESENTE => 1,
                    Attendance::STATUS_ATRASO => 1,
                    Attendance::STATUS_FALTA_JUSTIFICADA => 0,
                    Attendance::STATUS_FALTA_INJUSTIFICADA => 0,
                ],
            ]);
    }

    public function test_padre_no_ve_anual_del_ciclo_en_curso(): void
    {
        ['user' => $user, 'nino' => $nino] = $this->ambienteConFamilia();

        // Asistencia SOLO en el ciclo vigente, que todavía no se cierra:
        // el anual del ciclo anterior no tiene registros y no se muestra.
        $cicloVigente = CicloEscolar::vigente();
        Attendance::factory()->create([
            'child_id' => $nino->id,
            'date' => now()->startOfMonth()->day(1),
            'status' => Attendance::STATUS_PRESENTE,
        ]);
        $this->assertSame($cicloVigente, CicloEscolar::deFecha(now()->startOfMonth()->day(1)));

        Livewire::actingAs($user)
            ->test(Asistencia::class)
            ->assertSet('resumenAnual', null);
    }

    public function test_padre_si_ve_anual_del_ultimo_ciclo_cerrado(): void
    {
        ['user' => $user, 'nino' => $nino] = $this->ambienteConFamilia();
        $anioCerrado = CicloEscolar::ultimoCerrado();

        // Un registro en el mes del cierre: garantiza que el corte es el
        // 31 de julio (fin del ciclo) y no el calendario.-
        Attendance::factory()->create([
            'child_id' => $nino->id,
            'date' => CicloEscolar::fin($anioCerrado)->subMonth()->day(15),
            'status' => Attendance::STATUS_PRESENTE,
        ]);

        Livewire::actingAs($user)
            ->test(Asistencia::class)
            ->assertSet('resumenAnual', [
                'anio' => $anioCerrado,
                'etiqueta' => CicloEscolar::etiqueta($anioCerrado),
                'counts' => [
                    Attendance::STATUS_PRESENTE => 1,
                    Attendance::STATUS_ATRASO => 0,
                    Attendance::STATUS_FALTA_JUSTIFICADA => 0,
                    Attendance::STATUS_FALTA_INJUSTIFICADA => 0,
                ],
            ]);
    }

    public function test_solo_staff_y_guia_acceden_al_registro(): void
    {
        $staff = User::factory()->administrador()->create();

        $this->actingAs($staff);
        $this->assertTrue(RegistrarAsistencia::canAccess());

        $guia = User::factory()->guia()->create();
        $this->actingAs($guia);
        $this->assertTrue(RegistrarAsistencia::canAccess());

        $user = User::factory()->familia(Family::factory()->create())->create();
        $this->actingAs($user);
        $this->assertFalse(RegistrarAsistencia::canAccess());

        auth()->logout();
        $this->assertFalse(RegistrarAsistencia::canAccess());
    }
}