<?php

namespace App\Policies;

use App\Models\Area;
use App\Models\User;

class AreaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function view(User $user, Area $area): bool
    {
        return $user->isStaff();
    }

    public function create(User $user): bool
    {
        return $user->isStaff();
    }

    public function update(User $user, Area $area): bool
    {
        return $user->isStaff();
    }

    public function delete(User $user, Area $area): bool
    {
        return $user->isStaff();
    }

    public function restore(User $user, Area $area): bool
    {
        return $user->isStaff();
    }

    public function forceDelete(User $user, Area $area): bool
    {
        return $user->isAdministrador();
    }
}
