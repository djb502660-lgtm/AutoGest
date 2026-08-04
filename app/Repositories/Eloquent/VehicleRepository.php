<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\VehicleRepositoryInterface;
use App\Models\Vehicle;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

class VehicleRepository extends BaseRepository implements VehicleRepositoryInterface
{
    public function __construct(Vehicle $model)
    {
        parent::__construct($model);
    }

    public function findByPlate(string $plate)
    {
        return $this->model->where('plate', $plate)->first();
    }

    public function findByClient(int $clientId): Collection
    {
        return $this->model->where('client_id', $clientId)->get();
    }

    public function findWithVehicles($id)
    {
        return $this->model->with(['client', 'serviceOrders'])->find($id);
    }
}
