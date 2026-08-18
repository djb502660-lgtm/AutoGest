<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Models\ServiceOrder;
use App\Models\User;
use Illuminate\Http\JsonResponse;

trait AuthorizesOrders
{
    protected function denyUnlessCanView(User $user, ServiceOrder $order): ?JsonResponse
    {
        if ($user->cannot('view', $order)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        return null;
    }

    protected function denyUnlessCanUpdate(User $user, ServiceOrder $order): ?JsonResponse
    {
        if ($user->cannot('update', $order)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        return null;
    }
}
