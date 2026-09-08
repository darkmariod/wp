<?php

namespace App\Policies;

use App\Models\Family;
use App\Models\User;

class FamilyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function view(User $user, Family $family): bool
    {
        return $user->isStaff() || $user->family_id === $family->id;
    }

    public function create(User $user): bool
    {
        return $user->isStaff();
    }

    public function update(User $user, Family $family): bool
    {
        return $user->isStaff();
    }

    public function delete(User $user, Family $family): bool
    {
        return $user->isAdministrador();
    }
}
