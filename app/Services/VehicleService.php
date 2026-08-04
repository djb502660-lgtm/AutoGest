<?php

namespace App\Services;

use App\Contracts\Repositories\VehicleRepositoryInterface;
use App\DTOs\VehicleDTO;

class VehicleService
{
    public function __construct(
        protected VehicleRepositoryInterface $vehicleRepository
    ) {}

    public function getClientVehicles($clientId)
    {
        return $this->vehicleRepository->findByClient($clientId);
    }

    public function createVehicle(VehicleDTO $dto)
    {
        return $this->vehicleRepository->create($dto->toArray());
    }

    public function updateVehicle($id, VehicleDTO $dto)
    {
        return $this->vehicleRepository->update($id, $dto->toArray());
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
