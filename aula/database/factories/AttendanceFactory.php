<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\Child;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'child_id' => Child::factory(),
            'date' => fake()->dateTimeBetween('-3 months', 'now'),
            'status' => fake()->randomElement([
                Attendance::STATUS_PRESENTE,
                Attendance::STATUS_ATRASO,
                Attendance::STATUS_FALTA_JUSTIFICADA,
                Attendance::STATUS_FALTA_INJUSTIFICADA,
            ]),
            'recorded_by' => User::factory(),
            'notes' => null,
        ];
    }
}
