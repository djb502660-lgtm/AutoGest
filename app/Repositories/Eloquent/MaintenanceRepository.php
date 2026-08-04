<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\MaintenanceRepositoryInterface;
use App\Models\Maintenance;
use App\Repositories\BaseRepository;

class MaintenanceRepository extends BaseRepository implements MaintenanceRepositoryInterface
{
    public function __construct(Maintenance $model)
    {
        parent::__construct($model);
    }

    public function findByServiceOrder($serviceOrderId)
    {
        return $this->model->where('service_order_id', $serviceOrderId)->get();
    }

    public function findByVehicle($vehicleId)
    {
        return $this->model->whereHas('serviceOrder', function ($query) use ($vehicleId) {
            $query->where('vehicle_id', $vehicleId);
        })->get();
    }

    public function getRecentMaintenances($limit = 10)
    {
        return $this->model->orderByDesc('performed_at')->limit($limit)->get();
    }

    public function count()
    {
        return $this->model->count();
    }
}
