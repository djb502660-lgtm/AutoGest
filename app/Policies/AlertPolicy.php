<?php

namespace App\Policies;

use App\Models\Alert;
use App\Models\User;

class AlertPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Alert $alert): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $alert->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Alert $alert): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $alert->user_id === $user->id;
    }

    public function delete(User $user, Alert $alert): bool
    {
        return $user->isAdmin();
    }

    public function resolve(User $user, Alert $alert): bool
    {
        return $user->isAdmin();
    }
}
