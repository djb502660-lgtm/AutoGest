<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Concerns\AuthorizesOrders;
use App\Http\Controllers\Api\Concerns\SerializesMobileModels;
use App\Http\Controllers\Controller;
use App\Models\ServiceOrder;
use App\Models\ServicePhoto;
use App\Services\ServicePhotoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class PhotoController extends Controller
{
    use AuthorizesOrders;
    use SerializesMobileModels;

    public function __construct(
        private ServicePhotoService $servicePhotoService,
    ) {}

    public function index(Request $request, ServiceOrder $order): JsonResponse
    {
        if ($denied = $this->denyUnlessCanView($request->user(), $order)) {
            return $denied;
        }

        $photos = $this->servicePhotoService->getPhotosByOrder($order);

        return response()->json([
            'photos' => $photos->map(fn (ServicePhoto $photo) => $this->photoPayload($photo))->values(),
        ]);
    }

    public function store(Request $request, ServiceOrder $order): JsonResponse
    {
        $user = $request->user();

        if ($user->cannot('create', ServicePhoto::class) || $user->cannot('update', $order)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $validated = $request->validate([
            'photo' => ['required', 'file', 'max:10240'],
            'description' => 'nullable|string|max:255',
            'type' => 'required|in:reception,before,after,evidence',
        ]);

        $file = $request->file('photo');

        if (! $file instanceof UploadedFile || ! $this->looksLikeImage($file)) {
            return response()->json([
                'message' => 'El archivo debe ser una imagen (JPG, PNG, WEBP o similar).',
                'errors' => [
                    'photo' => ['El archivo debe ser una imagen (JPG, PNG, WEBP o similar).'],
                ],
            ], 422);
        }

        $photo = $this->servicePhotoService->storePhoto(
            $order,
            $file,
            $validated['type'],
            $validated['description'] ?? null,
            $user->id,
        );

        return response()->json([
            'success' => true,
            'photo' => $this->photoPayload($photo->load('user')),
        ], 201);
    }

    public function destroy(Request $request, ServicePhoto $photo): JsonResponse
    {
        if ($request->user()->cannot('delete', $photo)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $deleted = $this->servicePhotoService->deletePhoto($photo, $request->user()->id);

        if (! $deleted) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        return response()->json(['success' => true]);
    }

    private function looksLikeImage(?UploadedFile $file): bool
    {
        if (! $file) {
            return false;
        }

        $mime = strtolower((string) $file->getMimeType());

        if (str_starts_with($mime, 'image/')) {
            return true;
        }

        $path = $file->getRealPath();

        return is_string($path) && $path !== '' && @getimagesize($path) !== false;
    }
}
