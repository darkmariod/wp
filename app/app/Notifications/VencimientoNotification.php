<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VencimientoNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $tipo,
        private readonly string $mensaje,
        private readonly array $datos,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'tipo' => $this->tipo,
            'mensaje' => $this->mensaje,
            'datos' => $this->datos,
            'url' => $this->obtenerUrl(),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $asunto = match ($this->tipo) {
            'critica' => 'ALERTA CRÍTICA: Vencimiento de cuenta',
            'urgente' => 'ALERTA: Cuenta por vencer pronto',
            default => 'Informativo: Cuenta próxima a vencer',
        };

        return (new MailMessage)
            ->subject($asunto)
            ->line($this->mensaje)
            ->line("Monto: \${$this->datos['monto']}")
            ->line("Fecha de vencimiento: {$this->datos['fecha_vencimiento']}")
            ->action('Ver detalle', $this->obtenerUrl());
    }

    private function obtenerUrl(): string
    {
        $tipo = $this->datos['tipo_documento'] ?? 'cuenta';

        if ($tipo === 'cuenta_por_cobrar') {
            return '/admin/cuentas-por-cobrar/' . ($this->datos['documento_id'] ?? '');
        }

        return '/admin/cuentas-por-pagar/' . ($this->datos['documento_id'] ?? '');
    }
}
