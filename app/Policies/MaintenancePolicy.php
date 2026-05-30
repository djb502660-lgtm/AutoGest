<?php

namespace App\Policies;

use App\Models\Maintenance;
use App\Models\User;

class MaintenancePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isMechanic() || $user->isClient();
    }

    public function view(User $user, Maintenance $maintenance): bool
    {
        if ($user->isAdmin() || $user->isMechanic()) {
            return $user->isAdmin() || $maintenance->mechanic_id === $user->id;
        }

        return $user->isClient()
            && $maintenance->vehicle?->client_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isMechanic();
    }

    public function update(User $user, Maintenance $maintenance): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isMechanic() && $maintenance->mechanic_id === $user->id;
    }

    public function delete(User $user, Maintenance $maintenance): bool
    {
        return $user->isAdmin();
    }
}
