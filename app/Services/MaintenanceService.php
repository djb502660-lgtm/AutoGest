<?php

namespace App\Services;

use App\Contracts\Repositories\MaintenanceRepositoryInterface;
use App\DTOs\MaintenanceDTO;
use App\Models\Maintenance;
use Carbon\Carbon;

class MaintenanceService
{
    public function __construct(
        protected MaintenanceRepositoryInterface $maintenanceRepository
    ) {}

    public function createMaintenance(MaintenanceDTO $dto)
    {
        return $this->maintenanceRepository->create($dto->toArray());
    }

    public function updateMaintenance($id, MaintenanceDTO $dto)
    {
        return $this->maintenanceRepository->update($id, $dto->toArray());
    }

    public function deleteMaintenance($id)
    {
        return $this->maintenanceRepository->delete($id);
    }

    public function updateStatus($id, $status)
    {
        return $this->maintenanceRepository->update($id, ['status' => $status]);
    }

    public function findByServiceOrder($serviceOrderId)
    {
        return $this->maintenanceRepository->findByServiceOrder($serviceOrderId);
    }

    public function findByVehicle($vehicleId)
    {
        return $this->maintenanceRepository->findByVehicle($vehicleId);
    }

    public function getRecentMaintenances($limit = 10)
    {
        return $this->maintenanceRepository->getRecentMaintenances($limit);
    }

    public function countMaintenances()
    {
        return $this->maintenanceRepository->count();
    }

    public function getClientExpensesSummary($clientId, $year = null)
    {
        $year = $year ?? (int) now()->year;
        $startOfYear = Carbon::create($year, 1, 1)->startOfDay();

        $maintenances = Maintenance::query()
            ->whereHas('vehicle', function ($q) use ($clientId) {
                $q->where('client_id', $clientId);
            })
            ->where('status', 'completado')
            ->where('performed_at', '>=', $startOfYear)
            ->with('vehicle')
            ->get();

        $count = $maintenances->count();
        $total = $maintenances->sum('cost');
        $mostExpensive = $maintenances->sortByDesc('cost')->first();

        return [
            'count' => $count,
            'total' => $total,
            'most_expensive' => $mostExpensive,
            'year' => $year,
        ];
    }

    public function getOrderMaintenancesSummary($serviceOrderId)
    {
        $maintenances = $this->findByServiceOrder($serviceOrderId);

        $completed = $maintenances->where('status', 'completado')->pluck('type')->take(5);
        $pending = $maintenances->whereIn('status', ['pendiente', 'en_proceso'])->pluck('type')->take(5);

        return [
            'completed' => $completed,
            'pending' => $pending,
        ];
    }
}
