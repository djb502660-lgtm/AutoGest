<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\ServiceOrderRepositoryInterface;
use App\Models\ServiceOrder;
use App\Repositories\BaseRepository;

class ServiceOrderRepository extends BaseRepository implements ServiceOrderRepositoryInterface
{
    public function __construct(ServiceOrder $model)
    {
        parent::__construct($model);
    }

    public function findByStatus($status)
    {
        return $this->model->where('status', $status)->get();
    }

    public function findByMechanic($mechanicId)
    {
        return $this->model->where('mechanic_id', $mechanicId)->get();
    }

    public function findByVehicle($vehicleId)
    {
        return $this->model->where('vehicle_id', $vehicleId)->get();
    }

    public function findWithRelations($id)
    {
        return $this->model->with(['vehicle', 'mechanic', 'client', 'maintenances'])->find($id);
    }

    public function count()
    {
        return $this->model->count();
    }
}
