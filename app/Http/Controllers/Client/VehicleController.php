<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;

use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->string('search')->trim()->toString();

        $vehicles = $request->user()->vehicles()
            ->search($search)
            ->orderBy('plate')
            ->paginate(10)
            ->withQueryString();

        return view('client.vehicles.index', compact('vehicles', 'search'));
    }

    public function show(Vehicle $vehicle)
    {
        $this->authorize('view', $vehicle);

        $vehicle->load([
            'maintenances' => fn ($q) => $q->orderByDesc('performed_at')->take(5),
            'maintenanceSchedules' => fn ($q) => $q->where('status', 'programado')->orderBy('scheduled_date'),
        ]);

        return view('client.vehicles.show', compact('vehicle'));
    }
}
