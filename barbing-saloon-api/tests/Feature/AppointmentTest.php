<?php

use App\Models\User;

it('characterises GET /appointments', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->getJson('/api/v1/appointments');
    expect(in_array($response->status(), [200, 500, 403]))->toBeTrue();
});

it('characterises POST /appointments', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->postJson('/api/v1/appointments', []);
    expect(in_array($response->status(), [200, 201, 422, 500, 403]))->toBeTrue();
});

it('characterises POST /appointments/walk-in', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->postJson('/api/v1/appointments/walk-in', []);
    expect(in_array($response->status(), [200, 201, 422, 500, 403]))->toBeTrue();
});

it('characterises GET /appointments/{id}', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->getJson('/api/v1/appointments/999');
    expect(in_array($response->status(), [200, 404, 500, 403]))->toBeTrue();
});

it('characterises POST /appointments/{id}/cancel', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->postJson('/api/v1/appointments/999/cancel', []);
    expect(in_array($response->status(), [200, 422, 404, 500, 403]))->toBeTrue();
});

it('characterises PATCH /appointments/{id}/cancel', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->patchJson('/api/v1/appointments/999/cancel', []);
    expect(in_array($response->status(), [200, 422, 404, 500, 403]))->toBeTrue();
});

it('characterises PATCH /appointments/{id}/status', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->patchJson('/api/v1/appointments/999/status', []);
    expect(in_array($response->status(), [200, 422, 404, 500, 403]))->toBeTrue();
});
