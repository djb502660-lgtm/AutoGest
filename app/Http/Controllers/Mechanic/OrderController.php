<?php

namespace App\Http\Controllers\Mechanic;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\OrderComment;
use App\Models\ServiceOrder;
use App\Services\ServiceOrderService;
use App\Services\ServicePhotoService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    private $serviceOrderService;

    private $servicePhotoService;

    public function __construct(ServiceOrderService $serviceOrderService, ServicePhotoService $servicePhotoService)
    {
        $this->serviceOrderService = $serviceOrderService;
        $this->servicePhotoService = $servicePhotoService;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $search = $request->string('search')->trim();
        $status = $request->string('status')->toString();
        $priority = $request->string('priority')->toString();

        $orders = $user->assignedOrders()
            ->with('vehicle', 'client')
            ->when($search->isNotEmpty(), function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('order_number', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('vehicle', fn ($v) => $v->where('plate', 'like', "%{$search}%"));
                });
            })
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->when($priority !== '', fn ($q) => $q->where('priority', $priority))
            ->when($priority === 'alta' || $priority === 'urgente', fn ($q) => $q->orderByRaw("FIELD(priority, 'urgente', 'alta', 'normal', 'baja')"))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('mechanic.orders.index', compact('orders', 'search', 'status', 'priority'));
    }

    public function show(ServiceOrder $order)
    {
        $this->authorize('view', $order);

        $order->load(['vehicle.client', 'client', 'maintenances', 'comments.user']);

        // Cargar resumen de fotos para el diagnóstico (Sprint 5A.3)
        $photoSummary = $this->servicePhotoService->getPhotoSummary($order);
        $photos = $this->servicePhotoService->getPhotosByOrder($order);

        return view('mechanic.orders.show', compact('order', 'photoSummary', 'photos'));
    }

    public function updateStatus(Request $request, ServiceOrder $order)
    {
        $this->authorize('update', $order);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['recibida', 'en_proceso', 'completada', 'entregada'])],
            'progress' => ['required', 'integer', 'min:0', 'max:100'],
            'diagnosis' => ['nullable', 'string'],
            'recommendations' => ['nullable', 'string'],
            'completed_at' => ['nullable', 'date'],
        ]);

        // Validar requisitos fotográficos antes de finalizar (Sprint 5A.3)
        if (in_array($validated['status'], ['completada', 'entregada'])) {
            $photoValidation = $this->servicePhotoService->validatePhotoRequirements($order, $validated['status']);
            if (! $photoValidation['valid']) {
                return redirect()
                    ->route('mechanic.orders.show', $order)
                    ->with('error', $photoValidation['message']);
            }
        }

        $this->serviceOrderService->updateOrderStatusWithDetails(
            $order->id,
            $validated['status'],
            $validated['progress'],
            $validated['diagnosis'] ?? null,
            $validated['recommendations'] ?? null,
            $validated['completed_at'] ?? null
        );

        ActivityLog::record(
            'order.status_updated',
            "Mecánico actualizó orden {$order->order_number} a {$order->statusLabel()}.",
            $order,
        );

        return redirect()
            ->route('mechanic.orders.show', $order)
            ->with('success', 'Estado de la orden actualizado.');
    }

    public function storeComment(Request $request, ServiceOrder $order)
    {
        $this->authorize('update', $order);

        $validated = $request->validate([
            'comment' => ['required', 'string', 'max:2000'],
        ]);

        OrderComment::create([
            'service_order_id' => $order->id,
            'user_id' => $request->user()->id,
            'comment' => $validated['comment'],
        ]);

        if ($request->filled('diagnosis')) {
            $order->update(['diagnosis' => $request->input('diagnosis')]);
        }

        ActivityLog::record(
            'order.comment_added',
            "Observación técnica en orden {$order->order_number}.",
            $order,
        );

        return redirect()
            ->route('mechanic.orders.show', $order)
            ->with('success', 'Observación registrada correctamente.');
    }

    public function updateProgress(Request $request, ServiceOrder $order)
    {
        $this->authorize('update', $order);

        $validated = $request->validate([
            'progress' => ['required', 'integer', 'min:0', 'max:100'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->serviceOrderService->updateProgress($order->id, $validated['progress'], $validated['comment'] ?? null);

        if (! empty($validated['comment'])) {
            OrderComment::create([
                'service_order_id' => $order->id,
                'user_id' => $request->user()->id,
                'comment' => $validated['comment'],
            ]);
        }

        ActivityLog::record(
            'order.progress_updated',
            "Mecánico actualizó el avance de la orden {$order->order_number} a {$order->progress}%.",
            $order,
        );

        return redirect()
            ->route('mechanic.orders.show', $order)
            ->with('success', 'Avance actualizado correctamente.');
    }

    public function history(Request $request)
    {
        $user = $request->user();

        $maintenances = ServiceOrder::with('vehicle')
            ->where('mechanic_id', $user->id)
            ->whereIn('status', ['completada', 'entregada'])
            ->orderByDesc('completed_at')
            ->paginate(12);

        return view('mechanic.history', compact('maintenances'));
    }
}
