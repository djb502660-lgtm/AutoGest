<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Vehicle::with('client');

        // Filtrar por rol del usuario
        $user = $request->user();

        if ($user->isClient()) {
            $query->where('client_id', $user->id);
        } elseif ($user->isMechanic()) {
            $query->whereIn('id', $user->accessibleVehicleIds());
        }

        $vehicles = $query->get();

        return response()->json([
            'vehicles' => $vehicles->map(function ($vehicle) {
                return [
                    'id' => $vehicle->id,
                    'plate' => $vehicle->plate,
                    'brand' => $vehicle->brand,
                    'model' => $vehicle->model,
                    'year' => $vehicle->year,
                    'color' => $vehicle->color,
                    'mileage' => $vehicle->mileage,
                    'status' => $vehicle->status,
                    'client' => $vehicle->client ? [
                        'id' => $vehicle->client->id,
                        'name' => $vehicle->client->name,
                    ] : null,
                ];
            }),
        ]);
    }

    public function show(Request $request, Vehicle $vehicle): JsonResponse
    {
        $user = $request->user();

        // Verificar permisos
        if ($user->isClient() && $vehicle->client_id !== $user->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        if ($user->isMechanic() && ! $user->accessibleVehicleIds()->contains($vehicle->id)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $vehicle->load(['client', 'serviceOrders', 'maintenances']);

        return response()->json([
            'vehicle' => [
                'id' => $vehicle->id,
                'plate' => $vehicle->plate,
                'brand' => $vehicle->brand,
                'model' => $vehicle->model,
                'sub_model' => $vehicle->sub_model,
                'year' => $vehicle->year,
                'color' => $vehicle->color,
                'mileage' => $vehicle->mileage,
                'status' => $vehicle->status,
                'vin' => $vehicle->vin,
                'engine_number' => $vehicle->engine_number,
                'transmission_type' => $vehicle->transmission_type,
                'client' => $vehicle->client ? [
                    'id' => $vehicle->client->id,
                    'name' => $vehicle->client->name,
                    'email' => $vehicle->client->email,
                    'phone' => $vehicle->client->phone,
                ] : null,
                'service_orders_count' => $vehicle->serviceOrders->count(),
                'maintenances_count' => $vehicle->maintenances->count(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'plate' => 'required|string|max:20|unique:vehicles',
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'sub_model' => 'nullable|string|max:100',
            'year' => 'required|integer|min:1900|max:'.(date('Y') + 1),
            'color' => 'nullable|string|max:50',
            'mileage' => 'required|integer|min:0',
            'vin' => 'nullable|string|max:50|unique:vehicles',
            'engine_number' => 'nullable|string|max:50',
            'transmission_type' => 'nullable|string|max:50',
        ]);

        $vehicle = Vehicle::create([
            'plate' => $request->plate,
            'brand' => $request->brand,
            'model' => $request->model,
            'sub_model' => $request->sub_model,
            'year' => $request->year,
            'color' => $request->color,
            'mileage' => $request->mileage,
            'vin' => $request->vin,
            'engine_number' => $request->engine_number,
            'transmission_type' => $request->transmission_type,
            'client_id' => $request->user()->id,
            'status' => 'activo',
        ]);

        return response()->json([
            'vehicle' => [
                'id' => $vehicle->id,
                'plate' => $vehicle->plate,
                'brand' => $vehicle->brand,
                'model' => $vehicle->model,
                'year' => $vehicle->year,
            ],
            'message' => 'Vehículo creado exitosamente.',
        ], 201);
    }

    public function update(Request $request, Vehicle $vehicle): JsonResponse
    {
        $user = $request->user();

        // Verificar permisos
        if ($user->isClient() && $vehicle->client_id !== $user->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $request->validate([
            'plate' => 'sometimes|required|string|max:20|unique:vehicles,plate,'.$vehicle->id,
            'brand' => 'sometimes|required|string|max:100',
            'model' => 'sometimes|required|string|max:100',
            'sub_model' => 'nullable|string|max:100',
            'year' => 'sometimes|required|integer|min:1900|max:'.(date('Y') + 1),
            'color' => 'nullable|string|max:50',
            'mileage' => 'sometimes|required|integer|min:0',
            'vin' => 'nullable|string|max:50|unique:vehicles,vin,'.$vehicle->id,
            'engine_number' => 'nullable|string|max:50',
            'transmission_type' => 'nullable|string|max:50',
        ]);

        $vehicle->update($request->only([
            'plate', 'brand', 'model', 'sub_model', 'year', 'color',
            'mileage', 'vin', 'engine_number', 'transmission_type',
        ]));

        return response()->json([
            'vehicle' => [
                'id' => $vehicle->id,
                'plate' => $vehicle->plate,
                'brand' => $vehicle->brand,
                'model' => $vehicle->model,
                'year' => $vehicle->year,
            ],
            'message' => 'Vehículo actualizado exitosamente.',
        ]);
    }
}
