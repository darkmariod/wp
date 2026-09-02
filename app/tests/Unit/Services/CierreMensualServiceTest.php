<?php

namespace Tests\Unit\Services;

use App\Models\AsientoContable;
use App\Models\DetalleAsiento;
use App\Models\PlanCuenta;
use App\Models\User;
use App\Services\CierreMensualService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CierreMensualServiceTest extends TestCase
{
    use RefreshDatabase;

    private CierreMensualService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CierreMensualService;
    }

    public function test_creates_closing_entry_with_correct_structure(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        PlanCuenta::factory()->create([
            'codigo' => '3.1.1.04',
            'grupo' => 'patrimonio',
            'tipo' => 'acreedor',
        ]);

        $cuentaIngresos = PlanCuenta::factory()->create([
            'codigo' => '4',
            'grupo' => 'ingreso',
            'tipo' => 'acreedor',
        ]);

        $asientoIngreso = AsientoContable::factory()->create([
            'fecha' => Carbon::create(2026, 8, 15),
            'estado' => 'aprobado',
        ]);
        DetalleAsiento::factory()->create([
            'asiento_id' => $asientoIngreso->id,
            'cuenta_id' => $cuentaIngresos->id,
            'debe' => 0,
            'haber' => 50000.00,
        ]);

        $asiento = $this->service->cerrarMes(8, 2026);

        $this->assertNotNull($asiento->id);
        $this->assertStringStartsWith('CIE-2026-', $asiento->numero_asiento);
        $this->assertEquals('cierre', $asiento->tipo);
        $this->assertEquals('2026-08-31', $asiento->fecha->toDateString());
    }

    public function test_closes_income_accounts_debit_income_credit_resultado(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $cuentaResultado = PlanCuenta::factory()->create([
            'codigo' => '3.1.1.04',
            'grupo' => 'patrimonio',
            'tipo' => 'acreedor',
        ]);

        $cuentaIngresos = PlanCuenta::factory()->create([
            'codigo' => '4',
            'grupo' => 'ingreso',
            'tipo' => 'acreedor',
        ]);

        $asientoIngreso = AsientoContable::factory()->create([
            'fecha' => Carbon::create(2026, 8, 10),
            'estado' => 'aprobado',
        ]);
        DetalleAsiento::factory()->create([
            'asiento_id' => $asientoIngreso->id,
            'cuenta_id' => $cuentaIngresos->id,
            'debe' => 0,
            'haber' => 80000.00,
        ]);

        $asiento = $this->service->cerrarMes(8, 2026);

        $detalles = $asiento->detalles;
        $ingresoDetalle = $detalles->firstWhere('cuenta_id', $cuentaIngresos->id);
        $this->assertNotNull($ingresoDetalle);
        $this->assertEquals(80000.00, (float) $ingresoDetalle->debe);
        $this->assertEquals(0.00, (float) $ingresoDetalle->haber);

        $resultadoDetalle = $detalles->firstWhere('cuenta_id', $cuentaResultado->id);
        $this->assertNotNull($resultadoDetalle);
        $this->assertEquals(0.00, (float) $resultadoDetalle->debe);
        $this->assertEquals(80000.00, (float) $resultadoDetalle->haber);
    }

    public function test_closes_expense_accounts_debit_resultado_credit_expenses(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $cuentaResultado = PlanCuenta::factory()->create([
            'codigo' => '3.1.1.04',
            'grupo' => 'patrimonio',
            'tipo' => 'acreedor',
        ]);

        $cuentaGastos = PlanCuenta::factory()->create([
            'codigo' => '5',
            'grupo' => 'gasto',
            'tipo' => 'deudor',
        ]);

        $cuentaGastoDetalle = PlanCuenta::factory()->create([
            'codigo' => '5.1.1.01',
            'grupo' => 'gasto',
            'tipo' => 'deudor',
            'padre_id' => $cuentaGastos->id,
        ]);

        $asientoGasto = AsientoContable::factory()->create([
            'fecha' => Carbon::create(2026, 8, 12),
            'estado' => 'aprobado',
        ]);
        DetalleAsiento::factory()->create([
            'asiento_id' => $asientoGasto->id,
            'cuenta_id' => $cuentaGastoDetalle->id,
            'debe' => 30000.00,
            'haber' => 0,
        ]);

        $asiento = $this->service->cerrarMes(8, 2026);

        $detalles = $asiento->detalles;
        $gastoDetalle = $detalles->firstWhere('cuenta_id', $cuentaGastos->id);
        $this->assertNotNull($gastoDetalle);
        $this->assertEquals(0.00, (float) $gastoDetalle->debe);
        $this->assertEquals(30000.00, (float) $gastoDetalle->haber);

        $resultadoDetalle = $detalles->firstWhere('cuenta_id', $cuentaResultado->id);
        $this->assertNotNull($resultadoDetalle);
        $this->assertEquals(30000.00, (float) $resultadoDetalle->debe);
        $this->assertEquals(0.00, (float) $resultadoDetalle->haber);
    }

    public function test_handles_month_with_no_transactions(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        PlanCuenta::factory()->create([
            'codigo' => '3.1.1.04',
            'grupo' => 'patrimonio',
            'tipo' => 'acreedor',
        ]);

        $asiento = $this->service->cerrarMes(1, 2026);

        $this->assertNotNull($asiento->id);
        $this->assertEquals('CIE-2026-01', $asiento->numero_asiento);
        $this->assertCount(0, $asiento->detalles);
    }
}
