<?php

namespace App\Policies;

use App\Models\ServiceOrder;
use App\Models\User;

class ServiceOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isMechanic() || $user->isClient();
    }

    public function view(User $user, ServiceOrder $order): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isMechanic()) {
            return $order->mechanic_id === $user->id;
        }

        return $user->isClient() && $order->client_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, ServiceOrder $order): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isMechanic() && $order->mechanic_id === $user->id;
    }

    public function delete(User $user, ServiceOrder $order): bool
    {
        return $user->isAdmin();
    }

    public function assign(User $user, ServiceOrder $order): bool
    {
        return $user->isAdmin();
    }
}
