<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class PublicStorageTest extends TestCase
{
    public function test_uploaded_service_photos_are_served_from_storage(): void
    {
        $relative = 'service-photos/public-storage-test.txt';
        $fullPath = storage_path('app/public/'.$relative);

        File::ensureDirectoryExists(dirname($fullPath));
        File::put($fullPath, 'photo-bytes');

        try {
            $response = $this->get('/storage/'.$relative);
            $response->assertOk();
            $this->assertSame('photo-bytes', $response->streamedContent());
        } finally {
            File::delete($fullPath);
        }
    }

    public function test_storage_route_rejects_path_traversal(): void
    {
        $this->get('/storage/../.env')->assertNotFound();
        $this->get('/storage/foo/../../.env')->assertNotFound();
    }
}
