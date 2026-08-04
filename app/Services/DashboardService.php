<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Alert;
use App\Models\Maintenance;
use App\Models\MaintenanceSchedule;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;

class DashboardService
{
    public function getAdminSummary($year = null, $month = null)
    {
        $year = $year ?? Carbon::now()->year;
        $month = $month ?? Carbon::now()->month;

        return [
            'vehiculos' => Vehicle::count(),
            'mantenimientos' => Maintenance::whereMonth('performed_at', $month)
                ->whereYear('performed_at', $year)
                ->count(),
            'pendientes' => ServiceOrder::whereIn('status', ['recibida', 'en_proceso'])->count(),
            'alertas' => Alert::where('is_resolved', false)->count(),
            'alertas_criticas' => Alert::where('is_resolved', false)->where('severity', 'critical')->count(),
            'usuarios' => User::where('status', 'activo')->count(),
            'gasto_mes' => Maintenance::whereMonth('performed_at', $month)
                ->whereYear('performed_at', $year)
                ->sum('cost'),
        ];
    }

    public function getHealthSummary()
    {
        $totalVehicles = max(Vehicle::count(), 1);
        $healthyVehicles = Vehicle::where('status', 'activo')->count();
        $upcomingTasks = ServiceOrder::whereIn('status', ['recibida', 'en_proceso'])->count();
        $criticalAlerts = Alert::where('is_resolved', false)->where('severity', 'critical')->count();
        $todayLogs = ActivityLog::whereDate('created_at', Carbon::today())->count();

        return [
            'flota_saludable' => (int) round(($healthyVehicles / $totalVehicles) * 100),
            'tareas_proximas' => $upcomingTasks,
            'alertas_criticas' => $criticalAlerts,
            'registros_hoy' => $todayLogs,
        ];
    }

    public function getRecentOrders($limit = 5)
    {
        return ServiceOrder::with('vehicle')
            ->latest()
            ->take($limit)
            ->get();
    }

    public function getRecentActivity($limit = 5)
    {
        return ActivityLog::with('user')
            ->latest()
            ->take($limit)
            ->get();
    }

    public function getUpcomingMaintenances($limit = 6)
    {
        return MaintenanceSchedule::with('vehicle')
            ->where('scheduled_date', '>=', Carbon::now()->toDateString())
            ->whereIn('status', ['programado', 'vencido'])
            ->orderBy('scheduled_date')
            ->take($limit)
            ->get();
    }

    public function getMonthlyCosts($year = null)
    {
        $year = $year ?? Carbon::now()->year;

        return Maintenance::selectRaw('MONTH(performed_at) as month, SUM(cost) as total')
            ->whereYear('performed_at', $year)
            ->groupByRaw('MONTH(performed_at)')
            ->orderByRaw('MONTH(performed_at)')
            ->get()
            ->keyBy('month')
            ->map(fn ($item) => (float) $item->total);
    }

    public function getCalendarData($startDate, $endDate)
    {
        $schedules = MaintenanceSchedule::with(['vehicle', 'assignedMechanic'])
            ->whereBetween('scheduled_date', [$startDate, $endDate])
            ->whereIn('status', ['programado', 'vencido'])
            ->get();

        $orders = ServiceOrder::with(['vehicle', 'mechanic'])
            ->whereNotNull('scheduled_at')
            ->whereBetween('scheduled_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay(),
            ])
            ->get();

        return [
            'schedules' => $schedules,
            'orders' => $orders,
        ];
    }
}
