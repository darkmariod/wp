<?php

namespace App\Observers;

use App\Models\ResourceFile;
use App\Services\Biblioteca\ResourceFileService;

/**
 * Cuando se borra una fila de resource_files directamente, el objeto
 * físico se retira del disco (si ya no existe, no pasa nada).
 */
class ResourceFileObserver
{
    public function __construct(private readonly ResourceFileService $archivos) {}

    public function deleted(ResourceFile $file): void
    {
        $this->archivos->deleteObjectAfterCommit($file->disk, $file->path);
    }
}
