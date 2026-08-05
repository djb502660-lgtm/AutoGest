<?php

namespace App\Services;

use App\Models\ServiceOrder;

class OrderStatusService
{
    private array $validTransitions = [
        'recibida' => ['en_proceso', 'cancelada'],
        'en_proceso' => ['completada', 'cancelada'],
        'completada' => ['entregada'],
        'entregada' => [],
        'cancelada' => [],
    ];

    public function canTransition(string $fromStatus, string $toStatus): bool
    {
        if (! isset($this->validTransitions[$fromStatus])) {
            return false;
        }

        return in_array($toStatus, $this->validTransitions[$fromStatus], true);
    }

    public function changeStatus(ServiceOrder $order, string $newStatus, ?string $reason = null): bool
    {
        if (! $this->canTransition($order->status, $newStatus)) {
            return false;
        }

        $order->status = $newStatus;

        if ($newStatus === 'en_proceso' && ! $order->started_at) {
            $order->started_at = now();
        }

        if ($newStatus === 'completada' && ! $order->completed_at) {
            $order->completed_at = now();
            $order->progress = 100;
        }

        if ($newStatus === 'entregada' && ! $order->delivered_at) {
            $order->delivered_at = now();
        }

        if ($reason) {
            $order->status_reason = $reason;
        }

        return $order->save();
    }

    public function getValidTransitions(string $fromStatus): array
    {
        return $this->validTransitions[$fromStatus] ?? [];
    }

    public function isValidStatus(string $status): bool
    {
        return isset($this->validTransitions[$status]);
    }

    public function getAllValidStatuses(): array
    {
        return array_keys($this->validTransitions);
    }
}
