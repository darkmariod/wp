<?php

namespace Database\Factories;

use App\Models\Child;
use App\Models\Content;
use App\Models\Evidence;
use App\Models\Family;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Evidence>
 */
class EvidenceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'content_id' => Content::factory(),
            'child_id' => Child::factory(),
            'family_id' => Family::factory(),
            'comment' => fake()->sentence(),
            'status' => Evidence::STATUS_SUBMITTED,
            'submitted_at' => now(),
            'reviewed_at' => null,
        ];
    }
}
