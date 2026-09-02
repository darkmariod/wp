<?php

namespace Tests\Unit\Services;

use App\Models\Obra;
use App\Services\PuntoEquilibrioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PuntoEquilibrioServiceTest extends TestCase
{
    use RefreshDatabase;

    private PuntoEquilibrioService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PuntoEquilibrioService;
    }

    public function test_calculates_break_even_units(): void
    {
        $obra = Obra::factory()->create();

        $result = $this->service->calcularPuntoEquilibrio($obra, 10000.00, 30.00, 50.00);

        $this->assertEquals(500.00, $result['unidades']);
        $this->assertEquals(25000.00, $result['monto']);
        $this->assertEquals(20.00, $result['margen_contribucion']);
    }

    public function test_calculates_break_even_amount(): void
    {
        $obra = Obra::factory()->create();

        $result = $this->service->calcularPuntoEquilibrio($obra, 25000.00, 45.00, 100.00);

        $this->assertEquals(454.54, $result['unidades']);
        $this->assertEquals(45454.00, $result['monto']);
        $this->assertEquals(55.00, $result['margen_contribucion']);
    }

    public function test_calculates_margin_of_contribution(): void
    {
        $obra = Obra::factory()->create();

        $result = $this->service->calcularPuntoEquilibrio($obra, 5000.00, 20.00, 80.00);

        $this->assertEquals(60.00, $result['margen_contribucion']);
        $this->assertEquals(83.33, $result['unidades']);
        $this->assertEquals(6666.40, $result['monto']);
    }

    public function test_handles_zero_margin_returns_zeroes(): void
    {
        $obra = Obra::factory()->create();

        $result = $this->service->calcularPuntoEquilibrio($obra, 10000.00, 50.00, 50.00);

        $this->assertEquals(0.0, $result['unidades']);
        $this->assertEquals(0.0, $result['monto']);
        $this->assertEquals(0.0, $result['margen_contribucion']);
    }

    public function test_handles_negative_margin_returns_zeroes(): void
    {
        $obra = Obra::factory()->create();

        $result = $this->service->calcularPuntoEquilibrio($obra, 10000.00, 70.00, 50.00);

        $this->assertEquals(0.0, $result['unidades']);
        $this->assertEquals(0.0, $result['monto']);
        $this->assertLessThan(0.0, $result['margen_contribucion']);
    }
}
