<?php

namespace Database\Factories;

use App\Models\Obra;
use App\Models\Presupuesto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Presupuesto>
 */
class PresupuestoFactory extends Factory
{
    protected $model = Presupuesto::class;

    public function definition(): array
    {
        return [
            'obra_id' => Obra::factory(),
            'codigo' => fake()->unique()->bothify('PRE-####'),
            'descripcion' => fake()->sentence(4),
            'unidad_medida' => fake()->randomElement(['m', 'm2', 'm3', 'kg', 'un', 'lt', 'gl']),
            'cantidad' => fake()->randomFloat(4, 1, 1000),
            'costo_unitario' => fake()->randomFloat(6, 1, 500),
            'precio_venta_unitario' => fake()->randomFloat(6, 1, 700),
            'subtotal_costo' => 0,
            'subtotal_venta' => 0,
        ];
    }
}
