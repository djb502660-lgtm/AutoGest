<?php

namespace App\Http\Controllers\Advisor;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\AppointmentRejectRequest;
use App\Http\Requests\AppointmentRequest as AppointmentFormRequest;
use App\Models\ActivityLog;
use App\Models\Alert;
use App\Models\AppointmentRequest;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleModelTemplate;
use Illuminate\Http\Request;

class AppointmentRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->string('status')->toString() ?: 'pendiente';

        $chatbotQuery = AppointmentRequest::query()->where('source', 'chatbot');

        $statusCounts = (clone $chatbotQuery)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $requests = (clone $chatbotQuery)
            ->with(['client', 'vehicle', 'advisor'])
            ->when($status !== 'todas', fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $chatbotAlerts = $request->user()->alerts()
            ->with('appointmentRequest')
            ->where('title', 'like', '%chatbot%')
            ->where('is_read', false)
            ->latest()
            ->take(8)
            ->get();

        return view('advisor.appointments.index', array_merge(
            compact('requests', 'status', 'chatbotAlerts', 'statusCounts'),
            $this->chatbotPanel($request),
        ));
    }

    public function show(AppointmentRequest $appointment)
    {
        $appointment->load(['client', 'vehicle', 'serviceOrder', 'advisor']);

        Alert::query()
            ->where('user_id', request()->user()?->id)
            ->where('appointment_request_id', $appointment->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return view('advisor.appointments.show', array_merge([
            'appointment' => $appointment,
            'mechanics' => User::where('role', UserRole::Mechanic)->where('status', 'activo')->orderBy('name')->get(),
        ], $this->chatbotPanel(request())));
    }

    public function confirm(AppointmentFormRequest $formRequest, AppointmentRequest $appointment)
    {
        if (! in_array($appointment->status, ['pendiente', 'confirmada'], true)) {
            return back()->withErrors(['appointment' => 'Esta solicitud ya fue procesada.']);
        }

        $validated = $formRequest->validated();

        $advisor = $formRequest->user();
        $scheduledAt = $appointment->requested_date->copy();

        if ($appointment->requested_time) {
            $scheduledAt->setTimeFromTimeString($appointment->requested_time);
        }

        $order = ServiceOrder::create([
            'order_number' => ServiceOrder::generateOrderNumber(),
            'vehicle_id' => $appointment->vehicle_id,
            'client_id' => $appointment->client_id,
            'mechanic_id' => $validated['mechanic_id'] ?? null,
            'advisor_id' => $advisor->isAdvisor() ? $advisor->id : null,
            'created_by' => $advisor->id,
            'source' => 'chatbot',
            'status' => 'recibida',
            'progress' => 0,
            'priority' => $appointment->requires_approval ? 'alta' : 'normal',
            'description' => $appointment->description,
            'scheduled_at' => $scheduledAt,
        ]);

        $appointment->update([
            'status' => 'convertida',
            'advisor_id' => $advisor->id,
            'service_order_id' => $order->id,
            'advisor_notes' => $validated['advisor_notes'] ?? null,
        ]);

        Alert::markChatbotAppointmentHandled($appointment);

        Alert::create([
            'vehicle_id' => $appointment->vehicle_id,
            'user_id' => $appointment->client_id,
            'type' => 'custom',
            'title' => 'Cita confirmada',
            'message' => "Tu cita para {$appointment->vehicle->plate} quedó confirmada el "
                .$appointment->requested_date->format('d/m/Y').". Orden {$order->order_number}.",
            'severity' => 'info',
            'due_date' => $appointment->requested_date,
        ]);

        ActivityLog::record(
            'appointment.confirmed',
            "Se confirmó la solicitud chatbot → orden {$order->order_number}.",
            $order,
        );

        $panel = $this->chatbotPanel($formRequest);

        return redirect()
            ->route($panel['orderRoute'], $order)
            ->with('success', 'Solicitud confirmada y convertida en orden de trabajo.');
    }

    public function reject(AppointmentRejectRequest $request, AppointmentRequest $appointment)
    {
        if ($appointment->status !== 'pendiente') {
            return back()->withErrors(['appointment' => 'Esta solicitud ya fue procesada.']);
        }

        $validated = $request->validated();

        $appointment->update([
            'status' => 'rechazada',
            'advisor_id' => $request->user()->id,
            'advisor_notes' => $validated['advisor_notes'],
        ]);

        Alert::markChatbotAppointmentHandled($appointment);

        Alert::create([
            'vehicle_id' => $appointment->vehicle_id,
            'user_id' => $appointment->client_id,
            'type' => 'custom',
            'title' => 'Solicitud de cita no confirmada',
            'message' => $validated['advisor_notes'],
            'severity' => 'warning',
        ]);

        return redirect()
            ->route($this->chatbotPanel($request)['indexRoute'])
            ->with('success', 'Solicitud rechazada. El cliente fue notificado.');
    }

    public function vehicleTemplates(Vehicle $vehicle)
    {
        $this->authorize('viewAny', ServiceOrder::class);

        $templates = VehicleModelTemplate::forVehicle($vehicle);

        return response()->json([
            'vehicle' => $vehicle->only(['id', 'plate', 'brand', 'model']),
            'templates' => $templates->map(fn ($t) => [
                'title' => $t->title,
                'maintenance_type' => $t->maintenanceTypeLabel(),
                'description' => $t->description,
                'interval_km' => $t->interval_km,
                'interval_months' => $t->interval_months,
            ]),
        ]);
    }

    /**
     * @return array{layout: string, indexRoute: string, showRoute: string, confirmRoute: string, rejectRoute: string, orderRoute: string}
     */
    private function chatbotPanel(Request $request): array
    {
        $admin = $request->user()?->isAdmin() ?? false;

        return [
            'layout' => $admin ? 'layouts.admin' : 'layouts.advisor',
            'indexRoute' => $admin ? 'admin.chatbot-appointments.index' : 'advisor.chatbot-appointments.index',
            'showRoute' => $admin ? 'admin.chatbot-appointments.show' : 'advisor.chatbot-appointments.show',
            'confirmRoute' => $admin ? 'admin.chatbot-appointments.confirm' : 'advisor.chatbot-appointments.confirm',
            'rejectRoute' => $admin ? 'admin.chatbot-appointments.reject' : 'advisor.chatbot-appointments.reject',
            'orderRoute' => $admin ? 'admin.orders.show' : 'advisor.orders.show',
        ];
    }
}
