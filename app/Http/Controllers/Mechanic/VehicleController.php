<?php

namespace App\Http\Controllers\Mechanic;

use App\Http\Controllers\Controller;

use App\Models\Maintenance;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $search = $request->string('search')->trim();

        $vehicles = Vehicle::with('client')
            ->whereIn('id', $user->accessibleVehicleIds())
            ->search($search)
            ->orderBy('plate')
            ->paginate(10)
            ->withQueryString();

        return view('mechanic.vehicles.index', compact('vehicles', 'search'));
    }

    public function show(Request $request, Vehicle $vehicle)
    {
        $user = $request->user();

        if (! $user->accessibleVehicleIds()->contains($vehicle->id)) {
            abort(403, 'No tienes acceso a este vehículo.');
        }

        $vehicle->load(['client', 'serviceOrders.mechanic']);

        // Cargar todas las órdenes de servicio del vehículo, con mecánico y cliente asociados
        $orders = $vehicle->serviceOrders()->with(['mechanic', 'client'])->orderByDesc('created_at')->get();

        $tab = $request->string('tab', 'info')->toString();

        return view('mechanic.vehicles.show', compact('vehicle', 'orders', 'tab'));
    }
}
