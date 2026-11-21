<?php

namespace Tests\Unit;

use App\Domain\Shared\Actions\SecureImageUpload;
use App\Domain\Shared\Traits\HasSecureUploads;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecureImageUploadTest extends TestCase
{
    public function test_it_converts_uploaded_image_to_webp_and_generates_thumbnail(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('gallery_sample.png', 800, 600);

        $action = new SecureImageUpload;
        $savedPath = $action->execute($file, 'uploads/gallery', 'public');

        // Path should have .webp extension
        $this->assertStringEndsWith('.webp', $savedPath);
        Storage::disk('public')->assertExists($savedPath);

        // Companion thumbnail should exist
        $thumbPath = dirname($savedPath).'/thumb_'.basename($savedPath);
        Storage::disk('public')->assertExists($thumbPath);

        $mainSize = Storage::disk('public')->size($savedPath);
        $thumbSize = Storage::disk('public')->size($thumbPath);

        $this->assertGreaterThan(0, $mainSize);
        $this->assertGreaterThan(0, $thumbSize);
        $this->assertLessThanOrEqual($mainSize, $thumbSize);
    }

    public function test_it_scales_down_oversized_images(): void
    {
        Storage::fake('public');

        // Oversized 2500x1500 image
        $file = UploadedFile::fake()->image('giant_banner.jpg', 2500, 1500);

        $action = new SecureImageUpload;
        $savedPath = $action->execute($file, 'uploads/hero', 'public');

        $this->assertStringEndsWith('.webp', $savedPath);
        Storage::disk('public')->assertExists($savedPath);

        $contents = Storage::disk('public')->get($savedPath);
        $img = imagecreatefromstring($contents);
        $this->assertNotFalse($img);
        $this->assertLessThanOrEqual(1920, imagesx($img));
        imagedestroy($img);
    }

    public function test_legacy_upload_trait_stores_bytes_matching_the_webp_extension(): void
    {
        Storage::fake('public');

        $uploader = new class
        {
            use HasSecureUploads;
        };

        $savedPath = $uploader->uploadFile(
            UploadedFile::fake()->image('legacy-gallery.jpg', 800, 600),
            'gallery'
        );

        $this->assertStringEndsWith('.webp', $savedPath);
        Storage::disk('public')->assertExists($savedPath);

        $image = @imagecreatefromstring(Storage::disk('public')->get($savedPath));

        $this->assertNotFalse($image);
        $this->assertSame('image/webp', (new \finfo(FILEINFO_MIME_TYPE))->buffer(Storage::disk('public')->get($savedPath)));
        imagedestroy($image);

        $thumbnailPath = dirname($savedPath).'/thumb_'.basename($savedPath);
        Storage::disk('public')->assertExists($thumbnailPath);

        $uploader->deleteFile($savedPath);

        Storage::disk('public')->assertMissing($savedPath);
        Storage::disk('public')->assertMissing($thumbnailPath);
    }
}
