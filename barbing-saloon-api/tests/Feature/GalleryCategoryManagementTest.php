<?php

use App\Models\Gallery;
use App\Models\GalleryCategory;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('allows anyone to retrieve active gallery categories with case-insensitive item counts', function () {
    GalleryCategory::create([
        'name' => 'Signature Fades',
        'slug' => 'signature-fades',
        'icon' => 'scissors',
        'description' => 'Clean taper and skin fades.',
        'display_order' => 1,
        'is_active' => true,
    ]);

    Gallery::create([
        'title' => 'Mid Drop Fade',
        'description' => 'Sharp line up and razor finish',
        'image_path' => '/storage/uploads/gallery/fade-test.webp',
        'category' => 'signature-fades',
        'display_order' => 1,
    ]);

    $response = $this->getJson('/api/v1/gallery-categories')
        ->assertOk()
        ->assertJsonStructure([
            'success',
            'data' => [
                '*' => ['id', 'name', 'slug', 'icon', 'description', 'display_order', 'items_count'],
            ],
        ]);

    $items = collect($response->json('data'));
    $fadeCategory = $items->firstWhere('slug', 'signature-fades');

    expect($fadeCategory)->not->toBeNull();
    expect($fadeCategory['items_count'])->toBe(1);
});

it('lets admins create, update, cascade slugs, and delete gallery categories', function () {
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->customer()->create();

    // 1. Non-admin & unauthenticated guards
    $this->postJson('/api/v1/gallery-categories', [
        'name' => 'Guest Category',
        'slug' => 'guest-category',
    ])->assertUnauthorized();

    $this->actingAs($customer)
        ->postJson('/api/v1/gallery-categories', [
            'name' => 'Customer Category',
            'slug' => 'customer-category',
        ])
        ->assertForbidden();

    // 2. Admin creates category & warms cache
    $createRes = $this->actingAs($admin)
        ->postJson('/api/v1/gallery-categories', [
            'name' => 'Beard Lounge',
            'slug' => 'beard-lounge',
            'icon' => 'sparkles',
            'description' => 'Sculpted beards and hot towels',
            'display_order' => 10,
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Beard Lounge')
        ->assertJsonPath('data.icon', 'sparkles');

    $categoryId = $createRes->json('data.id');
    expect(GalleryCategory::where('id', $categoryId)->exists())->toBeTrue();

    // Duplicate slug validation
    $this->actingAs($admin)
        ->postJson('/api/v1/gallery-categories', [
            'name' => 'Beard Lounge Copy',
            'slug' => 'beard-lounge',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['slug']);

    // Warm cache with 0 items
    $beforeWarm = collect($this->getJson('/api/v1/gallery-categories')->assertOk()->json('data'))
        ->firstWhere('id', $categoryId);
    expect($beforeWarm['items_count'])->toBe(0);

    // Create gallery item referencing this category
    Gallery::create([
        'title' => 'Executive Beard Sculpt',
        'image_path' => '/storage/uploads/gallery/beard-test.webp',
        'category' => 'beard-lounge',
    ]);

    // 3. Admin updates category slug -> must cascade to Gallery items & bust cache
    $this->actingAs($admin)
        ->putJson("/api/v1/gallery-categories/{$categoryId}", [
            'name' => 'Royal Beard Lounge',
            'slug' => 'royal-beard-lounge',
            'icon' => 'crown',
        ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Royal Beard Lounge')
        ->assertJsonPath('data.icon', 'crown');

    expect(Gallery::where('category', 'royal-beard-lounge')->count())->toBe(1);
    expect(Gallery::where('category', 'beard-lounge')->count())->toBe(0);

    $afterWarm = collect($this->getJson('/api/v1/gallery-categories')->assertOk()->json('data'))
        ->firstWhere('id', $categoryId);
    expect($afterWarm['name'])->toBe('Royal Beard Lounge');
    expect($afterWarm['items_count'])->toBe(1);

    // Filtering gallery by the updated slug returns the cascaded item
    $this->getJson('/api/v1/gallery?category=royal-beard-lounge')
        ->assertOk()
        ->assertJsonCount(1, 'data');

    // 4. Admin deletes category (photos remain)
    $this->actingAs($admin)
        ->deleteJson("/api/v1/gallery-categories/{$categoryId}")
        ->assertOk();

    expect(GalleryCategory::where('id', $categoryId)->exists())->toBeFalse();
    expect(Gallery::where('category', 'royal-beard-lounge')->count())->toBe(1);
});

it('resolves gallery upload categories canonically and busts category counts cache', function () {
    Storage::fake('public');

    $admin = User::factory()->admin()->create();
    GalleryCategory::create([
        'name' => 'Freestyle Designs',
        'slug' => 'freestyle-designs',
        'icon' => 'wand',
        'display_order' => 1,
        'is_active' => true,
    ]);

    // Warm categories cache first (0 items)
    $initial = collect($this->getJson('/api/v1/gallery-categories')->assertOk()->json('data'))
        ->firstWhere('slug', 'freestyle-designs');
    expect($initial['items_count'])->toBe(0);

    // Upload image using category name instead of slug
    $uploadRes = $this->actingAs($admin)
        ->postJson('/api/v1/gallery', [
            'title' => 'Lightning Razor Design',
            'category' => 'Freestyle Designs',
            'image' => UploadedFile::fake()->image('design.jpg', 400, 400),
        ])
        ->assertCreated()
        ->assertJsonPath('data.category', 'freestyle-designs');

    $galleryId = $uploadRes->json('data.id');

    // Cache should immediately reflect items_count = 1
    $afterUpload = collect($this->getJson('/api/v1/gallery-categories')->assertOk()->json('data'))
        ->firstWhere('slug', 'freestyle-designs');
    expect($afterUpload['items_count'])->toBe(1);

    // Deleting the item should bust cache back to 0
    $this->actingAs($admin)
        ->deleteJson("/api/v1/gallery/{$galleryId}")
        ->assertOk();

    $afterDelete = collect($this->getJson('/api/v1/gallery-categories')->assertOk()->json('data'))
        ->firstWhere('slug', 'freestyle-designs');
    expect($afterDelete['items_count'])->toBe(0);
});
