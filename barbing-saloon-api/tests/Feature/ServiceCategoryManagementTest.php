<?php

use App\Models\ServiceCategory;
use App\Models\ServiceCategoryRequest;
use App\Models\User;

it('lets barbers request categories and admins approve them for the shared service catalogue', function () {
    $barber = User::factory()->barber()->create();
    $admin = User::factory()->admin()->create();

    $this->actingAs($barber)
        ->postJson('/api/v1/service-category-requests', [
            'name' => 'Beard Grooming',
            'icon' => 'scissors',
        ])
        ->assertCreated()
        ->assertJsonPath('data.status', 'pending');

    $categoryRequest = ServiceCategoryRequest::query()->firstOrFail();

    $this->actingAs($barber)
        ->getJson('/api/v1/service-category-requests')
        ->assertOk()
        ->assertJsonCount(1, 'data');

    $otherBarber = User::factory()->barber()->create();
    $this->actingAs($otherBarber)
        ->postJson('/api/v1/service-category-requests', ['name' => 'Line Up'])
        ->assertCreated();

    $this->actingAs($barber)
        ->getJson('/api/v1/service-category-requests')
        ->assertOk()
        ->assertJsonCount(1, 'data');

    $this->actingAs($admin)
        ->getJson('/api/v1/service-category-requests')
        ->assertOk()
        ->assertJsonCount(2, 'data');

    $this->actingAs($barber)
        ->postJson('/api/v1/service-categories', [
            'name' => 'Restricted Category',
            'slug' => 'restricted-category',
        ])
        ->assertForbidden();

    $this->actingAs($admin)
        ->patchJson("/api/v1/service-category-requests/{$categoryRequest->id}/approve")
        ->assertOk()
        ->assertJsonPath('data.status', 'approved')
        ->assertJsonPath('data.service_category.slug', 'beard-grooming');

    expect(ServiceCategory::query()->where('slug', 'beard-grooming')->exists())->toBeTrue();
    expect($categoryRequest->fresh()->service_category_id)->not->toBeNull();

    $this->getJson('/api/v1/service-categories')
        ->assertOk()
        ->assertJsonPath('data.0.name', 'Beard Grooming');
});

it('lets admins create categories directly and prevents non-admins from doing so', function () {
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->customer()->create();

    $createdCategory = $this->actingAs($admin)
        ->postJson('/api/v1/service-categories', [
            'name' => 'Hair Coloring',
            'slug' => 'hair-coloring',
            'display_order' => 1,
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Hair Coloring');

    expect(ServiceCategory::query()->where('slug', 'hair-coloring')->exists())->toBeTrue();

    $categoryId = $createdCategory->json('data.id');
    $this->actingAs($admin)
        ->putJson("/api/v1/service-categories/{$categoryId}", [
            'name' => 'Hair Colouring',
            'slug' => 'hair-colouring',
            'display_order' => 1,
        ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Hair Colouring');

    $this->actingAs($customer)
        ->postJson('/api/v1/service-categories', [
            'name' => 'Unauthorized Category',
            'slug' => 'unauthorized-category',
        ])
        ->assertForbidden();
});

it('prevents duplicate pending category requests and repeated reviews', function () {
    $barber = User::factory()->barber()->create();
    $admin = User::factory()->admin()->create();

    $this->actingAs($barber)
        ->postJson('/api/v1/service-category-requests', ['name' => 'Skin Fade'])
        ->assertCreated();

    $this->actingAs($barber)
        ->postJson('/api/v1/service-category-requests', ['name' => 'Skin Fade'])
        ->assertUnprocessable();

    $categoryRequest = ServiceCategoryRequest::query()->firstOrFail();
    $this->actingAs($admin)
        ->patchJson("/api/v1/service-category-requests/{$categoryRequest->id}/reject", [
            'review_note' => 'Please use an existing category.',
        ])
        ->assertOk()
        ->assertJsonPath('data.status', 'rejected');

    $this->actingAs($admin)
        ->patchJson("/api/v1/service-category-requests/{$categoryRequest->id}/approve")
        ->assertStatus(409);
});
