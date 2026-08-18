<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Concerns\SerializesMobileModels;
use App\Http\Controllers\Controller;
use App\Http\Requests\AppointmentRejectRequest;
use App\Http\Requests\AppointmentRequest as AppointmentFormRequest;
use App\Models\ActivityLog;
use App\Models\Alert;
use App\Models\AppointmentRequest;
use App\Models\ServiceOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    use SerializesMobileModels;

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $status = $request->string('status')->toString();

        $query = AppointmentRequest::query()->with(['client', 'vehicle', 'advisor', 'serviceOrder']);

        if ($user->isClient()) {
            $query->where('client_id', $user->id);
        } elseif ($user->isAdvisor() || $user->isAdmin()) {
            $query->where('source', 'chatbot');
        } else {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        if ($status !== '' && $status !== 'todas') {
            $query->where('status', $status);
        }

        $appointments = $query->latest()->limit(50)->get();

        return response()->json([
            'appointments' => $appointments->map(fn ($item) => $this->appointmentPayload($item))->values(),
        ]);
    }

    public function show(Request $request, AppointmentRequest $appointment): JsonResponse
    {
        $user = $request->user();

        if ($user->isClient() && $appointment->client_id !== $user->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        if ($user->isMechanic()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        return response()->json([
            'appointment' => $this->appointmentPayload($appointment),
        ]);
    }

    public function confirm(AppointmentFormRequest $formRequest, AppointmentRequest $appointment): JsonResponse
    {
        $user = $formRequest->user();

        if (! $user->isAdvisor() && ! $user->isAdmin()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        if (! in_array($appointment->status, ['pendiente', 'confirmada'], true)) {
            return response()->json(['message' => 'Esta solicitud ya fue procesada.'], 422);
        }

        $validated = $formRequest->validated();
        $scheduledAt = $appointment->requested_date->copy();

        if ($appointment->requested_time) {
            $scheduledAt->setTimeFromTimeString($appointment->requested_time);
        }

        $order = ServiceOrder::create([
            'order_number' => ServiceOrder::generateOrderNumber(),
            'vehicle_id' => $appointment->vehicle_id,
            'client_id' => $appointment->client_id,
            'mechanic_id' => $validated['mechanic_id'] ?? null,
            'advisor_id' => $user->isAdvisor() ? $user->id : null,
            'created_by' => $user->id,
            'source' => 'chatbot',
            'status' => 'recibida',
            'progress' => 0,
            'priority' => $appointment->requires_approval ? 'alta' : 'normal',
            'description' => $appointment->description,
            'scheduled_at' => $scheduledAt,
        ]);

        $appointment->update([
            'status' => 'convertida',
            'advisor_id' => $user->id,
            'service_order_id' => $order->id,
            'advisor_notes' => $validated['advisor_notes'] ?? null,
        ]);

        Alert::markChatbotAppointmentHandled($appointment);

        ActivityLog::record(
            'appointment.confirmed',
            "Se confirmó la solicitud chatbot → orden {$order->order_number}.",
            $order,
        );

        return response()->json([
            'message' => 'Solicitud confirmada y convertida en orden.',
            'appointment' => $this->appointmentPayload($appointment->fresh()),
            'order' => $this->orderSummary($order),
        ]);
    }

    public function reject(AppointmentRejectRequest $request, AppointmentRequest $appointment): JsonResponse
    {
        $user = $request->user();

        if (! $user->isAdvisor() && ! $user->isAdmin()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        if ($appointment->status !== 'pendiente') {
            return response()->json(['message' => 'Esta solicitud ya fue procesada.'], 422);
        }

        $validated = $request->validated();

        $appointment->update([
            'status' => 'rechazada',
            'advisor_id' => $user->id,
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

        return response()->json([
            'message' => 'Solicitud rechazada.',
            'appointment' => $this->appointmentPayload($appointment->fresh()),
        ]);
    }
}
