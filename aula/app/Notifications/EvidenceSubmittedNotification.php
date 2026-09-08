<?php

namespace App\Notifications;

use App\Models\Evidence;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EvidenceSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Evidence $evidence) {}

    public function via(object $notifiable): array
    {
        return $notifiable->wantsNotification('evidence_submitted') ? ['database', 'mail'] : ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'evidence_submitted',
            'evidence_id' => $this->evidence->id,
            'child_name' => $this->evidence->child->name,
            'message' => "{$this->evidence->child->name} compartió una experiencia",
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nueva experiencia compartida')
            ->greeting('Hola')
            ->line("{$this->evidence->child->name} compartió la experiencia \"{$this->evidence->content->title}\".")
            ->action('Revisar', url('/admin'));
    }
}
