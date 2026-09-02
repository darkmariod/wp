<?php

namespace Tests\Unit\Services;

use App\Models\AmortizacionAnticipo;
use App\Models\AnticipoCliente;
use App\Models\Obra;
use App\Models\PlanCuenta;
use App\Models\User;
use App\Services\AmortizacionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AmortizacionServiceTest extends TestCase
{
    use RefreshDatabase;

    private AmortizacionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AmortizacionService;
    }

    public function test_calculates_amortization_at_50_percent_advance(): void
    {
        $obra = Obra::factory()->create();
        $anticipo = AnticipoCliente::factory()->create([
            'obra_id' => $obra->id,
            'monto_total' => 100000.00,
        ]);

        $result = $this->service->calcularAmortizacion($anticipo, 50.00);

        $this->assertEquals(50000.00, $result['monto_a_amortizar']);
        $this->assertEquals(50000.00, $result['amortizacion_acumulada']);
        $this->assertEquals(50000.00, $result['saldo_pendiente']);
    }

    public function test_calculates_amortization_at_100_percent_advance(): void
    {
        $obra = Obra::factory()->create();
        $anticipo = AnticipoCliente::factory()->create([
            'obra_id' => $obra->id,
            'monto_total' => 50000.00,
        ]);

        $result = $this->service->calcularAmortizacion($anticipo, 100.00);

        $this->assertEquals(50000.00, $result['monto_a_amortizar']);
        $this->assertEquals(50000.00, $result['amortizacion_acumulada']);
        $this->assertEquals(0.00, $result['saldo_pendiente']);
    }

    public function test_handles_multiple_amortizations_accumulated(): void
    {
        $obra = Obra::factory()->create();
        $anticipo = AnticipoCliente::factory()->create([
            'obra_id' => $obra->id,
            'monto_total' => 100000.00,
        ]);

        AmortizacionAnticipo::factory()->create([
            'anticipo_id' => $anticipo->id,
            'monto_amortizado' => 20000.00,
            'avance_porcentaje' => 20.00,
        ]);

        $result = $this->service->calcularAmortizacion($anticipo, 30.00);

        $this->assertEquals(30000.00, $result['monto_a_amortizar']);
        $this->assertEquals(50000.00, $result['amortizacion_acumulada']);
        $this->assertEquals(50000.00, $result['saldo_pendiente']);
    }

    public function test_generates_accounting_entry_for_amortization(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $obra = Obra::factory()->create();
        $anticipo = AnticipoCliente::factory()->create([
            'obra_id' => $obra->id,
            'monto_total' => 80000.00,
        ]);

        $cuentaIngresos = PlanCuenta::factory()->create([
            'codigo' => '4.1.1.01',
            'grupo' => 'ingreso',
            'tipo' => 'acreedor',
        ]);
        $cuentaAnticipo = PlanCuenta::factory()->create([
            'codigo' => '2.1.1.02',
            'grupo' => 'pasivo',
            'tipo' => 'acreedor',
        ]);

        $amortizacion = AmortizacionAnticipo::factory()->create([
            'anticipo_id' => $anticipo->id,
            'monto_amortizado' => 25000.00,
            'avance_porcentaje' => 25.00,
            'fecha_amortizacion' => now()->toDateString(),
            'asiento_id' => null,
        ]);

        $asiento = $this->service->generarAsientoAmortizacion($amortizacion);

        $this->assertNotNull($asiento->id);
        $this->assertStringStartsWith('AMI-', $asiento->numero_asiento);
        $this->assertEquals($obra->id, $asiento->obra_id);
        $this->assertEquals('automatico', $asiento->tipo);
        $this->assertCount(2, $asiento->detalles);

        $debe = $asiento->detalles->firstWhere('debe', '25000.00');
        $this->assertNotNull($debe);
        $this->assertEquals($cuentaIngresos->id, $debe->cuenta_id);

        $haber = $asiento->detalles->firstWhere('haber', '25000.00');
        $this->assertNotNull($haber);
        $this->assertEquals($cuentaAnticipo->id, $haber->cuenta_id);

        $this->assertEquals($asiento->id, $amortizacion->fresh()->asiento_id);
    }
}
