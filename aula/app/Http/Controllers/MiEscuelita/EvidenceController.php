<?php

namespace App\Http\Controllers\MiEscuelita;

use App\Http\Controllers\Controller;
use App\Http\Requests\MiEscuelita\StoreEvidenceRequest;
use App\Models\Child;
use App\Models\Content;
use App\Models\Evidence;
use App\Models\Media;
use App\Notifications\EvidenceSubmittedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class EvidenceController extends Controller
{
    /**
     * Fase 6 — el paso más delicado de todo el sistema. La validación
     * de "¿es tu hijo? ¿la experiencia pide evidencia? ¿está publicada?"
     * vive en EvidencePolicy::create, NO en si el botón se mostró o no
     * en React — ese botón se puede evitar armando el POST a mano.
     */
    public function store(StoreEvidenceRequest $request, Content $content): RedirectResponse
    {
        $child = Child::findOrFail($request->validated('child_id'));

        Gate::authorize('create', [Evidence::class, $content, $child]);

        $evidence = DB::transaction(function () use ($request, $content, $child) {
            $evidence = Evidence::create([
                'content_id' => $content->id,
                'child_id' => $child->id,
                'family_id' => Auth::user()->family_id,
                'comment' => $request->validated('comment'),
                'status' => Evidence::STATUS_SUBMITTED,
                'submitted_at' => now(),
            ]);

            foreach ($request->file('photos', []) as $photo) {
                $this->attachMedia($evidence, $photo, Media::TYPE_IMAGE);
            }

            if ($request->hasFile('video')) {
                $this->attachMedia($evidence, $request->file('video'), Media::TYPE_VIDEO);
            }

            if ($request->hasFile('document')) {
                $ext = strtolower($request->file('document')->getClientOriginalExtension());
                $type = $ext === 'pdf' ? Media::TYPE_PDF : Media::TYPE_DOCUMENT;
                $this->attachMedia($evidence, $request->file('document'), $type);
            }

            return $evidence;
        });

        $content->teacher->notify(new EvidenceSubmittedNotification($evidence));

        return redirect()
            ->route('mi-escuelita.experiencias.show', $content->slug)
            ->with('success', 'Experiencia compartida. Gracias por compartir este momento con nosotros.');
    }

    /**
     * Guarda el archivo en el disco PRIVADO (storage/app/private, sin
     * symlink público) y registra el Media apuntando a esa ruta — nunca
     * a algo servible directo por URL.
     */
    private function attachMedia(Evidence $evidence, $file, string $type): void
    {
        $path = $file->store('evidence/'.$evidence->id, 'local');

        $evidence->media()->create([
            'type' => $type,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);
    }
}
