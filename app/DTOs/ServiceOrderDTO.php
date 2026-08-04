<?php

namespace App\DTOs;

class ServiceOrderDTO
{
    public function __construct(
        public ?int $vehicleId,
        public ?int $mechanicId,
        public ?int $clientId,
        public string $status,
        public ?string $description = null,
        public ?float $cost = null,
        public ?float $partsCost = null,
        public ?float $laborCost = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            vehicleId: $data['vehicle_id'] ?? null,
            mechanicId: $data['mechanic_id'] ?? null,
            clientId: $data['client_id'] ?? null,
            status: $data['status'],
            description: $data['description'] ?? null,
            cost: $data['cost'] ?? null,
            partsCost: $data['parts_cost'] ?? null,
            laborCost: $data['labor_cost'] ?? null
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
        ], fn ($value) => $value !== null);
    }
}
