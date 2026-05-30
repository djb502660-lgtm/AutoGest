<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\ActivityLog;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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

        return view('vehicles.index', compact('vehicles', 'search', 'status'));
    }

    public function create()
    {
        $this->authorize('create', Vehicle::class);

        return view('vehicles.create', [
            'clients' => $this->clients(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Vehicle::class);

        $validated = $request->validate($this->rules());

        $vehicle = Vehicle::create($validated);

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

        return view('vehicles.edit', [
            'vehicle' => $vehicle,
            'clients' => $this->clients(),
        ]);
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $this->authorize('update', $vehicle);

        $vehicle->update($request->validate($this->rules($vehicle)));

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

    private function rules(?Vehicle $vehicle = null): array
    {
        return [
            'client_id' => ['required', 'exists:users,id'],
            'plate' => ['required', 'string', 'max:20', Rule::unique('vehicles', 'plate')->ignore($vehicle?->id)],
            'brand' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:100'],
            'year' => ['nullable', 'integer', 'min:1980', 'max:'.(date('Y') + 1)],
            'color' => ['nullable', 'string', 'max:50'],
            'mileage' => ['required', 'integer', 'min:0'],
            'vin' => ['nullable', 'string', 'max:50'],
            'status' => ['required', Rule::in(['activo', 'inactivo', 'en_taller'])],
            'insurance_expiry' => ['nullable', 'date'],
            'inspection_expiry' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
