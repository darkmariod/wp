<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement([
                'Gastronomía Profesional', 'Bartender Profesional', 'Panadería', 'Pastelería',
            ]),
            'description' => fake()->paragraph(),
            'teacher_id' => null,
            'is_active' => true,
        ];
    }

    public function taughtBy(User $teacher): static
    {
        return $this->state(fn () => ['teacher_id' => $teacher->id]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
