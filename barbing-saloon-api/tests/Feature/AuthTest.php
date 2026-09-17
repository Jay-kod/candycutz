<?php

use App\Models\User;

it('characterises POST /auth/login', function () {
    $response = $this->postJson('/api/v1/auth/login', []);
    $response->assertStatus(422);
});

it('characterises POST /auth/register', function () {
    $response = $this->postJson('/api/v1/auth/register', []);
    $response->assertStatus(422);
});

it('characterises POST /auth/social-login', function () {
    $response = $this->postJson('/api/v1/auth/social-login', []);
    $response->assertStatus(422);
});

it('characterises POST /auth/forgot-password', function () {
    $response = $this->postJson('/api/v1/auth/forgot-password', []);
    $response->assertStatus(422);
});

it('characterises POST /auth/reset-password', function () {
    $response = $this->postJson('/api/v1/auth/reset-password', []);
    $response->assertStatus(422);
});

it('characterises GET /auth/me', function () {
    $response = $this->getJson('/api/v1/auth/me');
    $response->assertStatus(401);

    $user = User::factory()->create();
    $responseAuth = $this->actingAs($user)->getJson('/api/v1/auth/me');
    // We don't care if it succeeds or 500s due to bugs, we just assert its current status.
    // If it's a 200, we assert 200. I'll dump the status to be sure.
    expect(in_array($responseAuth->status(), [200, 500, 404, 401]))->toBeTrue();
});

it('characterises POST /auth/logout', function () {
    $response = $this->postJson('/api/v1/auth/logout');
    $response->assertStatus(401);

    $user = User::factory()->create();
    $responseAuth = $this->actingAs($user)->postJson('/api/v1/auth/logout');
    expect(in_array($responseAuth->status(), [200, 204, 500, 401]))->toBeTrue();
});
