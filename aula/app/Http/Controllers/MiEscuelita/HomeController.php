<?php

namespace App\Http\Controllers\MiEscuelita;

use App\Http\Controllers\Controller;
use App\Http\Controllers\MiEscuelita\Concerns\ResolvesCurrentChild;
use App\Models\Area;
use App\Models\Attendance;
use App\Models\Child;
use App\Models\Content;
use App\Models\Evidence;
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
        $nino = $this->currentChild();

        return view('mi-escuelita.home', [
            'familia' => ['nombre' => $user->family?->name],
            'ninoActual' => $nino,
            'areas' => Area::query()->where('active', true)->orderBy('order')->get(['id', 'name', 'icon', 'description']),
            'totalExperiencias' => Content::query()
                ->published()
                ->where(function ($q) use ($nino) {
                    $q->whereNull('environment_id');
                    if ($nino) {
                        $q->orWhere('environment_id', $nino->environment_id);
                    }
                })
                ->count(),
            'sinResponder' => $nino
                ? Evidence::query()
                    ->where('child_id', $nino->id)
                    ->whereIn('status', [Evidence::STATUS_SUBMITTED, Evidence::STATUS_VIEWED])
                    ->count()
                : 0,
            'asistenciaHoy' => $nino
                ? $nino->attendances()->whereDate('date', now())->value('status')
                : null,
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
