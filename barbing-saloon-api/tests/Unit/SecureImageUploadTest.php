<?php

namespace Tests\Unit;

use App\Domain\Shared\Actions\SecureImageUpload;
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
}
