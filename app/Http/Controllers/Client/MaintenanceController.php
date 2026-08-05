<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Maintenance;
use App\Models\MaintenanceSchedule;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    public function history(Request $request)
    {
        $vehicleIds = $request->user()->vehicles()->pluck('id');

        $maintenances = Maintenance::with('vehicle')
            ->whereIn('vehicle_id', $vehicleIds)
            ->orderByDesc('performed_at')
            ->paginate(12);

        return view('client.maintenances.history', compact('maintenances'));
    }

    public function upcoming(Request $request)
    {
        $vehicleIds = $request->user()->vehicles()->pluck('id');

        $schedules = MaintenanceSchedule::with('vehicle', 'assignedMechanic')
            ->whereIn('vehicle_id', $vehicleIds)
            ->where('status', 'programado')
            ->where('scheduled_date', '>=', now()->toDateString())
            ->orderBy('scheduled_date')
            ->paginate(10);

        return view('client.maintenances.upcoming', compact('schedules'));
    }
}
