<?php

namespace App\Services;

use App\Contracts\Repositories\ServicePhotoRepositoryInterface;
use App\DTOs\ServicePhotoDTO;
use App\Models\ServiceOrder;
use App\Models\ServicePhoto;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ServicePhotoService
{
    public function __construct(
        protected ServicePhotoRepositoryInterface $servicePhotoRepository
    ) {}

    public function storePhoto(ServiceOrder $serviceOrder, $file, $type, $description = null, $userId = null)
    {
        try {
            $path = $file->store('service-photos', 'public');

            $dto = new ServicePhotoDTO(
                serviceOrderId: $serviceOrder->id,
                userId: $userId ?? auth()->id(),
                photoPath: $path,
                description: $description,
                type: $type,
            );

            $photo = $this->servicePhotoRepository->create($dto->toArray());

            Log::info('Service photo stored', [
                'photo_id' => $photo->id,
                'service_order_id' => $serviceOrder->id,
                'type' => $type,
            ]);

            return $photo;
        } catch (\Exception $e) {
            Log::error('Failed to store service photo', [
                'error' => $e->getMessage(),
                'service_order_id' => $serviceOrder->id,
            ]);
            throw $e;
        }
    }

    public function getPhotosByOrder(ServiceOrder $serviceOrder)
    {
        return $this->servicePhotoRepository->findByServiceOrder($serviceOrder->id);
    }

    public function getPhotosByType(ServiceOrder $serviceOrder, $type)
    {
        return $this->servicePhotoRepository->findByServiceOrderAndType($serviceOrder->id, $type);
    }

    public function getReceptionPhotos(ServiceOrder $serviceOrder)
    {
        return $this->getPhotosByType($serviceOrder, 'reception');
    }

    public function getBeforePhotos(ServiceOrder $serviceOrder)
    {
        return $this->getPhotosByType($serviceOrder, 'before');
    }

    public function getAfterPhotos(ServiceOrder $serviceOrder)
    {
        return $this->getPhotosByType($serviceOrder, 'after');
    }

    public function getEvidencePhotos(ServiceOrder $serviceOrder)
    {
        return $this->getPhotosByType($serviceOrder, 'evidence');
    }

    public function deletePhoto(ServicePhoto $photo, $userId = null)
    {
        $authUserId = $userId ?? auth()->id();

        if ($photo->user_id !== $authUserId && ! in_array(auth()->user()->role, ['admin', 'asesor'])) {
            return false;
        }

        Storage::disk('public')->delete($photo->photo_path);
        $this->servicePhotoRepository->delete($photo->id);

        Log::info('Service photo deleted', [
            'photo_id' => $photo->id,
            'deleted_by' => $authUserId,
        ]);

        return true;
    }

    public function hasReceptionPhotos(ServiceOrder $serviceOrder)
    {
        return $this->servicePhotoRepository->where('service_order_id', $serviceOrder->id)
            ->where('type', 'reception')
            ->exists();
    }

    public function hasEvidencePhotos(ServiceOrder $serviceOrder)
    {
        return $this->servicePhotoRepository->where('service_order_id', $serviceOrder->id)
            ->where('type', 'evidence')
            ->exists();
    }

    public function getPhotoCountByType(ServiceOrder $serviceOrder)
    {
        $photos = $this->servicePhotoRepository->findByServiceOrder($serviceOrder->id);

        return [
            'reception' => $photos->where('type', 'reception')->count(),
            'before' => $photos->where('type', 'before')->count(),
            'after' => $photos->where('type', 'after')->count(),
            'evidence' => $photos->where('type', 'evidence')->count(),
            'total' => $photos->count(),
        ];
    }
}
