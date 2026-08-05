<?php

namespace App\Http\Controllers\Mechanic;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Maintenance;
use App\Models\OrderComment;
use App\Models\ServiceOrder;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MaintenanceController extends Controller
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

        $order = null;

        if ($validated['service_order_id']) {
            $order = ServiceOrder::findOrFail($validated['service_order_id']);
            $this->authorize('update', $order);
        } elseif (! $user->accessibleVehicleIds()->contains($validated['vehicle_id'])) {
            abort(403, 'No tienes acceso a este vehículo.');
        }

        $maintenance = DB::transaction(function () use ($validated, $user, $order) {
            $maintenance = Maintenance::create([
                ...$validated,
                'mechanic_id' => $user->id,
                'cost' => $validated['cost'] ?? 0,
                'service_order_id' => $validated['service_order_id'] ?: null,
            ]);

            $this->syncOperationalState($maintenance, $order, $user->id);

            return $maintenance;
        });

        ActivityLog::record(
            'maintenance.created',
            "Mecánico registró mantenimiento: {$maintenance->description}.",
            $maintenance,
        );

        return redirect()
            ->route('mechanic.history')
            ->with('success', 'Mantenimiento registrado correctamente.');
    }

    private function syncOperationalState(Maintenance $maintenance, ?ServiceOrder $order, int $mechanicId): void
    {
        $vehicle = Vehicle::find($maintenance->vehicle_id);

        if ($vehicle && $maintenance->mileage_at_service && $maintenance->mileage_at_service > $vehicle->mileage) {
            $vehicle->mileage = $maintenance->mileage_at_service;
        }

        if ($vehicle) {
            if (in_array($maintenance->status, ['pendiente', 'en_proceso'], true)) {
                $vehicle->status = 'en_taller';
            } elseif (! $vehicle->serviceOrders()->whereIn('status', ['recibida', 'en_proceso'])->exists()) {
                $vehicle->status = 'activo';
            }

            $vehicle->save();
        }

        if (! $order) {
            return;
        }

        $order->total_cost = $order->maintenances()->sum('cost');

        if ($maintenance->status === 'en_proceso') {
            $order->status = 'en_proceso';
            $order->started_at ??= $maintenance->performed_at ?? now();
            $order->progress = max((int) ($order->progress ?? 0), 50);
        }

        if ($maintenance->status === 'completado') {
            $order->status = $order->status === 'entregada' ? 'entregada' : 'completada';
            $order->progress = 100;
            $order->completed_at = $maintenance->performed_at ?? now();
            $order->started_at ??= $maintenance->performed_at ?? now();
        }

        $order->save();

        if ($vehicle && $maintenance->status === 'completado' && ! $vehicle->serviceOrders()->whereIn('status', ['recibida', 'en_proceso'])->exists()) {
            $vehicle->update(['status' => 'activo']);
        }

        $summary = "Trabajo registrado: {$maintenance->description}. Estado: {$maintenance->statusLabel()}.";

        if ($maintenance->parts_used) {
            $summary .= " Repuestos: {$maintenance->parts_used}.";
        }

        if ($maintenance->technical_notes) {
            $summary .= " Notas: {$maintenance->technical_notes}.";
        }

        OrderComment::create([
            'service_order_id' => $order->id,
            'user_id' => $mechanicId,
            'comment' => $summary,
        ]);
    }
}
