<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Maintenance;
use App\Models\ServiceOrder;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MechanicMaintenanceController extends Controller
{
    public function create(Request $request)
    {
        $user = $request->user();

        $orders = $user->assignedOrders()
            ->with('vehicle')
            ->whereIn('status', ['recibida', 'en_proceso'])
            ->get();

        $vehicles = Vehicle::whereIn('id', $user->accessibleVehicleIds())
            ->orderBy('plate')
            ->get();

        return view('mechanic.maintenances.create', [
            'orders' => $orders,
            'vehicles' => $vehicles,
            'selectedOrder' => $request->integer('order_id') ?: null,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'service_order_id' => ['nullable', 'exists:service_orders,id'],
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'type' => ['required', Rule::in(['preventivo', 'correctivo'])],
            'description' => ['required', 'string', 'max:255'],
            'mileage_at_service' => ['nullable', 'integer', 'min:0'],
            'parts_used' => ['nullable', 'string'],
            'technical_notes' => ['nullable', 'string'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['pendiente', 'en_proceso', 'completado'])],
            'performed_at' => ['nullable', 'date'],
        ]);

        if ($validated['service_order_id']) {
            $order = ServiceOrder::findOrFail($validated['service_order_id']);
            $this->authorize('update', $order);
        } elseif (! $user->accessibleVehicleIds()->contains($validated['vehicle_id'])) {
            abort(403, 'No tienes acceso a este vehículo.');
        }

        $maintenance = Maintenance::create([
            ...$validated,
            'mechanic_id' => $user->id,
            'cost' => $validated['cost'] ?? 0,
            'service_order_id' => $validated['service_order_id'] ?: null,
        ]);

        if ($maintenance->service_order_id) {
            $order = ServiceOrder::find($maintenance->service_order_id);
            $order?->update(['total_cost' => $order->maintenances()->sum('cost')]);
        }

        ActivityLog::record(
            'maintenance.created',
            "Mecánico registró mantenimiento: {$maintenance->description}.",
            $maintenance,
        );

        return redirect()
            ->route('mechanic.history')
            ->with('success', 'Mantenimiento registrado correctamente.');
    }
}
