<?php

namespace Tests\Unit\Services;

use App\Models\DetalleAPU;
use App\Models\Obra;
use App\Models\Presupuesto;
use App\Services\AIUService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AIUServiceTest extends TestCase
{
    use RefreshDatabase;

    private AIUService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AIUService;
    }

    public function test_calculates_aiu_with_default_percentages(): void
    {
        $obra = Obra::factory()->create([
            'aiu_administracion' => 10.00,
            'aiu_imprevistos' => 5.00,
            'aiu_utilidad' => 10.00,
        ]);

        $result = $this->service->calcularAIU($obra, 100000.00);

        $this->assertEquals(10000.00, $result['administracion']);
        $this->assertEquals(5000.00, $result['imprevistos']);
        $this->assertEquals(10000.00, $result['utilidad']);
        $this->assertEquals(25000.00, $result['total_aiu']);
        $this->assertEquals(125000.00, $result['precio_venta']);
    }

    public function test_calculates_aiu_with_custom_percentages(): void
    {
        $obra = Obra::factory()->create([
            'aiu_administracion' => 8.50,
            'aiu_imprevistos' => 3.20,
            'aiu_utilidad' => 12.00,
        ]);

        $result = $this->service->calcularAIU($obra, 200000.00);

        $this->assertEquals(17000.00, $result['administracion']);
        $this->assertEquals(6400.00, $result['imprevistos']);
        $this->assertEquals(24000.00, $result['utilidad']);
        $this->assertEquals(47400.00, $result['total_aiu']);
        $this->assertEquals(247400.00, $result['precio_venta']);
    }

    public function test_recalculates_presupuesto_subtotals_from_detalles(): void
    {
        $obra = Obra::factory()->create();
        $presupuesto = Presupuesto::factory()->create([
            'obra_id' => $obra->id,
            'cantidad' => 10,
            'precio_venta_unitario' => 50.00,
            'subtotal_costo' => 0,
            'subtotal_venta' => 0,
        ]);

        DetalleAPU::factory()->create([
            'presupuesto_id' => $presupuesto->id,
            'costo_total' => 200.00,
        ]);
        DetalleAPU::factory()->create([
            'presupuesto_id' => $presupuesto->id,
            'costo_total' => 350.00,
        ]);

        $result = $this->service->recalcularPresupuesto($presupuesto);

        $this->assertEquals(550.00, (float) $result->subtotal_costo);
        $this->assertEquals(500.00, (float) $result->subtotal_venta);
    }

    public function test_handles_zero_costo_directo(): void
    {
        $obra = Obra::factory()->create([
            'aiu_administracion' => 10.00,
            'aiu_imprevistos' => 5.00,
            'aiu_utilidad' => 10.00,
        ]);

        $result = $this->service->calcularAIU($obra, 0.00);

        $this->assertEquals(0.00, $result['administracion']);
        $this->assertEquals(0.00, $result['imprevistos']);
        $this->assertEquals(0.00, $result['utilidad']);
        $this->assertEquals(0.00, $result['total_aiu']);
        $this->assertEquals(0.00, $result['precio_venta']);
    }
}
