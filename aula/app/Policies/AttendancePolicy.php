<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\User;

class AttendancePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff() || $user->isGuia();
    }

    public function view(User $user, Attendance $attendance): bool
    {
        if ($user->isStaff()) {
            return true;
        }

        if ($user->isGuia()) {
            return $attendance->child->environment?->teacher_id === $user->id;
        }

        if ($user->isFamilia()) {
            return $attendance->child->family_id === $user->family_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isStaff() || $user->isGuia();
    }

    public function update(User $user): bool
    {
        return $user->isStaff() || $user->isGuia();
    }

    public function delete(User $user): bool
    {
        return $user->isStaff();
    }
}
