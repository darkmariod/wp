<?php

namespace App\Http\Controllers\MiEscuelita\Concerns;

use App\Models\Child;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * Una familia puede tener más de un niño (Fase 3). En vez de repetir
 * "¿de quién es esta página?" en cada controller, se resuelve acá una
 * sola vez — y SIEMPRE validado contra la familia real de la sesión,
 * nunca contra lo que venga suelto en la URL o en el propio session().
 */
trait ResolvesCurrentChild
{
    protected function currentChild(): ?Child
    {
        $user = Auth::user();

        $children = $user->family?->children()->where('status', 'active')->get() ?? collect();

        if ($children->isEmpty()) {
            return null;
        }

        $selectedId = session('current_child_id');

        $child = $selectedId ? $children->firstWhere('id', $selectedId) : null;

        return $child ?? $children->first();
    }

    protected function siblings(): Collection
    {
        return Auth::user()->family?->children()->where('status', 'active')->get()
            ?? collect();
    }
}
