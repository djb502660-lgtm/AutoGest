<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\VehicleRepositoryInterface;
use App\Models\Vehicle;
use App\Repositories\BaseRepository;

class VehicleRepository extends BaseRepository implements VehicleRepositoryInterface
{
    public function __construct(Vehicle $model)
    {
        parent::__construct($model);
    }

    public function findByPlate($plate)
    {
        return $this->model->where('plate', $plate)->first();
    }

    public function findByClient($clientId)
    {
        return $this->model->where('client_id', $clientId)->get();
    }

    public function findWithVehicles($id)
    {
        return $this->model->with(['client', 'serviceOrders'])->find($id);
    }

    public function count()
    {
        return $this->model->count();
    }
}
