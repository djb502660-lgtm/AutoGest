<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AdminReportMail;
use App\Models\ActivityLog;
use App\Models\Maintenance;
use App\Models\ServiceOrder;
use App\Models\Vehicle;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ReportController extends Controller
{
    public function index()
    {
        $maintenances = Maintenance::with(['vehicle', 'mechanic', 'serviceOrder'])->orderByDesc('performed_at')->get();
        $vehicles = Vehicle::orderBy('plate')->get();
        $schedules = \App\Models\MaintenanceSchedule::with('vehicle')->orderBy('scheduled_date')->get();

        // Preparar datos para los reportes
        $reportData = [
            'mantenimientos' => $maintenances->map(function ($m) {
                return [
                    $m->serviceOrder->reference ?? 'N/A',
                    $m->vehicle->plate . ' — ' . $m->vehicle->brand . ' ' . $m->vehicle->model,
                    $m->typeLabel(),
                    $m->mechanic->name ?? 'N/A',
                    $m->performed_at?->format('Y-m-d') ?? 'N/A'
                ];
            })->toArray(),
            'gastos' => $maintenances->where('status', 'completado')->map(function ($m) {
                return [
                    'Repuestos',
                    $m->description,
                    $m->vehicle->plate,
                    '$' . number_format($m->cost, 2)
                ];
            })->toArray(),
            'vehiculos' => $vehicles->map(function ($v) {
                return [
                    $v->plate,
                    $v->brand . ' ' . $v->model,
                    $v->year,
                    number_format($v->mileage) . ' km',
                    $v->statusLabel()
                ];
            })->toArray(),
            'pendientes' => $schedules->map(function ($s) {
                return [
                    $s->id,
                    $s->vehicle->plate . ' — ' . $s->vehicle->brand,
                    $s->title,
                    'Media',
                    $s->scheduled_date?->format('Y-m-d') ?? 'N/A'
                ];
            })->toArray(),
        ];

        return view('admin.reports.index', [
            'vehicles' => $vehicles,
            'maintenances' => $maintenances,
            'schedules' => $schedules,
            'reportData' => $reportData,
        ]);
    }

    public function generate(Request $request)
    {
        $validated = $this->validateFilters($request);
        $report = $this->buildReportData($validated);

        return view('admin.reports.result', [
            ...$report,
            'filters' => $validated,
            'vehicles' => Vehicle::orderBy('plate')->get(),
        ]);
    }

    public function downloadPdf(Request $request)
    {
        $validated = $this->validateFilters($request);
        $report = $this->buildReportData($validated);

        $pdf = Pdf::loadView('admin.reports.pdf', [
            'title' => $report['title'],
            'summary' => $report['summary'],
            'columns' => $report['columns'],
            'rows' => $report['rows'],
            'filtersLabel' => $report['filters_label'],
            'generatedAt' => now()->format('d/m/Y H:i'),
        ]);

        $filename = 'reporte-'.$validated['type'].'-'.now()->format('Y-m-d-His').'.pdf';

        ActivityLog::record(
            'report.downloaded',
            "Se descargó el reporte «{$report['title']}» en PDF.",
            user: $request->user(),
        );

        return $pdf->download($filename);
    }

    public function sendEmail(Request $request)
    {
        $validated = $this->validateFilters($request);
        $report = $this->buildReportData($validated);
        $admin = $request->user();

        Mail::to($admin->email)->send(new AdminReportMail($report, $admin));

        ActivityLog::record(
            'report.emailed',
            "Se envió por correo el reporte «{$report['title']}» a {$admin->email}.",
            user: $admin,
        );

        return redirect()
            ->route('reports.generate', $validated)
            ->with('success', "Reporte enviado correctamente a {$admin->email}. Revisa tu bandeja de entrada.");
    }

    public function downloadCsv(Request $request)
    {
        $validated = $this->validateFilters($request);
        $report = $this->buildReportData($validated);

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$report['type'].'-'.now()->format('Y-m-d-His').'.csv"',
        ];

        $callback = function () use ($report) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8 Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Write headers
            fputcsv($file, $report['columns'], ';');
            
            // Write rows
            foreach ($report['rows'] as $row) {
                fputcsv($file, $row, ';');
            }
            
            fclose($file);
        };

        ActivityLog::record(
            'report.downloaded',
            "Se descargó el reporte «{$report['title']}» en CSV.",
            user: $request->user(),
        );

        return response()->stream($callback, 200, $headers);
    }

    private function validateFilters(Request $request): array
    {
        return $request->validate([
            'type' => ['required', 'in:mantenimientos,gastos,vehiculos,pendientes'],
            'vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);
    }

    private function buildReportData(array $validated): array
    {
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

        $data['type'] = $type;
        $data['filters_label'] = $this->filtersLabel($validated);

        return $data;
    }

    private function filtersLabel(array $filters): string
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
