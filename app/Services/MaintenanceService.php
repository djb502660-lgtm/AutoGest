<?php

namespace App\Services;

use App\Contracts\Repositories\MaintenanceRepositoryInterface;
use App\DTOs\MaintenanceDTO;

class MaintenanceService
{
    public function __construct(
        protected MaintenanceRepositoryInterface $maintenanceRepository
    ) {}

    public function createMaintenance(MaintenanceDTO $dto)
    {
        return $this->maintenanceRepository->create($dto->toArray());
    }

    public function updateMaintenance($id, MaintenanceDTO $dto)
    {
        return $this->maintenanceRepository->update($id, $dto->toArray());
    }

    public function deleteMaintenance($id)
    {
        return $this->maintenanceRepository->delete($id);
    }

    public function updateStatus($id, $status)
    {
        return $this->maintenanceRepository->update($id, ['status' => $status]);
    }

    public function findByServiceOrder($serviceOrderId)
    {
        return $this->maintenanceRepository->findByServiceOrder($serviceOrderId);
    }

    public function findByVehicle($vehicleId)
    {
        return $this->maintenanceRepository->findByVehicle($vehicleId);
    }

    public function getRecentMaintenances($limit = 10)
    {
        return $this->maintenanceRepository->getRecentMaintenances($limit);
    }

    public function countMaintenances()
    {
        return $this->maintenanceRepository->count();
    }
}
