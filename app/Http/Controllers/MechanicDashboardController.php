<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MechanicDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $orders = $user->assignedOrders()
            ->with('vehicle', 'client')
            ->latest()
            ->get();

        return view('mechanic.dashboard', compact('user', 'orders'));
    }
}
