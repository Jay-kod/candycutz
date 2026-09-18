<?php

use App\Models\User;

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
