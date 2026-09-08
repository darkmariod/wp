<?php

namespace App\Policies;

use App\Models\Environment;
use App\Models\User;

class EnvironmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->active && ! $user->isFamilia();
    }

    public function view(User $user, Environment $environment): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isStaff();
    }

    /**
     * Una guía gestiona SU ambiente, no el de otra guía.
     */
    public function update(User $user, Environment $environment): bool
    {
        if ($user->isStaff()) {
            return true;
        }

        return $user->isGuia() && $environment->teacher_id === $user->id;
    }

    public function delete(User $user, Environment $environment): bool
    {
        return $user->isStaff();
    }
}
