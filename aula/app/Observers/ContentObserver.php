<?php

namespace App\Observers;

use App\Models\Content;
use App\Models\User;
use App\Notifications\ContentPublishedNotification;

class ContentObserver
{
    /**
     * Fase 9: "cuando se publique una experiencia, familia recibe
     * aviso". Vive en el Observer y no en el controller de Filament
     * para que dispare igual si lo publica el scheduler (Fase 20).
     */
    public function updated(Content $content): void
    {
        if (! $content->wasChanged('status')) {
            return;
        }

        if ($content->status !== Content::STATUS_PUBLISHED) {
            return;
        }

        $familias = User::query()
            ->where('role', User::ROLE_FAMILIA)
            ->whereHas('family.children', function ($q) use ($content) {
                if ($content->environment_id) {
                    $q->where('environment_id', $content->environment_id);
                }
            })
            ->get();

        foreach ($familias as $familia) {
            $familia->notify(new ContentPublishedNotification($content));
        }
    }
}
