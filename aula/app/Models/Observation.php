<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['child_id', 'content_id', 'teacher_id', 'observation'])]
class Observation extends Model
{
    /** @use HasFactory<ObservationFactory> */
    use HasFactory, SoftDeletes;

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }

    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }
}
