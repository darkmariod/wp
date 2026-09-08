<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AreaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Vida Práctica', 'Lenguaje', 'Matemáticas', 'Cultura y Naturaleza', 'Arte y Expresión']),
            'order' => 0,
            'active' => true,
        ];
    }
}
