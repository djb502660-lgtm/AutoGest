<?php

namespace Tests\Unit;

use App\Support\VehiclePlate;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class VehiclePlateTest extends TestCase
{
    #[DataProvider('plates')]
    public function test_it_extracts_flexible_plate_formats(string $input, string $normalized): void
    {
        $this->assertSame($normalized, VehiclePlate::extract($input));
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function plates(): array
    {
        return [
            'classic' => ['ABC-123', 'ABC123'],
            'no hyphen' => ['ABC123', 'ABC123'],
            'ecuador 4 digits' => ['PVP-7506', 'PVP7506'],
            'embedded' => ['cita para PVP-7506 mañana', 'PVP7506'],
            'suffix letter' => ['ABC-123A', 'ABC123A'],
        ];
    }

    public function test_it_does_not_treat_dates_or_greetings_as_plates(): void
    {
        $this->assertNull(VehiclePlate::extract('hola'));
        $this->assertNull(VehiclePlate::extract('mañana'));
        $this->assertNull(VehiclePlate::extract('creame una reserva'));
        $this->assertNull(VehiclePlate::extract('Kia Rio 2021'));
        $this->assertNull(VehiclePlate::extract('Toyota Corolla'));
    }
}
