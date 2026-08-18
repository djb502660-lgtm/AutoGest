<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppointmentRequest;
use App\Models\MaintenanceSchedule;
use App\Models\ServiceOrder;
use App\Services\DashboardCalendarService;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    private DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index(Request $request, DashboardCalendarService $calendarService)
    {
        $user = $request->user();
        $period = $calendarService->resolvePeriod(
            $request->integer('month'),
            $request->integer('year'),
        );

        $stats = $this->dashboardService->getAdminSummary($period['current']->year, $period['current']->month);
        $summary = $this->dashboardService->getHealthSummary();
        $recentOrders = $this->dashboardService->getRecentOrders(5);
        $recentActivity = $this->dashboardService->getRecentActivity(5);
        $upcomingMaintenances = $this->dashboardService->getUpcomingMaintenances(6);
        $monthlyCosts = $this->dashboardService->getMonthlyCosts($period['current']->year);

        $calendarData = $this->dashboardService->getCalendarData(
            $period['grid_start']->toDateString(),
            $period['grid_end']->toDateString()
        );

        $calendarSchedules = $calendarData['schedules'];
        $calendarOrders = $calendarData['orders'];
        $calendarAppointments = $calendarData['appointments'];

        $pendingAppointments = AppointmentRequest::with(['client', 'vehicle'])
            ->where('source', 'chatbot')
            ->whereIn('status', ['pendiente', 'confirmada'])
            ->orderByDesc('updated_at')
            ->take(6)
            ->get();

        $chatbotAlerts = $user->alerts()
            ->with('appointmentRequest')
            ->where('title', 'like', '%chatbot%')
            ->where('is_read', false)
            ->latest()
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
            [
                'items' => $calendarAppointments,
                'date' => fn (AppointmentRequest $appointment) => $appointment->requested_date,
                'label' => fn (AppointmentRequest $appointment) => 'Chatbot '.$appointment->vehicle?->plate,
                'meta' => fn (AppointmentRequest $appointment) => $appointment->client?->name.' · '.$appointment->statusLabel(),
                'variant' => fn (AppointmentRequest $appointment) => $appointment->status === 'confirmada' ? 'event-green' : 'event-red',
                'url' => fn (AppointmentRequest $appointment) => route('admin.chatbot-appointments.show', $appointment),
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
                ['label' => 'Cita chatbot', 'variant' => 'event-red'],
            ],
            'upcoming_limit' => 8,
        ]);

        return view('admin.dashboard.index', compact(
            'user',
            'stats',
            'recentOrders',
            'recentActivity',
            'summary',
            'upcomingMaintenances',
            'monthlyCosts',
            'calendarWidget',
            'pendingAppointments',
            'chatbotAlerts',
        ));
    }
}
