<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['content_id', 'child_id', 'family_id', 'comment', 'status', 'submitted_at', 'reviewed_at'])]
class Evidence extends Model
{
    /** @use HasFactory<EvidenceFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'evidence';

    public const STATUS_PENDING = 'pending';

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_VIEWED = 'viewed';

    public const STATUS_RESPONDED = 'responded';

    public const STATUS_ARCHIVED = 'archived';

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable', 'mediable_type', 'mediable_id');
    }
}
