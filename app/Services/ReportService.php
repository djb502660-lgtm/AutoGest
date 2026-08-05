<?php

namespace App\Services;

use App\Models\Maintenance;
use App\Models\MaintenanceSchedule;
use App\Models\Vehicle;

class ReportService
{
    private $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    public function getMaintenanceReport(?int $vehicleId = null, ?string $from = null, ?string $to = null): array
    {
        $query = Maintenance::with(['vehicle', 'mechanic'])
            ->when($vehicleId, fn ($q) => $q->where('vehicle_id', $vehicleId))
            ->when($from, fn ($q) => $q->whereDate('performed_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('performed_at', '<=', $to))
            ->orderByDesc('performed_at');

        $items = $query->get();

        return [
            'title' => 'Reporte de mantenimientos',
            'summary' => [
                'Total registros' => $items->count(),
                'Completados' => $items->where('status', 'completado')->count(),
                'Costo total' => '$'.number_format($items->sum('cost'), 2),
            ],
            'columns' => ['Fecha', 'Vehículo', 'Tipo', 'Descripción', 'Mecánico', 'Costo', 'Estado'],
            'rows' => $items->map(function ($m) {
                return [
                    $m->performed_at?->format('Y-m-d') ?? 'N/A',
                    $m->vehicle->plate.' — '.$m->vehicle->brand.' '.$m->vehicle->model,
                    $m->typeLabel(),
                    $m->description,
                    $m->mechanic->name ?? 'N/A',
                    '$'.number_format($m->cost, 2),
                    $m->statusLabel(),
                ];
            })->toArray(),
        ];
    }

    public function getExpensesReport(?int $vehicleId = null, ?string $from = null, ?string $to = null): array
    {
        $query = Maintenance::with(['vehicle', 'serviceOrder'])
            ->where('status', 'completado')
            ->when($vehicleId, fn ($q) => $q->where('vehicle_id', $vehicleId))
            ->when($from, fn ($q) => $q->whereDate('performed_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('performed_at', '<=', $to))
            ->orderByDesc('performed_at');

        $items = $query->get();

        return [
            'title' => 'Reporte de gastos',
            'summary' => [
                'Total registros' => $items->count(),
                'Total gastos' => '$'.number_format($items->sum('cost'), 2),
                'Gasto promedio' => '$'.number_format($items->avg('cost'), 2),
            ],
            'columns' => ['Fecha', 'Vehículo', 'Descripción', 'Orden de servicio', 'Costo'],
            'rows' => $items->map(function ($m) {
                return [
                    $m->performed_at?->format('Y-m-d') ?? 'N/A',
                    $m->vehicle->plate.' — '.$m->vehicle->brand.' '.$m->vehicle->model,
                    $m->description,
                    $m->serviceOrder->reference ?? 'N/A',
                    '$'.number_format($m->cost, 2),
                ];
            })->toArray(),
        ];
    }

    public function getVehiclesReport(?int $vehicleId = null): array
    {
        $query = Vehicle::orderBy('plate')
            ->when($vehicleId, fn ($q) => $q->where('id', $vehicleId));

        $items = $query->get();

        return [
            'title' => 'Reporte de vehículos',
            'summary' => [
                'Total vehículos' => $items->count(),
                'Activos' => $items->where('status', 'activo')->count(),
                'Kilometraje promedio' => number_format($items->avg('mileage')).' km',
            ],
            'columns' => ['Placa', 'Vehículo', 'Año', 'Kilometraje', 'Estado', 'Cliente'],
            'rows' => $items->map(function ($v) {
                return [
                    $v->plate,
                    $v->brand.' '.$v->model,
                    $v->year,
                    number_format($v->mileage).' km',
                    $v->statusLabel(),
                    $v->client->name ?? 'N/A',
                ];
            })->toArray(),
        ];
    }

    public function getPendingReport(?int $vehicleId = null): array
    {
        $query = MaintenanceSchedule::with('vehicle')
            ->whereIn('status', ['programado', 'vencido'])
            ->when($vehicleId, fn ($q) => $q->where('vehicle_id', $vehicleId))
            ->orderBy('scheduled_date');

        $items = $query->get();

        return [
            'title' => 'Reporte de pendientes',
            'summary' => [
                'Total pendientes' => $items->count(),
                'Programados' => $items->where('status', 'programado')->count(),
                'Vencidos' => $items->where('status', 'vencido')->count(),
            ],
            'columns' => ['ID', 'Vehículo', 'Título', 'Prioridad', 'Fecha programada', 'Estado'],
            'rows' => $items->map(function ($s) {
                return [
                    $s->id,
                    $s->vehicle->plate.' — '.$s->vehicle->brand,
                    $s->title,
                    'Media',
                    $s->scheduled_date?->format('Y-m-d') ?? 'N/A',
                    $s->statusLabel(),
                ];
            })->toArray(),
        ];
    }

    public function getFiltersLabel(array $filters): string
    {
        $types = [
            'mantenimientos' => 'Mantenimientos',
            'gastos' => 'Gastos',
            'vehiculos' => 'Vehículos',
            'pendientes' => 'Pendientes',
        ];

        $parts = ['Tipo: '.($types[$filters['type']] ?? $filters['type'])];

        if (! empty($filters['vehicle_id'])) {
            $vehicle = Vehicle::find($filters['vehicle_id']);
            $parts[] = 'Vehículo: '.($vehicle?->plate ?? $filters['vehicle_id']);
        }

        if (! empty($filters['from'])) {
            $parts[] = 'Desde: '.date('d/m/Y', strtotime($filters['from']));
        }

        if (! empty($filters['to'])) {
            $parts[] = 'Hasta: '.date('d/m/Y', strtotime($filters['to']));
        }

        return implode(' · ', $parts);
    }

    public function buildReportData(array $validated): array
    {
        $type = $validated['type'];
        $vehicleId = $validated['vehicle_id'] ?? null;
        $from = $validated['from'] ?? null;
        $to = $validated['to'] ?? null;

        $data = match ($type) {
            'mantenimientos' => $this->getMaintenanceReport($vehicleId, $from, $to),
            'gastos' => $this->getExpensesReport($vehicleId, $from, $to),
            'vehiculos' => $this->getVehiclesReport($vehicleId),
            'pendientes' => $this->getPendingReport($vehicleId),
        };

        $data['type'] = $type;
        $data['filters_label'] = $this->getFiltersLabel($validated);

        $this->auditService->logReportAction(
            'report_generated',
            "Reporte de tipo {$type} generado con filtros: {$data['filters_label']}",
            auth()->id(),
            null,
            ['type' => $type, 'filters' => $validated]
        );

        return $data;
    }
}
