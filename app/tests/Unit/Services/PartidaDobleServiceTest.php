<?php

namespace Tests\Unit\Services;

use App\Exceptions\LineaInvalidaException;
use App\Exceptions\PartidaDobleException;
use App\Models\PlanCuenta;
use App\Models\User;
use App\Services\PartidaDobleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartidaDobleServiceTest extends TestCase
{
    use RefreshDatabase;

    private PartidaDobleService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PartidaDobleService;
    }

    public function test_validate_debit_credit_balance_with_balanced_lines(): void
    {
        $lines = [
            ['cuenta_id' => 1, 'debe' => '100.0000', 'haber' => '0'],
            ['cuenta_id' => 2, 'debe' => '0', 'haber' => '100.0000'],
        ];

        $this->service->validateDebitCreditBalance($lines);

        $this->assertTrue(true);
    }

    public function test_throws_exception_when_debit_not_equal_credit(): void
    {
        $lines = [
            ['cuenta_id' => 1, 'debe' => '100.0000', 'haber' => '0'],
            ['cuenta_id' => 2, 'debe' => '0', 'haber' => '80.0000'],
        ];

        $this->expectException(PartidaDobleException::class);
        $this->expectExceptionMessage('DEBE');

        $this->service->validateDebitCreditBalance($lines);
    }

    public function test_throws_exception_when_line_has_both_debit_and_credit(): void
    {
        $lines = [
            ['cuenta_id' => 1, 'debe' => '50.0000', 'haber' => '50.0000'],
        ];

        $this->expectException(LineaInvalidaException::class);
        $this->expectExceptionMessage('Línea #1');

        $this->service->validateLineIntegrity($lines);
    }

    public function test_throws_exception_when_line_has_zero_debit_and_credit(): void
    {
        $lines = [
            ['cuenta_id' => 1, 'debe' => '0', 'haber' => '0'],
        ];

        $this->expectException(LineaInvalidaException::class);
        $this->expectExceptionMessage('Línea #1');

        $this->service->validateLineIntegrity($lines);
    }

    public function test_creates_asiento_contable_with_balanced_lines(): void
    {
        $user = User::factory()->create();
        $cuentaDebe = PlanCuenta::factory()->create(['grupo' => 'activo', 'tipo' => 'deudor']);
        $cuentaHaber = PlanCuenta::factory()->create(['grupo' => 'pasivo', 'tipo' => 'acreedor']);

        $data = [
            'descripcion' => 'Compra de material',
            'fecha' => '2026-08-15',
            'tipo' => 'manual',
            'usuario_creacion' => $user->id,
        ];

        $lines = [
            ['cuenta_id' => $cuentaDebe->id, 'debe' => '500.0000', 'haber' => '0', 'referencia' => 'Ref compra'],
            ['cuenta_id' => $cuentaHaber->id, 'debe' => '0', 'haber' => '500.0000', 'referencia' => 'Ref compra'],
        ];

        $asiento = $this->service->createAsiento($data, $lines);

        $this->assertNotNull($asiento->id);
        $this->assertEquals('Compra de material', $asiento->descripcion);
        $this->assertEquals('manual', $asiento->tipo);
        $this->assertEquals('borrador', $asiento->estado);
        $this->assertCount(2, $asiento->detalles);
        $this->assertStringStartsWith('ASI-', $asiento->numero_asiento);
    }

    public function test_create_asiento_throws_on_unbalanced(): void
    {
        $user = User::factory()->create();
        $cuenta = PlanCuenta::factory()->create();

        $data = [
            'descripcion' => 'Asiento desbalanceado',
            'usuario_creacion' => $user->id,
        ];

        $lines = [
            ['cuenta_id' => $cuenta->id, 'debe' => '100.0000', 'haber' => '0'],
            ['cuenta_id' => $cuenta->id, 'debe' => '0', 'haber' => '50.0000'],
        ];

        $this->expectException(PartidaDobleException::class);

        $this->service->createAsiento($data, $lines);
    }
}
