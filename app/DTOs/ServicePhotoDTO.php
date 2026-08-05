<?php

namespace App\DTOs;

class ServicePhotoDTO
{
    public function __construct(
        public readonly int $serviceOrderId,
        public readonly int $userId,
        public readonly string $photoPath,
        public readonly ?string $description = null,
        public readonly string $type = 'evidence',
    ) {}

    public function toArray(): array
    {
        return [
            'service_order_id' => $this->serviceOrderId,
            'user_id' => $this->userId,
            'photo_path' => $this->photoPath,
            'description' => $this->description,
            'type' => $this->type,
        ];
    }
}
