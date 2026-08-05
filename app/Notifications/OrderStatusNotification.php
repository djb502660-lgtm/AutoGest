<?php

namespace App\Notifications;

use App\Models\ServiceOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $order;

    private $oldStatus;

    private $newStatus;

    private $reason;

    public function __construct(ServiceOrder $order, string $oldStatus, string $newStatus, ?string $reason = null)
    {
        $this->order = $order;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
        $this->reason = $reason;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $statusLabels = [
            'recibida' => 'Recibida',
            'en_proceso' => 'En Proceso',
            'completada' => 'Completada',
            'entregada' => 'Entregada',
            'cancelada' => 'Cancelada',
        ];

        $oldStatusLabel = $statusLabels[$this->oldStatus] ?? $this->oldStatus;
        $newStatusLabel = $statusLabels[$this->newStatus] ?? $this->newStatus;

        $mail = (new MailMessage)
            ->subject("Actualización de Orden {$this->order->order_number} - AutoGest")
            ->greeting("Hola {$notifiable->name},")
            ->line("Tu orden de servicio **{$this->order->order_number}** ha cambiado de estado.")
            ->line("**Estado anterior:** {$oldStatusLabel}")
            ->line("**Nuevo estado:** {$newStatusLabel}");

        if ($this->reason) {
            $mail->line("**Motivo:** {$this->reason}");
        }

        $mail->line("**Vehículo:** {$this->order->vehicle->brand} {$this->order->vehicle->model} ({$this->order->vehicle->plate})")
            ->line("**Descripción:** {$this->order->description}");

        if ($this->newStatus === 'completada' && $this->order->recommendations) {
            $mail->line("**Recomendaciones:** {$this->order->recommendations}");
        }

        return $mail->action('Ver Orden', url('/cliente/ordenes/'.$this->order->id))
            ->line('Gracias por confiar en AutoGest para el cuidado de tu vehículo.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'reason' => $this->reason,
            'vehicle_plate' => $this->order->vehicle->plate,
            'message' => "Orden {$this->order->order_number} cambió de {$this->oldStatus} a {$this->newStatus}",
        ];
    }
}
