<?php

namespace App\Services;

use App\Contracts\Repositories\ServiceOrderRepositoryInterface;
use App\DTOs\ServiceOrderDTO;
use App\Models\ServiceOrder;

class ServiceOrderService
{
    public function __construct(
        protected ServiceOrderRepositoryInterface $serviceOrderRepository,
        protected OrderStatusService $orderStatusService,
        protected AuditService $auditService
    ) {}

    public function createOrderFromAdvisor(array $data, $advisorId)
    {
        $orderNumber = ServiceOrder::generateOrderNumber();

        $orderData = array_merge($data, [
            'order_number' => $orderNumber,
            'advisor_id' => $advisorId,
            'created_by' => $advisorId,
            'source' => 'manual',
            'status' => 'recibida',
            'progress' => 0,
        ]);

        return $this->serviceOrderRepository->create($orderData);
    }

    public function createOrder(ServiceOrderDTO $dto)
    {
        return $this->serviceOrderRepository->create($dto->toArray());
    }

    public function updateOrder($id, ServiceOrderDTO $dto)
    {
        return $this->serviceOrderRepository->update($id, $dto->toArray());
    }

    public function deleteOrder($id)
    {
        return $this->serviceOrderRepository->delete($id);
    }

    public function reassignMechanic($orderId, $mechanicId)
    {
        return $this->serviceOrderRepository->update($orderId, ['mechanic_id' => $mechanicId]);
    }

    public function updateStatus($orderId, $status, ?string $reason = null)
    {
        $order = $this->serviceOrderRepository->find($orderId);
        if (!$order) return false;

        $oldStatus = $order->status;
        $result = $this->orderStatusService->changeStatus($order, $status, $reason);

        if ($result) {
            $this->auditService->logOrderAction(
                'update_status',
                "Orden {$order->order_number} cambió estado de {$oldStatus} a {$status}",
                auth()->id(),
                ['status' => $oldStatus],
                ['status' => $status, 'reason' => $reason]
            );
        }

        return $result;
    }

    public function updateProgress($orderId, $progress, $comment = null)
    {
        $order = $this->serviceOrderRepository->find($orderId);
        if (!$order) return false;

        $updates = ['progress' => $progress];

        if ($progress > 0 && $order->status === 'recibida') {
            $updates['status'] = 'en_proceso';
            $updates['started_at'] = $order->started_at ?? now();
        }

        if ($progress >= 100) {
            $updates['status'] = 'completada';
            $updates['completed_at'] = now();
        }

        return $this->serviceOrderRepository->update($orderId, $updates);
    }

    public function updateOrderStatusWithDetails($orderId, $status, $progress, $diagnosis = null, $recommendations = null, $completedAt = null)
    {
        $order = $this->serviceOrderRepository->find($orderId);
        if (!$order) return false;

        $updates = [
            'status' => $status,
            'progress' => $progress,
        ];

        if ($status === 'en_proceso' && !$order->started_at) {
            $updates['started_at'] = now();
        }

        if (in_array($status, ['completada', 'entregada'], true)) {
            $updates['completed_at'] = $completedAt ?? now();
            $updates['progress'] = 100;
        }

        if ($diagnosis) $updates['diagnosis'] = $diagnosis;
        if ($recommendations) $updates['recommendations'] = $recommendations;

        return $this->serviceOrderRepository->update($orderId, $updates);
    }

    public function findByStatus($status)
    {
        return $this->serviceOrderRepository->findByStatus($status);
    }

    public function findByMechanic($mechanicId)
    {
        return $this->serviceOrderRepository->findByMechanic($mechanicId);
    }

    public function findByVehicle($vehicleId)
    {
        return $this->serviceOrderRepository->findByVehicle($vehicleId);
    }

    public function findWithRelations($id)
    {
        return $this->serviceOrderRepository->findWithRelations($id);
    }

    public function countOrders()
    {
        return $this->serviceOrderRepository->count();
    }

    public function getClientOrders($clientId, $search = null, $status = null, $perPage = 10)
    {
        $orders = $this->serviceOrderRepository->where('client_id', $clientId);

        if ($search !== null && $search !== '') {
            $orders = $orders->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($status !== null && $status !== '') {
            $orders = $orders->where('status', $status);
        }

        return $orders->latest()->paginate($perPage);
    }

    public function getOrderDetailsForClient($orderId)
    {
        return $this->serviceOrderRepository->findWithRelations($orderId);
    }
}
