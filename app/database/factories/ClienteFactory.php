<?php

namespace Database\Factories;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cliente>
 */
class ClienteFactory extends Factory
{
    protected $model = Cliente::class;

    public function definition(): array
    {
        return [
            'razon_social' => fake()->company(),
            'ruc' => fake()->unique()->numerify('#############'),
            'tipo' => fake()->randomElement(['publico', 'privado']),
            'email' => fake()->safeEmail(),
            'telefono' => fake()->numerify('##########'),
            'direccion' => fake()->address(),
            'representa_legal' => fake()->name(),
        ];
    }
}
