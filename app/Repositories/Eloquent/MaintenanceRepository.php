<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\MaintenanceRepositoryInterface;
use App\Models\Maintenance;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

class MaintenanceRepository extends BaseRepository implements MaintenanceRepositoryInterface
{
    public function __construct(Maintenance $model)
    {
        parent::__construct($model);
    }

    public function findByServiceOrder(int $serviceOrderId): Collection
    {
        return $this->model->where('service_order_id', $serviceOrderId)->get();
    }

    public function findByVehicle(int $vehicleId): Collection
    {
        return $this->model->whereHas('serviceOrder', function ($query) use ($vehicleId) {
            $query->where('vehicle_id', $vehicleId);
        })->get();
    }

    public function getRecentMaintenances(int $limit = 10): Collection
    {
        return $this->model->orderByDesc('performed_at')->limit($limit)->get();
    }
}
