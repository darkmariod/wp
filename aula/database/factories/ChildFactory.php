<?php

namespace Database\Factories;

use App\Models\Family;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChildFactory extends Factory
{
    public function definition(): array
    {
        return [
            'family_id' => Family::factory(),
            'environment_id' => null,
            'name' => fake()->firstName(),
            'birth_date' => fake()->dateTimeBetween('-9 years', '-3 years'),
            'status' => 'active',
        ];
    }
}
