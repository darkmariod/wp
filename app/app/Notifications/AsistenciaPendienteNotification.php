<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AsistenciaPendienteNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $trabajadorNombre,
        private readonly string $obraNombre,
        private readonly string $fecha,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'tipo' => 'asistencia_pendiente',
            'mensaje' => "{$this->trabajadorNombre} no registró asistencia en {$this->obraNombre}",
            'datos' => [
                'trabajador_nombre' => $this->trabajadorNombre,
                'obra_nombre' => $this->obraNombre,
                'fecha' => $this->fecha,
            ],
            'url' => '/admin/asistencia-obras',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Alerta: Asistencia pendiente')
            ->line("El trabajador {$this->trabajadorNombre} no registró horas en la obra {$this->obraNombre} el día {$this->fecha}.")
            ->action('Ver asistencias', '/admin/asistencia-obras');
    }
}
