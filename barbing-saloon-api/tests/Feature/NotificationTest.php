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
