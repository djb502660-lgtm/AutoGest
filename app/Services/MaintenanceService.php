<?php

namespace App\Services;

use App\Contracts\Repositories\MaintenanceRepositoryInterface;

class MaintenanceService
{
    public function __construct(
        protected MaintenanceRepositoryInterface $maintenanceRepository
    ) {}

    public function createMaintenance(array $data)
    {
        return $this->maintenanceRepository->create($data);
    }

    public function updateMaintenance($id, array $data)
    {
        return $this->maintenanceRepository->update($id, $data);
    }

    public function deleteMaintenance($id)
    {
        return $this->maintenanceRepository->delete($id);
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
