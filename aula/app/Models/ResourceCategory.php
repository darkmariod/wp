<?php

namespace App\Models;

use App\Models\Concerns\GeneratesUniqueSlug;
use Database\Factories\ResourceCategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'description', 'sort_order', 'show_in_tabs', 'is_active'])]
class ResourceCategory extends Model
{
    /** @use HasFactory<ResourceCategoryFactory> */
    use GeneratesUniqueSlug, HasFactory;

    protected $table = 'resource_categories';

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'show_in_tabs' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected function slugSourceColumn(): string
    {
        return 'name';
    }

    protected function slugFallback(): string
    {
        return 'categoria';
    }

    public function resources(): HasMany
    {
        return $this->hasMany(LibraryResource::class, 'category_id');
    }
}
