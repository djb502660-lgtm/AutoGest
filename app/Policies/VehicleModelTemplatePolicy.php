<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VehicleModelTemplate;

class VehicleModelTemplatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, VehicleModelTemplate $template): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, VehicleModelTemplate $template): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, VehicleModelTemplate $template): bool
    {
        return $user->isAdmin();
    }
}
