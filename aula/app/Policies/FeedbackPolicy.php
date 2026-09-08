<?php

namespace App\Policies;

use App\Models\Feedback;
use App\Models\Observation;
use App\Models\User;

class FeedbackPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Feedback $feedback): bool
    {
        if ($user->isStaff()) {
            return true;
        }

        $child = $feedback->observation->child;

        if ($user->isGuia()) {
            return $child->environment?->teacher_id === $user->id;
        }

        if ($user->isFamilia()) {
            return $child->family_id === $user->family_id;
        }

        return false;
    }

    public function create(User $user, Observation $observation): bool
    {
        if ($user->isStaff()) {
            return true;
        }

        return $user->isGuia() && $observation->child->environment?->teacher_id === $user->id;
    }

    public function update(User $user, Feedback $feedback): bool
    {
        return $user->isStaff()
            || ($user->isGuia() && $feedback->teacher_id === $user->id);
    }

    public function delete(User $user, Feedback $feedback): bool
    {
        return $this->update($user, $feedback);
    }
}
