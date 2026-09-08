<?php

namespace App\Http\Controllers\MiEscuelita;

use App\Http\Controllers\Controller;
use App\Http\Controllers\MiEscuelita\Concerns\ResolvesCurrentChild;
use App\Models\Area;
use App\Models\Child;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HomeController extends Controller
{
    use ResolvesCurrentChild;

    /**
     * ninoActual/hermanos llegan a la vista compartidos desde el View
     * Composer del layout (layouts.app) — acá solo lo que es propio de
     * esta página.
     */
    public function index(): View
    {
        $user = Auth::user();

        return view('mi-escuelita.home', [
            'familia' => ['nombre' => $user->family?->name],
            'ninoActual' => $this->currentChild(),
            'areas' => Area::query()->where('active', true)->orderBy('order')->get(['id', 'name', 'icon', 'description']),
        ]);
    }

    /**
     * Cambiar de niño (Fase 3): valida vía ResolvesCurrentChild que el
     * id pedido sea de verdad un hijo de esta familia antes de guardarlo.
     */
    public function switchChild(Child $child): RedirectResponse
    {
        abort_unless(Auth::user()->can('view', $child), 403);

        session(['current_child_id' => $child->id]);

        return back();
    }
}
