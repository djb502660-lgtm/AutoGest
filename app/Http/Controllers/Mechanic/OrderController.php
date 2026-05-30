<?php

namespace App\Http\Controllers\Mechanic;

use App\Http\Controllers\Controller;

use App\Models\ActivityLog;
use App\Models\Maintenance;
use App\Models\OrderComment;
use App\Models\ServiceOrder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $search = $request->string('search')->trim();
        $status = $request->string('status')->toString();

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
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('mechanic.orders.index', compact('orders', 'search', 'status'));
    }

    public function show(ServiceOrder $order)
    {
        $this->authorize('view', $order);

        $order->load(['vehicle.client', 'client', 'maintenances', 'comments.user']);

        return view('mechanic.orders.show', compact('order'));
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

        if ($validated['status'] === 'en_proceso' && ! $order->started_at) {
            $order->started_at = now();
        }

        if (in_array($validated['status'], ['completada', 'entregada'], true)) {
            $order->completed_at = $validated['completed_at'] ?? now();
            $validated['progress'] = 100;
        }

        $order->fill($validated);
        $order->save();

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

        $order->progress = $validated['progress'];

        if ($validated['progress'] > 0 && $order->status === 'recibida') {
            $order->status = 'en_proceso';
            $order->started_at = $order->started_at ?? now();
        }

        if ($validated['progress'] >= 100) {
            $order->status = 'completada';
            $order->completed_at = now();
        }

        $order->save();

        if (! empty($validated['comment'])) {
            OrderComment::create([
                'service_order_id' => $order->id,
                'user_id' => $request->user()->id,
                'comment' => $validated['comment'],
            ]);
        }

        return redirect()
            ->route('mechanic.orders.show', $order)
            ->with('success', 'Avance actualizado correctamente.');
    }

    public function history(Request $request)
    {
        $user = $request->user();

        $maintenances = Maintenance::with('vehicle', 'serviceOrder')
            ->where('mechanic_id', $user->id)
            ->orderByDesc('performed_at')
            ->paginate(12);

        return view('mechanic.history', compact('maintenances'));
    }
}
