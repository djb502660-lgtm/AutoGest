<?php

namespace Tests\Unit\Models;

use App\Models\MaintenanceSchedule;
use App\Models\User;
use App\Models\Vehicle;
use Tests\TestCase;

class MaintenanceScheduleTest extends TestCase
{
    private function schedule(array $attributes = []): MaintenanceSchedule
    {
        return (new MaintenanceSchedule)->forceFill($attributes);
    }

    public function test_status_label_translates_known_statuses(): void
    {
        $this->assertSame('Programado', $this->schedule(['status' => 'programado'])->statusLabel());
        $this->assertSame('Confirmado', $this->schedule(['status' => 'confirmado'])->statusLabel());
        $this->assertSame('En Taller', $this->schedule(['status' => 'en_taller'])->statusLabel());
        $this->assertSame('Completado', $this->schedule(['status' => 'completado'])->statusLabel());
        $this->assertSame('Vencido', $this->schedule(['status' => 'vencido'])->statusLabel());
        $this->assertSame('Cancelado', $this->schedule(['status' => 'cancelado'])->statusLabel());
    }

    public function test_status_label_falls_back_to_a_capitalized_raw_status(): void
    {
        $this->assertSame('Reprogramado', $this->schedule(['status' => 'reprogramado'])->statusLabel());
    }

    public function test_color_class_maps_each_status_to_a_calendar_color(): void
    {
        $this->assertSame('event-green', $this->schedule(['status' => 'completado'])->colorClass());
        $this->assertSame('event-yellow', $this->schedule(['status' => 'en_taller'])->colorClass());
        $this->assertSame('event-red', $this->schedule(['status' => 'vencido'])->colorClass());
        $this->assertSame('event-muted', $this->schedule(['status' => 'cancelado'])->colorClass());
        $this->assertSame('event-teal', $this->schedule(['status' => 'confirmado'])->colorClass());
        $this->assertSame('event-blue', $this->schedule(['status' => 'programado'])->colorClass());
    }

    public function test_scheduled_date_is_cast_to_a_date(): void
    {
        $this->assertSame('date', $this->schedule()->getCasts()['scheduled_date']);
    }

    public function test_relations_use_their_own_foreign_keys(): void
    {
        $schedule = $this->schedule();

        $this->assertInstanceOf(User::class, $schedule->client()->getRelated());
        $this->assertSame('client_id', $schedule->client()->getForeignKeyName());
        $this->assertInstanceOf(Vehicle::class, $schedule->vehicle()->getRelated());
        $this->assertInstanceOf(User::class, $schedule->assignedMechanic()->getRelated());
        $this->assertSame('assigned_mechanic_id', $schedule->assignedMechanic()->getForeignKeyName());
    }
}
