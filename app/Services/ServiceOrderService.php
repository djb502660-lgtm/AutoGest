<?php

namespace App\Services;

use App\Contracts\Repositories\ServiceOrderRepositoryInterface;

class ServiceOrderService
{
    public function __construct(
        protected ServiceOrderRepositoryInterface $serviceOrderRepository
    ) {}

    public function createOrder(array $data)
    {
        return $this->serviceOrderRepository->create($data);
    }

    public function updateOrder($id, array $data)
    {
        return $this->serviceOrderRepository->update($id, $data);
    }

    public function deleteOrder($id)
    {
        return $this->serviceOrderRepository->delete($id);
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
