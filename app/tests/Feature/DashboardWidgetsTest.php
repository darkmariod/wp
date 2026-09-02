<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\CuentaPorCobrar;
use App\Models\CuentaPorPagar;
use App\Models\FlujoCaja;
use App\Models\Obra;
use App\Models\PlanCuenta;
use App\Models\Proveedor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class DashboardWidgetsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        Auth::login($this->user);
        $this->session(['_auth_password_timeout_' . Auth::getDefaultDriver() => now()->addMinutes(120)->getTimestamp()]);
    }

    public function test_dashboard_with_empty_database_loads(): void
    {
        $response = $this->get('/admin');

        $response->assertStatus(200);
    }

    public function test_dashboard_with_data_loads(): void
    {
        $cliente = Cliente::create([
            'razon_social' => 'Cliente Test',
            'ruc' => '1799999999999',
            'tipo' => 'privado',
        ]);

        $proveedor = Proveedor::create([
            'razon_social' => 'Proveedor Test',
            'ruc' => '1788888888888',
            'tipo' => 'material',
        ]);

        $obra = Obra::create([
            'codigo' => 'OBR-001',
            'nombre' => 'Obra Test',
            'cliente_id' => $cliente->id,
            'estado' => 'en_curse',
            'contrato_monto' => 100000,
            'fecha_inicio' => now()->subMonth(),
            'fecha_fin_estimada' => now()->addMonths(6),
            'anticipo_porcentaje' => 10,
            'aiu_administracion' => 5,
            'aiu_imprevistos' => 10,
            'aiu_utilidad' => 10,
            'costo_fijo_mensual' => 0,
        ]);

        FlujoCaja::create([
            'obra_id' => $obra->id,
            'fecha' => now(),
            'tipo' => 'ingreso',
            'categoria' => 'anticipo_cliente',
            'monto' => 50000,
            'descripcion' => 'Anticipo cliente',
        ]);

        FlujoCaja::create([
            'obra_id' => $obra->id,
            'fecha' => now(),
            'tipo' => 'egreso',
            'categoria' => 'compra_material',
            'monto' => 20000,
            'descripcion' => 'Materiales',
        ]);

        CuentaPorCobrar::create([
            'obra_id' => $obra->id,
            'cliente_id' => $cliente->id,
            'tipo' => 'factura',
            'numero_comprobante' => '001-001-00001',
            'fecha_emision' => now(),
            'fecha_vencimiento' => now()->addDays(3),
            'monto_total' => 30000,
            'monto_cobrado' => 0,
            'estado' => 'pendiente',
        ]);

        CuentaPorPagar::create([
            'obra_id' => $obra->id,
            'proveedor_id' => $proveedor->id,
            'tipo' => 'factura_compra',
            'numero_comprobante' => '001-001-00002',
            'fecha_emision' => now(),
            'fecha_vencimiento' => now()->addDays(5),
            'monto_total' => 15000,
            'monto_pagado' => 0,
            'estado' => 'pendiente',
        ]);

        $response = $this->get('/admin');

        $response->assertStatus(200);
    }

    public function test_cuentas_por_vencer_widget_union_query_works(): void
    {
        $cliente = Cliente::create([
            'razon_social' => 'Cliente Union',
            'ruc' => '1711111111111',
            'tipo' => 'privado',
        ]);
        $proveedor = Proveedor::create([
            'razon_social' => 'Proveedor Union',
            'ruc' => '1722222222222',
            'tipo' => 'material',
        ]);
        $obra = Obra::create([
            'codigo' => 'OBR-U',
            'nombre' => 'Obra Union',
            'cliente_id' => $cliente->id,
            'estado' => 'en_curse',
            'contrato_monto' => 50000,
            'fecha_inicio' => now(),
            'fecha_fin_estimada' => now()->addMonths(3),
            'anticipo_porcentaje' => 10,
            'aiu_administracion' => 5,
            'aiu_imprevistos' => 10,
            'aiu_utilidad' => 10,
            'costo_fijo_mensual' => 0,
        ]);

        CuentaPorCobrar::create([
            'obra_id' => $obra->id,
            'cliente_id' => $cliente->id,
            'tipo' => 'factura',
            'numero_comprobante' => '001-001-00003',
            'fecha_emision' => now(),
            'fecha_vencimiento' => now()->addDays(2),
            'monto_total' => 10000,
            'monto_cobrado' => 0,
            'estado' => 'pendiente',
        ]);

        CuentaPorPagar::create([
            'obra_id' => $obra->id,
            'proveedor_id' => $proveedor->id,
            'tipo' => 'factura_compra',
            'numero_comprobante' => '001-001-00004',
            'fecha_emision' => now(),
            'fecha_vencimiento' => now()->addDays(4),
            'monto_total' => 5000,
            'monto_pagado' => 0,
            'estado' => 'pendiente',
        ]);

        $response = $this->get('/admin');

        $response->assertStatus(200);
    }

    public function test_obras_activas_widget_query_works(): void
    {
        $cliente = Cliente::create([
            'razon_social' => 'Cliente Obras',
            'ruc' => '1733333333333',
            'tipo' => 'privado',
        ]);

        Obra::create([
            'codigo' => 'OBR-ACT',
            'nombre' => 'Obra Activa',
            'cliente_id' => $cliente->id,
            'estado' => 'en_curse',
            'contrato_monto' => 200000,
            'fecha_inicio' => now()->subMonth(),
            'fecha_fin_estimada' => now()->addMonths(6),
            'anticipo_porcentaje' => 10,
            'aiu_administracion' => 5,
            'aiu_imprevistos' => 10,
            'aiu_utilidad' => 10,
            'costo_fijo_mensual' => 0,
        ]);

        Obra::create([
            'codigo' => 'OBR-TERM',
            'nombre' => 'Obra Terminada',
            'cliente_id' => $cliente->id,
            'estado' => 'culminada',
            'contrato_monto' => 150000,
            'fecha_inicio' => now()->subYear(),
            'fecha_fin_estimada' => now()->subMonth(),
            'anticipo_porcentaje' => 10,
            'aiu_administracion' => 5,
            'aiu_imprevistos' => 10,
            'aiu_utilidad' => 10,
            'costo_fijo_mensual' => 0,
        ]);

        $response = $this->get('/admin');

        $response->assertStatus(200);
    }
}
