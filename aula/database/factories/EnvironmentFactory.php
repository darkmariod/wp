<?php

namespace Database\Factories;

use App\Models\Environment;
use Illuminate\Database\Eloquent\Factories\Factory;

class EnvironmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Inicial 1', 'Inicial 2', 'Primero de Básica', 'Segundo de Básica']),
            'age_range' => '3-6',
            'stage' => Environment::STAGE_ABSORBENTES,
            'teacher_id' => null,
            'active' => true,
            'order' => 0,
        ];
    }

    public function razonadoras(): static
    {
        return $this->state(fn () => [
            'stage' => Environment::STAGE_RAZONADORAS,
        ]);
    }
}
