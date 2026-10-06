<?php

namespace Database\Factories;

use App\Enums\ResourceStatus;
use App\Enums\ResourceType;
use App\Models\LibraryResource;
use App\Models\ResourceCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LibraryResource>
 */
class LibraryResourceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->randomElement([
                'Manual de cocina básica', 'Guía de higiene en la cocina', 'Receta de pan de molde',
                'Técnicas de corte', 'Introducción a la coctelería', 'Presentación de emplatado',
            ]),
            'description' => fake()->paragraph(),
            'type' => ResourceType::Pdf,
            'category_id' => ResourceCategory::factory(),
            'external_url' => null,
            'status' => ResourceStatus::Draft,
            'is_downloadable' => false,
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status' => ResourceStatus::Published,
            'published_at' => now()->subDay(),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'status' => ResourceStatus::Draft,
            'published_at' => null,
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn () => ['status' => ResourceStatus::Archived]);
    }

    public function ofType(ResourceType $type): static
    {
        return $this->state(fn () => [
            'type' => $type,
            // Enlaces y videos por URL necesitan una dirección para ser válidos.
            'external_url' => $type->usesExternalUrl() ? fake()->url() : null,
        ]);
    }

    public function downloadable(): static
    {
        return $this->state(fn () => ['is_downloadable' => true]);
    }

    public function createdBy(User $user): static
    {
        return $this->state(fn () => ['created_by' => $user->id]);
    }
}
