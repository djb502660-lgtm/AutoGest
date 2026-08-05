<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\VehicleRequest;
use App\Models\ActivityLog;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleModelTemplate;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Vehicle::class);

        $search = $request->string('search')->trim();
        $status = $request->string('status')->toString();

        $vehicles = Vehicle::with('client')
            ->when($search->isNotEmpty(), function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('plate', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%")
                        ->orWhere('model', 'like', "%{$search}%");
                });
            })
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->orderBy('plate')
            ->paginate(10)
            ->withQueryString();

        return view('admin.vehicles.index', compact('vehicles', 'search', 'status'));
    }

    public function create()
    {
        $this->authorize('create', Vehicle::class);

        return view('admin.vehicles.create', [
            'clients' => $this->clients(),
        ]);
    }

    public function store(VehicleRequest $request)
    {
        $this->authorize('create', Vehicle::class);

        $vehicle = Vehicle::create($request->validated());

        VehicleModelTemplate::forVehicle($vehicle)->each(
            fn (VehicleModelTemplate $template) => $template->createScheduleFor($vehicle),
        );

        ActivityLog::record(
            'vehicle.created',
            "Se registró el vehículo {$vehicle->plate} ({$vehicle->brand} {$vehicle->model}).",
            $vehicle,
        );

        return redirect()->route('vehicles.index')->with('success', 'Vehículo registrado correctamente.');
    }

    public function edit(Vehicle $vehicle)
    {
        $this->authorize('update', $vehicle);

        return view('admin.vehicles.edit', [
            'vehicle' => $vehicle,
            'clients' => $this->clients(),
        ]);
    }

    public function update(VehicleRequest $request, Vehicle $vehicle)
    {
        $this->authorize('update', $vehicle);

        $vehicle->update($request->validated());

        ActivityLog::record(
            'vehicle.updated',
            "Se actualizó el vehículo {$vehicle->plate}.",
            $vehicle,
        );

        return redirect()->route('vehicles.index')->with('success', 'Vehículo actualizado correctamente.');
    }

    public function destroy(Vehicle $vehicle)
    {
        $this->authorize('delete', $vehicle);

        if ($vehicle->serviceOrders()->exists() || $vehicle->maintenances()->exists()) {
            $vehicle->update(['status' => 'inactivo']);

            ActivityLog::record(
                'vehicle.deactivated',
                "Se desactivó el vehículo {$vehicle->plate} (tiene registros vinculados).",
                $vehicle,
            );

            return redirect()->route('vehicles.index')->with('success', 'Vehículo desactivado por tener historial vinculado.');
        }

        $plate = $vehicle->plate;
        $vehicle->delete();

        ActivityLog::record('vehicle.deleted', "Se eliminó el vehículo {$plate}.");

        return redirect()->route('vehicles.index')->with('success', 'Vehículo eliminado correctamente.');
    }

    private function clients()
    {
        return User::where('role', UserRole::Client)
            ->where('status', 'activo')
            ->orderBy('name')
            ->get();
    }
}
