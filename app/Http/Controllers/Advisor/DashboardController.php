<?php

namespace App\Http\Controllers\Advisor;

use App\Http\Controllers\Controller;
use App\Models\AppointmentRequest;
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

        $baseQuery = ServiceOrder::query()->where(function ($q) use ($user) {
            $q->where('advisor_id', $user->id)
                ->orWhere('created_by', $user->id);
        });

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'sin_mecanico' => (clone $baseQuery)->whereNull('mechanic_id')->whereIn('status', ['recibida', 'en_proceso'])->count(),
            'en_proceso' => (clone $baseQuery)->where('status', 'en_proceso')->count(),
            'recibidas' => (clone $baseQuery)->where('status', 'recibida')->count(),
            'solicitudes_chatbot' => AppointmentRequest::where('status', 'pendiente')->count(),
        ];

        $recentOrders = (clone $baseQuery)
            ->with(['vehicle', 'client', 'mechanic'])
            ->latest()
            ->take(8)
            ->get();

        $unassigned = (clone $baseQuery)
            ->with(['vehicle', 'client'])
            ->whereNull('mechanic_id')
            ->whereIn('status', ['recibida', 'en_proceso'])
            ->orderBy('scheduled_at')
            ->take(5)
            ->get();

        $pendingAppointments = AppointmentRequest::with(['client', 'vehicle'])
            ->where('status', 'pendiente')
            ->orderBy('requested_date')
            ->take(5)
            ->get();

        $calendarOrders = (clone $baseQuery)
            ->with(['vehicle', 'mechanic'])
            ->whereNotNull('scheduled_at')
            ->whereBetween('scheduled_at', [
                $period['grid_start']->copy()->startOfDay(),
                $period['grid_end']->copy()->endOfDay(),
            ])
            ->get();

        $calendarAppointments = AppointmentRequest::with(['client', 'vehicle'])
            ->whereBetween('requested_date', [
                $period['grid_start']->toDateString(),
                $period['grid_end']->toDateString(),
            ])
            ->whereIn('status', ['pendiente', 'confirmada'])
            ->get();

        $calendarWidget = $calendarService->makeWidget($period, [
            [
                'items' => $calendarOrders,
                'date' => fn (ServiceOrder $order) => $order->scheduled_at,
                'label' => fn (ServiceOrder $order) => $order->order_number,
                'meta' => fn (ServiceOrder $order) => $order->vehicle?->plate.' · '.($order->mechanic?->name ?? 'Sin mecánico'),
                'variant' => fn (ServiceOrder $order) => $order->mechanic_id ? 'event-blue' : 'event-purple',
                'url' => fn (ServiceOrder $order) => route('advisor.orders.show', $order),
            ],
            [
                'items' => $calendarAppointments,
                'date' => fn (AppointmentRequest $appointment) => $appointment->requested_date,
                'label' => fn (AppointmentRequest $appointment) => 'Cita '.$appointment->vehicle?->plate,
                'meta' => fn (AppointmentRequest $appointment) => $appointment->client?->name.' · '.$appointment->statusLabel(),
                'variant' => fn (AppointmentRequest $appointment) => $appointment->status === 'confirmada' ? 'event-green' : 'event-red',
                'url' => fn (AppointmentRequest $appointment) => route('advisor.appointments.show', $appointment),
            ],
        ], [
            'title' => 'Agenda del asesor',
            'subtitle' => 'Órdenes y solicitudes visibles desde el dashboard del rol.',
            'upcoming_title' => 'Próximas citas y órdenes',
            'prev_url' => route('advisor.dashboard', ['month' => $period['prev']->month, 'year' => $period['prev']->year]),
            'next_url' => route('advisor.dashboard', ['month' => $period['next']->month, 'year' => $period['next']->year]),
            'legend' => [
                ['label' => 'Orden asignada', 'variant' => 'event-blue'],
                ['label' => 'Orden sin mecánico', 'variant' => 'event-purple'],
                ['label' => 'Cita confirmada', 'variant' => 'event-green'],
                ['label' => 'Cita pendiente', 'variant' => 'event-red'],
            ],
            'upcoming_limit' => 8,
        ]);

        return view('advisor.dashboard', compact('stats', 'recentOrders', 'unassigned', 'pendingAppointments', 'calendarWidget'));
    }
}
