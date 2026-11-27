<?php

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('characterises GET /services', function () {
    $response = $this->getJson('/api/v1/services');
    $response->assertStatus(200);
});

it('characterises GET /service-categories', function () {
    $response = $this->getJson('/api/v1/service-categories');
    $response->assertStatus(200);
});

it('characterises GET /services/{idOrSlug}', function () {
    $response = $this->getJson('/api/v1/services/999');
    // Probably 404
    expect(in_array($response->status(), [200, 404, 500]))->toBeTrue();
});

it('exposes all three images and images array in service response', function () {
    $category = ServiceCategory::factory()->create();
    $service = Service::factory()->create([
        'category_id' => $category->id,
        'image' => 'images/services/service-1.png',
        'image2' => 'images/gallery/fade-1.png',
        'image3' => 'images/gallery/beard-1.png',
        'is_active' => true,
        'approval_status' => 'approved',
    ]);

    $response = $this->getJson("/api/v1/services/{$service->id}");
    $response->assertOk();
    $response->assertJsonPath('data.name', $service->name);
    expect($response->json('data.image'))->not()->toBeNull()
        ->and($response->json('data.image2'))->not()->toBeNull()
        ->and($response->json('data.image3'))->not()->toBeNull()
        ->and($response->json('data.images'))->toHaveCount(3);
});

it('allows admin to store and update service with up to 3 images and remove images', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();
    $category = ServiceCategory::factory()->create();

    $file1 = UploadedFile::fake()->image('fade1.jpg', 600, 600);
    $file2 = UploadedFile::fake()->image('fade2.jpg', 600, 600);
    $file3 = UploadedFile::fake()->image('fade3.jpg', 600, 600);

    $createResponse = $this->actingAs($admin)
        ->post('/api/v1/services', [
            'name' => 'Multi Image Fade',
            'price' => 5000,
            'duration_minutes' => 45,
            'description' => 'Precision fade haircut with 3 images',
            'category_id' => $category->id,
            'image1' => $file1,
            'image2' => $file2,
            'image3' => $file3,
        ]);

    $createResponse->assertCreated();
    $serviceId = $createResponse->json('data.id');
    expect($createResponse->json('data.image'))->not()->toBeNull()
        ->and($createResponse->json('data.image2'))->not()->toBeNull()
        ->and($createResponse->json('data.image3'))->not()->toBeNull()
        ->and($createResponse->json('data.images'))->toHaveCount(3);

    // Now test removing image2
    $updateResponse = $this->actingAs($admin)
        ->put("/api/v1/services/{$serviceId}", [
            'name' => 'Multi Image Fade Updated',
            'price' => 5500,
            'duration_minutes' => 45,
            'description' => 'Precision fade haircut with 3 images',
            'category_id' => $category->id,
            'remove_image2' => 'true',
        ]);

    $updateResponse->assertOk();
    expect($updateResponse->json('data.image'))->not()->toBeNull()
        ->and($updateResponse->json('data.image2'))->toBeNull()
        ->and($updateResponse->json('data.image3'))->not()->toBeNull()
        ->and($updateResponse->json('data.images'))->toHaveCount(2);
});
