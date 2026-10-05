<?php

namespace App\Policies;

use App\Models\CourseModule;
use App\Models\User;

class CourseModulePolicy
{
    public function __construct(private readonly CoursePolicy $courses) {}

    public function viewAny(User $user): bool
    {
        return $this->courses->viewAny($user);
    }

    /**
     * Un módulo se ve con la misma regla que su curso.
     */
    public function view(User $user, CourseModule $module): bool
    {
        return $module->course !== null && $this->courses->view($user, $module->course);
    }

    public function create(User $user): bool
    {
        return $user->canUseBiblioteca() && $user->isStaff();
    }

    public function update(User $user, CourseModule $module): bool
    {
        return $user->canUseBiblioteca() && $user->isStaff();
    }

    public function delete(User $user, CourseModule $module): bool
    {
        return $user->canUseBiblioteca() && $user->isStaff();
    }
}
