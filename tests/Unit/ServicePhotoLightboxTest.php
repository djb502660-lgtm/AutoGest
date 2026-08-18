<?php

namespace Tests\Unit;

use App\Models\ServicePhoto;
use Tests\TestCase;

class ServicePhotoLightboxTest extends TestCase
{
    public function test_lightbox_caption_uses_the_photo_type_label(): void
    {
        $photo = new ServicePhoto(['type' => 'reception']);

        $this->assertSame('Recepción', $photo->lightboxCaption());
        $this->assertSame('', $photo->lightboxMeta());
    }

    public function test_relative_photo_path_is_served_from_public_storage(): void
    {
        $photo = new ServicePhoto(['photo_path' => 'service-photos/reception.jpg']);

        $this->assertSame(url('storage/service-photos/reception.jpg'), $photo->url);
    }
}
