<?php

use App\Models\Barber;
use App\Models\Gallery;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('coordinates full end-to-end service creation, approval, and chair-link workflow', function () {
    Storage::fake('public');

    $category = ServiceCategory::firstOrCreate(
        ['name' => 'Haircuts'],
        ['slug' => 'haircuts', 'display_order' => 1]
    );

    // 1. Barber creates a service
    $barberUser = User::factory()->create(['role' => 'barber']);
    $barber = Barber::factory()->create(['user_id' => $barberUser->id]);

    $response = $this->actingAs($barberUser)->postJson('/api/v1/services', [
        'name' => 'Barber Custom High Fade',
        'category_id' => $category->id,
        'price' => 4500,
        'duration_minutes' => 40,
        'description' => 'Precision cut designed by barber',
    ]);

    $response->assertStatus(201);
    $createdServiceId = $response->json('data.id');
    expect($response->json('data.approval_status'))->toBe('pending');
    expect($response->json('data.is_active'))->toBeFalse();
    expect($response->json('data.barber_id'))->toBe($barber->id);

    // 2. Public / Customer cannot see the pending service
    $customerUser = User::factory()->create(['role' => 'customer']);
    $publicResponse = $this->actingAs($customerUser)->getJson('/api/v1/services');
    $publicResponse->assertOk();
    $serviceIds = collect($publicResponse->json('data'))->pluck('id')->all();
    expect($serviceIds)->not->toContain($createdServiceId);

    // 3. Admin approves service
    $adminUser = User::factory()->create(['role' => 'admin']);
    $approveResponse = $this->actingAs($adminUser)->patchJson("/api/v1/services/{$createdServiceId}/approve");
    $approveResponse->assertOk();
    expect($approveResponse->json('data.approval_status'))->toBe('approved');
    expect($approveResponse->json('data.is_active'))->toBeTrue();

    // 4. Verify auto-link in barber_services
    $this->assertDatabaseHas('barber_services', [
        'barber_id' => $barber->id,
        'service_id' => $createdServiceId,
        'is_offered' => 1,
    ]);

    // 5. Public / Customer can now see the approved service
    $publicResponseNow = $this->getJson('/api/v1/services');
    $publicResponseNow->assertOk();
    $updatedServiceIds = collect($publicResponseNow->json('data'))->pluck('id')->all();
    expect($updatedServiceIds)->toContain($createdServiceId);
});

it('allows barbers to upload gallery cut photos and syncs across platforms', function () {
    Storage::fake('public');

    $barberUser = User::factory()->create(['role' => 'barber']);
    $barber = Barber::factory()->create(['user_id' => $barberUser->id]);

    $tempFile = tempnam(sys_get_temp_dir(), 'test_img');
    $jpegBytes = base64_decode('/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////wgALCAABAAEBAREA/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPxA=');
    file_put_contents($tempFile, $jpegBytes);
    $file = new UploadedFile($tempFile, 'fresh_fade.jpg', 'image/jpeg', null, true);

    $response = $this->actingAs($barberUser)->postJson('/api/v1/gallery', [
        'title' => 'Signature Burst Fade',
        'category' => 'haircut',
        'description' => 'Clean taper with textured crown',
        'image' => $file,
    ]);

    $response->assertStatus(201);
    $galleryId = $response->json('data.id');
    expect($response->json('data.barber.id'))->toBe($barber->id);

    // Visible in public gallery feed
    $publicGallery = $this->getJson('/api/v1/gallery');
    $publicGallery->assertOk();
    $galleryIds = collect($publicGallery->json('data'))->pluck('id')->all();
    expect($galleryIds)->toContain($galleryId);
});

it('supports service-tagged testimonials and filters by service', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    $service = Service::factory()->create([
        'approval_status' => 'approved',
        'is_active' => true,
    ]);

    $response = $this->actingAs($customer)->postJson('/api/v1/testimonials', [
        'service_id' => $service->id,
        'rating' => 5,
        'review' => 'Best fade in town! Razor sharp lines.',
    ]);

    $response->assertStatus(201);
    $testimonialId = $response->json('data.id');
    expect($response->json('data.service_id'))->toBe($service->id);
    expect($response->json('data.review'))->toBe('Best fade in town! Razor sharp lines.');

    // Approve testimonial so it is visible in public listing
    Testimonial::find($testimonialId)->update([
        'is_approved' => true,
        'is_featured' => true,
    ]);

    $listResponse = $this->getJson("/api/v1/testimonials?service_id={$service->id}");
    $listResponse->assertOk();
    $items = collect($listResponse->json('data'));
    expect($items->pluck('id'))->toContain($testimonialId);
    $firstItem = $items->firstWhere('id', $testimonialId);
    expect($firstItem['service']['id'])->toBe($service->id);
    expect($firstItem['service']['name'])->toBe($service->name);
});

