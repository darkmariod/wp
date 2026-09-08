<?php

namespace Database\Factories;

use App\Models\Child;
use App\Models\Content;
use App\Models\Observation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Observation>
 */
class ObservationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'child_id' => Child::factory(),
            'content_id' => Content::factory(),
            'teacher_id' => User::factory()->guia(),
            'observation' => fake()->paragraph(),
        ];
    }
}
