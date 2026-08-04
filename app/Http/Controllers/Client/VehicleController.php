<?php

namespace App\Http\Controllers\Client;

use App\Contracts\Repositories\VehicleRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    private VehicleRepositoryInterface $vehicleRepository;

    public function __construct(VehicleRepositoryInterface $vehicleRepository)
    {
        $this->vehicleRepository = $vehicleRepository;
    }

    public function index(Request $request)
    {
        $search = $request->string('search')->trim()->toString();

        $vehicles = $request->user()->vehicles()
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('plate', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%")
                        ->orWhere('model', 'like', "%{$search}%");
                });
            })
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
