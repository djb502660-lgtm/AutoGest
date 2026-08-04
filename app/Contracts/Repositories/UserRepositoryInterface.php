<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface
{
    public function find($id);
    public function all(): Collection;
    public function paginate($perPage = 15);
    public function create(array $data);
    public function update($id, array $data): bool;
    public function delete($id): bool;
    public function findByRole(string $role): Collection;
    public function findActiveUsers(): Collection;
    public function findByEmail(string $email);
    public function findWithVehicles($id);
}
