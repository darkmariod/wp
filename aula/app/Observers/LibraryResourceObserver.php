<?php

namespace App\Observers;

use App\Models\LibraryResource;
use App\Services\Biblioteca\ResourceFileService;

/**
 * Al borrar un recurso la base de datos elimina sus filas de resource_files
 * en cascada, sin eventos de Eloquent: por eso se cargan antes de borrar y
 * sus objetos se retiran del disco cuando el borrado ya se confirmó.
 *
 * Laravel crea un observer nuevo por cada evento, así que lo cargado se
 * guarda en el propio modelo (relación `files`), no en el observer.
 */
class LibraryResourceObserver
{
    public function __construct(private readonly ResourceFileService $archivos) {}

    public function deleting(LibraryResource $resource): void
    {
        $resource->load('files');
    }

    public function deleted(LibraryResource $resource): void
    {
        foreach ($resource->files as $file) {
            $this->archivos->deleteObjectAfterCommit($file->disk, $file->path);
        }

        if ($resource->thumbnail) {
            $this->archivos->deleteObjectAfterCommit($this->archivos->diskName(), $resource->thumbnail);
        }
    }
}
