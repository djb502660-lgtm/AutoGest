<?php

namespace Tests\Unit\Models;

use App\Models\AppointmentRequest;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Models\Vehicle;
use Tests\TestCase;

class AppointmentRequestTest extends TestCase
{
    private function request(array $attributes = []): AppointmentRequest
    {
        return (new AppointmentRequest)->forceFill($attributes);
    }

    public function test_status_label_translates_known_statuses(): void
    {
        $this->assertSame('Pendiente', $this->request(['status' => 'pendiente'])->statusLabel());
        $this->assertSame('Confirmada', $this->request(['status' => 'confirmada'])->statusLabel());
        $this->assertSame('Rechazada', $this->request(['status' => 'rechazada'])->statusLabel());
        $this->assertSame('Convertida a orden', $this->request(['status' => 'convertida'])->statusLabel());
        $this->assertSame('Cancelada', $this->request(['status' => 'cancelada'])->statusLabel());
        $this->assertSame('Expirada', $this->request(['status' => 'expirada'])->statusLabel());
    }

    public function test_status_badge_class_maps_statuses_to_colors(): void
    {
        $this->assertSame('green', $this->request(['status' => 'convertida'])->statusBadgeClass());
        $this->assertSame('green', $this->request(['status' => 'confirmada'])->statusBadgeClass());
        $this->assertSame('red', $this->request(['status' => 'rechazada'])->statusBadgeClass());
        $this->assertSame('red', $this->request(['status' => 'cancelada'])->statusBadgeClass());
        $this->assertSame('yellow', $this->request(['status' => 'pendiente'])->statusBadgeClass());
    }

    public function test_requested_date_and_approval_flag_are_cast(): void
    {
        $casts = $this->request()->getCasts();

        $this->assertSame('date', $casts['requested_date']);
        $this->assertSame('boolean', $casts['requires_approval']);
    }

    public function test_relations_use_their_own_foreign_keys(): void
    {
        $request = $this->request();

        $this->assertInstanceOf(User::class, $request->client()->getRelated());
        $this->assertSame('client_id', $request->client()->getForeignKeyName());
        $this->assertInstanceOf(User::class, $request->advisor()->getRelated());
        $this->assertSame('advisor_id', $request->advisor()->getForeignKeyName());
        $this->assertInstanceOf(Vehicle::class, $request->vehicle()->getRelated());
        $this->assertInstanceOf(ServiceOrder::class, $request->serviceOrder()->getRelated());
    }
}
