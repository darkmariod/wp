<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable(['type', 'path', 'original_name', 'mime_type', 'size', 'order'])]
class Media extends Model
{
    protected $table = 'media';

    public const TYPE_IMAGE = 'image';

    public const TYPE_VIDEO = 'video';

    public const TYPE_DOCUMENT = 'document';

    public const TYPE_PDF = 'pdf';

    public function mediable(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'mediable_type', 'mediable_id');
    }
}
