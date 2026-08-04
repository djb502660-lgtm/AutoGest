<?php

namespace App\DTOs;

class VehicleDTO
{
    public function __construct(
        public ?int $clientId,
        public string $brand,
        public string $model,
        public string $plate,
        public int $year,
        public ?string $subModel = null,
        public ?int $mileage = null,
        public ?string $color = null,
        public ?string $vin = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            clientId: $data['client_id'] ?? null,
            brand: $data['brand'],
            model: $data['model'],
            plate: $data['plate'],
            year: $data['year'],
            subModel: $data['sub_model'] ?? null,
            mileage: $data['mileage'] ?? null,
            color: $data['color'] ?? null,
            vin: $data['vin'] ?? null
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'client_id' => $this->clientId,
            'brand' => $this->brand,
            'model' => $this->model,
            'plate' => $this->plate,
            'year' => $this->year,
            'sub_model' => $this->subModel,
            'mileage' => $this->mileage,
            'color' => $this->color,
            'vin' => $this->vin,
        ], fn ($value) => $value !== null);
    }
}
