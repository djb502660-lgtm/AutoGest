<?php

namespace App\DTOs;

class ServicePhotoDTO
{
    private $serviceOrderId;

    private $userId;

    private $photoPath;

    private $description;

    private $type;

    public function __construct(
        int $serviceOrderId,
        int $userId,
        string $photoPath,
        ?string $description = null,
        string $type = 'evidence'
    ) {
        $this->serviceOrderId = $serviceOrderId;
        $this->userId = $userId;
        $this->photoPath = $photoPath;
        $this->description = $description;
        $this->type = $type;
    }

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
