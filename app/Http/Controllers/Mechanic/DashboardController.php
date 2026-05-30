<?php

namespace App\Http\Controllers\Mechanic;

use App\Http\Controllers\Controller;

use App\Models\ServiceOrder;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $ordersQuery = $user->assignedOrders();

        $stats = [
            'asignadas' => (clone $ordersQuery)->count(),
            'en_proceso' => (clone $ordersQuery)->where('status', 'en_proceso')->count(),
            'pendientes' => (clone $ordersQuery)->where('status', 'recibida')->count(),
            'completadas' => (clone $ordersQuery)->whereIn('status', ['completada', 'entregada'])->count(),
        ];

        $recentOrders = $user->assignedOrders()
            ->with('vehicle', 'client')
            ->latest()
            ->take(6)
            ->get();

        $reminders = collect();
        if ($stats['en_proceso'] > 0) {
            $reminders->push("Tienes {$stats['en_proceso']} órdenes en proceso.");
        }
        if ($stats['pendientes'] > 0) {
            $reminders->push("Tienes {$stats['pendientes']} órdenes pendientes por iniciar.");
        }
        $urgent = (clone $ordersQuery)->where('priority', 'urgente')->whereIn('status', ['recibida', 'en_proceso'])->count();
        if ($urgent > 0) {
            $reminders->push("{$urgent} orden(es) marcada(s) como urgente.");
        }

        return view('mechanic.dashboard', compact('user', 'stats', 'recentOrders', 'reminders'));
    }
}
