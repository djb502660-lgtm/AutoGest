<?php

namespace App\DTOs;

class VehicleDTO
{
    private $clientId;

    private $brand;

    private $model;

    private $plate;

    private $year;

    private $subModel;

    private $mileage;

    private $color;

    private $vin;

    public function __construct(
        ?int $clientId,
        string $brand,
        string $model,
        string $plate,
        int $year,
        ?string $subModel = null,
        ?int $mileage = null,
        ?string $color = null,
        ?string $vin = null
    ) {
        $this->clientId = $clientId;
        $this->brand = $brand;
        $this->model = $model;
        $this->plate = $plate;
        $this->year = $year;
        $this->subModel = $subModel;
        $this->mileage = $mileage;
        $this->color = $color;
        $this->vin = $vin;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['client_id'] ?? null,
            $data['brand'],
            $data['model'],
            $data['plate'],
            $data['year'],
            $data['sub_model'] ?? null,
            $data['mileage'] ?? null,
            $data['color'] ?? null,
            $data['vin'] ?? null
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
        ], function ($value) {
            return $value !== null;
        });
    }
}
