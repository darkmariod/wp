<?php

namespace Database\Factories;

use App\Models\FlujoCaja;
use App\Models\Obra;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FlujoCaja>
 */
class FlujoCajaFactory extends Factory
{
    protected $model = FlujoCaja::class;

    public function definition(): array
    {
        return [
            'obra_id' => Obra::factory(),
            'fecha' => fake()->dateTimeThisMonth(),
            'tipo' => fake()->randomElement(['ingreso', 'egreso']),
            'categoria' => fake()->randomElement([
                'anticipo_cliente',
                'pago_cliente',
                'compra_material',
                'pago_mano_obra',
                'pago_subcontrato',
                'pago_equipo',
                'gasto_administrativo',
                'otro',
            ]),
            'monto' => fake()->randomFloat(2, 100, 50000),
            'referencia' => fake()->optional()->bothify('REF-####'),
            'asiento_id' => null,
        ];
    }
}
