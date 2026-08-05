<?php

namespace App\Services;

use App\Contracts\Repositories\ServicePhotoRepositoryInterface;
use App\DTOs\ServicePhotoDTO;
use App\Models\ServiceOrder;
use App\Models\ServicePhoto;
use Illuminate\Support\Facades\Log;

class ServicePhotoService
{
    private $servicePhotoRepository;

    private $auditService;

    public function __construct(ServicePhotoRepositoryInterface $servicePhotoRepository, AuditService $auditService)
    {
        $this->servicePhotoRepository = $servicePhotoRepository;
        $this->auditService = $auditService;
    }

    public function storePhoto(ServiceOrder $serviceOrder, $file, $type, $description = null, $userId = null)
    {
        try {
            $path = $file->store('service-photos', 'public');

            $dto = new ServicePhotoDTO(
                $serviceOrder->id,
                $userId ?? auth()->id(),
                $path,
                $description,
                $type
            );

            $photo = $this->servicePhotoRepository->create($dto->toArray());

            Log::info('Service photo stored', [
                'photo_id' => $photo->id,
                'service_order_id' => $serviceOrder->id,
                'type' => $type,
            ]);

            // NOTA: Notificaciones agrupadas (Quality Gate Sprint 5A)
            // Las notificaciones se envían cuando se completa la orden, no por cada foto
            // Esto evita saturar al cliente con múltiples emails

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

        // Soft Delete para mantener trazabilidad (Quality Gate Sprint 5A)
        $this->auditService->logOrderAction(
            'photo_deleted',
            "Evidencia fotográfica eliminada de orden {$photo->serviceOrder->order_number}",
            $authUserId,
            ['photo_id' => $photo->id, 'photo_path' => $photo->photo_path, 'type' => $photo->type],
            ['deleted_at' => now()]
        );

        $this->servicePhotoRepository->delete($photo->id);

        Log::info('Service photo soft deleted', [
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

    // Métodos específicos para diagnóstico (Sprint 5A.3)
    public function attachToDiagnosis(ServiceOrder $serviceOrder, $file, $description = null, $userId = null)
    {
        return $this->storePhoto($serviceOrder, $file, 'evidence', $description, $userId);
    }

    public function getDiagnosisPhotos(ServiceOrder $serviceOrder)
    {
        return $this->getPhotosByType($serviceOrder, 'evidence');
    }

    public function countOrderPhotos(ServiceOrder $serviceOrder)
    {
        return $this->servicePhotoRepository->findByServiceOrder($serviceOrder->id)->count();
    }

    public function hasInitialPhotos(ServiceOrder $serviceOrder)
    {
        return $this->hasReceptionPhotos($serviceOrder) ||
               $this->servicePhotoRepository->where('service_order_id', $serviceOrder->id)
                   ->where('type', 'before')
                   ->exists();
    }

    public function hasFinalPhotos(ServiceOrder $serviceOrder)
    {
        return $this->servicePhotoRepository->where('service_order_id', $serviceOrder->id)
            ->where('type', 'after')
            ->exists();
    }

    public function getPhotoSummary(ServiceOrder $serviceOrder)
    {
        $counts = $this->getPhotoCountByType($serviceOrder);
        $photos = $this->servicePhotoRepository->findByServiceOrder($serviceOrder->id);

        return [
            'total' => $counts['total'],
            'by_type' => $counts,
            'has_initial' => $this->hasInitialPhotos($serviceOrder),
            'has_final' => $this->hasFinalPhotos($serviceOrder),
            'has_evidence' => $this->hasEvidencePhotos($serviceOrder),
            'latest_photos' => $photos->take(5),
        ];
    }

    public function validatePhotoRequirements(ServiceOrder $serviceOrder, $targetStatus)
    {
        if (in_array($targetStatus, ['completada', 'entregada'])) {
            if (! $this->hasInitialPhotos($serviceOrder)) {
                return [
                    'valid' => false,
                    'message' => 'No se puede finalizar la orden sin evidencias fotográficas iniciales (recepción o antes del trabajo).',
                ];
            }

            if (! $this->hasFinalPhotos($serviceOrder)) {
                return [
                    'valid' => false,
                    'message' => 'No se puede finalizar la orden sin evidencias fotográficas finales (después del trabajo).',
                ];
            }
        }

        return ['valid' => true];
    }
}
