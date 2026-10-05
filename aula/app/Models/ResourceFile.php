<?php

namespace App\Models;

use App\Observers\ResourceFileObserver;
use Database\Factories\ResourceFileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['resource_id', 'disk', 'path', 'original_name', 'mime_type', 'size', 'extension'])]
#[ObservedBy(ResourceFileObserver::class)]
class ResourceFile extends Model
{
    /** @use HasFactory<ResourceFileFactory> */
    use HasFactory;

    protected $table = 'resource_files';

    protected function casts(): array
    {
        return [
            'size' => 'integer',
        ];
    }

    public function resource(): BelongsTo
    {
        return $this->belongsTo(LibraryResource::class, 'resource_id');
    }
}
