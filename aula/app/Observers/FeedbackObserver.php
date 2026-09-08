<?php

namespace App\Observers;

use App\Models\Feedback;
use App\Models\User;
use App\Notifications\GuideRespondedNotification;

class FeedbackObserver
{
    /**
     * Fase 9: "cuando la guía responde, familia recibe aviso".
     */
    public function created(Feedback $feedback): void
    {
        $family = $feedback->observation->child->family;

        $familyUser = $family?->users()->where('role', User::ROLE_FAMILIA)->first();

        $familyUser?->notify(new GuideRespondedNotification($feedback));
    }
}
