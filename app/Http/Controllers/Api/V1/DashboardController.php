<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Concerns\SerializesMobileModels;
use App\Http\Controllers\Controller;
use App\Models\AppointmentRequest;
use App\Models\Maintenance;
use App\Models\MaintenanceSchedule;
use App\Models\ServiceOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use SerializesMobileModels;

    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isClient()) {
            return $this->clientDashboard($request);
        }

        if ($user->isAdvisor()) {
            return $this->advisorDashboard($request);
        }

        if ($user->isMechanic()) {
            return $this->mechanicDashboard($request);
        }

        return response()->json([
            'role' => $user->role->value,
            'message' => 'El panel de administrador está disponible en la web.',
        ]);
    }

    private function clientDashboard(Request $request): JsonResponse
    {
        $user = $request->user();
        $vehicleIds = $user->vehicles()->pluck('id');

        $recentOrders = $user->clientOrders()->with('vehicle')->latest()->take(5)->get();
        $appointments = AppointmentRequest::query()
            ->where('client_id', $user->id)
            ->with('vehicle')
            ->latest()
            ->take(5)
            ->get();

        return response()->json([
            'role' => $user->role->value,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'stats' => [
                'vehicles' => $vehicleIds->count(),
                'in_shop' => $user->vehicles()->where('status', 'en_taller')->count(),
                'upcoming_services' => MaintenanceSchedule::whereIn('vehicle_id', $vehicleIds)
                    ->where('status', 'programado')
                    ->where('scheduled_date', '>=', now()->toDateString())
                    ->count(),
                'completed_services' => Maintenance::whereIn('vehicle_id', $vehicleIds)->where('status', 'completado')->count(),
                'total_expenses' => Maintenance::whereIn('vehicle_id', $vehicleIds)->where('status', 'completado')->sum('cost'),
                'open_orders' => $user->clientOrders()->whereNotIn('status', ['completada', 'entregada', 'cancelada'])->count(),
            ],
            'recent_orders' => $recentOrders->map(fn ($order) => $this->orderSummary($order))->values(),
            'appointments' => $appointments->map(fn ($item) => $this->appointmentPayload($item))->values(),
        ]);
    }

    private function advisorDashboard(Request $request): JsonResponse
    {
        $user = $request->user();

        $pendingAppointments = AppointmentRequest::query()
            ->where('source', 'chatbot')
            ->where('status', 'pendiente')
            ->with(['client', 'vehicle'])
            ->latest()
            ->take(8)
            ->get();

        $orders = ServiceOrder::query()
            ->where(function ($q) use ($user) {
                $q->where('advisor_id', $user->id)
                    ->orWhere('created_by', $user->id)
                    ->orWhereNull('advisor_id')
                    ->orWhere('source', 'chatbot');
            })
            ->with(['vehicle', 'client'])
            ->latest()
            ->take(8)
            ->get();

        return response()->json([
            'role' => $user->role->value,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'stats' => [
                'pending_appointments' => AppointmentRequest::query()->where('source', 'chatbot')->where('status', 'pendiente')->count(),
                'open_orders' => ServiceOrder::query()->whereNotIn('status', ['completada', 'entregada', 'cancelada'])->count(),
            ],
            'pending_appointments' => $pendingAppointments->map(fn ($item) => $this->appointmentPayload($item))->values(),
            'recent_orders' => $orders->map(fn ($order) => $this->orderSummary($order))->values(),
        ]);
    }

    private function mechanicDashboard(Request $request): JsonResponse
    {
        $user = $request->user();
        $orders = $user->assignedOrders()->with(['vehicle', 'client'])->latest()->take(8)->get();

        return response()->json([
            'role' => $user->role->value,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'stats' => [
                'assigned' => $user->assignedOrders()->count(),
                'in_progress' => $user->assignedOrders()->where('status', 'en_proceso')->count(),
                'received' => $user->assignedOrders()->where('status', 'recibida')->count(),
            ],
            'recent_orders' => $orders->map(fn ($order) => $this->orderSummary($order))->values(),
        ]);
    }
}
