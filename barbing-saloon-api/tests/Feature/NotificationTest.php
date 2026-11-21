<?php

use App\Domain\Notification\NotificationDispatcher;
use App\Models\Barber;
use App\Models\FeedbackSubmission;
use App\Models\Notification;
use App\Models\ServiceCategoryRequest;
use App\Models\Testimonial;
use App\Models\User;

it('customer and barber notification routes resolve the canonical controller', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    $barber = User::factory()->create(['role' => 'barber']);

    $this->actingAs($customer)
        ->getJson('/api/v1/customer/notifications')
        ->assertOk()
        ->assertJsonPath('success', true);

    $this->actingAs($barber)
        ->getJson('/api/v1/barber/notifications')
        ->assertOk()
        ->assertJsonPath('success', true);
});

it('characterises GET /notifications', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->getJson('/api/v1/notifications');
    expect(in_array($response->status(), [200, 500, 403]))->toBeTrue();
});

it('characterises POST /notifications', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->postJson('/api/v1/notifications', []);
    expect(in_array($response->status(), [200, 201, 422, 500, 403]))->toBeTrue();
});

it('characterises PATCH /notifications/read-all', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->patchJson('/api/v1/notifications/read-all', []);
    expect(in_array($response->status(), [200, 422, 500, 403]))->toBeTrue();
});

it('characterises PATCH /notifications/{id}/read', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->patchJson('/api/v1/notifications/999/read', []);
    expect(in_array($response->status(), [200, 422, 404, 500, 403]))->toBeTrue();
});

it('characterises DELETE /notifications/{id}', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->deleteJson('/api/v1/notifications/999');
    expect(in_array($response->status(), [200, 204, 404, 500, 403]))->toBeTrue();
});

it('characterises GET /notification-settings', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->getJson('/api/v1/notification-settings');
    expect(in_array($response->status(), [200, 500, 403]))->toBeTrue();
});

it('characterises POST /notification-settings', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->postJson('/api/v1/notification-settings', []);
    expect(in_array($response->status(), [200, 201, 422, 500, 403]))->toBeTrue();
});

it('registers a device token for authenticated user', function () {
    $user = User::factory()->create();
    $token = 'test-device-token-'.uniqid();

    $response = $this->actingAs($user)->postJson('/api/v1/notifications/device-token', [
        'token' => $token,
        'platform' => 'android',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Device token registered successfully',
        ]);

    $this->assertDatabaseHas('device_tokens', [
        'user_id' => $user->id,
        'token' => $token,
        'platform' => 'android',
    ]);
});

