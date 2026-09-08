<?php

namespace Database\Factories;

use App\Models\Content;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ContentFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 999999),
            'type' => Content::TYPE_EXPERIENCE,
            'description' => fake()->paragraph(),
            'teacher_id' => User::factory()->guia(),
            'requires_evidence' => false,
            'status' => Content::STATUS_DRAFT,
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status' => Content::STATUS_PUBLISHED,
            'published_at' => now()->subDay(),
        ]);
    }

    public function requiresEvidence(): static
    {
        return $this->state(fn () => ['requires_evidence' => true]);
    }
}
