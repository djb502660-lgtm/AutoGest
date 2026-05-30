<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Maintenance;
use App\Models\ServiceOrder;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        return view('admin.reports.index', [
            'vehicles' => Vehicle::orderBy('plate')->get(),
        ]);
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'in:mantenimientos,gastos,vehiculos,pendientes'],
            'vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $type = $validated['type'];
        $vehicleId = $validated['vehicle_id'] ?? null;
        $from = $validated['from'] ?? null;
        $to = $validated['to'] ?? null;

        $data = match ($type) {
            'mantenimientos' => $this->maintenanceReport($vehicleId, $from, $to),
            'gastos' => $this->expensesReport($vehicleId, $from, $to),
            'vehiculos' => $this->vehiclesReport($vehicleId),
            'pendientes' => $this->pendingReport($vehicleId),
        };

        return view('admin.reports.result', [
            'type' => $type,
            'title' => $data['title'],
            'summary' => $data['summary'],
            'rows' => $data['rows'],
            'columns' => $data['columns'],
            'filters' => $validated,
            'vehicles' => Vehicle::orderBy('plate')->get(),
        ]);
    }

    private function maintenanceReport(?int $vehicleId, ?string $from, ?string $to): array
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
            'rows' => $items->map(fn ($m) => [
                $m->performed_at?->format('d/m/Y') ?? '—',
                $m->vehicle->plate,
                $m->typeLabel(),
                $m->description,
                $m->mechanic->name,
                '$'.number_format($m->cost, 2),
                $m->statusLabel(),
            ]),
        ];
    }

    private function expensesReport(?int $vehicleId, ?string $from, ?string $to): array
    {
        $query = Maintenance::with('vehicle')
            ->when($vehicleId, fn ($q) => $q->where('vehicle_id', $vehicleId))
            ->when($from, fn ($q) => $q->whereDate('performed_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('performed_at', '<=', $to))
            ->where('status', 'completado');

        $items = $query->get();
        $byVehicle = $items->groupBy('vehicle_id');

        return [
            'title' => 'Reporte de gastos',
            'summary' => [
                'Gasto total' => '$'.number_format($items->sum('cost'), 2),
                'Vehículos' => $byVehicle->count(),
                'Promedio por servicio' => '$'.number_format($items->count() ? $items->avg('cost') : 0, 2),
            ],
            'columns' => ['Vehículo', 'Servicios', 'Gasto total'],
            'rows' => $byVehicle->map(function ($group) {
                $vehicle = $group->first()->vehicle;

                return [
                    $vehicle->displayName(),
                    $group->count(),
                    '$'.number_format($group->sum('cost'), 2),
                ];
            })->values(),
        ];
    }

    private function vehiclesReport(?int $vehicleId): array
    {
        $items = Vehicle::with('client')
            ->when($vehicleId, fn ($q) => $q->where('id', $vehicleId))
            ->orderBy('plate')
            ->get();

        return [
            'title' => 'Reporte de vehículos',
            'summary' => [
                'Total vehículos' => $items->count(),
                'Activos' => $items->where('status', 'activo')->count(),
                'En taller' => $items->where('status', 'en_taller')->count(),
            ],
            'columns' => ['Placa', 'Marca', 'Modelo', 'Año', 'Kilometraje', 'Cliente', 'Estado'],
            'rows' => $items->map(fn ($v) => [
                $v->plate,
                $v->brand,
                $v->model,
                $v->year ?? '—',
                number_format($v->mileage).' km',
                $v->client->name,
                $v->statusLabel(),
            ]),
        ];
    }

    private function pendingReport(?int $vehicleId): array
    {
        $maintenances = Maintenance::with('vehicle')
            ->whereIn('status', ['pendiente', 'en_proceso'])
            ->when($vehicleId, fn ($q) => $q->where('vehicle_id', $vehicleId))
            ->orderBy('performed_at')
            ->get();

        $orders = ServiceOrder::with('vehicle')
            ->whereIn('status', ['recibida', 'en_proceso'])
            ->when($vehicleId, fn ($q) => $q->where('vehicle_id', $vehicleId))
            ->orderBy('scheduled_at')
            ->get();

        return [
            'title' => 'Reporte de pendientes',
            'summary' => [
                'Mantenimientos pendientes' => $maintenances->count(),
                'Órdenes abiertas' => $orders->count(),
                'Urgentes' => $orders->where('priority', 'urgente')->count(),
            ],
            'columns' => ['Tipo', 'Referencia', 'Vehículo', 'Descripción', 'Estado', 'Fecha'],
            'rows' => $maintenances->map(fn ($m) => [
                'Mantenimiento',
                '—',
                $m->vehicle->plate,
                $m->description,
                $m->statusLabel(),
                $m->performed_at?->format('d/m/Y') ?? 'Sin fecha',
            ])->concat($orders->map(fn ($o) => [
                'Orden',
                $o->order_number,
                $o->vehicle->plate,
                $o->description ?? '—',
                ucfirst(str_replace('_', ' ', $o->status)),
                $o->scheduled_at?->format('d/m/Y') ?? '—',
            ])),
        ];
    }
}
