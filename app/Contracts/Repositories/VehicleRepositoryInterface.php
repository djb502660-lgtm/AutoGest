<?php

namespace App\Contracts\Repositories;

interface VehicleRepositoryInterface extends BaseRepositoryInterface
{
    public function findByPlate($plate);

    public function findByClient($clientId);

    public function findWithVehicles($id);
}
