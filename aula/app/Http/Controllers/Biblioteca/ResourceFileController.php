<?php

namespace App\Http\Controllers\Biblioteca;

use App\Http\Controllers\Controller;
use App\Models\LibraryResource;
use App\Services\Biblioteca\ResourceAccessLogger;
use App\Services\Biblioteca\ResourceDelivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

/**
 * Única puerta a los archivos de la Biblioteca. Conocer la URL no basta: se
 * exige poder ver el recurso (si no, 404, para no revelar que existe) y,
 * para descargar, el permiso de descarga (403).
 */
class ResourceFileController extends Controller
{
    public function __construct(
        private readonly ResourceDelivery $delivery,
        private readonly ResourceAccessLogger $logger,
    ) {}

    /**
     * Entrega en línea para los visores (PDF.js, video, audio, imagen). No
     * anota nada: ver se anota en la página del recurso y los visores piden
     * muchos rangos por archivo.
     */
    public function inline(Request $request, LibraryResource $resource): Response
    {
        $this->ensureVisible($request, $resource);

        $file = $resource->primaryFile();

        abort_if($file === null || ! $resource->type->isViewableInline(), 404);

        return $this->delivery->inline($file, $request);
    }

    public function download(Request $request, LibraryResource $resource): Response
    {
        $this->ensureVisible($request, $resource);

        // Un enlace o un video por URL no tiene archivo: 404, no 403.
        $file = $resource->primaryFile();

        abort_if($file === null || ! $resource->type->usesFile(), 404);

        Gate::authorize('download', $resource);

        $response = $this->delivery->download($file, $request);

        // Solo se anota si el archivo realmente se entrega.
        $this->logger->downloaded($resource, $request->user());

        return $response;
    }

    private function ensureVisible(Request $request, LibraryResource $resource): void
    {
        abort_unless($request->user()?->can('view', $resource), 404);
    }
}