it('keeps unapproved testimonials hidden from the public feed for signed-in admins', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create(['role' => 'customer']);
    $approved = Testimonial::factory()->create([
        'customer_id' => $customer->id,
        'service_id' => null,
        'barber_id' => null,
        'is_approved' => true,
        'is_featured' => true,
    ]);
    $approvedHidden = Testimonial::factory()->create([
        'customer_id' => $customer->id,
        'service_id' => null,
        'barber_id' => null,
        'is_approved' => true,
        'is_featured' => false,
    ]);
    $pending = Testimonial::factory()->create([
        'customer_id' => $customer->id,
        'service_id' => null,
        'barber_id' => null,
        'is_approved' => false,
    ]);

    $publicList = $this->actingAs($admin)->getJson('/api/v1/testimonials');
    $publicList->assertOk();
    $publicIds = collect($publicList->json('data'))->pluck('id');
    expect($publicIds)->toContain($approved->id)
        ->not->toContain($approvedHidden->id)
        ->not->toContain($pending->id);

    $adminList = $this->getJson('/api/v1/admin/testimonials');
    $adminList->assertOk();
    $adminIds = collect($adminList->json('data'))->pluck('id');
    expect($adminIds)->toContain($approved->id)
        ->toContain($approvedHidden->id)
        ->toContain($pending->id);

    $unapproveResponse = $this->patchJson("/api/v1/admin/testimonials/{$approvedHidden->id}/approve", [
        'is_approved' => false,
    ]);
    $unapproveResponse->assertOk();
    expect($approvedHidden->fresh()->is_featured)->toBeFalse();

    $approveResponse = $this->patchJson("/api/v1/admin/testimonials/{$approvedHidden->id}/approve", [
        'is_approved' => true,
    ]);
    $approveResponse->assertOk();
    expect($approvedHidden->fresh()->is_approved)->toBeTrue()
        ->and($approvedHidden->fresh()->is_featured)->toBeFalse();

    $pendingFeatureResponse = $this->patchJson("/api/v1/admin/testimonials/{$pending->id}/feature", [
        'is_featured' => true,
    ]);
    $pendingFeatureResponse->assertUnprocessable();
});

it('allows admins to feature and unfeature testimonials through the approval flow', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create(['role' => 'customer']);
    $service = Service::factory()->create([
        'approval_status' => 'approved',
        'is_active' => true,
    ]);

    $testimonial = Testimonial::factory()->create([
        'customer_id' => $customer->id,
        'service_id' => $service->id,
        'barber_id' => $service->barber_id,
        'is_approved' => true,
        'is_featured' => false,
    ]);

    $featureResponse = $this->actingAs($admin)->patchJson("/api/v1/admin/testimonials/{$testimonial->id}/feature", [
        'is_featured' => true,
    ]);
    $featureResponse->assertOk();
    expect($featureResponse->json('data.is_featured'))->toBeTrue();
    expect($testimonial->fresh()->is_featured)->toBeTrue();
    expect($testimonial->fresh()->is_approved)->toBeTrue();

    $featuredList = $this->getJson('/api/v1/testimonials?featured=true');
    $featuredList->assertOk();
    $ids = collect($featuredList->json('data'))->pluck('id')->all();
    expect($ids)->toContain($testimonial->id);

    $toggleBack = $this->actingAs($admin)->patchJson("/api/v1/admin/testimonials/{$testimonial->id}/feature", [
        'is_featured' => false,
    ]);
    $toggleBack->assertOk();
    expect($toggleBack->json('data.is_featured'))->toBeFalse();
    expect($testimonial->fresh()->is_featured)->toBeFalse();
});

it('allows barbers to author blog posts and syncs to blog feed', function () {
    $barberUser = User::factory()->create(['role' => 'barber']);

    $response = $this->actingAs($barberUser)->postJson('/api/v1/blog', [
        'title' => 'How to Maintain a Wave Pattern at Home',
        'content' => '<p>Daily brushing and using a silk durag are essential for wave maintenance.</p>',
        'excerpt' => 'Daily brushing tips for waves.',
        'status' => 'published',
    ]);

    $response->assertStatus(201);
    $slug = $response->json('data.slug');

    $feed = $this->getJson('/api/v1/blog');
    $feed->assertOk();
    $slugs = collect($feed->json('data'))->pluck('slug')->all();
    expect($slugs)->toContain($slug);
});
