<?php

namespace App\Services;

use App\DTOs\ServiceTimelineDTO;
use App\Models\Maintenance;
use App\Models\ServiceOrder;
use App\Models\ServicePhoto;

class ServiceTimelineService
{
    public function getOrderTimeline(ServiceOrder $order): ServiceTimelineDTO
    {
        $events = $this->generateTimelineEvents($order);
        $maintenances = $this->getMaintenancesTimeline($order);
        $photos = $this->getPhotosTimeline($order);

        return new ServiceTimelineDTO(
            orderId: $order->id,
            orderNumber: $order->order_number,
            status: $order->status,
            events: $events,
            maintenances: $maintenances,
            photos: $photos,
        );
    }

    private function generateTimelineEvents(ServiceOrder $order): array
    {
        $events = [];

        // Evento de creación de la orden
        $events[] = [
            'type' => 'order_created',
            'title' => 'Orden recibida',
            'description' => 'Vehículo recibido en taller',
            'date' => $order->created_at->format('d/m/Y H:i'),
            'icon' => '📥',
            'color' => 'blue',
        ];

        // Evento de inicio de trabajo
        if ($order->started_at) {
            $events[] = [
                'type' => 'work_started',
                'title' => 'Trabajo iniciado',
                'description' => 'Se inició el diagnóstico/reparación',
                'date' => $order->started_at->format('d/m/Y H:i'),
                'icon' => '🔧',
                'color' => 'orange',
            ];
        }

        // Evento de finalización
        if ($order->completed_at) {
            $events[] = [
                'type' => 'work_completed',
                'title' => 'Trabajo completado',
                'description' => 'Mantenimiento finalizado',
                'date' => $order->completed_at->format('d/m/Y H:i'),
                'icon' => '✅',
                'color' => 'green',
            ];
        }

        // Evento de entrega
        if ($order->delivered_at) {
            $events[] = [
                'type' => 'vehicle_delivered',
                'title' => 'Vehículo entregado',
                'description' => 'Entregado al cliente',
                'date' => $order->delivered_at->format('d/m/Y H:i'),
                'icon' => '🚗',
                'color' => 'purple',
            ];
        }

        // Ordenar eventos por fecha (cronológico)
        usort($events, function ($a, $b) {
            $dateA = \DateTime::createFromFormat('d/m/Y H:i', $a['date']);
            $dateB = \DateTime::createFromFormat('d/m/Y H:i', $b['date']);
            return $dateA <=> $dateB;
        });

        return $events;
    }

    private function getMaintenancesTimeline(ServiceOrder $order): array
    {
        return $order->maintenances->map(function (Maintenance $maintenance) {
            return [
                'id' => $maintenance->id,
                'type' => $maintenance->type,
                'description' => $maintenance->description,
                'status' => $maintenance->status,
                'performed_at' => $maintenance->performed_at?->format('d/m/Y H:i'),
                'mileage' => $maintenance->mileage_at_service,
                'icon' => $this->getMaintenanceIcon($maintenance->type),
                'color' => $this->getMaintenanceColor($maintenance->status),
            ];
        })->toArray();
    }

    private function getPhotosTimeline(ServiceOrder $order): array
    {
        return $order->photos->map(function (ServicePhoto $photo) {
            return [
                'id' => $photo->id,
                'type' => $photo->type,
                'description' => $photo->description,
                'photo_path' => $photo->photo_path,
                'created_at' => $photo->created_at->format('d/m/Y H:i'),
                'icon' => $this->getPhotoIcon($photo->type),
                'color' => 'cyan',
            ];
        })->toArray();
    }

    private function getMaintenanceIcon(string $type): string
    {
        return match ($type) {
            'preventivo' => '🔧',
            'correctivo' => '⚠️',
            'garantia' => '🛡️',
            default => '🔧',
        };
    }

    private function getMaintenanceColor(string $status): string
    {
        return match ($status) {
            'pendiente' => 'gray',
            'en_proceso' => 'orange',
            'completado' => 'green',
            'cancelado' => 'red',
            default => 'gray',
        };
    }

    private function getPhotoIcon(string $type): string
    {
        return match ($type) {
            'before' => '📸',
            'during' => '📹',
            'after' => '✨',
            'evidence' => '📋',
            default => '📷',
        };
    }
}
