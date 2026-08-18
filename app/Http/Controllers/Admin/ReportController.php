<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\AuditService;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    private ReportService $reportService;
    private AuditService $auditService;

    public function __construct(ReportService $reportService, AuditService $auditService)
    {
        $this->reportService = $reportService;
        $this->auditService = $auditService;
    }

    public function index()
    {
        $vehicles = Vehicle::with('client')->orderBy('plate')->get();
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        $clientIds = Vehicle::whereNotNull('client_id')->distinct()->pluck('client_id');
        $clients = User::where('role', UserRole::Client)
            ->orWhereIn('id', $clientIds)
            ->orderBy('name')
            ->get();

        return view('admin.reports.index', [
            'vehicles' => $vehicles->map(fn (Vehicle $v) => [
                'id' => $v->id,
                'client_id' => $v->client_id,
                'plate' => $v->plate,
                'brand' => $v->brand,
                'model' => $v->model,
                'year' => $v->year,
            ])->values(),
            'categories' => $categories,
            'brands' => $brands,
            'clients' => $clients->map(fn (User $c) => [
                'id' => $c->id,
                'name' => $c->name,
            ])->values(),
        ]);
    }

    public function downloadPdf(Request $request)
    {
        $validated = $this->validateFilters($request);
        $report = $this->reportService->buildReportData($validated);

        $pdf = Pdf::loadView('admin.reports.pdf', [
            'title' => $report['title'],
            'summary' => $report['summary'],
            'columns' => $report['columns'],
            'rows' => $report['rows'],
            'filtersLabel' => $report['filters_label'],
            'generatedAt' => now()->format('d/m/Y H:i'),
        ]);

        $filename = 'reporte-'.$validated['type'].'-'.now()->format('Y-m-d-His').'.pdf';

        $this->auditService->logReportAction(
            'report_downloaded',
            "Reporte «{$report['title']}» descargado en PDF",
            auth()->id(),
            null,
            ['type' => $validated['type'], 'format' => 'pdf']
        );

        return $pdf->download($filename);
    }

    public function generate(Request $request)
    {
        $validated = $this->validateFilters($request);
        $report = $this->reportService->buildReportData($validated);

        return view('admin.reports.result', [
            'title' => $report['title'],
            'summary' => $report['summary'],
            'columns' => $report['columns'],
            'rows' => $report['rows'],
            'filters' => $request->all(),
        ]);
    }

    public function downloadCsv(Request $request)
    {
        $validated = $this->validateFilters($request);
        $report = $this->reportService->buildReportData($validated);

        $this->auditService->logReportAction(
            'report_downloaded',
            "Reporte «{$report['title']}» descargado en CSV",
            auth()->id(),
            null,
            ['type' => $validated['type'], 'format' => 'csv']
        );

        $filename = 'reporte-'.$validated['type'].'-'.now()->format('Y-m-d-His').'.csv';
        
        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = $report['columns'];
        $rows = $report['rows'];

        $callback = function() use($columns, $rows) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for Excel UTF-8 support
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, $columns);
            foreach ($rows as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function validateFilters(Request $request): array
    {
        return $request->validate([
            'type' => ['required', 'in:mantenimientos,gastos,vehiculos,pendientes,inventario,productos,movimientos,categorias'],
            'scope' => ['nullable', 'in:vehicle,all'],
            'vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'client_id' => ['nullable', 'exists:users,id'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'status' => ['nullable', 'string'],
            'mechanic_id' => ['nullable', 'exists:users,id'],
            'maintenance_type' => ['nullable', 'in:preventivo,correctivo,garantia'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'stock_status' => ['nullable', 'in:all,low,out'],
        ]);
    }

    /**
     * Reporte individual de vehículo
     */
    public function vehicleDetail(Request $request, int $vehicleId)
    {
        $this->authorize('viewAny', ServiceOrder::class);

        $report = $this->reportService->getVehicleDetailReport($vehicleId);

        return view('admin.reports.vehicle-detail', [
            'report' => $report,
            'generatedAt' => now()->format('d/m/Y H:i'),
        ]);
    }

    /**
     * Reporte general de la flota
     */
    public function vehicleFleet(Request $request)
    {
        $this->authorize('viewAny', ServiceOrder::class);

        $filters = [
            'client_id' => $request->input('client_id'),
            'status' => $request->input('status'),
            'from' => $request->input('from'),
            'to' => $request->input('to'),
        ];

        $report = $this->reportService->getVehicleFleetReport($filters);

        return view('admin.reports.vehicle-fleet', [
            'report' => $report,
            'generatedAt' => now()->format('d/m/Y H:i'),
        ]);
    }

    /**
     * PDF de reporte individual de vehículo
     */
    public function downloadVehicleDetailPdf(int $vehicleId)
    {
        $this->authorize('viewAny', ServiceOrder::class);

        $report = $this->reportService->getVehicleDetailReport($vehicleId);

        $pdf = Pdf::loadView('admin.reports.pdf.vehicle-detail', [
            'report' => $report,
            'generatedAt' => now()->format('d/m/Y H:i'),
            'user' => auth()->user(),
        ]);

        $filename = 'expediente-vehiculo-'.$report['vehicle']->plate.'-'.now()->format('Y-m-d-His').'.pdf';

        $this->auditService->logReportAction(
            'report_downloaded',
            "Expediente del vehículo {$report['vehicle']->plate} descargado en PDF",
            auth()->id(),
            null,
            ['type' => 'vehicle_detail', 'vehicle_id' => $vehicleId]
        );

        return $pdf->download($filename);
    }

    /**
     * PDF de reporte general de flota
     */
    public function downloadVehicleFleetPdf(Request $request)
    {
        $this->authorize('viewAny', ServiceOrder::class);

        $filters = [
            'client_id' => $request->input('client_id'),
            'status' => $request->input('status'),
            'from' => $request->input('from'),
            'to' => $request->input('to'),
        ];

        $report = $this->reportService->getVehicleFleetReport($filters);

        $pdf = Pdf::loadView('admin.reports.pdf.vehicle-fleet', [
            'report' => $report,
            'generatedAt' => now()->format('d/m/Y H:i'),
            'user' => auth()->user(),
        ]);

        $filename = 'expediente-flota-completa-'.now()->format('Y-m-d-His').'.pdf';

        $this->auditService->logReportAction(
            'report_downloaded',
            "Expediente completo de la flota descargado en PDF",
            auth()->id(),
            null,
            ['type' => 'vehicle_fleet', 'filters' => $filters]
        );

        return $pdf->download($filename);
    }
}