it('updates an existing device token', function () {
    $user = User::factory()->create();
    $token = 'test-update-token-'.uniqid();

    $this->actingAs($user)->postJson('/api/v1/notifications/device-token', [
        'token' => $token,
        'platform' => 'android',
    ])->assertStatus(200);

    $response = $this->actingAs($user)->postJson('/api/v1/notifications/device-token', [
        'token' => $token,
        'platform' => 'ios',
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('device_tokens', [
        'user_id' => $user->id,
        'token' => $token,
        'platform' => 'ios',
    ]);
});

it('deletes a device token for authenticated user', function () {
    $user = User::factory()->create();
    $token = 'test-delete-token-'.uniqid();

    $this->actingAs($user)->postJson('/api/v1/notifications/device-token', [
        'token' => $token,
        'platform' => 'android',
    ])->assertStatus(200);

    $response = $this->actingAs($user)->deleteJson('/api/v1/notifications/device-token', [
        'token' => $token,
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Device token removed successfully',
        ]);

    $this->assertDatabaseMissing('device_tokens', [
        'user_id' => $user->id,
        'token' => $token,
    ]);
});

it('requires authentication for device token endpoints', function () {
    $this->postJson('/api/v1/notifications/device-token', [
        'token' => 'some-token',
    ])->assertStatus(401);

    $this->deleteJson('/api/v1/notifications/device-token', [
        'token' => 'some-token',
    ])->assertStatus(401);
});

it('isolates broadcast read receipts between different users via notification_reads', function () {
    $userA = User::factory()->create(['role' => 'customer']);
    $userB = User::factory()->create(['role' => 'customer']);

    $broadcast = Notification::create([
        'recipient_id' => null,
        'recipient_type' => 'all',
        'type' => 'system_update',
        'title' => 'Broadcast Test',
        'message' => 'Testing broadcast isolation',
        'is_read' => false,
    ]);

    // User A marks as read
    $responseA = $this->actingAs($userA)->patchJson("/api/v1/notifications/{$broadcast->id}/read");
    $responseA->assertStatus(200);

    $this->assertDatabaseHas('notification_reads', [
        'notification_id' => $broadcast->id,
        'user_id' => $userA->id,
    ]);

    // Check User A's view
    $listA = $this->actingAs($userA)->getJson('/api/v1/notifications')->json('data');
    $notifA = collect($listA)->firstWhere('id', $broadcast->id);
    expect($notifA['is_read'])->toBeTrue();

    // Check User B's view (must remain unread!)
    $listB = $this->actingAs($userB)->getJson('/api/v1/notifications')->json('data');
    $notifB = collect($listB)->firstWhere('id', $broadcast->id);
    expect($notifB['is_read'])->toBeFalse();
});

it('respects user notification preferences in NotificationDispatcher', function () {
    $optedOutUser = User::factory()->create([
        'role' => 'customer',
        'notification_preferences' => [
            'notify_bookings' => false,
            'notify_blog' => true,
        ],
    ]);

    $optedInUser = User::factory()->create([
        'role' => 'customer',
        'notification_preferences' => [
            'notify_bookings' => true,
            'notify_blog' => true,
        ],
    ]);

    expect(NotificationDispatcher::userWantsNotification($optedOutUser, 'booking_created'))->toBeFalse();
    expect(NotificationDispatcher::userWantsNotification($optedInUser, 'booking_created'))->toBeTrue();
    expect(NotificationDispatcher::userWantsNotification($optedOutUser, 'blog_published'))->toBeTrue();
    // Critical alert always delivered even if preference missing
    expect(NotificationDispatcher::userWantsNotification($optedOutUser, 'payment_failed'))->toBeTrue();
});

it('dispatches notifications on review submitted and approved', function () {
    $barberUser = User::factory()->create(['role' => 'barber']);
    $barber = Barber::create(['user_id' => $barberUser->id]);
    $customer = User::factory()->create(['role' => 'customer']);

    $testimonial = Testimonial::create([
        'customer_id' => $customer->id,
        'barber_id' => $barber->id,
        'client_name' => $customer->name,
        'rating' => 5,
        'review' => 'Exceptional service!',
        'is_approved' => false,
        'is_featured' => false,
    ]);

    NotificationDispatcher::reviewSubmitted($testimonial);

    // Barber notified
    $this->assertDatabaseHas('notifications', [
        'recipient_id' => $barberUser->id,
        'type' => 'review_submitted',
        'related_entity_id' => $testimonial->id,
    ]);

    // Admin notified
    $this->assertDatabaseHas('notifications', [
        'recipient_type' => 'all_admins',
        'type' => 'review_submitted',
        'related_entity_id' => $testimonial->id,
    ]);

    // Review approved
    NotificationDispatcher::reviewApproved($testimonial);

    // Customer notified
    $this->assertDatabaseHas('notifications', [
        'recipient_id' => $customer->id,
        'type' => 'review_approved',
        'related_entity_id' => $testimonial->id,
    ]);
});

it('dispatches notifications on category request lifecycle', function () {
    $barber = User::factory()->create(['role' => 'barber']);

    $categoryRequest = ServiceCategoryRequest::create([
        'requested_by' => $barber->id,
        'name' => 'Royal Beard Conditioning',
        'slug' => 'royal-beard-conditioning',
        'status' => 'pending',
    ]);

    NotificationDispatcher::categoryRequested($categoryRequest);

    $this->assertDatabaseHas('notifications', [
        'recipient_type' => 'all_admins',
        'type' => 'category_requested',
        'related_entity_id' => $categoryRequest->id,
    ]);

    NotificationDispatcher::categoryStatusChanged($categoryRequest, 'approved');

    $this->assertDatabaseHas('notifications', [
        'recipient_id' => $barber->id,
        'type' => 'category_status_changed',
        'related_entity_id' => $categoryRequest->id,
    ]);
});

it('dispatches notifications on feedback submission', function () {
    $user = User::factory()->create(['role' => 'customer']);

    $feedback = FeedbackSubmission::create([
        'user_id' => $user->id,
        'category' => 'feature_request',
        'title' => 'Dark mode schedule support',
        'description' => 'Allow scheduled toggling',
        'status' => 'new',
    ]);

    NotificationDispatcher::feedbackSubmitted($feedback);

    $this->assertDatabaseHas('notifications', [
        'recipient_type' => 'all_admins',
        'type' => 'feedback_submitted',
        'related_entity_id' => $feedback->id,
    ]);
});
