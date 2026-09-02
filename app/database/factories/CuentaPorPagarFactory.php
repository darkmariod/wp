<?php

namespace Database\Factories;

use App\Models\CuentaPorPagar;
use App\Models\Obra;
use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CuentaPorPagar>
 */
class CuentaPorPagarFactory extends Factory
{
    protected $model = CuentaPorPagar::class;

    public function definition(): array
    {
        return [
            'obra_id' => Obra::factory(),
            'proveedor_id' => Proveedor::factory(),
            'tipo' => fake()->randomElement(['factura_compra', 'liquidacion_subcontrato', 'planilla_mano_obra']),
            'numero_comprobante' => fake()->bothify('FP-####'),
            'fecha_emision' => fake()->dateTimeBetween('-2 months', '-1 week'),
            'fecha_vencimiento' => fake()->dateTimeBetween('+1 day', '+30 days'),
            'monto_total' => fake()->randomFloat(2, 500, 50000),
            'monto_pagado' => 0,
            'estado' => 'pendiente',
        ];
    }
}
