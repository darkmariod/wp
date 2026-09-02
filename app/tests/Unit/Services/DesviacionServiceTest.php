<?php

namespace Tests\Unit\Services;

use App\Services\DesviacionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DesviacionServiceTest extends TestCase
{
    use RefreshDatabase;

    private DesviacionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DesviacionService;
    }

    public function test_calculates_positive_deviation_sobrecosto(): void
    {
        $result = $this->service->calcularDesviacion(100000.00, 125000.00);

        $this->assertEquals(25000.00, $result['desviacion']);
        $this->assertEquals(25.00, $result['porcentaje']);
        $this->assertEquals('sobrecosto', $result['tipo']);
    }

    public function test_calculates_negative_deviation_subconsumo(): void
    {
        $result = $this->service->calcularDesviacion(100000.00, 80000.00);

        $this->assertEquals(-20000.00, $result['desviacion']);
        $this->assertEquals(-20.00, $result['porcentaje']);
        $this->assertEquals('subconsumo', $result['tipo']);
    }

    public function test_calculates_zero_deviation_exacto(): void
    {
        $result = $this->service->calcularDesviacion(100000.00, 100000.00);

        $this->assertEquals(0.00, $result['desviacion']);
        $this->assertEquals(0.00, $result['porcentaje']);
        $this->assertEquals('exacto', $result['tipo']);
    }

    public function test_handles_division_by_zero_presupuestado_zero(): void
    {
        $result = $this->service->calcularDesviacion(0.00, 5000.00);

        $this->assertEquals(5000.00, $result['desviacion']);
        $this->assertEquals(0.0, $result['porcentaje']);
        $this->assertEquals('sobrecosto', $result['tipo']);
    }

    public function test_handles_both_zero_values(): void
    {
        $result = $this->service->calcularDesviacion(0.00, 0.00);

        $this->assertEquals(0.00, $result['desviacion']);
        $this->assertEquals(0.0, $result['porcentaje']);
        $this->assertEquals('exacto', $result['tipo']);
    }
}
