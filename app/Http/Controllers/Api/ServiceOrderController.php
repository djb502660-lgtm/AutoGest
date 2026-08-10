<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceOrder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ServiceOrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ServiceOrder::with(['vehicle', 'client', 'mechanic', 'advisor']);

        $user = $request->user();

        // Filtrar por rol del usuario
        if ($user->isClient()) {
            $query->where('client_id', $user->id);
        } elseif ($user->isMechanic()) {
            $query->where('mechanic_id', $user->id);
        } elseif ($user->isAdvisor()) {
            $query->where('advisor_id', $user->id);
        }

        $orders = $query->latest()->get();

        return response()->json([
            'orders' => $orders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'description' => $order->description,
                    'status' => $order->status,
                    'priority' => $order->priority,
                    'estimated_cost' => $order->estimated_cost,
                    'total_cost' => $order->total_cost,
                    'created_at' => $order->created_at->format('Y-m-d H:i'),
                    'vehicle' => [
                        'id' => $order->vehicle->id,
                        'plate' => $order->vehicle->plate,
                        'brand' => $order->vehicle->brand,
                        'model' => $order->vehicle->model,
                    ],
                    'client' => $order->client ? [
                        'id' => $order->client->id,
                        'name' => $order->client->name,
                    ] : null,
                    'mechanic' => $order->mechanic ? [
                        'id' => $order->mechanic->id,
                        'name' => $order->mechanic->name,
                    ] : null,
                    'advisor' => $order->advisor ? [
                        'id' => $order->advisor->id,
                        'name' => $order->advisor->name,
                    ] : null,
                ];
            }),
        ]);
    }

    public function show(Request $request, ServiceOrder $order): JsonResponse
    {
        $user = $request->user();

        // Verificar permisos
        if ($user->isClient() && $order->client_id !== $user->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        if ($user->isMechanic() && $order->mechanic_id !== $user->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        if ($user->isAdvisor() && $order->advisor_id !== $user->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $order->load(['vehicle', 'client', 'mechanic', 'advisor', 'comments', 'photos']);

        return response()->json([
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'description' => $order->description,
                'diagnosis' => $order->diagnosis,
                'recommendations' => $order->recommendations,
                'status' => $order->status,
                'priority' => $order->priority,
                'estimated_cost' => $order->estimated_cost,
                'total_cost' => $order->total_cost,
                'created_at' => $order->created_at->format('Y-m-d H:i'),
                'completed_at' => $order->completed_at?->format('Y-m-d H:i'),
                'vehicle' => [
                    'id' => $order->vehicle->id,
                    'plate' => $order->vehicle->plate,
                    'brand' => $order->vehicle->brand,
                    'model' => $order->vehicle->model,
                    'year' => $order->vehicle->year,
                ],
                'client' => $order->client ? [
                    'id' => $order->client->id,
                    'name' => $order->client->name,
                    'email' => $order->client->email,
                    'phone' => $order->client->phone,
                ] : null,
                'mechanic' => $order->mechanic ? [
                    'id' => $order->mechanic->id,
                    'name' => $order->mechanic->name,
                ] : null,
                'advisor' => $order->advisor ? [
                    'id' => $order->advisor->id,
                    'name' => $order->advisor->name,
                ] : null,
                'comments' => $order->comments->map(function ($comment) {
                    return [
                        'id' => $comment->id,
                        'comment' => $comment->comment,
                        'user' => [
                            'id' => $comment->user->id,
                            'name' => $comment->user->name,
                        ],
                        'created_at' => $comment->created_at->format('Y-m-d H:i'),
                    ];
                }),
                'photos' => $order->photos->map(function ($photo) {
                    return [
                        'id' => $photo->id,
                        'photo_path' => $photo->photo_path,
                        'photo_type' => $photo->photo_type,
                        'description' => $photo->description,
                        'created_at' => $photo->created_at->format('Y-m-d H:i'),
                    ];
                }),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'description' => 'required|string',
            'priority' => 'required|in:baja,media,alta',
            'estimated_cost' => 'nullable|numeric|min:0',
        ]);

        $order = ServiceOrder::create([
            'vehicle_id' => $request->vehicle_id,
            'client_id' => $request->user()->id,
            'description' => $request->description,
            'priority' => $request->priority,
            'estimated_cost' => $request->estimated_cost,
            'status' => 'pendiente',
            'order_number' => 'ORD-' . time() . rand(1000, 9999),
        ]);

        return response()->json([
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'description' => $order->description,
                'status' => $order->status,
            ],
            'message' => 'Orden de servicio creada exitosamente.',
        ], 201);
    }

    public function update(Request $request, ServiceOrder $order): JsonResponse
    {
        $user = $request->user();

        // Verificar permisos
        if ($user->isClient() && $order->client_id !== $user->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $request->validate([
            'description' => 'sometimes|required|string',
            'diagnosis' => 'nullable|string',
            'recommendations' => 'nullable|string',
            'priority' => 'sometimes|required|in:baja,media,alta',
            'estimated_cost' => 'nullable|numeric|min:0',
            'total_cost' => 'nullable|numeric|min:0',
        ]);

        $order->update($request->only([
            'description', 'diagnosis', 'recommendations',
            'priority', 'estimated_cost', 'total_cost'
        ]));

        return response()->json([
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'description' => $order->description,
                'status' => $order->status,
            ],
            'message' => 'Orden de servicio actualizada exitosamente.',
        ]);
    }

    public function updateStatus(Request $request, ServiceOrder $order): JsonResponse
    {
        $user = $request->user();

        // Solo mecánicos y asesores pueden actualizar el estado
        if (!$user->isMechanic() && !$user->isAdvisor() && !$user->isAdmin()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $request->validate([
            'status' => 'required|in:pendiente,en_proceso,completado,cancelado',
        ]);

        $order->status = $request->status;
        
        if ($request->status === 'completado') {
            $order->completed_at = now();
        }
        
        $order->save();

        return response()->json([
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'completed_at' => $order->completed_at?->format('Y-m-d H:i'),
            ],
            'message' => 'Estado de la orden actualizado exitosamente.',
        ]);
    }
}