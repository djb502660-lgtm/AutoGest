<?php

namespace App\DTOs;

class MaintenanceDTO
{
    private $serviceOrderId;

    private $vehicleId;

    private $mechanicId;

    private $type;

    private $description;

    private $mileageAtService;

    private $fuelLevel;

    private $inventorySpareWheel;

    private $inventoryTools;

    private $inventoryRadio;

    private $inventoryDocuments;

    private $partsUsed;

    private $technicalNotes;

    private $cost;

    private $partsCost;

    private $laborCost;

    private $status;

    public function __construct(
        ?int $serviceOrderId,
        ?int $vehicleId,
        ?int $mechanicId,
        string $type,
        ?string $description = null,
        ?int $mileageAtService = null,
        ?string $fuelLevel = null,
        ?string $inventorySpareWheel = null,
        ?string $inventoryTools = null,
        ?string $inventoryRadio = null,
        ?string $inventoryDocuments = null,
        ?string $partsUsed = null,
        ?string $technicalNotes = null,
        ?float $cost = null,
        ?float $partsCost = null,
        ?float $laborCost = null,
        string $status = 'pendiente'
    ) {
        $this->serviceOrderId = $serviceOrderId;
        $this->vehicleId = $vehicleId;
        $this->mechanicId = $mechanicId;
        $this->type = $type;
        $this->description = $description;
        $this->mileageAtService = $mileageAtService;
        $this->fuelLevel = $fuelLevel;
        $this->inventorySpareWheel = $inventorySpareWheel;
        $this->inventoryTools = $inventoryTools;
        $this->inventoryRadio = $inventoryRadio;
        $this->inventoryDocuments = $inventoryDocuments;
        $this->partsUsed = $partsUsed;
        $this->technicalNotes = $technicalNotes;
        $this->cost = $cost;
        $this->partsCost = $partsCost;
        $this->laborCost = $laborCost;
        $this->status = $status;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['service_order_id'] ?? null,
            $data['vehicle_id'] ?? null,
            $data['mechanic_id'] ?? null,
            $data['type'],
            $data['description'] ?? null,
            $data['mileage_at_service'] ?? null,
            $data['fuel_level'] ?? null,
            $data['inventory_spare_wheel'] ?? null,
            $data['inventory_tools'] ?? null,
            $data['inventory_radio'] ?? null,
            $data['inventory_documents'] ?? null,
            $data['parts_used'] ?? null,
            $data['technical_notes'] ?? null,
            $data['cost'] ?? null,
            $data['parts_cost'] ?? null,
            $data['labor_cost'] ?? null,
            $data['status'] ?? 'pendiente'
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
        ], function ($value) {
            return $value !== null;
        });
    }
}
