<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;

use App\Models\ServiceOrder;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->toString();

        $orders = $request->user()->clientOrders()
            ->with('vehicle', 'mechanic')
            ->search($search)
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('client.orders.index', compact('orders', 'search', 'status'));
    }

    public function show(ServiceOrder $order)
    {
        $this->authorize('view', $order);

        $order->load(['vehicle', 'mechanic', 'maintenances', 'comments.user']);

        return view('client.orders.show', compact('order'));
    }
}
