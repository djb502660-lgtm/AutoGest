<?php

namespace App\Contracts\Repositories;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    public function findByRole($role);

    public function findActiveUsers();

    public function findByEmail($email);

    public function findWithVehicles($id);
}
