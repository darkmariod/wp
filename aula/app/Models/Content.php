<?php

namespace App\Models;

use App\Observers\ContentObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'title', 'slug', 'type', 'description', 'body',
    'environment_id', 'area_id', 'teacher_id',
    'cover_image', 'video_url',
    'requires_evidence', 'published_at', 'status',
])]
#[ObservedBy(ContentObserver::class)]
class Content extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'content';

    public const TYPE_EXPERIENCE = 'experience';

    public const TYPE_READING = 'reading';

    public const TYPE_VIDEO = 'video';

    public const TYPE_TASK = 'task';

    public const TYPE_DOCUMENT = 'document';

    public const TYPE_GALLERY = 'gallery';

    public const TYPE_ANNOUNCEMENT = 'announcement';

    public const STATUS_DRAFT = 'draft';

    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_ARCHIVED = 'archived';

    protected function casts(): array
    {
        return [
            'requires_evidence' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function environment(): BelongsTo
    {
        return $this->belongsTo(Environment::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable', 'mediable_type', 'mediable_id');
    }

    public function evidence(): HasMany
    {
        return $this->hasMany(Evidence::class);
    }

    public function observations(): HasMany
    {
        return $this->hasMany(Observation::class);
    }

    /**
     * Visible para familias: publicado y ya en fecha.
     */
    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED)
            ->where('published_at', '<=', now());
    }

    /**
     * Validación de negocio: en un ambiente "razonadoras" solo se
     * permiten lectura y tarea. Devuelve true si el tipo es válido para
     * el ambiente asignado (o si el contenido no tiene ambiente / el
     * ambiente no es razonadoras).
     */
    public function tipoPermitidoEnSuAmbiente(): bool
    {
        if ($this->type === null) {
            return true;
        }

        if ($this->environment?->stage !== Environment::STAGE_RAZONADORAS) {
            return true;
        }

        return in_array($this->type, [self::TYPE_READING, self::TYPE_TASK], true);
    }
}
