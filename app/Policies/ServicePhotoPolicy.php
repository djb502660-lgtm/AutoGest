<?php

namespace App\Policies;

use App\Models\ServicePhoto;
use App\Models\User;

class ServicePhotoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin()
            || $user->isAdvisor()
            || $user->isMechanic()
            || $user->isClient();
    }

    public function view(User $user, ServicePhoto $photo): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isClient()) {
            // Cliente solo puede ver fotos de sus propias órdenes
            return $photo->serviceOrder->client_id === $user->id;
        }

        if ($user->isAdvisor()) {
            // Asesor puede ver fotos de órdenes que gestiona
            return $photo->serviceOrder->advisor_id === $user->id
                || $photo->serviceOrder->created_by === $user->id;
        }

        if ($user->isMechanic()) {
            // Mecánico puede ver fotos de órdenes asignadas
            return $photo->serviceOrder->mechanic_id === $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        // Cliente no puede crear fotos
        if ($user->isClient()) {
            return false;
        }

        // Asesor puede crear fotos (opcional)
        if ($user->isAdvisor()) {
            return true;
        }

        // Mecánico puede crear fotos (obligatorio)
        if ($user->isMechanic()) {
            return true;
        }

        // Admin puede crear fotos
        return $user->isAdmin();
    }

    public function delete(User $user, ServicePhoto $photo): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Solo el creador puede eliminar (además de admin)
        if ($photo->user_id === $user->id) {
            return true;
        }

        return false;
    }
}
