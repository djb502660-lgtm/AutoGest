<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\ActivityLog;
use App\Models\Alert;
use App\Models\ChatbotFaq;
use App\Models\Maintenance;
use App\Models\MaintenanceSchedule;
use App\Models\ServiceOrder;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleModelTemplate;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@autogest.test'],
            [
                'name' => 'Jokabeth Verenice Valdes Jaramillo',
                'password' => 'password',
                'role' => UserRole::Admin,
                'phone' => '0991234567',
                'status' => 'activo',
            ]);

        $mechanic1 = User::updateOrCreate(
            ['email' => 'mecanico1@autogest.test'],
            [
                'name' => 'Carlos Méndez',
                'password' => 'password',
                'role' => UserRole::Mechanic,
                'phone' => '0992345678',
                'status' => 'activo',
            ]);

        $mechanic2 = User::updateOrCreate(
            ['email' => 'mecanico2@autogest.test'],
            [
                'name' => 'Ana Torres',
                'password' => 'password',
                'role' => UserRole::Mechanic,
                'phone' => '0993456789',
                'status' => 'activo',
            ]);

        $client1 = User::updateOrCreate(
            ['email' => 'cliente1@autogest.test'],
            [
                'name' => 'María González',
                'password' => 'password',
                'role' => UserRole::Client,
                'phone' => '0994567890',
                'status' => 'activo',
            ]);

        $client2 = User::updateOrCreate(
            ['email' => 'cliente2@autogest.test'],
            [
                'name' => 'Pedro Ramírez',
                'password' => 'password',
                'role' => UserRole::Client,
                'phone' => '0995678901',
                'status' => 'activo',
            ]);

        $client3 = User::updateOrCreate(
            ['email' => 'cliente3@autogest.test'],
            [
                'name' => 'Lucía Herrera',
                'password' => 'password',
                'role' => UserRole::Client,
                'phone' => '0996789012',
                'status' => 'activo',
            ]);

        $advisor1 = User::updateOrCreate(
            ['email' => 'asesor1@autogest.test'],
            [
                'name' => 'Laura Mendieta',
                'password' => 'password',
                'role' => UserRole::Advisor,
                'phone' => '0997890123',
                'status' => 'activo',
            ]);

        $vehicles = [
            Vehicle::create([
                'client_id' => $client1->id,
                'plate' => 'ABC-123',
                'brand' => 'Toyota',
                'model' => 'Corolla',
                'year' => 2020,
                'color' => 'Blanco',
                'mileage' => 45200,
                'status' => 'activo',
                'insurance_expiry' => now()->addMonths(8),
                'inspection_expiry' => now()->subDays(5),
            ]),
            Vehicle::create([
                'client_id' => $client1->id,
                'plate' => 'DEF-456',
                'brand' => 'Hyundai',
                'model' => 'Tucson',
                'year' => 2019,
                'color' => 'Gris',
                'mileage' => 67800,
                'status' => 'en_taller',
                'insurance_expiry' => now()->addDays(5),
                'inspection_expiry' => now()->addMonths(3),
            ]),
            Vehicle::create([
                'client_id' => $client2->id,
                'plate' => 'GHI-789',
                'brand' => 'Chevrolet',
                'model' => 'Onix',
                'year' => 2021,
                'color' => 'Rojo',
                'mileage' => 32100,
                'status' => 'activo',
                'insurance_expiry' => now()->addMonths(6),
                'inspection_expiry' => now()->subDays(10),
            ]),
            Vehicle::create([
                'client_id' => $client2->id,
                'plate' => 'JKL-012',
                'brand' => 'Kia',
                'model' => 'Rio',
                'year' => 2018,
                'color' => 'Azul',
                'mileage' => 89500,
                'status' => 'activo',
                'insurance_expiry' => now()->addMonths(4),
                'inspection_expiry' => now()->addMonths(2),
            ]),
            Vehicle::create([
                'client_id' => $client3->id,
                'plate' => 'MNO-345',
                'brand' => 'Nissan',
                'model' => 'Sentra',
                'year' => 2022,
                'color' => 'Negro',
                'mileage' => 18400,
                'status' => 'activo',
                'insurance_expiry' => now()->addYear(),
                'inspection_expiry' => now()->addMonths(5),
            ]),
        ];

        $order1 = ServiceOrder::create([
            'order_number' => 'OS-2026-0001',
            'vehicle_id' => $vehicles[0]->id,
            'client_id' => $client1->id,
            'mechanic_id' => $mechanic1->id,
            'created_by' => $admin->id,
            'status' => 'completada',
            'priority' => 'normal',
            'description' => 'Revisión motor',
            'diagnosis' => 'Filtro de aire saturado, bujías en buen estado.',
            'recommendations' => 'Cambiar filtro en próximo servicio.',
            'scheduled_at' => now()->subDays(3),
            'started_at' => now()->subDays(3),
            'completed_at' => now()->subDays(2),
            'total_cost' => 185.50,
        ]);

        $order2 = ServiceOrder::create([
            'order_number' => 'OS-2026-0002',
            'vehicle_id' => $vehicles[1]->id,
            'client_id' => $client1->id,
            'mechanic_id' => $mechanic2->id,
            'created_by' => $admin->id,
            'status' => 'en_proceso',
            'priority' => 'alta',
            'description' => 'Cambio de frenos',
            'scheduled_at' => now(),
            'started_at' => now()->subHours(4),
            'estimated_cost' => 320.00,
            'total_cost' => 0,
        ]);

        $order3 = ServiceOrder::create([
            'order_number' => 'OS-2026-0003',
            'vehicle_id' => $vehicles[2]->id,
            'client_id' => $client2->id,
            'mechanic_id' => $mechanic1->id,
            'advisor_id' => $advisor1->id,
            'created_by' => $advisor1->id,
            'source' => 'manual',
            'status' => 'recibida',
            'priority' => 'urgente',
            'description' => 'Alerta suspensión',
            'scheduled_at' => now()->addDay(),
            'estimated_cost' => 450.00,
            'total_cost' => 0,
        ]);

        ServiceOrder::create([
            'order_number' => 'OS-2026-0005',
            'vehicle_id' => $vehicles[4]->id,
            'client_id' => $client3->id,
            'mechanic_id' => null,
            'advisor_id' => $advisor1->id,
            'created_by' => $advisor1->id,
            'source' => 'manual',
            'status' => 'recibida',
            'priority' => 'normal',
            'description' => 'Revisión general programada por asesoría',
            'scheduled_at' => now()->addDays(2),
            'estimated_cost' => 120.00,
            'total_cost' => 0,
        ]);

        $order4 = ServiceOrder::create([
            'order_number' => 'OS-2026-0004',
            'vehicle_id' => $vehicles[3]->id,
            'client_id' => $client2->id,
            'mechanic_id' => $mechanic2->id,
            'created_by' => $admin->id,
            'status' => 'entregada',
            'priority' => 'normal',
            'description' => 'Cambio de aceite y filtros',
            'scheduled_at' => now()->subDays(10),
            'completed_at' => now()->subDays(9),
            'total_cost' => 95.00,
        ]);

        Maintenance::create([
            'service_order_id' => $order1->id,
            'vehicle_id' => $vehicles[0]->id,
            'mechanic_id' => $mechanic1->id,
            'type' => 'preventivo',
            'description' => 'Revisión motor',
            'mileage_at_service' => 45000,
            'cost' => 185.50,
            'status' => 'completado',
            'performed_at' => now()->subDays(2),
        ]);

        Maintenance::create([
            'service_order_id' => $order2->id,
            'vehicle_id' => $vehicles[1]->id,
            'mechanic_id' => $mechanic2->id,
            'type' => 'correctivo',
            'description' => 'Cambio pastillas de freno delanteras',
            'mileage_at_service' => 67800,
            'cost' => 145.00,
            'status' => 'en_proceso',
            'performed_at' => now(),
        ]);

        Maintenance::create([
            'service_order_id' => $order4->id,
            'vehicle_id' => $vehicles[3]->id,
            'mechanic_id' => $mechanic2->id,
            'type' => 'preventivo',
            'description' => 'Cambio de aceite',
            'mileage_at_service' => 89000,
            'cost' => 95.00,
            'status' => 'completado',
            'performed_at' => now()->subDays(9),
        ]);

        MaintenanceSchedule::create([
            'vehicle_id' => $vehicles[0]->id,
            'title' => 'Cambio de aceite',
            'maintenance_type' => 'preventivo',
            'scheduled_date' => now()->addDays(3),
            'mileage_target' => 50000,
            'assigned_mechanic_id' => $mechanic1->id,
            'status' => 'programado',
        ]);

        MaintenanceSchedule::create([
            'vehicle_id' => $vehicles[1]->id,
            'title' => 'Revisión general',
            'maintenance_type' => 'preventivo',
            'scheduled_date' => now()->addDays(5),
            'mileage_target' => 70000,
            'assigned_mechanic_id' => $mechanic2->id,
            'status' => 'programado',
        ]);

        MaintenanceSchedule::create([
            'vehicle_id' => $vehicles[2]->id,
            'title' => 'Cambio de frenos',
            'maintenance_type' => 'correctivo',
            'scheduled_date' => now()->addDays(7),
            'assigned_mechanic_id' => $mechanic1->id,
            'status' => 'programado',
        ]);

        Alert::create([
            'vehicle_id' => $vehicles[0]->id,
            'user_id' => $admin->id,
            'type' => 'maintenance_due',
            'title' => 'Mantenimiento vencido',
            'message' => 'El vehículo ABC-123 necesita mantenimiento. Vencido desde el 15/05/2026.',
            'severity' => 'critical',
            'due_date' => now()->subDays(14),
        ]);

        Alert::create([
            'vehicle_id' => $vehicles[1]->id,
            'user_id' => $client1->id,
            'type' => 'insurance_expiry',
            'title' => 'Seguro por vencer',
            'message' => 'El seguro del vehículo DEF-456 vence en 5 días.',
            'severity' => 'warning',
            'due_date' => now()->addDays(5),
        ]);

        Alert::create([
            'vehicle_id' => $vehicles[2]->id,
            'user_id' => $client2->id,
            'type' => 'inspection_expiry',
            'title' => 'Revisión técnica vencida',
            'message' => 'La revisión técnica del vehículo GHI-789 está vencida.',
            'severity' => 'critical',
            'due_date' => now()->subDays(10),
        ]);

        Alert::create([
            'vehicle_id' => $vehicles[0]->id,
            'user_id' => $client1->id,
            'type' => 'custom',
            'title' => 'Recordatorio de servicio',
            'message' => 'Próximo cambio de aceite programado.',
            'severity' => 'info',
            'due_date' => now()->addDays(3),
        ]);

        ActivityLog::create([
            'user_id' => $admin->id,
            'action' => 'vehicle.created',
            'model_type' => Vehicle::class,
            'model_id' => $vehicles[0]->id,
            'description' => 'Se registró el vehículo ABC-123 (Toyota Corolla 2020).',
            'ip_address' => '127.0.0.1',
            'created_at' => now()->subDays(5),
        ]);

        ActivityLog::create([
            'user_id' => $mechanic2->id,
            'action' => 'maintenance.updated',
            'model_type' => Maintenance::class,
            'description' => 'Se actualizó mantenimiento: Cambio de aceite para DEF-456.',
            'ip_address' => '127.0.0.1',
            'created_at' => now()->subDays(2),
        ]);

        ActivityLog::create([
            'user_id' => $admin->id,
            'action' => 'user.created',
            'model_type' => User::class,
            'description' => 'Se registró un nuevo usuario: cliente1@autogest.test.',
            'ip_address' => '127.0.0.1',
            'created_at' => now()->subDays(7),
        ]);

        ActivityLog::create([
            'user_id' => $admin->id,
            'action' => 'login',
            'description' => 'Inicio de sesión en el sistema.',
            'ip_address' => '127.0.0.1',
            'created_at' => now()->subHours(2),
        ]);

        SystemSetting::setValue('app.name', 'AutoGest', 'general');
        SystemSetting::setValue('app.language', 'es', 'general');
        SystemSetting::setValue('app.currency', 'USD', 'empresa');
        SystemSetting::setValue('app.timezone', 'America/Guayaquil', 'general');
        SystemSetting::setValue('notifications.email_enabled', 'true', 'notificaciones');

        ChatbotFaq::create([
            'category' => 'General',
            'question' => '¿Cuál es el horario del taller?',
            'answer' => 'Atendemos de lunes a viernes de 8:00 a 18:00 y sábados de 8:00 a 13:00.',
            'keywords' => 'horario,taller,atención',
            'sort_order' => 1,
        ]);

        ChatbotFaq::create([
            'category' => 'Mantenimiento',
            'question' => '¿Cómo consulto el estado de mi vehículo?',
            'answer' => 'Ingresa a tu panel de cliente o pregúntame la placa de tu vehículo para consultar el estado actual.',
            'keywords' => 'estado,vehículo,placa,seguimiento',
            'sort_order' => 2,
        ]);

        ChatbotFaq::create([
            'category' => 'Servicios',
            'question' => '¿Qué servicios ofrecen?',
            'answer' => 'Ofrecemos mantenimiento preventivo y correctivo: cambio de aceite, frenos, revisión general, diagnóstico y más.',
            'keywords' => 'servicios,mantenimiento,preventivo,correctivo',
            'sort_order' => 3,
        ]);

        ChatbotFaq::create([
            'category' => 'Citas',
            'question' => '¿Cómo agendo una cita?',
            'answer' => 'Escribe por ejemplo: "Quiero agendar cita para ABC-123 el viernes" o "Solicitar cita mañana cambio de aceite". Un asesor confirmará tu solicitud.',
            'keywords' => 'agendar,cita,reservar,solicitar',
            'sort_order' => 4,
        ]);

        $modelTemplates = [
            ['Toyota', 'Corolla', 'preventivo', 'Cambio de aceite 10.000 km', 'Filtro de aceite y revisión de fluidos.', 10000, 6],
            ['Toyota', 'Corolla', 'preventivo', 'Revisión de frenos', 'Inspección de pastillas y discos.', 20000, 12],
            ['Hyundai', 'Tucson', 'preventivo', 'Servicio 5.000 km SUV', 'Aceite sintético y rotación de neumáticos.', 5000, 4],
            ['Chevrolet', 'Onix', 'preventivo', 'Mantenimiento básico', 'Aceite, filtros y chequeo general.', 10000, 6],
            ['Kia', 'Rio', 'preventivo', 'Revisión 15.000 km', 'Bujías, filtros y alineación.', 15000, 9],
            ['Nissan', 'Sentra', 'preventivo', 'Cambio de aceite y filtros', 'Servicio estándar sedán.', 10000, 6],
        ];

        foreach ($modelTemplates as $index => [$brand, $model, $type, $title, $desc, $km, $months]) {
            VehicleModelTemplate::updateOrCreate(
                ['brand' => $brand, 'model' => $model, 'title' => $title],
                [
                    'maintenance_type' => $type,
                    'description' => $desc,
                    'interval_km' => $km,
                    'interval_months' => $months,
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ],
            );
        }
    }
}
