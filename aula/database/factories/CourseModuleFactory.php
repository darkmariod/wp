<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\CourseModule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CourseModule>
 */
class CourseModuleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'name' => fake()->randomElement([
                'Módulo 1: Introducción', 'Módulo 2: Técnicas básicas',
                'Módulo 3: Práctica guiada', 'Módulo 4: Evaluación',
            ]),
            'description' => fake()->sentence(),
            'sort_order' => 0,
        ];
    }
}
