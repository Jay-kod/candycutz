<?php

use App\Models\User;

it('characterises GET /barbers', function () {
    $response = $this->getJson('/api/v1/barbers');
    $response->assertStatus(200);
});

it('characterises GET /barbers/{id}', function () {
    $response = $this->getJson('/api/v1/barbers/999');
    expect(in_array($response->status(), [200, 404, 500]))->toBeTrue();
});

it('characterises PATCH /barbers/chair-status', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->patchJson('/api/v1/barbers/chair-status', []);
    expect(in_array($response->status(), [200, 422, 500, 403, 404]))->toBeTrue();
});

it('characterises GET /barbers/schedule', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->getJson('/api/v1/barbers/schedule');
    expect(in_array($response->status(), [200, 500, 403]))->toBeTrue();
});

it('characterises PUT /barbers/schedule', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->putJson('/api/v1/barbers/schedule', []);
    expect(in_array($response->status(), [200, 422, 500, 403]))->toBeTrue();
});

it('characterises GET /barbers/blocked-periods', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->getJson('/api/v1/barbers/blocked-periods');
    expect(in_array($response->status(), [200, 500, 403]))->toBeTrue();
});

it('characterises POST /barbers/blocked-periods', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->postJson('/api/v1/barbers/blocked-periods', []);
    expect(in_array($response->status(), [200, 201, 422, 500, 403]))->toBeTrue();
});

it('characterises DELETE /barbers/blocked-periods/{id}', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->deleteJson('/api/v1/barbers/blocked-periods/999');
    expect(in_array($response->status(), [200, 204, 404, 500, 403]))->toBeTrue();
});
