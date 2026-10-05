<?php

namespace Database\Factories;

use App\Models\ResourceCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ResourceCategory>
 */
class ResourceCategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement([
                'Recetas', 'Técnicas de cocina', 'Seguridad e higiene', 'Pastelería',
                'Panadería', 'Coctelería', 'Servicio y atención', 'Videos de apoyo',
            ]),
            'description' => fake()->sentence(),
            'sort_order' => 0,
            'show_in_tabs' => false,
            'is_active' => true,
        ];
    }

    public function inTabs(): static
    {
        return $this->state(fn () => ['show_in_tabs' => true]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
