<?php

namespace Tests\Unit\Services;

use App\Models\CuentaPorCobrar;
use App\Models\CuentaPorPagar;
use App\Models\Obra;
use App\Services\VencimientoService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VencimientoServiceTest extends TestCase
{
    use RefreshDatabase;

    private VencimientoService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new VencimientoService;
    }

    public function test_identifies_accounts_receivable_past_due(): void
    {
        $obra = Obra::factory()->create();
        CuentaPorCobrar::factory()->create([
            'obra_id' => $obra->id,
            'fecha_vencimiento' => Carbon::now()->subDays(5),
            'estado' => 'pendiente',
        ]);

        $result = $this->service->verificarVencimientos();

        $this->assertCount(1, $result['vencidos_cobrar']);
    }

    public function test_identifies_accounts_payable_due_in_7_days(): void
    {
        $obra = Obra::factory()->create();
        CuentaPorPagar::factory()->create([
            'obra_id' => $obra->id,
            'fecha_vencimiento' => Carbon::now()->addDays(3),
            'estado' => 'pendiente',
        ]);

        $result = $this->service->verificarVencimientos();

        $this->assertCount(1, $result['por_vencer_pagar']);
    }

    public function test_auto_updates_status_to_vencida_when_past_due(): void
    {
        $obra = Obra::factory()->create();
        $cuenta = CuentaPorCobrar::factory()->create([
            'obra_id' => $obra->id,
            'fecha_vencimiento' => Carbon::now()->subDays(10),
            'estado' => 'pendiente',
        ]);

        $this->service->verificarVencimientos();

        $cuenta->refresh();
        $this->assertEquals('vencida', $cuenta->estado);
    }

    public function test_does_not_update_already_cobrada_accounts(): void
    {
        $obra = Obra::factory()->create();
        $cuenta = CuentaPorCobrar::factory()->create([
            'obra_id' => $obra->id,
            'fecha_vencimiento' => Carbon::now()->subDays(5),
            'estado' => 'cobrada',
        ]);

        $this->service->verificarVencimientos();

        $cuenta->refresh();
        $this->assertEquals('cobrada', $cuenta->estado);
    }

    public function test_groups_by_urgency_7_3_1_day_alerts(): void
    {
        $obra = Obra::factory()->create();

        CuentaPorCobrar::factory()->create([
            'obra_id' => $obra->id,
            'fecha_vencimiento' => Carbon::now()->addDay(),
            'estado' => 'pendiente',
        ]);
        CuentaPorCobrar::factory()->create([
            'obra_id' => $obra->id,
            'fecha_vencimiento' => Carbon::now()->addDays(3),
            'estado' => 'pendiente',
        ]);
        CuentaPorCobrar::factory()->create([
            'obra_id' => $obra->id,
            'fecha_vencimiento' => Carbon::now()->addDays(7),
            'estado' => 'pendiente',
        ]);
        CuentaPorCobrar::factory()->create([
            'obra_id' => $obra->id,
            'fecha_vencimiento' => Carbon::now()->addDays(10),
            'estado' => 'pendiente',
        ]);

        $result = $this->service->verificarVencimientos();

        $this->assertCount(3, $result['por_vencer_cobrar']);
    }
}
