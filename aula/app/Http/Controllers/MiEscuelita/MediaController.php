<?php

namespace App\Http\Controllers\MiEscuelita;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaController extends Controller
{
    /**
     * Fase 10, no negociable: la ruta de storage de una evidencia
     * privada nunca se hace pública. Este es el ÚNICO camino para bajar
     * un archivo, y pasa por la misma Policy que protege al dueño del
     * Content o de la Evidence — no por conocer la ruta en disco.
     */
    public function show(Media $medium): StreamedResponse
    {
        $mediable = $medium->mediable;

        abort_unless($mediable && Auth::user()?->can('view', $mediable), 403);

        abort_unless(Storage::disk('local')->exists($medium->path), 404);

        return Storage::disk('local')->response(
            $medium->path,
            $medium->original_name,
        );
    }
}
