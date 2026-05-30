<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;

use App\Models\Maintenance;
use App\Models\MaintenanceSchedule;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $vehicleIds = $user->vehicles()->pluck('id');

        $stats = [
            'vehiculos' => $vehicleIds->count(),
            'proximo_servicio' => MaintenanceSchedule::whereIn('vehicle_id', $vehicleIds)
                ->where('status', 'programado')
                ->where('scheduled_date', '>=', now()->toDateString())
                ->count(),
            'servicios_realizados' => Maintenance::whereIn('vehicle_id', $vehicleIds)
                ->where('status', 'completado')
                ->count(),
            'gastos_totales' => Maintenance::whereIn('vehicle_id', $vehicleIds)
                ->where('status', 'completado')
                ->sum('cost'),
        ];

        $vehicles = $user->vehicles()
            ->with(['maintenanceSchedules' => fn ($q) => $q->where('status', 'programado')->orderBy('scheduled_date')])
            ->get();

        $recentOrders = $user->clientOrders()
            ->with('vehicle')
            ->latest()
            ->take(5)
            ->get();

        $alerts = $user->alerts()
            ->where('is_resolved', false)
            ->latest()
            ->take(3)
            ->get();

        return view('client.dashboard', compact('user', 'stats', 'vehicles', 'recentOrders', 'alerts'));
    }
}
