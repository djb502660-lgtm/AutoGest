<?php

namespace App\DTOs;

class MaintenanceDTO
{
    public function __construct(
        public ?int $serviceOrderId,
        public ?int $vehicleId,
        public ?int $mechanicId,
        public string $type,
        public ?string $description = null,
        public ?int $mileageAtService = null,
        public ?string $fuelLevel = null,
        public ?string $inventorySpareWheel = null,
        public ?string $inventoryTools = null,
        public ?string $inventoryRadio = null,
        public ?string $inventoryDocuments = null,
        public ?string $partsUsed = null,
        public ?string $technicalNotes = null,
        public ?float $cost = null,
        public ?float $partsCost = null,
        public ?float $laborCost = null,
        public string $status = 'pendiente'
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            serviceOrderId: $data['service_order_id'] ?? null,
            vehicleId: $data['vehicle_id'] ?? null,
            mechanicId: $data['mechanic_id'] ?? null,
            type: $data['type'],
            description: $data['description'] ?? null,
            mileageAtService: $data['mileage_at_service'] ?? null,
            fuelLevel: $data['fuel_level'] ?? null,
            inventorySpareWheel: $data['inventory_spare_wheel'] ?? null,
            inventoryTools: $data['inventory_tools'] ?? null,
            inventoryRadio: $data['inventory_radio'] ?? null,
            inventoryDocuments: $data['inventory_documents'] ?? null,
            partsUsed: $data['parts_used'] ?? null,
            technicalNotes: $data['technical_notes'] ?? null,
            cost: $data['cost'] ?? null,
            partsCost: $data['parts_cost'] ?? null,
            laborCost: $data['labor_cost'] ?? null,
            status: $data['status'] ?? 'pendiente'
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'service_order_id' => $this->serviceOrderId,
            'vehicle_id' => $this->vehicleId,
            'mechanic_id' => $this->mechanicId,
            'type' => $this->type,
            'description' => $this->description,
            'mileage_at_service' => $this->mileageAtService,
            'fuel_level' => $this->fuelLevel,
            'inventory_spare_wheel' => $this->inventorySpareWheel,
            'inventory_tools' => $this->inventoryTools,
            'inventory_radio' => $this->inventoryRadio,
            'inventory_documents' => $this->inventoryDocuments,
            'parts_used' => $this->partsUsed,
            'technical_notes' => $this->technicalNotes,
            'cost' => $this->cost,
            'parts_cost' => $this->partsCost,
            'labor_cost' => $this->laborCost,
            'status' => $this->status,
        ], fn ($value) => $value !== null);
    }
}
