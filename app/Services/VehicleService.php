<?php

namespace App\Services;

use App\Contracts\Repositories\VehicleRepositoryInterface;

class VehicleService
{
    public function __construct(
        protected VehicleRepositoryInterface $vehicleRepository
    ) {}

    public function getClientVehicles($clientId)
    {
        return $this->vehicleRepository->findByClient($clientId);
    }

    public function createVehicle(array $data)
    {
        return $this->vehicleRepository->create($data);
    }

    public function updateVehicle($id, array $data)
    {
        return $this->vehicleRepository->update($id, $data);
    }

    public function deleteVehicle($id)
    {
        return $this->vehicleRepository->delete($id);
    }

    public function findByPlate($plate)
    {
        return $this->vehicleRepository->findByPlate($plate);
    }

    public function findWithRelations($id)
    {
        return $this->vehicleRepository->findWithVehicles($id);
    }

    public function countVehicles()
    {
        return $this->vehicleRepository->count();
    }
}
