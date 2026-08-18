<?php

namespace App\Http\Controllers\Client;

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
        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->toString();

        $orders = $request->user()->clientOrders()
            ->with('vehicle', 'mechanic')
            ->when($search !== '', function ($q) use ($search) {
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

        return view('client.orders.index', compact('orders', 'search', 'status'));
    }

    public function show(ServiceOrder $order)
    {
        $this->authorize('view', $order);

        $order->load(['vehicle', 'mechanic', 'maintenances', 'comments.user', 'photos.user']);

        return view('client.orders.show', compact('order'));
    }
}
