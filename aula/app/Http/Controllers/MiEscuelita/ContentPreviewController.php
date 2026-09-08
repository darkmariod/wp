<?php

namespace App\Http\Controllers\MiEscuelita;

use App\Http\Controllers\Controller;
use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Vista previa por enlace firmado (Fase 22).
 *
 * El staff genera un enlace firmado (via ContentResource) que abre la
 * vista REAL de la familia — la misma página Show — en una pestaña
 * nueva. La firma expira y solo puede generarla/pedirla alguien
 * autenticado del staff.
 */
class ContentPreviewController extends Controller
{
    public function show(Request $request, Content $content): View
    {
        Gate::authorize('view', $content);

        return view('mi-escuelita.experiencias-show', [
            'content' => $content,
            'yaEnviada' => false,
            'esPrevia' => true,
        ]);
    }
}
