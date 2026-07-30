<?php

namespace App\Http\Controllers\Advisor;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleModelTemplate;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', ServiceOrder::class);

        $user = $request->user();
        $search = $request->string('search')->trim();
        $status = $request->string('status')->toString();

        $orders = ServiceOrder::query()
            ->where(function ($q) use ($user) {
                $q->where('advisor_id', $user->id)
                    ->orWhere('created_by', $user->id);
            })
            ->with(['vehicle', 'client', 'mechanic'])
            ->when($search->isNotEmpty(), function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('order_number', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('vehicle', fn ($v) => $v->where('plate', 'like', "%{$search}%"));
                });
            })
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('advisor.orders.index', compact('orders', 'search', 'status'));
    }

    public function create()
    {
        $this->authorize('create', ServiceOrder::class);

        return view('advisor.orders.create', $this->formData());
    }

    public function store(Request $request)
    {
        $this->authorize('create', ServiceOrder::class);

        $validated = $this->validateOrder($request);
        $vehicle = Vehicle::findOrFail($validated['vehicle_id']);
        $user = $request->user();

        $order = ServiceOrder::create([
            'order_number' => ServiceOrder::generateOrderNumber(),
            'vehicle_id' => $vehicle->id,
            'client_id' => $vehicle->client_id,
            'mechanic_id' => $validated['mechanic_id'] ?? null,
            'advisor_id' => $user->id,
            'created_by' => $user->id,
            'source' => 'manual',
            'status' => 'recibida',
            'progress' => 0,
            'priority' => $validated['priority'],
            'description' => $validated['description'],
            'scheduled_at' => $validated['scheduled_at'] ?? null,
            'estimated_cost' => $validated['estimated_cost'] ?? null,
        ]);

        ActivityLog::record(
            'order.created',
            "Asesor registró la orden {$order->order_number} para {$vehicle->plate}.",
            $order,
        );

        return redirect()
            ->route('advisor.orders.show', $order)
            ->with('success', 'Orden de trabajo registrada correctamente.');
    }

    public function show(ServiceOrder $order)
    {
        $this->authorize('view', $order);

        $order->load(['vehicle.client', 'client', 'mechanic', 'advisor', 'maintenances', 'comments.user']);

        return view('advisor.orders.show', array_merge(
            [
                'order' => $order,
                'vehicleTemplates' => VehicleModelTemplate::forVehicle($order->vehicle),
            ],
            $this->formData(),
        ));
    }

    public function edit(ServiceOrder $order)
    {
        $this->authorize('update', $order);

        $order->load('vehicle', 'client', 'mechanic');

        return view('advisor.orders.edit', array_merge(
            [
                'order' => $order,
                'vehicleTemplates' => VehicleModelTemplate::forVehicle($order->vehicle),
            ],
            $this->formData(),
        ));
    }

    public function update(Request $request, ServiceOrder $order)
    {
        $this->authorize('update', $order);

        $validated = $this->validateOrder($request, $order);
        $vehicle = Vehicle::findOrFail($validated['vehicle_id']);

        $order->update([
            'vehicle_id' => $vehicle->id,
            'client_id' => $vehicle->client_id,
            'mechanic_id' => $validated['mechanic_id'] ?? null,
            'priority' => $validated['priority'],
            'description' => $validated['description'],
            'scheduled_at' => $validated['scheduled_at'] ?? null,
            'estimated_cost' => $validated['estimated_cost'] ?? null,
            'status' => $validated['status'],
        ]);

        ActivityLog::record(
            'order.updated',
            "Asesor actualizó la orden {$order->order_number}.",
            $order,
        );

        return redirect()
            ->route('advisor.orders.show', $order)
            ->with('success', 'Orden actualizada correctamente.');
    }

    public function assignMechanic(Request $request, ServiceOrder $order)
    {
        $this->authorize('assign', $order);

        $validated = $request->validate([
            'mechanic_id' => ['required', 'exists:users,id'],
        ]);

        $mechanic = User::where('id', $validated['mechanic_id'])
            ->where('role', UserRole::Mechanic)
            ->where('status', 'activo')
            ->firstOrFail();

        $previousMechanic = $order->mechanic;
        $order->update(['mechanic_id' => $mechanic->id]);

        $description = $previousMechanic && $previousMechanic->isNot($mechanic)
            ? "Asesor reasignó la orden {$order->order_number} de {$previousMechanic->name} a {$mechanic->name}."
            : "Asesor asignó a {$mechanic->name} en la orden {$order->order_number}.";

        ActivityLog::record(
            'order.mechanic_assigned',
            $description,
            $order,
        );

        return redirect()
            ->route('advisor.orders.show', $order)
            ->with('success', $previousMechanic && $previousMechanic->isNot($mechanic)
                ? "Orden reasignada correctamente a {$mechanic->name}."
                : "Mecánico {$mechanic->name} asignado a la orden.");
    }

    private function validateOrder(Request $request, ?ServiceOrder $order = null): array
    {
        $validated = $request->validate([
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'mechanic_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(fn ($q) => $q->where('role', UserRole::Mechanic->value)->where('status', 'activo')),
            ],
            'priority' => ['required', Rule::in(['baja', 'normal', 'alta', 'urgente'])],
            'description' => ['required', 'string', 'max:1000'],
            'scheduled_at' => ['nullable', 'date'],
            'estimated_cost' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', Rule::in(['recibida', 'en_proceso', 'completada', 'entregada', 'cancelada'])],
        ], [], [
            'vehicle_id' => 'vehículo',
            'mechanic_id' => 'mecánico',
            'description' => 'descripción',
            'scheduled_at' => 'fecha programada',
            'estimated_cost' => 'costo estimado',
        ]);

        $validated['status'] = $validated['status'] ?? $order?->status ?? 'recibida';

        return $validated;
    }

    private function formData(): array
    {
        return [
            'vehicles' => Vehicle::with('client')->orderBy('plate')->get(),
            'mechanics' => User::where('role', UserRole::Mechanic)
                ->where('status', 'activo')
                ->orderBy('name')
                ->get(),
            'vehicleTemplates' => collect(),
        ];
    }
}
