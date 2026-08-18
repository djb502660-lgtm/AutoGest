<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\AuthorizesOrders;
use App\Http\Controllers\Api\Concerns\SerializesMobileModels;
use App\Http\Controllers\Controller;
use App\Models\ServiceOrder;
use App\Services\ServiceOrderService;
use App\Services\ServicePhotoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServiceOrderController extends Controller
{
    use AuthorizesOrders;
    use SerializesMobileModels;

    public function __construct(
        private ServiceOrderService $serviceOrderService,
        private ServicePhotoService $servicePhotoService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = ServiceOrder::with(['vehicle', 'client', 'mechanic', 'advisor']);
        $user = $request->user();

        if ($user->isClient()) {
            $query->where('client_id', $user->id);
        } elseif ($user->isMechanic()) {
            $query->where('mechanic_id', $user->id);
        } elseif ($user->isAdvisor()) {
            $query->where(function ($q) use ($user) {
                $q->where('advisor_id', $user->id)
                    ->orWhere('created_by', $user->id)
                    ->orWhereNull('advisor_id')
                    ->orWhere('source', 'chatbot');
            });
        }

        $orders = $query->latest()->limit(50)->get();

        return response()->json([
            'orders' => $orders->map(fn ($order) => $this->orderSummary($order))->values(),
        ]);
    }

    public function show(Request $request, ServiceOrder $order): JsonResponse
    {
        if ($denied = $this->denyUnlessCanView($request->user(), $order)) {
            return $denied;
        }

        $order->load(['vehicle', 'client', 'mechanic', 'advisor', 'comments.user', 'photos.user']);

        return response()->json([
            'order' => array_merge($this->orderSummary($order), [
                'diagnosis' => $order->diagnosis,
                'recommendations' => $order->recommendations,
                'completed_at' => $order->completed_at?->format('Y-m-d H:i'),
                'client' => $order->client ? [
                    'id' => $order->client->id,
                    'name' => $order->client->name,
                    'email' => $order->client->email,
                    'phone' => $order->client->phone,
                ] : null,
                'comments' => $order->comments->map(fn ($comment) => [
                    'id' => $comment->id,
                    'comment' => $comment->comment,
                    'user' => [
                        'id' => $comment->user?->id,
                        'name' => $comment->user?->name,
                    ],
                    'created_at' => $comment->created_at?->format('Y-m-d H:i'),
                ])->values(),
                'photos' => $order->photos->map(fn ($photo) => $this->photoPayload($photo))->values(),
            ]),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->cannot('create', ServiceOrder::class) && ! $user->isClient()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'description' => 'required|string',
            'priority' => 'nullable|in:baja,normal,media,alta,urgente',
            'estimated_cost' => 'nullable|numeric|min:0',
            'mechanic_id' => 'nullable|exists:users,id',
            'client_id' => 'nullable|exists:users,id',
        ]);

        $order = ServiceOrder::create([
            'vehicle_id' => $request->vehicle_id,
            'client_id' => $user->isClient() ? $user->id : ($request->integer('client_id') ?: $user->id),
            'advisor_id' => $user->isAdvisor() ? $user->id : null,
            'created_by' => $user->id,
            'mechanic_id' => $request->mechanic_id,
            'description' => $request->description,
            'priority' => $request->priority ?: 'normal',
            'estimated_cost' => $request->estimated_cost,
            'status' => 'recibida',
            'progress' => 0,
            'source' => 'manual',
            'order_number' => ServiceOrder::generateOrderNumber(),
        ]);

        return response()->json([
            'order' => $this->orderSummary($order->load(['vehicle', 'client', 'mechanic', 'advisor'])),
            'message' => 'Orden de servicio creada exitosamente.',
        ], 201);
    }

    public function update(Request $request, ServiceOrder $order): JsonResponse
    {
        if ($denied = $this->denyUnlessCanUpdate($request->user(), $order)) {
            return $denied;
        }

        $request->validate([
            'description' => 'sometimes|required|string',
            'diagnosis' => 'nullable|string',
            'recommendations' => 'nullable|string',
            'priority' => 'sometimes|required|in:baja,normal,media,alta,urgente',
            'estimated_cost' => 'nullable|numeric|min:0',
            'total_cost' => 'nullable|numeric|min:0',
            'mechanic_id' => 'nullable|exists:users,id',
        ]);

        $order->update($request->only([
            'description', 'diagnosis', 'recommendations',
            'priority', 'estimated_cost', 'total_cost', 'mechanic_id',
        ]));

        return response()->json([
            'order' => $this->orderSummary($order->fresh(['vehicle', 'client', 'mechanic', 'advisor'])),
            'message' => 'Orden de servicio actualizada exitosamente.',
        ]);
    }

    public function updateStatus(Request $request, ServiceOrder $order): JsonResponse
    {
        if ($denied = $this->denyUnlessCanUpdate($request->user(), $order)) {
            return $denied;
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(['recibida', 'en_proceso', 'completada', 'entregada', 'cancelada'])],
            'progress' => ['nullable', 'integer', 'min:0', 'max:100'],
            'diagnosis' => ['nullable', 'string'],
            'recommendations' => ['nullable', 'string'],
        ]);

        if (in_array($validated['status'], ['completada', 'entregada'], true)) {
            $photoValidation = $this->servicePhotoService->validatePhotoRequirements($order, $validated['status']);
            if (! $photoValidation['valid']) {
                return response()->json(['message' => $photoValidation['message']], 422);
            }
        }

        $progress = $validated['progress'] ?? $order->progress ?? 0;

        $this->serviceOrderService->updateOrderStatusWithDetails(
            $order->id,
            $validated['status'],
            $progress,
            $validated['diagnosis'] ?? null,
            $validated['recommendations'] ?? null,
        );

        $order->refresh();

        return response()->json([
            'order' => $this->orderSummary($order),
            'message' => 'Estado de la orden actualizado exitosamente.',
        ]);
    }
}
