<?php

namespace App\Policies;

use App\Models\Child;
use App\Models\Observation;
use App\Models\User;

class ObservationPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * La familia solo LEE observaciones de su propio hijo — nunca crea
     * ni edita una observación, eso es exclusivo de la guía.
     */
    public function view(User $user, Observation $observation): bool
    {
        if ($user->isStaff()) {
            return true;
        }

        if ($user->isGuia()) {
            return $observation->child->environment?->teacher_id === $user->id;
        }

        if ($user->isFamilia()) {
            return $observation->child->family_id === $user->family_id;
        }

        return false;
    }

    public function create(User $user, Child $child): bool
    {
        if ($user->isStaff()) {
            return true;
        }

        return $user->isGuia() && $child->environment?->teacher_id === $user->id;
    }

    public function update(User $user, Observation $observation): bool
    {
        return $user->isStaff()
            || ($user->isGuia() && $observation->teacher_id === $user->id);
    }

    public function delete(User $user, Observation $observation): bool
    {
        return $this->update($user, $observation);
    }
}
