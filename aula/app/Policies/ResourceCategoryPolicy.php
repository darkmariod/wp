<?php

namespace App\Policies;

use App\Models\ResourceCategory;
use App\Models\User;

class ResourceCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canUseBiblioteca();
    }

    public function view(User $user, ResourceCategory $category): bool
    {
        return $user->canUseBiblioteca();
    }

    public function create(User $user): bool
    {
        return $user->canUseBiblioteca() && $user->isStaff();
    }

    public function update(User $user, ResourceCategory $category): bool
    {
        return $user->canUseBiblioteca() && $user->isStaff();
    }

    public function delete(User $user, ResourceCategory $category): bool
    {
        return $user->canUseBiblioteca() && $user->isStaff();
    }
}
