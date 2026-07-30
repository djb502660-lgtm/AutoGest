<?php

namespace App\Http\Controllers\Mechanic;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceSchedule;
use App\Models\ServiceOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        Carbon::setLocale('es');

        $user = $request->user();
        $month = max(1, min(12, (int) $request->input('month', now()->month)));
        $year = (int) $request->input('year', now()->year);

        $current = Carbon::create($year, $month, 1)->locale('es');
        $startOfMonth = $current->copy()->startOfMonth();
        $endOfMonth = $current->copy()->endOfMonth();

        $gridStart = $startOfMonth->copy()->startOfWeek(Carbon::MONDAY);
        $gridEnd = $endOfMonth->copy()->endOfWeek(Carbon::SUNDAY);

        $schedules = MaintenanceSchedule::with('vehicle')
            ->where('assigned_mechanic_id', $user->id)
            ->whereBetween('scheduled_date', [$gridStart->toDateString(), $gridEnd->toDateString()])
            ->get()
            ->groupBy(fn ($item) => $item->scheduled_date->format('Y-m-d'));

        $orders = ServiceOrder::with('vehicle')
            ->where('mechanic_id', $user->id)
            ->whereNotNull('scheduled_at')
            ->whereBetween('scheduled_at', [$gridStart->startOfDay(), $gridEnd->endOfDay()])
            ->get()
            ->groupBy(fn ($item) => $item->scheduled_at->format('Y-m-d'));

        $weeks = [];
        $day = $gridStart->copy();

        while ($day <= $gridEnd) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $key = $day->format('Y-m-d');
                $week[] = [
                    'date' => $day->copy(),
                    'in_month' => $day->month === $month,
                    'is_today' => $day->isToday(),
                    'schedules' => $schedules->get($key, collect()),
                    'orders' => $orders->get($key, collect()),
                ];
                $day->addDay();
            }
            $weeks[] = $week;
        }

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
            'current' => $current,
            'prev' => $startOfMonth->copy()->subMonth(),
            'next' => $startOfMonth->copy()->addMonth(),
            'upcomingSchedules' => $upcomingSchedules,
            'upcomingOrders' => $upcomingOrders,
        ]);
    }
}
