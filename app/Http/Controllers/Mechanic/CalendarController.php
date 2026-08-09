<?php

namespace App\Http\Controllers\Mechanic;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceSchedule;
use App\Models\ServiceOrder;
use App\Services\DashboardCalendarService;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index(Request $request, DashboardCalendarService $calendar)
    {
        $user = $request->user();
        $period = $calendar->resolvePeriod(
            (int) $request->input('month', now()->month),
            (int) $request->input('year', now()->year),
        );

        $schedules = MaintenanceSchedule::with('vehicle')
            ->where('assigned_mechanic_id', $user->id)
            ->whereBetween('scheduled_date', [$period['grid_start']->toDateString(), $period['grid_end']->toDateString()])
            ->get()
            ->groupBy(fn ($item) => $item->scheduled_date->format('Y-m-d'));

        $orders = ServiceOrder::with('vehicle')
            ->where('mechanic_id', $user->id)
            ->whereNotNull('scheduled_at')
            ->whereBetween('scheduled_at', [$period['grid_start'], $period['grid_end']])
            ->get()
            ->groupBy(fn ($item) => $item->scheduled_at->format('Y-m-d'));

        $weeks = $calendar->makeGrid($period, [
            'schedules' => $schedules,
            'orders' => $orders,
        ]);

        $upcomingSchedules = MaintenanceSchedule::with('vehicle')
            ->where('assigned_mechanic_id', $user->id)
            ->where('status', 'programado')
            ->where('scheduled_date', '>=', now()->toDateString())
            ->orderBy('scheduled_date')
            ->take(8)
            ->get();

        $upcomingOrders = ServiceOrder::with('vehicle')
            ->where('mechanic_id', $user->id)
            ->whereNotNull('scheduled_at')
            ->whereDate('scheduled_at', '>=', now()->toDateString())
            ->orderBy('scheduled_at')
            ->take(8)
            ->get();

        return view('mechanic.calendar.index', [
            'weeks' => $weeks,
            'current' => $period['current'],
            'prev' => $period['prev'],
            'next' => $period['next'],
            'upcomingSchedules' => $upcomingSchedules,
            'upcomingOrders' => $upcomingOrders,
        ]);
    }
}
