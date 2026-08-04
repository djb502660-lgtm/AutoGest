<?php

namespace App\Services;

use App\Contracts\Repositories\ServiceOrderRepositoryInterface;
use App\DTOs\ServiceOrderDTO;

class ServiceOrderService
{
    public function __construct(
        protected ServiceOrderRepositoryInterface $serviceOrderRepository
    ) {}

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

    public function updateStatus($orderId, $status)
    {
        return $this->serviceOrderRepository->update($orderId, ['status' => $status]);
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
}
