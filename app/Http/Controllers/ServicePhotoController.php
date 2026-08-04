<?php

namespace App\Http\Controllers;

use App\Models\ServiceOrder;
use App\Models\ServicePhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ServicePhotoController extends Controller
{
    public function store(Request $request, ServiceOrder $serviceOrder)
    {
        try {
            \Log::info('Photo upload attempt', [
                'service_order_id' => $serviceOrder->id,
                'has_file' => $request->hasFile('photo'),
                'file_info' => $request->hasFile('photo') ? [
                    'name' => $request->file('photo')->getClientOriginalName(),
                    'size' => $request->file('photo')->getSize(),
                    'mime' => $request->file('photo')->getMimeType(),
                ] : null,
                'type' => $request->type,
            ]);

            $request->validate([
                'photo' => 'required|image|max:10240',
                'description' => 'nullable|string|max:255',
                'type' => 'required|in:reception,before,after,evidence',
            ]);

            $path = $request->file('photo')->store('service-photos', 'public');

            \Log::info('Photo stored', ['path' => $path]);

            $photo = ServicePhoto::create([
                'service_order_id' => $serviceOrder->id,
                'user_id' => auth()->id(),
                'photo_path' => $path,
                'description' => $request->description,
                'type' => $request->type,
            ]);

            \Log::info('Photo created', ['photo_id' => $photo->id]);

            return response()->json([
                'success' => true,
                'photo' => $photo->load('user'),
                'url' => Storage::url($path),
            ]);
        } catch (\Exception $e) {
            \Log::error('Photo upload error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(ServicePhoto $photo)
    {
        if ($photo->user_id !== auth()->id() && ! in_array(auth()->user()->role, ['admin', 'asesor'])) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        Storage::disk('public')->delete($photo->photo_path);
        $photo->delete();

        return response()->json(['success' => true]);
    }

    public function index(ServiceOrder $serviceOrder)
    {
        $photos = $serviceOrder->photos()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($photo) {
                return [
                    'id' => $photo->id,
                    'url' => Storage::url($photo->photo_path),
                    'description' => $photo->description,
                    'type' => $photo->type,
                    'type_label' => $photo->type_label,
                    'user' => $photo->user->name,
                    'created_at' => $photo->created_at->format('d/m/Y H:i'),
                ];
            });

        return response()->json($photos);
    }
}
