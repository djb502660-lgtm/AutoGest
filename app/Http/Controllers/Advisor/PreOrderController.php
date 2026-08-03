<?php

namespace App\Http\Controllers\Advisor;

use App\Http\Controllers\Controller;
use App\Models\AppointmentRequest;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PreOrderController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->string('search')->trim();
        $source = $request->string('source')->toString();
        $status = $request->string('status')->toString();

        $preOrders = AppointmentRequest::query()
            ->with(['client', 'vehicle', 'vehicleModelTemplate'])
            ->when($search->isNotEmpty(), function ($query) use ($search) {
                $query->whereHas('client', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('vehicle', fn ($q) => $q->where('plate', 'like', "%{$search}%"));
            })
            ->when($source !== '', fn ($q) => $q->where('source', $source))
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('advisor.pre-orders.index', [
            'preOrders' => $preOrders,
            'search' => $search->toString(),
            'source' => $source,
            'status' => $status,
        ]);
    }

    public function create()
    {
        return view('advisor.pre-orders.create', [
            'clients' => User::where('role', 'cliente')->where('status', 'activo')->orderBy('name')->get(),
            'vehicles' => Vehicle::where('status', 'activo')->orderBy('plate')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => ['required', 'exists:users,id'],
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'vehicle_model_template_id' => ['nullable', 'exists:vehicle_model_templates,id'],
            'requested_date' => ['required', 'date', 'after:today'],
            'preferred_time' => ['nullable', 'string', 'max:50'],
            'service_type' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'priority' => ['required', 'in:baja,normal,alta,urgente'],
            'notes' => ['nullable', 'string'],
        ]);

        AppointmentRequest::create([
            'client_id' => $validated['client_id'],
            'vehicle_id' => $validated['vehicle_id'],
            'vehicle_model_template_id' => $validated['vehicle_model_template_id'] ?? null,
            'requested_date' => $validated['requested_date'],
            'preferred_time' => $validated['preferred_time'] ?? null,
            'service_type' => $validated['service_type'],
            'description' => $validated['description'],
            'priority' => $validated['priority'],
            'notes' => $validated['notes'] ?? null,
            'source' => 'manual',
            'status' => 'pendiente',
        ]);

        return redirect()
            ->route('advisor.pre-orders.index')
            ->with('success', 'Preorden creada correctamente.');
    }

    public function show(AppointmentRequest $preOrder)
    {
        $preOrder->load(['client', 'vehicle', 'vehicleModelTemplate']);

        return view('advisor.pre-orders.show', [
            'preOrder' => $preOrder,
        ]);
    }

    public function edit(AppointmentRequest $preOrder)
    {
        if ($preOrder->status !== 'pendiente') {
            return redirect()
                ->route('advisor.pre-orders.index')
                ->with('error', 'Solo se pueden editar preordenes pendientes.');
        }

        return view('advisor.pre-orders.edit', [
            'preOrder' => $preOrder,
            'clients' => User::where('role', 'cliente')->where('status', 'activo')->orderBy('name')->get(),
            'vehicles' => Vehicle::where('status', 'activo')->orderBy('plate')->get(),
        ]);
    }

    public function update(Request $request, AppointmentRequest $preOrder)
    {
        if ($preOrder->status !== 'pendiente') {
            return redirect()
                ->route('advisor.pre-orders.index')
                ->with('error', 'Solo se pueden editar preordenes pendientes.');
        }

        $validated = $request->validate([
            'client_id' => ['required', 'exists:users,id'],
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'vehicle_model_template_id' => ['nullable', 'exists:vehicle_model_templates,id'],
            'requested_date' => ['required', 'date', 'after:today'],
            'preferred_time' => ['nullable', 'string', 'max:50'],
            'service_type' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'priority' => ['required', 'in:baja,normal,alta,urgente'],
            'notes' => ['nullable', 'string'],
        ]);

        $preOrder->update($validated);

        return redirect()
            ->route('advisor.pre-orders.index')
            ->with('success', 'Preorden actualizada correctamente.');
    }

    public function confirm(AppointmentRequest $preOrder)
    {
        if ($preOrder->status !== 'pendiente') {
            return redirect()
                ->route('advisor.pre-orders.index')
                ->with('error', 'Solo se pueden confirmar preordenes pendientes.');
        }

        $preOrder->update(['status' => 'confirmada']);

        return redirect()
            ->route('advisor.pre-orders.index')
            ->with('success', 'Preorden confirmada correctamente.');
    }

    public function reject(AppointmentRequest $preOrder)
    {
        if ($preOrder->status !== 'pendiente') {
            return redirect()
                ->route('advisor.pre-orders.index')
                ->with('error', 'Solo se pueden rechazar preordenes pendientes.');
        }

        $preOrder->update(['status' => 'rechazada']);

        return redirect()
            ->route('advisor.pre-orders.index')
            ->with('success', 'Preorden rechazada correctamente.');
    }

    public function convertToOrder(AppointmentRequest $preOrder)
    {
        if (! in_array($preOrder->status, ['confirmada', 'pendiente'])) {
            return redirect()
                ->route('advisor.pre-orders.index')
                ->with('error', 'Solo se pueden convertir preordenes confirmadas o pendientes.');
        }

        $orderNumber = 'OS-' . date('Y') . '-' . str_pad(ServiceOrder::count() + 1, 4, '0', STR_PAD_LEFT);

        $serviceOrder = ServiceOrder::create([
            'order_number' => $orderNumber,
            'vehicle_id' => $preOrder->vehicle_id,
            'client_id' => $preOrder->client_id,
            'advisor_id' => auth()->id(),
            'status' => 'recibida',
            'priority' => $preOrder->priority,
            'description' => $preOrder->description,
            'scheduled_at' => $preOrder->requested_date,
            'estimated_cost' => 0,
        ]);

        $preOrder->update([
            'status' => 'convertida',
            'service_order_id' => $serviceOrder->id,
        ]);

        return redirect()
            ->route('advisor.orders.show', $serviceOrder)
            ->with('success', 'Preorden convertida a orden de servicio correctamente.');
    }

    public function destroy(AppointmentRequest $preOrder)
    {
        if ($preOrder->status === 'convertida') {
            return redirect()
                ->route('advisor.pre-orders.index')
                ->with('error', 'No se puede eliminar una preorden ya convertida.');
        }

        $preOrder->delete();

        return redirect()
            ->route('advisor.pre-orders.index')
            ->with('success', 'Preorden eliminada correctamente.');
    }
}
