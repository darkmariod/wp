<?php

namespace Database\Factories;

use App\Models\LibraryResource;
use App\Models\ResourcePermission;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ResourcePermission>
 */
class ResourcePermissionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'resource_id' => LibraryResource::factory(),
            'role' => User::ROLE_ESTUDIANTE,
            'user_id' => null,
            'can_view' => true,
            'can_download' => false,
        ];
    }

    public function forRole(string $role): static
    {
        return $this->state(fn () => ['role' => $role, 'user_id' => null]);
    }

    public function forUser(User $user): static
    {
        return $this->state(fn () => ['role' => null, 'user_id' => $user->id]);
    }

    public function canDownload(): static
    {
        return $this->state(fn () => ['can_download' => true]);
    }
}
