<?php

namespace App\Notifications;

use App\Models\ServiceOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServicePhotoNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $order;

    private $photoSummary;

    public function __construct(ServiceOrder $order, array $photoSummary)
    {
        $this->order = $order;
        $this->photoSummary = $photoSummary;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $totalPhotos = $this->photoSummary['total'];
        $photoDetails = [];

        if ($this->photoSummary['by_type']['reception'] > 0) {
            $photoDetails[] = "{$this->photoSummary['by_type']['reception']} de recepción";
        }
        if ($this->photoSummary['by_type']['before'] > 0) {
            $photoDetails[] = "{$this->photoSummary['by_type']['before']} antes del trabajo";
        }
        if ($this->photoSummary['by_type']['evidence'] > 0) {
            $photoDetails[] = "{$this->photoSummary['by_type']['evidence']} de diagnóstico";
        }
        if ($this->photoSummary['by_type']['after'] > 0) {
            $photoDetails[] = "{$this->photoSummary['by_type']['after']} finales";
        }

        $photoDescription = implode(', ', $photoDetails);

        return (new MailMessage)
            ->subject("Evidencias Fotográficas Actualizadas - Orden {$this->order->order_number} - AutoGest")
            ->greeting("Hola {$notifiable->name},")
            ->line("Se han agregado **{$totalPhotos} nuevas evidencias fotográficas** a tu orden de servicio.")
            ->line("**Orden:** {$this->order->order_number}")
            ->line("**Vehículo:** {$this->order->vehicle->brand} {$this->order->vehicle->model} ({$this->order->vehicle->plate})")
            ->line("**Tipo de fotos:** {$photoDescription}")
            ->line("**Estado actual:** {$this->order->statusLabel()}")
            ->action('Ver Evidencias', url('/cliente/ordenes/'.$this->order->id))
            ->line('Estas evidencias fotográficas respaldan el diagnóstico técnico de tu vehículo.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'total_photos' => $this->photoSummary['total'],
            'photo_by_type' => $this->photoSummary['by_type'],
            'vehicle_plate' => $this->order->vehicle->plate,
            'message' => "Se agregaron {$this->photoSummary['total']} nuevas evidencias a orden {$this->order->order_number}",
        ];
    }
}
