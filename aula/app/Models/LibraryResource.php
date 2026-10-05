<?php

namespace App\Models;

use App\Enums\ResourceStatus;
use App\Enums\ResourceType;
use App\Models\Concerns\GeneratesUniqueSlug;
use App\Observers\LibraryResourceObserver;
use Database\Factories\LibraryResourceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Recurso de la Biblioteca. Se llama LibraryResource (y no Resource) para
 * no chocar con la clase Resource de Filament; la tabla sigue siendo
 * `resources`, por eso toda llave foránea hacia ella se pasa explícita.
 */
#[Fillable([
    'title', 'slug', 'description', 'type', 'category_id', 'thumbnail', 'external_url',
    'status', 'is_downloadable', 'created_by', 'updated_by', 'published_by', 'published_at',
])]
#[ObservedBy(LibraryResourceObserver::class)]
class LibraryResource extends Model
{
    /** @use HasFactory<LibraryResourceFactory> */
    use GeneratesUniqueSlug, HasFactory;

    protected $table = 'resources';

    protected function casts(): array
    {
        return [
            'type' => ResourceType::class,
            'status' => ResourceStatus::class,
            'is_downloadable' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    protected function slugSourceColumn(): string
    {
        return 'title';
    }

    protected function slugFallback(): string
    {
        return 'recurso';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ResourceCategory::class, 'category_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(ResourceFile::class, 'resource_id')->orderBy('id');
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_resource', 'resource_id', 'course_id')
            ->withPivot(['module_id', 'sort_order'])
            ->withTimestamps();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(ResourcePermission::class, 'resource_id');
    }

    public function accessLogs(): HasMany
    {
        return $this->hasMany(ResourceAccessLog::class, 'resource_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ResourceStatus::Published);
    }

    public function scopeOfType(Builder $query, ResourceType $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * El archivo con el que se abre el recurso (el más antiguo), o null si
     * es un enlace o un video por URL.
     */
    public function primaryFile(): ?ResourceFile
    {
        return $this->files->first();
    }
}
