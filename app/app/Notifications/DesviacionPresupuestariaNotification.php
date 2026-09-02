<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DesviacionPresupuestariaNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $obraNombre,
        private readonly float $porcentaje,
        private readonly string $tipo,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'tipo' => 'desviacion_presupuestaria',
            'mensaje' => "Desviación del {$this->porcentaje}% en {$this->obraNombre} ({$this->tipo})",
            'datos' => [
                'obra_nombre' => $this->obraNombre,
                'porcentaje' => $this->porcentaje,
                'tipo_desviacion' => $this->tipo,
            ],
            'url' => '/admin/presupuestos',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $descripcion = $this->tipo === 'sobrecosto'
            ? 'Se ha excedido el presupuesto'
            : 'El consumo está por debajo del presupuesto';

        return (new MailMessage)
            ->subject("Alerta: Desviación presupuestaria en {$this->obraNombre}")
            ->line("La obra {$this->obraNombre} presenta una desviación del {$this->porcentaje}% ({$this->tipo}).")
            ->line($descripcion)
            ->action('Ver presupuestos', '/admin/presupuestos');
    }
}
