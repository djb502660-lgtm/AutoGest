<?php

namespace App\Services;

use App\Contracts\Repositories\VehicleRepositoryInterface;
use App\DTOs\VehicleDTO;

class VehicleService
{
    public function __construct(
        protected VehicleRepositoryInterface $vehicleRepository,
        protected AuditService $auditService
    ) {}

    public function getClientVehiclesPaginated($userId, $search = null, $perPage = 10)
    {
        return $this->vehicleRepository->getClientVehicles($userId);
    }

    public function getClientVehicles($clientId)
    {
        return $this->vehicleRepository->findByClient($clientId);
    }

    public function createVehicle(VehicleDTO $dto)
    {
        $vehicle = $this->vehicleRepository->create($dto->toArray());

        $this->auditService->logVehicleAction(
            'vehicle_created',
            "Vehículo {$vehicle->plate} registrado para cliente {$vehicle->client_id}",
            auth()->id(),
            null,
            ['id' => $vehicle->id, 'plate' => $vehicle->plate, 'client_id' => $vehicle->client_id]
        );

        return $vehicle;
    }

    public function updateVehicle($id, VehicleDTO $dto)
    {
        $vehicle = $this->vehicleRepository->find($id);
        $oldValues = $vehicle->toArray();

        $result = $this->vehicleRepository->update($id, $dto->toArray());

        if ($result) {
            $this->auditService->logVehicleAction(
                'vehicle_updated',
                "Vehículo {$vehicle->plate} actualizado",
                auth()->id(),
                $oldValues,
                $dto->toArray()
            );
        }

        return $result;
    }

    public function deleteVehicle($id)
    {
        $vehicle = $this->vehicleRepository->find($id);

        $result = $this->vehicleRepository->delete($id);

        if ($result) {
            $this->auditService->logVehicleAction(
                'vehicle_deleted',
                "Vehículo {$vehicle->plate} eliminado",
                auth()->id(),
                ['id' => $vehicle->id, 'plate' => $vehicle->plate, 'client_id' => $vehicle->client_id],
                null
            );
        }

        return $result;
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

    // Métodos de conveniencia para consultas de referencia
    public function getActiveVehicles()
    {
        return Vehicle::where('status', 'activo')->orderBy('plate')->get();
    }
}
