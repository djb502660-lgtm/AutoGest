<?php

namespace App\Http\Controllers\Advisor;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceOrderRequest;
use App\Models\ActivityLog;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleModelTemplate;
use App\Services\ServiceOrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    private $serviceOrderService;

    public function __construct(ServiceOrderService $serviceOrderService)
    {
        $this->serviceOrderService = $serviceOrderService;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', ServiceOrder::class);

        $user = $request->user();
        $search = $request->string('search')->trim();
        $status = $request->string('status')->toString();
        $priority = $request->string('priority')->toString();
        $mechanicId = $request->input('mechanic_id');
        $unassigned = $request->boolean('unassigned');

        $orders = ServiceOrder::query()
            ->where(function ($q) use ($user) {
                $q->where('advisor_id', $user->id)
                    ->orWhere('created_by', $user->id)
                    ->orWhereNull('advisor_id')
                    ->orWhere('source', 'chatbot');
            })
            ->with(['vehicle', 'client', 'mechanic'])
            ->when($search->isNotEmpty(), function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('order_number', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('vehicle', fn ($v) => $v->where('plate', 'like', "%{$search}%"))
                        ->orWhereHas('client', fn ($c) => $c->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->when($unassigned, fn ($q) => $q->whereNull('mechanic_id')->whereIn('status', ['recibida', 'en_proceso']))
            ->when($priority !== '', fn ($q) => $q->where('priority', $priority))
            ->when($mechanicId, fn ($q) => $q->where('mechanic_id', $mechanicId))
            ->when($priority === 'alta' || $priority === 'urgente', fn ($q) => $q->orderByRaw("FIELD(priority, 'urgente', 'alta', 'normal', 'baja')"))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('advisor.orders.index', compact('orders', 'search', 'status', 'priority', 'mechanicId', 'unassigned'));
    }

    public function create()
    {
        $this->authorize('create', ServiceOrder::class);

        return view('advisor.orders.create', $this->formData());
    }

    public function store(ServiceOrderRequest $request)
    {
        $this->authorize('create', ServiceOrder::class);

        $validated = $request->validated();
        $vehicle = Vehicle::findOrFail($validated['vehicle_id']);
        $user = $request->user();

        $orderData = [
            'vehicle_id' => $vehicle->id,
            'client_id' => $vehicle->client_id,
            'mechanic_id' => $validated['mechanic_id'] ?? null,
            'priority' => $validated['priority'],
            'description' => $validated['description'],
            'scheduled_at' => $validated['scheduled_at'] ?? null,
            'estimated_cost' => $validated['estimated_cost'] ?? null,
        ];

        $order = $this->serviceOrderService->createOrderFromAdvisor($orderData, $user->id);

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

        $order->load(['vehicle.client', 'client', 'mechanic', 'advisor', 'maintenances', 'comments.user', 'photos.user']);

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

    public function update(ServiceOrderRequest $request, ServiceOrder $order)
    {
        $this->authorize('update', $order);

        $validated = $request->validated();
        $vehicle = Vehicle::findOrFail($validated['vehicle_id']);

        $order->update([
            'vehicle_id' => $vehicle->id,
            'client_id' => $vehicle->client_id,
            'mechanic_id' => $validated['mechanic_id'] ?? null,
            'priority' => $validated['priority'],
            'description' => $validated['description'],
            'scheduled_at' => $validated['scheduled_at'] ?? null,
            'estimated_cost' => $validated['estimated_cost'] ?? null,
            'status' => $validated['status'] ?? $order->status,
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
        $this->serviceOrderService->reassignMechanic($order->id, $mechanic->id);

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
