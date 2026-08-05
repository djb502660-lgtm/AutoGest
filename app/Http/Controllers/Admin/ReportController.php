<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AdminReportMail;
use App\Models\ActivityLog;
use App\Models\Maintenance;
use App\Models\MaintenanceSchedule;
use App\Models\Vehicle;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ReportController extends Controller
{
    private ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index()
    {
        $maintenances = $this->reportService->getMaintenanceReport();
        $vehicles = Vehicle::orderBy('plate')->get();
        $schedules = MaintenanceSchedule::with('vehicle')->orderBy('scheduled_date')->get();

        return view('admin.reports.index', [
            'vehicles' => $vehicles,
            'maintenances' => Maintenance::with(['vehicle', 'mechanic', 'serviceOrder'])->orderByDesc('performed_at')->get(),
            'schedules' => $schedules,
            'reportData' => [
                'mantenimientos' => $maintenances['rows'],
                'gastos' => $this->reportService->getExpensesReport()['rows'],
                'vehiculos' => $this->reportService->getVehiclesReport()['rows'],
                'pendientes' => $this->reportService->getPendingReport()['rows'],
            ],
        ]);
    }

    public function generate(Request $request)
    {
        $validated = $this->validateFilters($request);
        $report = $this->reportService->buildReportData($validated);

        return view('admin.reports.result', [
            ...$report,
            'filters' => $validated,
            'vehicles' => Vehicle::orderBy('plate')->get(),
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
        $report = $this->reportService->buildReportData($validated);
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
        $report = $this->reportService->buildReportData($validated);

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
}
