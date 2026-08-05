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

        $orders = ServiceOrder::with(['vehicle', 'client', 'mechanic'])
            ->when($search->isNotEmpty(), function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('vehicle', fn ($query) => $query->where('plate', 'like', "%{$search}%"));
            })
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.orders.index', compact('orders', 'search', 'status'));
    }

    public function show(ServiceOrder $order)
    {
        $this->authorize('view', $order);

        $order->load(['vehicle.client', 'client', 'mechanic', 'advisor', 'maintenances', 'comments.user', 'photos.user']);

        return view('admin.orders.show', compact('order'));
    }

    public function invoice(ServiceOrder $order)
    {
        $this->authorize('view', $order);

        $order->load(['vehicle.client', 'client', 'mechanic', 'advisor']);

        return view('admin.invoices.show', compact('order'));
    }
}
