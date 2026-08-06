<?php

namespace App\Services;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\DTOs\UserDTO;
use App\Models\User;

class UserService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected AuditService $auditService
    ) {}

    public function createUser(UserDTO $dto)
    {
        $user = $this->userRepository->create($dto->toArray());

        $roleValue = $user->role instanceof \BackedEnum ? $user->role->value : (string) $user->role;

        $this->auditService->logUserAction(
            'user_created',
            "Usuario {$user->email} creado con rol {$roleValue}",
            auth()->id(),
            null,
            ['id' => $user->id, 'email' => $user->email, 'role' => $roleValue, 'status' => $user->status]
        );

        return $user;
    }

    public function updateUser($id, UserDTO $dto)
    {
        $user = $this->userRepository->find($id);
        $oldValues = $user->toArray();

        $result = $this->userRepository->update($id, $dto->toArray());

        if ($result) {
            $this->auditService->logUserAction(
                'user_updated',
                "Usuario {$user->email} actualizado",
                auth()->id(),
                $oldValues,
                $dto->toArray()
            );
        }

        return $result;
    }

    public function deleteUser($id)
    {
        $user = $this->userRepository->find($id);

        $result = $this->userRepository->delete($id);

        if ($result) {
            $deletedRole = $user->role instanceof \BackedEnum ? $user->role->value : (string) $user->role;

            $this->auditService->logUserAction(
                'user_deleted',
                "Usuario {$user->email} eliminado",
                auth()->id(),
                ['id' => $user->id, 'email' => $user->email, 'role' => $deletedRole],
                null
            );
        }

        return $result;
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
        $query = User::query();

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
            $action = $status === 'inactivo' ? 'user_disabled' : 'update_status';
            $description = $status === 'inactivo'
                ? "Usuario {$user->email} desactivado"
                : "Usuario {$user->email} cambió estado de {$oldStatus} a {$status}";

            $this->auditService->logUserAction(
                $action,
                $description,
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
            $oldRoleValue = $oldRole instanceof \BackedEnum ? $oldRole->value : (string) $oldRole;
            $newRoleValue = $role instanceof \BackedEnum ? $role->value : (string) $role;

            $this->auditService->logUserAction(
                'update_role',
                "Usuario {$user->email} cambió rol de {$oldRoleValue} a {$newRoleValue}",
                auth()->id(),
                ['role' => $oldRoleValue],
                ['role' => $newRoleValue]
            );
        }

        return $result;
    }

    public function deactivateUserWithRelations($userId)
    {
        return $this->userRepository->update($userId, ['status' => 'inactivo']);
    }

    // Métodos de conveniencia para consultas de referencia
    public function getActiveClients()
    {
        return User::where('role', 'cliente')->where('status', 'activo')->orderBy('name')->get();
    }

    public function getActiveMechanics()
    {
        return User::where('role', 'mecanico')->where('status', 'activo')->orderBy('name')->get();
    }

    public function getActiveAdvisors()
    {
        return User::where('role', 'asesor')->where('status', 'activo')->orderBy('name')->get();
    }

    public function getNonClientUsers()
    {
        return User::where('role', '!=', 'cliente')->orderBy('name')->get();
    }
}
