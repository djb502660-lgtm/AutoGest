<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Collection;

interface MaintenanceRepositoryInterface
{
    public function find($id);
    public function all(): Collection;
    public function paginate($perPage = 15);
    public function create(array $data);
    public function update($id, array $data): bool;
    public function delete($id): bool;
    public function findByServiceOrder(int $serviceOrderId): Collection;
    public function findByVehicle(int $vehicleId): Collection;
    public function getRecentMaintenances(int $limit = 10): Collection;
}
