<?php

namespace App\DTOs;

class ServiceOrderDTO
{
    private $vehicleId;

    private $mechanicId;

    private $clientId;

    private $status;

    private $description;

    private $cost;

    private $partsCost;

    private $laborCost;

    public function __construct(
        ?int $vehicleId,
        ?int $mechanicId,
        ?int $clientId,
        string $status,
        ?string $description = null,
        ?float $cost = null,
        ?float $partsCost = null,
        ?float $laborCost = null
    ) {
        $this->vehicleId = $vehicleId;
        $this->mechanicId = $mechanicId;
        $this->clientId = $clientId;
        $this->status = $status;
        $this->description = $description;
        $this->cost = $cost;
        $this->partsCost = $partsCost;
        $this->laborCost = $laborCost;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['vehicle_id'] ?? null,
            $data['mechanic_id'] ?? null,
            $data['client_id'] ?? null,
            $data['status'],
            $data['description'] ?? null,
            $data['cost'] ?? null,
            $data['parts_cost'] ?? null,
            $data['labor_cost'] ?? null
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'vehicle_id' => $this->vehicleId,
            'mechanic_id' => $this->mechanicId,
            'client_id' => $this->clientId,
            'status' => $this->status,
            'description' => $this->description,
            'cost' => $this->cost,
            'parts_cost' => $this->partsCost,
            'labor_cost' => $this->laborCost,
        ], function ($value) {
            return $value !== null;
        });
    }
}
