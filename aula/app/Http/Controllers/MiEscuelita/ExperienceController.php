<?php

namespace App\Http\Controllers\MiEscuelita;

use App\Http\Controllers\Controller;
use App\Http\Controllers\MiEscuelita\Concerns\ResolvesCurrentChild;
use App\Models\Area;
use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ExperienceController extends Controller
{
    use ResolvesCurrentChild;

    /**
     * Fase 5: la vista monta un componente Livewire que solo muestra lo
     * publicado y del ambiente del niño actual (o avisos generales sin
     * ambiente), con el filtro por área reactivo en servidor.
     */
    public function index(Request $request): View
    {
        return view('mi-escuelita.experiencias-index', [
            'areas' => Area::query()->where('active', true)->orderBy('order')->get(['id', 'name', 'icon']),
            'filtroArea' => $request->integer('area') ?: null,
        ]);
    }

    public function show(Content $content): View
    {
        Gate::authorize('view', $content);

        $child = $this->currentChild();

        $yaEnviada = $child
            ? $content->evidence()->where('child_id', $child->id)->exists()
            : false;

        return view('mi-escuelita.experiencias-show', [
            'content' => $content,
            'yaEnviada' => $yaEnviada,
        ]);
    }

    /**
     * Fase 7 — "Mis experiencias": el componente Livewire lista lo que
     * esta familia ya compartió, con el estado y la respuesta de la guía.
     */
    public function historial(): View
    {
        return view('mi-escuelita.mis-experiencias');
    }
}
