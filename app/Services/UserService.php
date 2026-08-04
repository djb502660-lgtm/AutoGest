<?php

namespace App\Services;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\DTOs\UserDTO;

class UserService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected AuditService $auditService
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

    public function updateStatus($id, $status)
    {
        return $this->userRepository->update($id, ['status' => $status]);
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

    public function getUsersPaginated($search = null, $role = null, $status = null, $perPage = 10)
    {
        // Esta función será implementada por el repository
        // Por ahora, delegamos a una consulta directa que se migrará después
        $query = \App\Models\User::query();

        if ($search !== null && $search !== '') {
            $query = $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role !== null && $role !== '') {
            $query = $query->where('role', $role);
        }

        if ($status !== null && $status !== '') {
            $query = $query->where('status', $status);
        }

        return $query->orderBy('name')->paginate($perPage);
    }

    public function updateUserStatus($userId, $status)
    {
        $user = $this->userRepository->find($userId);
        $oldStatus = $user->status;

        $result = $this->userRepository->update($userId, ['status' => $status]);

        if ($result) {
            $this->auditService->logUserAction(
                'update_status',
                "Usuario {$user->email} cambió estado de {$oldStatus} a {$status}",
                auth()->id(),
                ['status' => $oldStatus],
                ['status' => $status]
            );
        }

        return $result;
    }

    public function updateUserRole($userId, $role)
    {
        $user = $this->userRepository->find($userId);
        $oldRole = $user->role;

        $result = $this->userRepository->update($userId, ['role' => $role]);

        if ($result) {
            $this->auditService->logUserAction(
                'update_role',
                "Usuario {$user->email} cambió rol de {$oldRole} a {$role}",
                auth()->id(),
                ['role' => $oldRole],
                ['role' => $role]
            );
        }

        return $result;
    }

    public function deactivateUserWithRelations($userId)
    {
        return $this->userRepository->update($userId, ['status' => 'inactivo']);
    }
}
