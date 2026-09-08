<?php

namespace Database\Factories;

use App\Models\Family;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => User::ROLE_FAMILIA,
            'active' => true,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function administrador(): static
    {
        return $this->state(fn () => ['role' => User::ROLE_ADMINISTRADOR, 'family_id' => null]);
    }

    public function coordinacion(): static
    {
        return $this->state(fn () => ['role' => User::ROLE_COORDINACION, 'family_id' => null]);
    }

    public function guia(): static
    {
        return $this->state(fn () => ['role' => User::ROLE_GUIA, 'family_id' => null]);
    }

    public function familia(?Family $family = null): static
    {
        return $this->state(fn () => [
            'role' => User::ROLE_FAMILIA,
            'family_id' => $family?->id ?? Family::factory(),
        ]);
    }
}
