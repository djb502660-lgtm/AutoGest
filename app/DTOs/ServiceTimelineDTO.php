<?php

namespace App\DTOs;

class ServiceTimelineDTO
{
    public function __construct(
        public readonly int $orderId,
        public readonly string $orderNumber,
        public readonly string $status,
        public readonly array $events,
        public readonly array $maintenances,
        public readonly array $photos,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            orderId: $data['order_id'],
            orderNumber: $data['order_number'],
            status: $data['status'],
            events: $data['events'],
            maintenances: $data['maintenances'],
            photos: $data['photos'],
        );
    }

    public function toArray(): array
    {
        return [
            'order_id' => $this->orderId,
            'order_number' => $this->orderNumber,
            'status' => $this->status,
            'events' => $this->events,
            'maintenances' => $this->maintenances,
            'photos' => $this->photos,
        ];
    }
}
