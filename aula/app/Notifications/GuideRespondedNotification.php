<?php

namespace App\Notifications;

use App\Models\Feedback;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GuideRespondedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Feedback $feedback) {}

    public function via(object $notifiable): array
    {
        return $notifiable->wantsNotification('guide_responded') ? ['database', 'mail'] : ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $child = $this->feedback->observation->child;

        return [
            'type' => 'guide_responded',
            'feedback_id' => $this->feedback->id,
            'child_name' => $child->name,
            'message' => "La docente respondió sobre {$child->name}",
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $child = $this->feedback->observation->child;

        return (new MailMessage)
            ->subject('Respuesta de la docente')
            ->greeting("Hola, familia de {$notifiable->family?->name}")
            ->line("La docente dejó una nueva retroalimentación sobre {$child->name}.")
            ->action('Ver respuesta', url('/mi-escuelita/mis-experiencias'));
    }
}
