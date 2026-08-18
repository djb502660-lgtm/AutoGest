<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $users = User::with('vehicles')->get();

        return response()->json([
            'users' => $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role->value,
                    'phone' => $user->phone,
                    'status' => $user->status,
                    'vehicles_count' => $user->vehicles->count(),
                    'created_at' => $user->created_at->format('Y-m-d H:i'),
                ];
            }),
        ]);
    }

    public function show(Request $request, User $user): JsonResponse
    {
        $user->load(['vehicles', 'assignedOrders', 'maintenances']);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->value,
                'phone' => $user->phone,
                'status' => $user->status,
                'last_login_at' => $user->last_login_at?->format('Y-m-d H:i'),
                'created_at' => $user->created_at->format('Y-m-d H:i'),
                'vehicles' => $user->vehicles->map(function ($vehicle) {
                    return [
                        'id' => $vehicle->id,
                        'plate' => $vehicle->plate,
                        'brand' => $vehicle->brand,
                        'model' => $vehicle->model,
                        'year' => $vehicle->year,
                    ];
                }),
                'assigned_orders_count' => $user->assignedOrders->count(),
                'maintenances_count' => $user->maintenances->count(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,asesor,mecanico,cliente',
            'phone' => 'nullable|string|max:20',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role,
            'phone' => $request->phone,
            'status' => 'activo',
        ]);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->value,
                'status' => $user->status,
            ],
            'message' => 'Usuario creado exitosamente.',
        ], 201);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,'.$user->id,
            'role' => 'sometimes|required|in:admin,asesor,mecanico,cliente',
            'phone' => 'nullable|string|max:20',
            'status' => 'sometimes|required|in:activo,inactivo',
            'password' => 'nullable|string|min:8',
        ]);

        $user->update($request->only([
            'name', 'email', 'role', 'phone', 'status',
        ]));

        if ($request->has('password')) {
            $user->password = bcrypt($request->password);
            $user->save();
        }

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->value,
                'status' => $user->status,
            ],
            'message' => 'Usuario actualizado exitosamente.',
        ]);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        $user->delete();

        return response()->json([
            'message' => 'Usuario eliminado exitosamente.',
        ]);
    }
}
