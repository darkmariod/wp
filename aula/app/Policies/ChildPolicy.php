<?php

namespace App\Policies;

use App\Models\Child;
use App\Models\User;

class ChildPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff() || $user->isGuia() || $user->isFamilia();
    }

    /**
     * Núcleo de la privacidad entre familias: una familia solo ve a SUS
     * hijos, comparando family_id — nunca confiando en un id que venga
     * del frontend.
     */
    public function view(User $user, Child $child): bool
    {
        if ($user->isStaff()) {
            return true;
        }

        if ($user->isGuia()) {
            return $child->environment?->teacher_id === $user->id;
        }

        if ($user->isFamilia()) {
            return $child->family_id === $user->family_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isStaff() || $user->isGuia();
    }

    public function update(User $user, Child $child): bool
    {
        return $user->isStaff();
    }

    public function delete(User $user, Child $child): bool
    {
        return $user->isStaff();
    }

    public function restore(User $user, Child $child): bool
    {
        return $user->isStaff();
    }

    public function forceDelete(User $user, Child $child): bool
    {
        return $user->isAdministrador();
    }
}
