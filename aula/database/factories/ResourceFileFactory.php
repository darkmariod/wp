<?php

namespace Database\Factories;

use App\Models\LibraryResource;
use App\Models\ResourceFile;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ResourceFile>
 */
class ResourceFileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'resource_id' => LibraryResource::factory(),
            'disk' => 'biblioteca',
            'path' => 'resources/'.Str::uuid().'.pdf',
            'original_name' => 'documento.pdf',
            'mime_type' => 'application/pdf',
            'size' => fake()->numberBetween(10_000, 5_000_000),
            'extension' => 'pdf',
        ];
    }
}
