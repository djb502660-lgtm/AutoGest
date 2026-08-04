<?php

namespace App\Contracts\Repositories;

interface MaintenanceRepositoryInterface extends BaseRepositoryInterface
{
    public function findByServiceOrder($serviceOrderId);

    public function findByVehicle($vehicleId);

    public function getRecentMaintenances($limit = 10);
}
