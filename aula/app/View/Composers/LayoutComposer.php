<?php

namespace App\View\Composers;

use Illuminate\View\View;

/**
 * Datos compartidos del layout autenticado: resuelve el niño actual de la
 * sesión (current_child_id) entre los hermanos activos de la familia, o
 * null si el usuario no es familia. Un solo lugar, para que ninguna página
 * se olvide de pasarlo.
 */
class LayoutComposer
{
    public function compose(View $view): void
    {
        $user = auth()->user();

        if (! $user || ! $user->isFamilia()) {
            $view->with('ninoActual', null)
                ->with('hermanos', collect());

            return;
        }

        $hermanos = $user->family?->children()
            ->where('status', 'active')
            ->get(['id', 'name', 'photo_path'])
            ?? collect();

        $seleccionado = session('current_child_id');

        $ninoActual = $hermanos->firstWhere('id', $seleccionado) ?? $hermanos->first();

        $view->with('ninoActual', $ninoActual)
            ->with('hermanos', $hermanos);
    }
}
