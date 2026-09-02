<?php

namespace Database\Factories;

use App\Models\Cliente;
use App\Models\CuentaPorCobrar;
use App\Models\Obra;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CuentaPorCobrar>
 */
class CuentaPorCobrarFactory extends Factory
{
    protected $model = CuentaPorCobrar::class;

    public function definition(): array
    {
        return [
            'obra_id' => Obra::factory(),
            'cliente_id' => Cliente::factory(),
            'tipo' => fake()->randomElement(['factura', 'nota_venta', 'anticipos']),
            'numero_comprobante' => fake()->bothify('FC-####'),
            'fecha_emision' => fake()->dateTimeBetween('-2 months', '-1 week'),
            'fecha_vencimiento' => fake()->dateTimeBetween('+1 day', '+30 days'),
            'monto_total' => fake()->randomFloat(2, 500, 50000),
            'monto_cobrado' => 0,
            'estado' => 'pendiente',
        ];
    }
}
