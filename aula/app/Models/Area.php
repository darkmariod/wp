<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'icon', 'image', 'description', 'order', 'active', 'is_required'])]
class Area extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'is_required' => 'boolean',
        ];
    }

    public function content(): HasMany
    {
        return $this->hasMany(Content::class);
    }
}
