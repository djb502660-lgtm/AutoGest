<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ClientDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $vehicles = $user->vehicles()->with('maintenanceSchedules')->get();
        $orders = $user->clientOrders()->with('vehicle')->latest()->take(5)->get();
        $alerts = $user->alerts()->where('is_resolved', false)->latest()->take(5)->get();

        return view('client.dashboard', compact('user', 'vehicles', 'orders', 'alerts'));
    }
}
