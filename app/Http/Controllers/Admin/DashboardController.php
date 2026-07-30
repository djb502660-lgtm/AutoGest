<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Alert;
use App\Models\Maintenance;
use App\Models\MaintenanceSchedule;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\DashboardCalendarService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, DashboardCalendarService $calendarService)
    {
        $user = $request->user();
        $period = $calendarService->resolvePeriod(
            $request->integer('month'),
            $request->integer('year'),
        );

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

        $calendarSchedules = MaintenanceSchedule::with(['vehicle', 'assignedMechanic'])
            ->whereBetween('scheduled_date', [
                $period['grid_start']->toDateString(),
                $period['grid_end']->toDateString(),
            ])
            ->whereIn('status', ['programado', 'vencido'])
            ->get();

        $calendarOrders = ServiceOrder::with(['vehicle', 'mechanic'])
            ->whereNotNull('scheduled_at')
            ->whereBetween('scheduled_at', [
                $period['grid_start']->copy()->startOfDay(),
                $period['grid_end']->copy()->endOfDay(),
            ])
            ->get();

        $upcomingMaintenances = MaintenanceSchedule::with('vehicle')
            ->where('scheduled_date', '>=', now()->toDateString())
            ->whereIn('status', ['programado', 'vencido'])
            ->orderBy('scheduled_date')
            ->take(6)
            ->get();

        $calendarWidget = $calendarService->makeWidget($period, [
            [
                'items' => $calendarSchedules,
                'date' => fn (MaintenanceSchedule $schedule) => $schedule->scheduled_date,
                'label' => fn (MaintenanceSchedule $schedule) => $schedule->title,
                'meta' => fn (MaintenanceSchedule $schedule) => $schedule->vehicle?->plate.' · '.($schedule->assignedMechanic?->name ?? 'Sin mecánico'),
                'variant' => fn (MaintenanceSchedule $schedule) => $schedule->status === 'vencido' ? 'event-red' : 'event-green',
                'url' => fn () => route('calendar.index'),
            ],
            [
                'items' => $calendarOrders,
                'date' => fn (ServiceOrder $order) => $order->scheduled_at,
                'label' => fn (ServiceOrder $order) => $order->order_number,
                'meta' => fn (ServiceOrder $order) => $order->vehicle?->plate.' · '.($order->mechanic?->name ?? 'Orden pendiente'),
                'variant' => fn () => 'event-blue',
            ],
        ], [
            'title' => 'Agenda operativa',
            'subtitle' => 'Vista mensual integrada del trabajo, sin separar el calendario del panel.',
            'upcoming_title' => 'Próximos mantenimientos',
            'prev_url' => route('dashboard', ['month' => $period['prev']->month, 'year' => $period['prev']->year]),
            'next_url' => route('dashboard', ['month' => $period['next']->month, 'year' => $period['next']->year]),
            'legend' => [
                ['label' => 'Mantenimiento programado', 'variant' => 'event-green'],
                ['label' => 'Mantenimiento vencido', 'variant' => 'event-red'],
                ['label' => 'Orden agendada', 'variant' => 'event-blue'],
            ],
            'upcoming_limit' => 8,
        ]);

        $monthlyCosts = Maintenance::selectRaw('MONTH(performed_at) as month, SUM(cost) as total')
            ->whereYear('performed_at', now()->year)
            ->groupByRaw('MONTH(performed_at)')
            ->orderByRaw('MONTH(performed_at)')
            ->get()
            ->keyBy('month')
            ->map(fn ($item) => (float) $item->total);

        return view('admin.dashboard.index', compact(
            'user',
            'stats',
            'recentOrders',
            'recentActivity',
            'summary',
            'upcomingMaintenances',
            'monthlyCosts',
            'calendarWidget',
        ));
    }
}
