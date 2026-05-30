<?php

namespace App\Http\Controllers;

use App\Models\Maintenance;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class MechanicVehicleController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $search = $request->string('search')->trim();

        $vehicles = Vehicle::with('client')
            ->whereIn('id', $user->accessibleVehicleIds())
            ->when($search->isNotEmpty(), function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('plate', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%")
                        ->orWhere('model', 'like', "%{$search}%");
                });
            })
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

        $vehicle->load('client');

        $maintenances = Maintenance::with('mechanic', 'serviceOrder')
            ->where('vehicle_id', $vehicle->id)
            ->orderByDesc('performed_at')
            ->get();

        $tab = $request->string('tab', 'info')->toString();

        return view('mechanic.vehicles.show', compact('vehicle', 'maintenances', 'tab'));
    }
}
