<?php

namespace App\Models;

use Database\Factories\ResourcePermissionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Permiso explícito sobre un recurso, para un rol o para un usuario.
 */
#[Fillable(['resource_id', 'role', 'user_id', 'can_view', 'can_download'])]
class ResourcePermission extends Model
{
    /** @use HasFactory<ResourcePermissionFactory> */
    use HasFactory;

    protected $table = 'resource_permissions';

    protected function casts(): array
    {
        return [
            'can_view' => 'boolean',
            'can_download' => 'boolean',
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
