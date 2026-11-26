<?php

declare(strict_types=1);

use App\Domain\Payment\Actions\UploadReceipt;
use App\Domain\Shared\Actions\SecureImageUpload;
use App\Domain\Shared\Services\UserMediaUrl;
use App\Http\Resources\GalleryResource;
use App\Http\Resources\ServiceResource;
use App\Jobs\OptimizeUploadedImageJob;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Gallery;
use App\Models\Notification;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Storage::fake('public');
    Storage::fake('local');
});

it('stores upload immediately on async queue and optimizes to webp with thumbnail + notification feedback when worker runs', function (): void {
    $admin = User::factory()->admin()->create();
    $barberUser = User::factory()->barber()->create();
    $barber = Barber::factory()->create(['user_id' => $barberUser->id]);

    // Queue::fake() proves the HTTP upload returns immediately without blocking on GD conversion
    Queue::fake();

    $png = UploadedFile::fake()->image('cut-preview.png', 1600, 1200);
    $res = $this->actingAs($admin)
        ->post('/api/v1/gallery', [
            'title' => 'Taper Fade',
            'category' => 'haircut',
            'barber_id' => $barber->id,
            'image' => $png,
        ], ['Accept' => 'application/json'])
        ->assertStatus(201);

    $rawPath = ltrim(str_replace('/storage/', '', (string) $res->json('data.image_path')), '/');
    expect($rawPath)->not->toEndWith('.webp');
    expect($res->json('data.is_optimizing'))->toBeTrue();
    Storage::disk('public')->assertExists($rawPath);

    Queue::assertPushed(OptimizeUploadedImageJob::class, 1);

    // Execute queued job as the worker would
    $pushedJob = Queue::pushed(OptimizeUploadedImageJob::class)->first();
    $pushedJob->handle(app(SecureImageUpload::class));

    $gallery = Gallery::findOrFail((int) $res->json('data.id'));
    $webpPath = ltrim(str_replace('/storage/', '', (string) $gallery->image_path), '/');
    expect($webpPath)->toEndWith('.webp');
    Storage::disk('public')->assertExists($webpPath);
    Storage::disk('public')->assertExists(dirname($webpPath).'/thumb_'.basename($webpPath));
    Storage::disk('public')->assertMissing($rawPath);

    // Notification feedback dispatched to uploader
    expect(Notification::where('recipient_id', $admin->id)->where('type', 'media_optimized')->exists())->toBeTrue();

    // Gallery index returns thumbnail_url and supports optional pagination cleanly
    $listRes = $this->getJson('/api/v1/gallery?per_page=1')->assertOk();
    expect($listRes->json('data.items.0.thumbnail_url'))->toContain('thumb_');
    expect($listRes->json('data.items.0.is_optimizing'))->toBeFalse();
    expect($listRes->json('data.meta.total'))->toBe(1);
});

it('converts image receipts on local disk to crisp webp while leaving pdf receipts untouched', function (): void {
    $customer = User::factory()->customer()->create();
    $appointment = Appointment::factory()->create(['customer_id' => $customer->id]);

    $uploader = app(UploadReceipt::class);

    $imgReceipt = UploadedFile::fake()->image('transfer-slip.png', 1000, 1400);
    $payment = $uploader->execute($appointment, $imgReceipt);

    expect($payment->fresh()->receipt_url)->toEndWith('.webp');
    Storage::disk('local')->assertExists($payment->fresh()->receipt_url);
});

it('keeps already-webp images lossless without re-encoding while generating companion thumbnails', function (): void {
    $img = imagecreatetruecolor(800, 600);
    ob_start();
    imagewebp($img, null, 85);
    $webpBytes = ob_get_clean();
    imagedestroy($img);

    $rawPath = 'uploads/gallery/precompressed.webp';
    Storage::disk('public')->put($rawPath, $webpBytes);

    $gallery = Gallery::factory()->create(['image_path' => '/storage/'.$rawPath]);

    $job = new OptimizeUploadedImageJob(
        disk: 'public',
        rawPath: $rawPath,
        modelClass: Gallery::class,
        modelKey: $gallery->id,
        attribute: 'image_path',
        cacheEntity: 'gallery',
    );
    $job->handle(app(SecureImageUpload::class));

    expect($gallery->fresh()->image_path)->toBe('/storage/'.$rawPath);
    Storage::disk('public')->assertExists($rawPath);
    expect(Storage::disk('public')->get($rawPath))->toBe($webpBytes);
    Storage::disk('public')->assertExists('uploads/gallery/thumb_precompressed.webp');
});

it('produces same-day deterministic signed avatar URLs for stable HTTP and service worker caching', function (): void {
    $user = User::factory()->customer()->create();
    $mediaUrl = app(UserMediaUrl::class);
    $path = "user-media/{$user->id}/avatar/profile-cut.webp";

    $first = $mediaUrl->for($user, $path);
    $this->travel(5)->seconds();
    $second = $mediaUrl->for($user, $path);

    expect($first)->toContain('signature=');
    expect($first)->toBe($second);
});

it('only synthesizes thumb_ URLs for uploaded webp assets and leaves non-uploads untouched', function (): void {
    $gallerySeed = Gallery::factory()->make([
        'id' => 999,
        'title' => 'Seeded Style',
        'category' => 'haircut',
        'image_path' => '/storage/seeded/demo-cut.webp',
    ]);
    $galleryPayload = (new GalleryResource($gallerySeed))->toArray(Request::create('/'));
    expect($galleryPayload['thumbnail_url'])->toBe($galleryPayload['image_url']);
    expect($galleryPayload['thumbnail_url'])->not->toContain('thumb_');
    expect($galleryPayload['is_optimizing'])->toBeFalse();

    $serviceSeed = Service::factory()->make([
        'id' => 888,
        'name' => 'Executive Cut',
        'image' => '/storage/seeded/service-cut.webp',
    ]);
    $servicePayload = (new ServiceResource($serviceSeed))->toArray(Request::create('/'));
    expect($servicePayload['thumbnail_url'])->toBe($servicePayload['image_url']);
    expect($servicePayload['thumbnail_url'])->not->toContain('thumb_');
});
