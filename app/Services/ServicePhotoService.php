<?php

namespace App\Services;

use App\Models\ServiceOrder;
use App\Models\ServicePhoto;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ServicePhotoService
{
    public function storePhoto(ServiceOrder $serviceOrder, $file, $type, $description = null, $userId = null)
    {
        try {
            $path = $file->store('service-photos', 'public');

            $photo = ServicePhoto::create([
                'service_order_id' => $serviceOrder->id,
                'user_id' => $userId ?? auth()->id(),
                'photo_path' => $path,
                'description' => $description,
                'type' => $type,
            ]);

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
        return $serviceOrder->photos()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getPhotosByType(ServiceOrder $serviceOrder, $type)
    {
        return $serviceOrder->photos()
            ->where('type', $type)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
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
        $photo->delete();

        Log::info('Service photo deleted', [
            'photo_id' => $photo->id,
            'deleted_by' => $authUserId,
        ]);

        return true;
    }

    public function hasReceptionPhotos(ServiceOrder $serviceOrder)
    {
        return $serviceOrder->photos()->where('type', 'reception')->exists();
    }

    public function hasEvidencePhotos(ServiceOrder $serviceOrder)
    {
        return $serviceOrder->photos()->where('type', 'evidence')->exists();
    }

    public function getPhotoCountByType(ServiceOrder $serviceOrder)
    {
        return [
            'reception' => $serviceOrder->photos()->where('type', 'reception')->count(),
            'before' => $serviceOrder->photos()->where('type', 'before')->count(),
            'after' => $serviceOrder->photos()->where('type', 'after')->count(),
            'evidence' => $serviceOrder->photos()->where('type', 'evidence')->count(),
            'total' => $serviceOrder->photos()->count(),
        ];
    }
}
