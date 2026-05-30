<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Enums\UserRole;
use App\Models\ActivityLog;
use App\Models\Maintenance;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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

    public function store(Request $request)
    {
        $this->authorize('create', Maintenance::class);

        $validated = $request->validate($this->rules());
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

    public function update(Request $request, Maintenance $maintenance)
    {
        $this->authorize('update', $maintenance);

        $validated = $request->validate($this->rules());
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

    private function rules(): array
    {
        return [
            'service_order_id' => ['nullable', 'exists:service_orders,id'],
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'mechanic_id' => ['required', 'exists:users,id'],
            'type' => ['required', Rule::in(['preventivo', 'correctivo'])],
            'description' => ['required', 'string', 'max:255'],
            'mileage_at_service' => ['nullable', 'integer', 'min:0'],
            'parts_used' => ['nullable', 'string'],
            'technical_notes' => ['nullable', 'string'],
            'cost' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['pendiente', 'en_proceso', 'completado', 'cancelado'])],
            'performed_at' => ['nullable', 'date'],
        ];
    }

    private function syncOrderCost(?int $serviceOrderId): void
    {
        if (! $serviceOrderId) {
            return;
        }

        $order = ServiceOrder::find($serviceOrderId);
        $order?->update(['total_cost' => $order->maintenances()->sum('cost')]);
    }
}
