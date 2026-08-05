<?php

namespace App\Http\Controllers;

use App\Models\ServiceOrder;
use App\Models\ServicePhoto;
use App\Services\ServicePhotoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ServicePhotoController extends Controller
{
    private ServicePhotoService $servicePhotoService;

    public function __construct(ServicePhotoService $servicePhotoService)
    {
        $this->servicePhotoService = $servicePhotoService;
    }

    public function store(Request $request, ServiceOrder $serviceOrder)
    {
        try {
            $request->validate([
                'photo' => 'required|image|max:10240',
                'description' => 'nullable|string|max:255',
                'type' => 'required|in:reception,before,after,evidence',
            ]);

            $photo = $this->servicePhotoService->storePhoto(
                $serviceOrder,
                $request->file('photo'),
                $request->type,
                $request->description
            );

            return response()->json([
                'success' => true,
                'photo' => $photo->load('user'),
                'url' => Storage::url($photo->photo_path),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(ServicePhoto $photo)
    {
        $result = $this->servicePhotoService->deletePhoto($photo);

        if (!$result) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        return response()->json(['success' => true]);
    }

    public function index(ServiceOrder $serviceOrder)
    {
        $photos = $this->servicePhotoService->getPhotosByOrder($serviceOrder);

        return response()->json($photos->map(function ($photo) {
            return [
                'id' => $photo->id,
                'url' => Storage::url($photo->photo_path),
                'description' => $photo->description,
                'type' => $photo->type,
                'type_label' => $photo->type_label,
                'user' => $photo->user->name,
                'created_at' => $photo->created_at->format('d/m/Y H:i'),
            ];
        }));
    }

    public function beforeAfter(ServiceOrder $serviceOrder)
    {
        $beforePhotos = $this->servicePhotoService->getBeforePhotos($serviceOrder);
        $afterPhotos = $this->servicePhotoService->getAfterPhotos($serviceOrder);

        return response()->json([
            'before' => $beforePhotos->map(function ($photo) {
                return [
                    'id' => $photo->id,
                    'url' => Storage::url($photo->photo_path),
                    'description' => $photo->description,
                    'created_at' => $photo->created_at->format('d/m/Y H:i'),
                ];
            }),
            'after' => $afterPhotos->map(function ($photo) {
                return [
                    'id' => $photo->id,
                    'url' => Storage::url($photo->photo_path),
                    'description' => $photo->description,
                    'created_at' => $photo->created_at->format('d/m/Y H:i'),
                ];
            }),
            'count' => [
                'before' => $beforePhotos->count(),
                'after' => $afterPhotos->count(),
            ],
        ]);
    }
}
