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

        $requests = AppointmentRequest::query()
            ->with(['client', 'vehicle', 'advisor'])
            ->when($status !== 'todas', fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('advisor.appointments.index', compact('requests', 'status'));
    }

    public function show(AppointmentRequest $appointment)
    {
        $appointment->load(['client', 'vehicle', 'serviceOrder', 'advisor']);

        return view('advisor.appointments.show', [
            'appointment' => $appointment,
            'mechanics' => User::where('role', UserRole::Mechanic)->where('status', 'activo')->orderBy('name')->get(),
        ]);
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
            'advisor_id' => $advisor->id,
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
            "Asesor confirmó solicitud chatbot → orden {$order->order_number}.",
            $order,
        );

        return redirect()
            ->route('advisor.orders.show', $order)
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

        Alert::create([
            'vehicle_id' => $appointment->vehicle_id,
            'user_id' => $appointment->client_id,
            'type' => 'custom',
            'title' => 'Solicitud de cita no confirmada',
            'message' => $validated['advisor_notes'],
            'severity' => 'warning',
        ]);

        return redirect()
            ->route('advisor.appointments.index')
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
}
