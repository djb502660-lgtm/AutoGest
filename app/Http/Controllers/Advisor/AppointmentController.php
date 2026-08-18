<?php

namespace App\Http\Controllers\Advisor;

use App\Http\Controllers\Controller;
use App\Models\AppointmentRequest;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->string('date')->toString();
        $status = $request->string('status')->toString();

        $query = AppointmentRequest::query()
            ->with(['client', 'vehicle'])
            ->when($date !== '', fn ($q) => $q->whereDate('requested_date', $date))
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->orderBy('requested_date')
            ->orderBy('preferred_time');

        if ($date === '') {
            $query->whereDate('requested_date', '>=', today());
        }

        $appointments = $query->paginate(10)->withQueryString();

        return view('advisor.appointments.agenda', [
            'appointments' => $appointments,
            'date' => $date,
            'status' => $status,
        ]);
    }

    public function create()
    {
        return view('advisor.appointments.create', [
            'clients' => User::where('role', 'cliente')->where('status', 'activo')->orderBy('name')->get(),
            'vehicles' => Vehicle::where('status', 'activo')->orderBy('plate')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => ['required', 'exists:users,id'],
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'requested_date' => ['required', 'date', 'after_or_equal:today'],
            'preferred_time' => ['nullable', 'string', 'max:50'],
            'service_type' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'priority' => ['required', 'in:baja,normal,alta,urgente'],
            'notes' => ['nullable', 'string'],
        ]);

        AppointmentRequest::create([
            'client_id' => $validated['client_id'],
            'vehicle_id' => $validated['vehicle_id'],
            'requested_date' => $validated['requested_date'],
            'preferred_time' => $validated['preferred_time'] ?? null,
            'service_type' => $validated['service_type'],
            'description' => $validated['description'],
            'priority' => $validated['priority'],
            'notes' => $validated['notes'] ?? null,
            'source' => 'manual',
            'status' => 'confirmada',
        ]);

        return redirect()
            ->route('advisor.appointments.index')
            ->with('success', 'Cita agendada correctamente.');
    }

    public function show(AppointmentRequest $appointment)
    {
        $appointment->load(['client', 'vehicle']);

        return view('advisor.appointments.show', [
            'appointment' => $appointment,
        ]);
    }

    public function edit(AppointmentRequest $appointment)
    {
        if ($appointment->status === 'completada' || $appointment->status === 'cancelada') {
            return redirect()
                ->route('advisor.appointments.index')
                ->with('error', 'No se pueden editar citas completadas o canceladas.');
        }

        return view('advisor.appointments.edit', [
            'appointment' => $appointment,
            'clients' => User::where('role', 'cliente')->where('status', 'activo')->orderBy('name')->get(),
            'vehicles' => Vehicle::where('status', 'activo')->orderBy('plate')->get(),
        ]);
    }

    public function update(Request $request, AppointmentRequest $appointment)
    {
        if ($appointment->status === 'completada' || $appointment->status === 'cancelada') {
            return redirect()
                ->route('advisor.appointments.index')
                ->with('error', 'No se pueden editar citas completadas o canceladas.');
        }

        $validated = $request->validate([
            'client_id' => ['required', 'exists:users,id'],
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'requested_date' => ['required', 'date', 'after_or_equal:today'],
            'preferred_time' => ['nullable', 'string', 'max:50'],
            'service_type' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'priority' => ['required', 'in:baja,normal,alta,urgente'],
            'notes' => ['nullable', 'string'],
        ]);

        $appointment->update($validated);

        return redirect()
            ->route('advisor.appointments.index')
            ->with('success', 'Cita actualizada correctamente.');
    }

    public function reschedule(Request $request, AppointmentRequest $appointment)
    {
        if ($appointment->status === 'completada' || $appointment->status === 'cancelada') {
            return redirect()
                ->route('advisor.appointments.index')
                ->with('error', 'No se pueden reprogramar citas completadas o canceladas.');
        }

        $validated = $request->validate([
            'requested_date' => ['required', 'date', 'after_or_equal:today'],
            'preferred_time' => ['nullable', 'string', 'max:50'],
        ]);

        $appointment->update([
            'requested_date' => $validated['requested_date'],
            'preferred_time' => $validated['preferred_time'] ?? null,
        ]);

        return redirect()
            ->route('advisor.appointments.index')
            ->with('success', 'Cita reprogramada correctamente.');
    }

    public function cancel(AppointmentRequest $appointment)
    {
        if ($appointment->status === 'completada' || $appointment->status === 'cancelada') {
            return redirect()
                ->route('advisor.appointments.index')
                ->with('error', 'Esta cita ya está completada o cancelada.');
        }

        $appointment->update(['status' => 'cancelada']);

        return redirect()
            ->route('advisor.appointments.index')
            ->with('success', 'Cita cancelada correctamente.');
    }

    public function calendar(Request $request)
    {
        $date = $request->string('date')->toString() ?: today()->toDateString();

        $appointments = AppointmentRequest::whereDate('requested_date', $date)
            ->with(['client', 'vehicle'])
            ->orderBy('preferred_time')
            ->get();

        return view('advisor.appointments.calendar', [
            'appointments' => $appointments,
            'date' => $date,
        ]);
    }
}
