<?php

namespace App\Policies;

use App\Models\Media;
use App\Models\User;

class MediaPolicy
{
    public function view(User $user, Media $medium): bool
    {
        $mediable = $medium->mediable;

        if (! $mediable) {
            return false;
        }

        return $user->can('view', $mediable);
    }
}
