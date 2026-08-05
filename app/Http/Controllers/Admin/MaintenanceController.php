<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\MaintenanceRequest;
use App\Models\ActivityLog;
use App\Models\Maintenance;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Maintenance::class);

        $vehicleId = $request->string('vehicle_id')->toString();
        $type = $request->string('type')->toString();
        $status = $request->string('status')->toString();
        $from = $request->string('from')->toString();
        $to = $request->string('to')->toString();

        $maintenances = Maintenance::with(['vehicle', 'mechanic'])
            ->when($vehicleId !== '', fn ($q) => $q->where('vehicle_id', $vehicleId))
            ->when($type !== '', fn ($q) => $q->where('type', $type))
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->when($from !== '', fn ($q) => $q->whereDate('performed_at', '>=', $from))
            ->when($to !== '', fn ($q) => $q->whereDate('performed_at', '<=', $to))
            ->orderByDesc('performed_at')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('admin.maintenances.index', [
            'maintenances' => $maintenances,
            'vehicles' => Vehicle::orderBy('plate')->get(),
            'vehicleId' => $vehicleId,
            'type' => $type,
            'status' => $status,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function create()
    {
        $this->authorize('create', Maintenance::class);

        return view('admin.maintenances.create', $this->formData());
    }

    public function store(MaintenanceRequest $request)
    {
        $this->authorize('create', Maintenance::class);

        $validated = $request->validated();
        $validated['service_order_id'] = $validated['service_order_id'] ?: null;

        $maintenance = Maintenance::create($validated);
        $maintenance->load('vehicle');

        $this->syncOrderCost($maintenance->service_order_id);

        ActivityLog::record(
            'maintenance.created',
            "Se registró mantenimiento: {$maintenance->description} para {$maintenance->vehicle->plate}.",
            $maintenance,
        );

        return redirect()->route('maintenances.index')->with('success', 'Mantenimiento registrado correctamente.');
    }

    public function edit(Maintenance $maintenance)
    {
        $this->authorize('update', $maintenance);

        return view('admin.maintenances.edit', [
            'maintenance' => $maintenance,
            ...$this->formData(),
        ]);
    }

    public function show(Maintenance $maintenance)
    {
        $this->authorize('view', $maintenance);

        $maintenance->load(['vehicle', 'mechanic', 'serviceOrder.photos']);

        return view('admin.maintenances.show', compact('maintenance'));
    }

    public function update(MaintenanceRequest $request, Maintenance $maintenance)
    {
        $this->authorize('update', $maintenance);

        $validated = $request->validated();
        $validated['service_order_id'] = $validated['service_order_id'] ?: null;

        $oldOrderId = $maintenance->service_order_id;
        $maintenance->update($validated);

        $this->syncOrderCost($maintenance->service_order_id);
        if ($oldOrderId && $oldOrderId !== $maintenance->service_order_id) {
            $this->syncOrderCost($oldOrderId);
        }

        ActivityLog::record(
            'maintenance.updated',
            "Se actualizó mantenimiento: {$maintenance->description}.",
            $maintenance,
        );

        return redirect()->route('maintenances.index')->with('success', 'Mantenimiento actualizado correctamente.');
    }

    public function destroy(Maintenance $maintenance)
    {
        $this->authorize('delete', $maintenance);

        $orderId = $maintenance->service_order_id;
        $description = $maintenance->description;
        $maintenance->delete();

        $this->syncOrderCost($orderId);

        ActivityLog::record('maintenance.deleted', "Se eliminó mantenimiento: {$description}.");

        return redirect()->route('maintenances.index')->with('success', 'Mantenimiento eliminado correctamente.');
    }

    private function formData(): array
    {
        return [
            'vehicles' => Vehicle::with('client')->orderBy('plate')->get(),
            'mechanics' => User::where('role', UserRole::Mechanic)->where('status', 'activo')->orderBy('name')->get(),
            'orders' => ServiceOrder::with('vehicle')->latest()->take(50)->get(),
        ];
    }

    private function syncOrderCost(?int $serviceOrderId): void
    {
        if (! $serviceOrderId) {
            return;
        }

        $order = ServiceOrder::find($serviceOrderId);
        $order?->update(['total_cost' => $order->maintenances()->sum('cost') + $order->maintenances()->sum('parts_cost') + $order->maintenances()->sum('labor_cost')]);
    }
}
