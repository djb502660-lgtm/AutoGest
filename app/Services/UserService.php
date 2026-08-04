<?php

namespace App\Services;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\DTOs\UserDTO;

class UserService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {}

    public function createUser(UserDTO $dto)
    {
        return $this->userRepository->create($dto->toArray());
    }

    public function updateUser($id, UserDTO $dto)
    {
        return $this->userRepository->update($id, $dto->toArray());
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
