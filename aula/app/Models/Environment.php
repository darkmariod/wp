<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'age_range', 'stage', 'teacher_id', 'description', 'image', 'active', 'order'])]
class Environment extends Model
{
    use HasFactory;

    public const STAGE_ABSORBENTES = 'absorbentes';

    public const STAGE_RAZONADORAS = 'razonadoras';

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Child::class);
    }

    public function content(): HasMany
    {
        return $this->hasMany(Content::class);
    }
}
