<?php

namespace Tests\Feature;

use App\Models\Maintenance;
use App\Models\MaintenanceSchedule;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_reports_index(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)
            ->get(route('reports.index'));

        $response->assertOk();
        $response->assertSee('Reportes del sistema');
    }

    public function test_admin_can_generate_maintenance_report(): void
    {
        $admin = User::factory()->admin()->create();
        $mechanic = User::factory()->mechanic()->create();
        $client = User::factory()->client()->create();

        $vehicle = Vehicle::create([
            'client_id' => $client->id,
            'plate' => 'RPT-001',
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2022,
            'mileage' => 45000,
            'status' => 'activo',
        ]);

        Maintenance::create([
            'vehicle_id' => $vehicle->id,
            'mechanic_id' => $mechanic->id,
            'type' => 'preventivo',
            'description' => 'Cambio de aceite',
            'cost' => 80.00,
            'status' => 'completado',
            'performed_at' => now()->subWeek(),
        ]);

        $response = $this->actingAs($admin)
            ->get(route('reports.generate', [
                'type' => 'mantenimientos',
                'vehicle_id' => $vehicle->id,
                'from' => now()->subMonth()->format('Y-m-d'),
                'to' => now()->format('Y-m-d'),
            ]));

        $response->assertOk();
        $response->assertSee('Reporte de mantenimientos');
        $response->assertSee('Cambio de aceite');
    }

    public function test_admin_can_generate_expenses_report(): void
    {
        $admin = User::factory()->admin()->create();
        $mechanic = User::factory()->mechanic()->create();
        $client = User::factory()->client()->create();

        $vehicle = Vehicle::create([
            'client_id' => $client->id,
            'plate' => 'RPT-002',
            'brand' => 'Honda',
            'model' => 'Civic',
            'year' => 2021,
            'mileage' => 35000,
            'status' => 'activo',
        ]);

        Maintenance::create([
            'vehicle_id' => $vehicle->id,
            'mechanic_id' => $mechanic->id,
            'type' => 'correctivo',
            'description' => 'Cambio de frenos',
            'cost' => 150.00,
            'status' => 'completado',
            'performed_at' => now()->subDays(10),
        ]);

        $response = $this->actingAs($admin)
            ->get(route('reports.generate', [
                'type' => 'gastos',
            ]));

        $response->assertOk();
        $response->assertSee('Reporte de gastos');
        $response->assertSee('150');
    }

    public function test_admin_can_generate_vehicles_report(): void
    {
        $admin = User::factory()->admin()->create();
        $client = User::factory()->client()->create();

        Vehicle::create([
            'client_id' => $client->id,
            'plate' => 'RPT-003',
            'brand' => 'Nissan',
            'model' => 'Sentra',
            'year' => 2023,
            'mileage' => 15000,
            'status' => 'activo',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('reports.generate', [
                'type' => 'vehiculos',
            ]));

        $response->assertOk();
        $response->assertSee('Reporte de vehículos');
        $response->assertSee('RPT-003');
    }

    public function test_admin_can_generate_pending_report(): void
    {
        $admin = User::factory()->admin()->create();
        $mechanic = User::factory()->mechanic()->create();
        $client = User::factory()->client()->create();

        $vehicle = Vehicle::create([
            'client_id' => $client->id,
            'plate' => 'RPT-004',
            'brand' => 'Kia',
            'model' => 'Rio',
            'year' => 2022,
            'mileage' => 25000,
            'status' => 'en_taller',
        ]);

        MaintenanceSchedule::create([
            'vehicle_id' => $vehicle->id,
            'assigned_mechanic_id' => $mechanic->id,
            'scheduled_date' => now()->addDays(7)->toDateString(),
            'title' => 'Cambio de aceite programado',
            'status' => 'programado',
            'priority' => 'media',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('reports.generate', [
                'type' => 'pendientes',
            ]));

        $response->assertOk();
        $response->assertSee('Reporte de pendientes');
        $response->assertSee('Cambio de aceite programado');
    }

    public function test_admin_can_download_pdf_report(): void
    {
        $admin = User::factory()->admin()->create();
        $client = User::factory()->client()->create();

        $vehicle = Vehicle::create([
            'client_id' => $client->id,
            'plate' => 'RPT-005',
            'brand' => 'Ford',
            'model' => 'Fiesta',
            'year' => 2021,
            'mileage' => 40000,
            'status' => 'activo',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('reports.pdf', [
                'type' => 'vehiculos',
                'vehicle_id' => $vehicle->id,
            ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_admin_can_download_csv_report(): void
    {
        $admin = User::factory()->admin()->create();
        $client = User::factory()->client()->create();

        $vehicle = Vehicle::create([
            'client_id' => $client->id,
            'plate' => 'RPT-006',
            'brand' => 'Chevrolet',
            'model' => 'Onix',
            'year' => 2022,
            'mileage' => 20000,
            'status' => 'activo',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('reports.csv', [
                'type' => 'vehiculos',
                'vehicle_id' => $vehicle->id,
            ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $response->assertHeader('content-disposition');
    }

    public function test_pdf_download_logs_activity(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('reports.pdf', [
                'type' => 'vehiculos',
            ]));

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'report.downloaded',
            'user_id' => $admin->id,
        ]);
    }

    public function test_csv_download_logs_activity(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('reports.csv', [
                'type' => 'vehiculos',
            ]));

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'report.downloaded',
            'user_id' => $admin->id,
        ]);
    }

    public function test_report_validation_requires_type(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)
            ->get(route('reports.generate', []));

        $response->assertSessionHasErrors(['type']);
    }

    public function test_report_validation_rejects_invalid_type(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)
            ->get(route('reports.generate', [
                'type' => 'invalid_type',
            ]));

        $response->assertSessionHasErrors(['type']);
    }

    public function test_report_validation_rejects_invalid_date_range(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)
            ->get(route('reports.generate', [
                'type' => 'mantenimientos',
                'from' => now()->format('Y-m-d'),
                'to' => now()->subMonth()->format('Y-m-d'),
            ]));

        $response->assertSessionHasErrors(['to']);
    }
}
