<?php

namespace App\Notifications;

use App\Models\Content;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContentPublishedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Content $content) {}

    public function via(object $notifiable): array
    {
        return $notifiable->wantsNotification('content_published') ? ['database', 'mail'] : ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'content_published',
            'content_id' => $this->content->id,
            'title' => $this->content->title,
            'message' => "Nueva experiencia: {$this->content->title}",
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nueva experiencia en Mi Escuelita')
            ->greeting("Hola, familia de {$notifiable->family?->name}")
            ->line("Se publicó una nueva experiencia: \"{$this->content->title}\".")
            ->action('Ver experiencia', url('/mi-escuelita/experiencias/'.$this->content->slug));
    }
}
