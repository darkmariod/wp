<?php

namespace App\Models;

use Database\Factories\ResourceAccessLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Registro inmutable de una consulta o descarga: solo tiene created_at.
 */
#[Fillable(['resource_id', 'user_id', 'action', 'created_at'])]
class ResourceAccessLog extends Model
{
    /** @use HasFactory<ResourceAccessLogFactory> */
    use HasFactory;

    protected $table = 'resource_access_logs';

    public $timestamps = false;

    public const ACTION_VIEWED = 'viewed';

    public const ACTION_DOWNLOADED = 'downloaded';

    protected static function booted(): void
    {
        // Sin timestamps automáticos, la fecha se completa acá al crear.
        static::creating(function (self $log): void {
            $log->created_at ??= now();
        });
    }

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function resource(): BelongsTo
    {
        return $this->belongsTo(LibraryResource::class, 'resource_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
