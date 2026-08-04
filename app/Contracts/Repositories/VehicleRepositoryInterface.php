<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Collection;

interface VehicleRepositoryInterface
{
    public function find($id);
    public function all(): Collection;
    public function paginate($perPage = 15);
    public function create(array $data);
    public function update($id, array $data): bool;
    public function delete($id): bool;
    public function findByPlate(string $plate);
    public function findByClient(int $clientId): Collection;
    public function findWithVehicles($id);
}
