<?php

namespace Tests\Feature;

use App\Models\Alert;
use App\Models\Maintenance;
use App\Models\MaintenanceSchedule;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SprintFourOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_can_view_dashboard_with_stats(): void
    {
        $client = User::factory()->client()->create();
        $mechanic = User::factory()->mechanic()->create();

        $vehicle = Vehicle::create([
            'client_id' => $client->id,
            'plate' => 'SPR-401',
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
            'performed_at' => now()->subMonth(),
        ]);

        $response = $this->actingAs($client)
            ->get(route('client.dashboard'));

        $response->assertOk();
        $response->assertSee('Dashboard principal');
        $response->assertSee('1'); // vehículos
        $response->assertSee('80'); // gastos totales
    }

    public function test_client_can_view_their_vehicles(): void
    {
        $client = User::factory()->client()->create();

        Vehicle::create([
            'client_id' => $client->id,
            'plate' => 'SPR-402',
            'brand' => 'Honda',
            'model' => 'Civic',
            'year' => 2021,
            'mileage' => 35000,
            'status' => 'activo',
        ]);

        $response = $this->actingAs($client)
            ->get(route('client.vehicles.index'));

        $response->assertOk();
        $response->assertSee('SPR-402');
        $response->assertSee('Honda Civic');
    }

    public function test_client_can_view_vehicle_details(): void
    {
        $client = User::factory()->client()->create();
        $mechanic = User::factory()->mechanic()->create();

        $vehicle = Vehicle::create([
            'client_id' => $client->id,
            'plate' => 'SPR-403',
            'brand' => 'Nissan',
            'model' => 'Sentra',
            'year' => 2023,
            'mileage' => 15000,
            'status' => 'activo',
        ]);

        Maintenance::create([
            'vehicle_id' => $vehicle->id,
            'mechanic_id' => $mechanic->id,
            'type' => 'correctivo',
            'description' => 'Cambio de frenos',
            'cost' => 120.00,
            'status' => 'completado',
            'performed_at' => now()->subWeek(),
        ]);

        $response = $this->actingAs($client)
            ->get(route('client.vehicles.show', $vehicle));

        $response->assertOk();
        $response->assertSee('SPR-403');
        $response->assertSee('Nissan Sentra');
        $response->assertSee('Cambio de frenos');
    }

    public function test_client_can_view_maintenance_history(): void
    {
        $client = User::factory()->client()->create();
        $mechanic = User::factory()->mechanic()->create();

        $vehicle = Vehicle::create([
            'client_id' => $client->id,
            'plate' => 'SPR-404',
            'brand' => 'Kia',
            'model' => 'Rio',
            'year' => 2020,
            'mileage' => 60000,
            'status' => 'activo',
        ]);

        Maintenance::create([
            'vehicle_id' => $vehicle->id,
            'mechanic_id' => $mechanic->id,
            'type' => 'preventivo',
            'description' => 'Revisión general',
            'cost' => 50.00,
            'status' => 'completado',
            'performed_at' => now()->subDays(15),
        ]);

        $response = $this->actingAs($client)
            ->get(route('client.maintenances.history'));

        $response->assertOk();
        $response->assertSee('Revisión general');
    }

    public function test_client_can_view_upcoming_maintenances(): void
    {
        $client = User::factory()->client()->create();

        $vehicle = Vehicle::create([
            'client_id' => $client->id,
            'plate' => 'SPR-405',
            'brand' => 'Chevrolet',
            'model' => 'Onix',
            'year' => 2022,
            'mileage' => 25000,
            'status' => 'activo',
        ]);

        MaintenanceSchedule::create([
            'vehicle_id' => $vehicle->id,
            'title' => 'Cambio de aceite programado',
            'scheduled_date' => now()->addDays(10),
            'status' => 'programado',
        ]);

        $response = $this->actingAs($client)
            ->get(route('client.maintenances.upcoming'));

        $response->assertOk();
        $response->assertSee('Cambio de aceite programado');
    }

    public function test_client_can_view_their_orders(): void
    {
        $client = User::factory()->client()->create();
        $mechanic = User::factory()->mechanic()->create();

        $vehicle = Vehicle::create([
            'client_id' => $client->id,
            'plate' => 'SPR-406',
            'brand' => 'Ford',
            'model' => 'Fiesta',
            'year' => 2021,
            'mileage' => 40000,
            'status' => 'activo',
        ]);

        ServiceOrder::create([
            'order_number' => 'OS-2026-0401',
            'vehicle_id' => $vehicle->id,
            'client_id' => $client->id,
            'mechanic_id' => $mechanic->id,
            'created_by' => $client->id,
            'source' => 'manual',
            'status' => 'en_proceso',
            'priority' => 'normal',
            'description' => 'Diagnóstico de motor',
            'progress' => 50,
        ]);

        $response = $this->actingAs($client)
            ->get(route('client.orders.index'));

        $response->assertOk();
        $response->assertSee('OS-2026-0401');
        $response->assertSee('Diagnóstico de motor');
    }

    public function test_client_can_view_order_details(): void
    {
        $client = User::factory()->client()->create();
        $mechanic = User::factory()->mechanic()->create();

        $vehicle = Vehicle::create([
            'client_id' => $client->id,
            'plate' => 'SPR-407',
            'brand' => 'Volkswagen',
            'model' => 'Golf',
            'year' => 2022,
            'mileage' => 30000,
            'status' => 'en_taller',
        ]);

        $order = ServiceOrder::create([
            'order_number' => 'OS-2026-0402',
            'vehicle_id' => $vehicle->id,
            'client_id' => $client->id,
            'mechanic_id' => $mechanic->id,
            'created_by' => $client->id,
            'source' => 'manual',
            'status' => 'en_proceso',
            'priority' => 'alta',
            'description' => 'Revisión de suspensión',
            'progress' => 75,
        ]);

        $response = $this->actingAs($client)
            ->get(route('client.orders.show', $order));

        $response->assertOk();
        $response->assertSee('OS-2026-0402');
        $response->assertSee('Revisión de suspensión');
    }

    public function test_client_can_view_expenses(): void
    {
        $client = User::factory()->client()->create();
        $mechanic = User::factory()->mechanic()->create();

        $vehicle = Vehicle::create([
            'client_id' => $client->id,
            'plate' => 'SPR-408',
            'brand' => 'Mazda',
            'model' => '3',
            'year' => 2021,
            'mileage' => 38000,
            'status' => 'activo',
        ]);

        Maintenance::create([
            'vehicle_id' => $vehicle->id,
            'mechanic_id' => $mechanic->id,
            'type' => 'preventivo',
            'description' => 'Cambio de aceite',
            'cost' => 75.00,
            'status' => 'completado',
            'performed_at' => now()->subMonth(),
        ]);

        $response = $this->actingAs($client)
            ->get(route('client.expenses.index'));

        $response->assertOk();
        $response->assertSee('Gastos');
        $response->assertSee('75');
    }

    public function test_client_can_view_notifications(): void
    {
        $client = User::factory()->client()->create();

        $vehicle = Vehicle::create([
            'client_id' => $client->id,
            'plate' => 'SPR-409',
            'brand' => 'Hyundai',
            'model' => 'Elantra',
            'year' => 2022,
            'mileage' => 28000,
            'status' => 'activo',
        ]);

        Alert::create([
            'user_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'type' => 'maintenance_due',
            'title' => 'Mantenimiento programado',
            'message' => 'Tienes un mantenimiento próximo',
            'severity' => 'info',
            'is_resolved' => false,
        ]);

        $response = $this->actingAs($client)
            ->get(route('client.notifications.index'));

        $response->assertOk();
        $response->assertSee('Mantenimiento programado');
    }

    public function test_client_can_mark_notification_as_read(): void
    {
        $client = User::factory()->client()->create();

        $vehicle = Vehicle::create([
            'client_id' => $client->id,
            'plate' => 'SPR-410',
            'brand' => 'Renault',
            'model' => 'Clio',
            'year' => 2021,
            'mileage' => 32000,
            'status' => 'activo',
        ]);

        $alert = Alert::create([
            'user_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'type' => 'custom',
            'title' => 'Alerta de prueba',
            'message' => 'Mensaje de prueba',
            'severity' => 'info',
            'is_resolved' => false,
        ]);

        $this->actingAs($client)
            ->put(route('client.notifications.read', $alert))
            ->assertRedirect();

        $this->assertDatabaseHas('alerts', [
            'id' => $alert->id,
            'is_read' => true,
        ]);
    }

    public function test_client_can_update_profile(): void
    {
        $client = User::factory()->client()->create([
            'name' => 'Juan Pérez',
            'email' => 'juan@autogest.test',
            'phone' => '0991234567',
        ]);

        $this->actingAs($client)
            ->put(route('client.profile.update'), [
                'name' => 'Juan Pérez Actualizado',
                'email' => 'juan.nuevo@autogest.test',
                'phone' => '0999876543',
                'password' => null,
                'password_confirmation' => null,
            ])
            ->assertRedirect(route('client.profile.edit'));

        $this->assertDatabaseHas('users', [
            'id' => $client->id,
            'name' => 'Juan Pérez Actualizado',
            'email' => 'juan.nuevo@autogest.test',
            'phone' => '0999876543',
        ]);
    }

    public function test_automatic_alerts_command_generates_insurance_expiry_alerts(): void
    {
        $client = User::factory()->client()->create();
        $admin = User::factory()->admin()->create(['status' => 'activo']);

        Vehicle::create([
            'client_id' => $client->id,
            'plate' => 'SPR-411',
            'brand' => 'Suzuki',
            'model' => 'Swift',
            'year' => 2022,
            'mileage' => 20000,
            'status' => 'activo',
            'insurance_expiry' => now()->addDays(15),
        ]);

        $this->artisan('autogest:generate-alerts')
            ->assertExitCode(0);

        $this->assertDatabaseHas('alerts', [
            'user_id' => $client->id,
            'type' => 'insurance_expiry',
            'is_resolved' => false,
        ]);

        $this->assertDatabaseHas('alerts', [
            'user_id' => $admin->id,
            'type' => 'insurance_expiry',
            'is_resolved' => false,
        ]);
    }

    public function test_automatic_alerts_command_generates_inspection_expiry_alerts(): void
    {
        $client = User::factory()->client()->create();
        $admin = User::factory()->admin()->create(['status' => 'activo']);

        Vehicle::create([
            'client_id' => $client->id,
            'plate' => 'SPR-412',
            'brand' => 'Peugeot',
            'model' => '208',
            'year' => 2021,
            'mileage' => 25000,
            'status' => 'activo',
            'inspection_expiry' => now()->addDays(10),
        ]);

        $this->artisan('autogest:generate-alerts')
            ->assertExitCode(0);

        $this->assertDatabaseHas('alerts', [
            'user_id' => $client->id,
            'type' => 'inspection_expiry',
            'is_resolved' => false,
        ]);
    }

    public function test_automatic_alerts_command_generates_maintenance_schedule_alerts(): void
    {
        $client = User::factory()->client()->create();
        $admin = User::factory()->admin()->create(['status' => 'activo']);

        $vehicle = Vehicle::create([
            'client_id' => $client->id,
            'plate' => 'SPR-413',
            'brand' => 'Fiat',
            'model' => 'Uno',
            'year' => 2020,
            'mileage' => 45000,
            'status' => 'activo',
        ]);

        MaintenanceSchedule::create([
            'vehicle_id' => $vehicle->id,
            'title' => 'Cambio de aceite',
            'scheduled_date' => now()->addDays(5),
            'status' => 'programado',
        ]);

        $this->artisan('autogest:generate-alerts')
            ->assertExitCode(0);

        $this->assertDatabaseHas('alerts', [
            'user_id' => $client->id,
            'type' => 'maintenance_due',
            'is_resolved' => false,
        ]);
    }

    public function test_automatic_alerts_does_not_duplicate_existing_alerts(): void
    {
        $client = User::factory()->client()->create();

        $vehicle = Vehicle::create([
            'client_id' => $client->id,
            'plate' => 'SPR-414',
            'brand' => 'Citroën',
            'model' => 'C3',
            'year' => 2022,
            'mileage' => 18000,
            'status' => 'activo',
            'insurance_expiry' => now()->addDays(15),
        ]);

        Alert::create([
            'user_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'type' => 'insurance_expiry',
            'title' => 'Seguro próximo a vencer',
            'message' => 'Mensaje de prueba',
            'severity' => 'warning',
            'due_date' => $vehicle->insurance_expiry,
            'is_resolved' => false,
        ]);

        $this->artisan('autogest:generate-alerts')
            ->assertExitCode(0);

        // Debe haber solo una alerta, no duplicada
        $alerts = Alert::where('user_id', $client->id)
            ->where('vehicle_id', $vehicle->id)
            ->where('type', 'insurance_expiry')
            ->where('due_date', $vehicle->insurance_expiry->toDateString())
            ->where('is_resolved', false)
            ->get();

        $this->assertCount(1, $alerts);
    }
}
