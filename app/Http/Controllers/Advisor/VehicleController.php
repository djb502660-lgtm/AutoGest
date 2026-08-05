<?php

namespace App\Http\Controllers\Advisor;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->string('search')->trim();
        $client = $request->string('client')->toString();

        $vehicles = Vehicle::query()
            ->with(['client'])
            ->when($search->isNotEmpty(), function ($query) use ($search) {
                $query->where('plate', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%");
            })
            ->when($client !== '', fn ($q) => $q->where('client_id', $client))
            ->orderBy('plate')
            ->paginate(10)
            ->withQueryString();

        return view('advisor.vehicles.index', [
            'vehicles' => $vehicles,
            'search' => $search->toString(),
            'client' => $client,
            'clients' => User::where('role', 'cliente')->where('status', 'activo')->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('advisor.vehicles.create', [
            'clients' => User::where('role', 'cliente')->where('status', 'activo')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => ['required', 'exists:users,id'],
            'plate' => ['required', 'string', 'max:20', 'unique:vehicles,plate'],
            'brand' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:100'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:'.(date('Y') + 1)],
            'color' => ['nullable', 'string', 'max:50'],
            'mileage' => ['required', 'integer', 'min:0'],
            'vin' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'in:activo,inactivo,en_taller'],
            'insurance_expiry' => ['nullable', 'date'],
            'inspection_expiry' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        Vehicle::create($validated);

        return redirect()
            ->route('advisor.vehicles.index')
            ->with('success', 'Vehículo registrado correctamente.');
    }

    public function show(Vehicle $vehicle)
    {
        $vehicle->load(['client', 'serviceOrders' => fn ($q) => $q->latest()->limit(5)]);

        return view('advisor.vehicles.show', [
            'vehicle' => $vehicle,
        ]);
    }

    public function edit(Vehicle $vehicle)
    {
        return view('advisor.vehicles.edit', [
            'vehicle' => $vehicle,
            'clients' => User::where('role', 'cliente')->where('status', 'activo')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'client_id' => ['required', 'exists:users,id'],
            'plate' => ['required', 'string', 'max:20', Rule::unique('vehicles', 'plate')->ignore($vehicle->id)],
            'brand' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:100'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:'.(date('Y') + 1)],
            'color' => ['nullable', 'string', 'max:50'],
            'mileage' => ['required', 'integer', 'min:0'],
            'vin' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'in:activo,inactivo,en_taller'],
            'insurance_expiry' => ['nullable', 'date'],
            'inspection_expiry' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $vehicle->update($validated);

        return redirect()
            ->route('advisor.vehicles.index')
            ->with('success', 'Vehículo actualizado correctamente.');
    }
}
