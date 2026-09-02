<?php

namespace Database\Factories;

use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Proveedor>
 */
class ProveedorFactory extends Factory
{
    protected $model = Proveedor::class;

    public function definition(): array
    {
        return [
            'razon_social' => fake()->company(),
            'ruc' => fake()->unique()->numerify('#############'),
            'tipo' => fake()->randomElement(['material', 'mano_obra', 'subcontrato', 'equipo']),
            'email' => fake()->safeEmail(),
            'telefono' => fake()->numerify('##########'),
            'direccion' => fake()->address(),
        ];
    }
}
