<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Collection;

interface ServiceOrderRepositoryInterface
{
    public function find($id);
    public function all(): Collection;
    public function paginate($perPage = 15);
    public function create(array $data);
    public function update($id, array $data): bool;
    public function delete($id): bool;
    public function findByStatus(string $status): Collection;
    public function findByMechanic(int $mechanicId): Collection;
    public function findByVehicle(int $vehicleId): Collection;
    public function findWithRelations($id);
}
