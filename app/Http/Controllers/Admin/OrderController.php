<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceOrder;
use App\Services\ServiceOrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    private $serviceOrderService;

    public function __construct(ServiceOrderService $serviceOrderService)
    {
        $this->serviceOrderService = $serviceOrderService;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', ServiceOrder::class);

        $search = $request->string('search')->trim();
        $status = $request->string('status')->toString();
        $priority = $request->string('priority')->toString();
        $clientId = $request->input('client_id');
        $mechanicId = $request->input('mechanic_id');

        $orders = ServiceOrder::with(['vehicle', 'client', 'mechanic'])
            ->when($search->isNotEmpty(), function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('vehicle', fn ($query) => $query->where('plate', 'like', "%{$search}%"))
                    ->orWhereHas('client', fn ($query) => $query->where('name', 'like', "%{$search}%"));
            })
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->when($priority !== '', fn ($q) => $q->where('priority', $priority))
            ->when($clientId, fn ($q) => $q->where('client_id', $clientId))
            ->when($mechanicId, fn ($q) => $q->where('mechanic_id', $mechanicId))
            ->when($priority === 'alta' || $priority === 'urgente', fn ($q) => $q->orderByRaw("FIELD(priority, 'urgente', 'alta', 'normal', 'baja')"))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.orders.index', compact('orders', 'search', 'status', 'priority', 'clientId', 'mechanicId'));
    }

    public function show(ServiceOrder $order)
    {
        $this->authorize('view', $order);

        $order->load(['vehicle.client', 'client', 'mechanic', 'advisor', 'maintenances', 'comments.user', 'photos.user']);

        return view('admin.orders.show', compact('order'));
    }
}
