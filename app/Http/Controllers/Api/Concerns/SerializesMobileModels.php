<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Models\AppointmentRequest;
use App\Models\ServiceOrder;
use App\Models\ServicePhoto;
use App\Models\Vehicle;

trait SerializesMobileModels
{
    protected function vehicleSummary(Vehicle $vehicle): array
    {
        return [
            'id' => $vehicle->id,
            'plate' => $vehicle->plate,
            'brand' => $vehicle->brand,
            'model' => $vehicle->model,
            'year' => $vehicle->year,
            'mileage' => $vehicle->mileage,
            'status' => $vehicle->status,
            'status_label' => $vehicle->statusLabel(),
        ];
    }

    protected function orderSummary(ServiceOrder $order): array
    {
        $order->loadMissing(['vehicle', 'client', 'mechanic', 'advisor']);

        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'description' => $order->description,
            'status' => $order->status,
            'status_label' => $order->statusLabel(),
            'priority' => $order->priority,
            'progress' => $order->progress,
            'estimated_cost' => $order->estimated_cost,
            'total_cost' => $order->total_cost,
            'created_at' => $order->created_at?->format('Y-m-d H:i'),
            'vehicle' => $order->vehicle ? $this->vehicleSummary($order->vehicle) : null,
            'client' => $order->client ? [
                'id' => $order->client->id,
                'name' => $order->client->name,
            ] : null,
            'mechanic' => $order->mechanic ? [
                'id' => $order->mechanic->id,
                'name' => $order->mechanic->name,
            ] : null,
            'advisor' => $order->advisor ? [
                'id' => $order->advisor->id,
                'name' => $order->advisor->name,
            ] : null,
        ];
    }

    protected function photoPayload(ServicePhoto $photo): array
    {
        return [
            'id' => $photo->id,
            'url' => $photo->url,
            'description' => $photo->description,
            'type' => $photo->type,
            'type_label' => $photo->type_label,
            'user' => $photo->user?->name,
            'created_at' => $photo->created_at?->format('d/m/Y H:i'),
        ];
    }

    protected function appointmentPayload(AppointmentRequest $appointment): array
    {
        $appointment->loadMissing(['client', 'vehicle', 'advisor', 'serviceOrder']);

        return [
            'id' => $appointment->id,
            'status' => $appointment->status,
            'status_label' => $appointment->statusLabel(),
            'source' => $appointment->source,
            'service_type' => $appointment->service_type,
            'description' => $appointment->description,
            'requested_date' => $appointment->requested_date?->format('Y-m-d'),
            'requested_time' => $appointment->requested_time,
            'advisor_notes' => $appointment->advisor_notes,
            'client' => $appointment->client ? [
                'id' => $appointment->client->id,
                'name' => $appointment->client->name,
                'phone' => $appointment->client->phone,
            ] : null,
            'vehicle' => $appointment->vehicle ? $this->vehicleSummary($appointment->vehicle) : null,
            'service_order_id' => $appointment->service_order_id,
            'order_number' => $appointment->serviceOrder?->order_number,
        ];
    }
}
