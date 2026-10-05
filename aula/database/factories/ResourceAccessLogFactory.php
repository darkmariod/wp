<?php

namespace Database\Factories;

use App\Models\LibraryResource;
use App\Models\ResourceAccessLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ResourceAccessLog>
 */
class ResourceAccessLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'resource_id' => LibraryResource::factory(),
            'user_id' => User::factory(),
            'action' => ResourceAccessLog::ACTION_VIEWED,
        ];
    }

    public function viewed(): static
    {
        return $this->state(fn () => ['action' => ResourceAccessLog::ACTION_VIEWED]);
    }

    public function downloaded(): static
    {
        return $this->state(fn () => ['action' => ResourceAccessLog::ACTION_DOWNLOADED]);
    }
}
