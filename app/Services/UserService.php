<?php

namespace App\Services;

use App\Contracts\Repositories\UserRepositoryInterface;

class UserService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {}

    public function createUser(array $data)
    {
        return $this->userRepository->create($data);
    }

    public function updateUser($id, array $data)
    {
        return $this->userRepository->update($id, $data);
    }

    public function deleteUser($id)
    {
        return $this->userRepository->delete($id);
    }

    public function findByRole($role)
    {
        return $this->userRepository->findByRole($role);
    }

    public function findActiveUsers()
    {
        return $this->userRepository->findActiveUsers();
    }

    public function findByEmail($email)
    {
        return $this->userRepository->findByEmail($email);
    }

    public function findWithVehicles($id)
    {
        return $this->userRepository->findWithVehicles($id);
    }

    public function countUsers()
    {
        return $this->userRepository->count();
    }
}
