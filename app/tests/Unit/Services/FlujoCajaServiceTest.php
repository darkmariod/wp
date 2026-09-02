<?php

namespace Tests\Unit\Services;

use App\Models\FlujoCaja;
use App\Models\Obra;
use App\Services\FlujoCajaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlujoCajaServiceTest extends TestCase
{
    use RefreshDatabase;

    private FlujoCajaService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new FlujoCajaService;
    }

    public function test_registers_income_and_calculates_balance(): void
    {
        $obra = Obra::factory()->create();

        $registro = $this->service->registrarIngreso($obra, 'anticipo_cliente', 50000.00, 'REF-001');

        $this->assertEquals('ingreso', $registro->tipo);
        $this->assertEquals(50000.00, (float) $registro->monto);

        $saldo = $this->service->saldoPorObra($obra);
        $this->assertEquals(50000.00, $saldo['ingresos']);
        $this->assertEquals(0.00, $saldo['egresos']);
        $this->assertEquals(50000.00, $saldo['resultado']);
    }

    public function test_registers_expense_and_calculates_balance(): void
    {
        $obra = Obra::factory()->create();

        $registro = $this->service->registrarEgreso($obra, 'compra_material', 15000.00, 'REF-002');

        $this->assertEquals('egreso', $registro->tipo);
        $this->assertEquals(15000.00, (float) $registro->monto);

        $saldo = $this->service->saldoPorObra($obra);
        $this->assertEquals(0.00, $saldo['ingresos']);
        $this->assertEquals(15000.00, $saldo['egresos']);
        $this->assertEquals(-15000.00, $saldo['resultado']);
    }

    public function test_calculates_resultado_neto(): void
    {
        $obra = Obra::factory()->create();

        $this->service->registrarIngreso($obra, 'pago_cliente', 100000.00, 'REF-003');
        $this->service->registrarEgreso($obra, 'compra_material', 40000.00, 'REF-004');
        $this->service->registrarEgreso($obra, 'pago_mano_obra', 25000.00, 'REF-005');

        $resultado = $this->service->resultadoNeto($obra);

        $this->assertEquals(35000.00, $resultado);
    }

    public function test_handles_multiple_operations_on_same_obra(): void
    {
        $obra = Obra::factory()->create();

        $this->service->registrarIngreso($obra, 'anticipo_cliente', 30000.00, 'REF-A');
        $this->service->registrarIngreso($obra, 'pago_cliente', 20000.00, 'REF-B');
        $this->service->registrarEgreso($obra, 'compra_material', 10000.00, 'REF-C');
        $this->service->registrarEgreso($obra, 'pago_equipo', 5000.00, 'REF-D');

        $saldo = $this->service->saldoPorObra($obra);

        $this->assertEquals(50000.00, $saldo['ingresos']);
        $this->assertEquals(15000.00, $saldo['egresos']);
        $this->assertEquals(35000.00, $saldo['resultado']);
    }
}
