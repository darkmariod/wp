<?php

namespace Database\Factories;

use App\Models\Trabajador;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Trabajador>
 */
class TrabajadorFactory extends Factory
{
    protected $model = Trabajador::class;

    public function definition(): array
    {
        return [
            'cedula' => fake()->unique()->numerify('##########'),
            'nombres' => fake()->firstName(),
            'apellidos' => fake()->lastName(),
            'cargo' => fake()->jobTitle(),
            'sueldo_base' => fake()->randomFloat(2, 400, 3000),
            'tipo_contrato' => fake()->randomElement(['indefinido', 'obra_determinada', 'servicio']),
            'activo' => true,
        ];
    }
}
