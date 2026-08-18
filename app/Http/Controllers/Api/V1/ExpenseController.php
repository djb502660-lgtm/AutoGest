<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Maintenance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ExpenseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->isClient() && ! $user->isAdmin()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $vehicleIds = $user->vehicles()->pluck('id');
        $since = now()->subMonths(12);

        $maintenances = Maintenance::query()
            ->whereIn('vehicle_id', $vehicleIds)
            ->where('status', 'completado')
            ->where('performed_at', '>=', $since)
            ->get();

        $categories = [
            'Cambio de aceite' => 0,
            'Frenos' => 0,
            'Revisiones' => 0,
            'Otros' => 0,
        ];

        foreach ($maintenances as $maintenance) {
            $desc = Str::lower((string) $maintenance->description);
            if (Str::contains($desc, ['aceite', 'oil'])) {
                $categories['Cambio de aceite'] += $maintenance->cost;
            } elseif (Str::contains($desc, ['freno', 'pastilla', 'disco'])) {
                $categories['Frenos'] += $maintenance->cost;
            } elseif ($maintenance->type === 'preventivo' || Str::contains($desc, ['revisión', 'revision', 'general'])) {
                $categories['Revisiones'] += $maintenance->cost;
            } else {
                $categories['Otros'] += $maintenance->cost;
            }
        }

        $recent = Maintenance::with('vehicle')
            ->whereIn('vehicle_id', $vehicleIds)
            ->where('status', 'completado')
            ->where('performed_at', '>=', $since)
            ->orderByDesc('performed_at')
            ->take(10)
            ->get();

        return response()->json([
            'total' => $maintenances->sum('cost'),
            'categories' => collect($categories)->map(fn ($amount, $name) => [
                'name' => $name,
                'amount' => $amount,
            ])->values(),
            'recent' => $recent->map(fn (Maintenance $item) => [
                'id' => $item->id,
                'description' => $item->description,
                'cost' => $item->cost,
                'performed_at' => $item->performed_at?->format('Y-m-d'),
                'vehicle' => $item->vehicle?->only(['id', 'plate', 'brand', 'model']),
            ]),
        ]);
    }
}
