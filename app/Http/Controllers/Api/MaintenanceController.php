<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Maintenance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Maintenance::with(['vehicle', 'mechanic']);

        $user = $request->user();

        // Filtrar por rol del usuario
        if ($user->isClient()) {
            $query->whereHas('vehicle', function ($q) use ($user) {
                $q->where('client_id', $user->id);
            });
        } elseif ($user->isMechanic()) {
            $query->where('mechanic_id', $user->id);
        }

        $maintenances = $query->latest('performed_at')->get();

        return response()->json([
            'maintenances' => $maintenances->map(function ($maintenance) {
                return [
                    'id' => $maintenance->id,
                    'type' => $maintenance->type,
                    'description' => $maintenance->description,
                    'status' => $maintenance->status,
                    'cost' => $maintenance->cost,
                    'performed_at' => $maintenance->performed_at?->format('Y-m-d H:i'),
                    'vehicle' => [
                        'id' => $maintenance->vehicle->id,
                        'plate' => $maintenance->vehicle->plate,
                        'brand' => $maintenance->vehicle->brand,
                        'model' => $maintenance->vehicle->model,
                    ],
                    'mechanic' => $maintenance->mechanic ? [
                        'id' => $maintenance->mechanic->id,
                        'name' => $maintenance->mechanic->name,
                    ] : null,
                ];
            }),
        ]);
    }

    public function show(Request $request, Maintenance $maintenance): JsonResponse
    {
        $user = $request->user();

        // Verificar permisos
        if ($user->isClient() && $maintenance->vehicle->client_id !== $user->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        if ($user->isMechanic() && $maintenance->mechanic_id !== $user->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $maintenance->load(['vehicle', 'mechanic', 'serviceOrder']);

        return response()->json([
            'maintenance' => [
                'id' => $maintenance->id,
                'type' => $maintenance->type,
                'description' => $maintenance->description,
                'status' => $maintenance->status,
                'cost' => $maintenance->cost,
                'performed_at' => $maintenance->performed_at?->format('Y-m-d H:i'),
                'vehicle' => [
                    'id' => $maintenance->vehicle->id,
                    'plate' => $maintenance->vehicle->plate,
                    'brand' => $maintenance->vehicle->brand,
                    'model' => $maintenance->vehicle->model,
                    'year' => $maintenance->vehicle->year,
                ],
                'mechanic' => $maintenance->mechanic ? [
                    'id' => $maintenance->mechanic->id,
                    'name' => $maintenance->mechanic->name,
                ] : null,
                'service_order' => $maintenance->serviceOrder ? [
                    'id' => $maintenance->serviceOrder->id,
                    'order_number' => $maintenance->serviceOrder->order_number,
                ] : null,
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'type' => 'required|in:preventivo,correctivo,garantia',
            'description' => 'required|string',
            'cost' => 'required|numeric|min:0',
            'performed_at' => 'required|date',
        ]);

        $maintenance = Maintenance::create([
            'vehicle_id' => $request->vehicle_id,
            'mechanic_id' => $request->user()->id,
            'type' => $request->type,
            'description' => $request->description,
            'cost' => $request->cost,
            'performed_at' => $request->performed_at,
            'status' => 'completado',
        ]);

        return response()->json([
            'maintenance' => [
                'id' => $maintenance->id,
                'type' => $maintenance->type,
                'description' => $maintenance->description,
                'status' => $maintenance->status,
            ],
            'message' => 'Mantenimiento registrado exitosamente.',
        ], 201);
    }

    public function update(Request $request, Maintenance $maintenance): JsonResponse
    {
        $user = $request->user();

        // Verificar permisos
        if ($user->isMechanic() && $maintenance->mechanic_id !== $user->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $request->validate([
            'type' => 'sometimes|required|in:preventivo,correctivo,garantia',
            'description' => 'sometimes|required|string',
            'cost' => 'sometimes|required|numeric|min:0',
            'performed_at' => 'sometimes|required|date',
            'status' => 'sometimes|required|in:pendiente,en_proceso,completado',
        ]);

        $maintenance->update($request->only([
            'type', 'description', 'cost', 'performed_at', 'status',
        ]));

        return response()->json([
            'maintenance' => [
                'id' => $maintenance->id,
                'type' => $maintenance->type,
                'description' => $maintenance->description,
                'status' => $maintenance->status,
            ],
            'message' => 'Mantenimiento actualizado exitosamente.',
        ]);
    }
}
