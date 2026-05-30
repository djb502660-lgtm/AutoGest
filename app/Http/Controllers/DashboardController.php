<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Alert;
use App\Models\Maintenance;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $stats = [
            'vehiculos' => Vehicle::count(),
            'mantenimientos' => Maintenance::whereMonth('performed_at', now()->month)
                ->whereYear('performed_at', now()->year)
                ->count(),
            'pendientes' => ServiceOrder::whereIn('status', ['recibida', 'en_proceso'])->count(),
            'alertas' => Alert::where('is_resolved', false)->count(),
            'alertas_criticas' => Alert::where('is_resolved', false)->where('severity', 'critical')->count(),
            'usuarios' => User::where('status', 'activo')->count(),
            'gasto_mes' => Maintenance::whereMonth('performed_at', now()->month)
                ->whereYear('performed_at', now()->year)
                ->sum('cost'),
        ];

        $recentOrders = ServiceOrder::with('vehicle')
            ->latest()
            ->take(5)
            ->get();

        $recentActivity = ActivityLog::with('user')
            ->latest()
            ->take(5)
            ->get();

        $totalVehicles = max(Vehicle::count(), 1);
        $healthyVehicles = Vehicle::where('status', 'activo')->count();
        $upcomingTasks = ServiceOrder::whereIn('status', ['recibida', 'en_proceso'])->count();
        $criticalAlerts = Alert::where('is_resolved', false)->where('severity', 'critical')->count();
        $todayLogs = ActivityLog::whereDate('created_at', today())->count();

        $summary = [
            'flota_saludable' => (int) round(($healthyVehicles / $totalVehicles) * 100),
            'tareas_proximas' => $upcomingTasks,
            'alertas_criticas' => $criticalAlerts,
            'registros_hoy' => $todayLogs,
        ];

        return view('dashboard.index', compact(
            'user',
            'stats',
            'recentOrders',
            'recentActivity',
            'summary',
        ));
    }
}
