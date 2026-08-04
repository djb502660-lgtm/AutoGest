<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\ServiceOrderRepositoryInterface;
use App\Models\ServiceOrder;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

class ServiceOrderRepository extends BaseRepository implements ServiceOrderRepositoryInterface
{
    public function __construct(ServiceOrder $model)
    {
        parent::__construct($model);
    }

    public function findByStatus(string $status): Collection
    {
        return $this->model->where('status', $status)->get();
    }

    public function findByMechanic(int $mechanicId): Collection
    {
        return $this->model->where('mechanic_id', $mechanicId)->get();
    }

    public function findByVehicle(int $vehicleId): Collection
    {
        return $this->model->where('vehicle_id', $vehicleId)->get();
    }

    public function findWithRelations($id)
    {
        return $this->model->with(['vehicle', 'mechanic', 'client', 'maintenances'])->find($id);
    }
}
