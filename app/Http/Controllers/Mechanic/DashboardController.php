<?php

namespace App\Http\Controllers\Mechanic;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceSchedule;
use App\Models\ServiceOrder;
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
        $ordersQuery = $user->assignedOrders();

        $stats = [
            'asignadas' => (clone $ordersQuery)->count(),
            'en_proceso' => (clone $ordersQuery)->where('status', 'en_proceso')->count(),
            'pendientes' => (clone $ordersQuery)->where('status', 'recibida')->count(),
            'completadas' => (clone $ordersQuery)->whereIn('status', ['completada', 'entregada'])->count(),
        ];

        $recentOrders = $user->assignedOrders()
            ->with(['vehicle', 'client', 'photos'])
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

        $calendarOrders = $user->assignedOrders()
            ->with('vehicle')
            ->whereNotNull('scheduled_at')
            ->whereBetween('scheduled_at', [
                $period['grid_start']->copy()->startOfDay(),
                $period['grid_end']->copy()->endOfDay(),
            ])
            ->get();

        $calendarSchedules = MaintenanceSchedule::with('vehicle')
            ->where('assigned_mechanic_id', $user->id)
            ->whereBetween('scheduled_date', [
                $period['grid_start']->toDateString(),
                $period['grid_end']->toDateString(),
            ])
            ->whereIn('status', ['programado', 'vencido'])
            ->get();

        $calendarWidget = $calendarService->makeWidget($period, [
            [
                'items' => $calendarSchedules,
                'date' => fn (MaintenanceSchedule $schedule) => $schedule->scheduled_date,
                'label' => fn (MaintenanceSchedule $schedule) => $schedule->title,
                'meta' => fn (MaintenanceSchedule $schedule) => $schedule->vehicle?->plate.' · '.$schedule->statusLabel(),
                'variant' => fn (MaintenanceSchedule $schedule) => $schedule->status === 'vencido' ? 'event-red' : 'event-green',
            ],
            [
                'items' => $calendarOrders,
                'date' => fn (ServiceOrder $order) => $order->scheduled_at,
                'label' => fn (ServiceOrder $order) => $order->order_number,
                'meta' => fn (ServiceOrder $order) => $order->vehicle?->plate.' · '.$order->statusLabel(),
                'variant' => fn () => 'event-blue',
                'url' => fn (ServiceOrder $order) => route('mechanic.orders.show', $order),
            ],
        ], [
            'title' => 'Agenda del taller',
            'subtitle' => 'Calendario reutilizable integrado en el dashboard del mecánico.',
            'upcoming_title' => 'Próximos trabajos',
            'prev_url' => route('mechanic.dashboard', ['month' => $period['prev']->month, 'year' => $period['prev']->year]),
            'next_url' => route('mechanic.dashboard', ['month' => $period['next']->month, 'year' => $period['next']->year]),
            'legend' => [
                ['label' => 'Mantenimiento programado', 'variant' => 'event-green'],
                ['label' => 'Mantenimiento vencido', 'variant' => 'event-red'],
                ['label' => 'Orden asignada', 'variant' => 'event-blue'],
            ],
            'upcoming_limit' => 8,
        ]);

        return view('mechanic.dashboard', compact('user', 'stats', 'recentOrders', 'reminders', 'calendarWidget'));
    }
}
