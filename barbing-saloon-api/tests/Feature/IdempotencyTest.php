<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\IdempotencyKey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('stores and replays response when same Idempotency-Key and payload are provided', function () {
    $user = User::factory()->customer()->create();
    $key = 'idem_key_'.uniqid();

    $payload = [
        'name' => 'John Doe Profile',
        'email' => $user->email,
        'phone' => '+2348011112233',
    ];

    // First request
    $response1 = $this->actingAs($user)
        ->withHeaders(['Idempotency-Key' => $key])
        ->postJson('/api/v1/customer/profile', $payload);

    $response1->assertOk();
    expect($response1->headers->get('X-Idempotency-Key'))->toBe($key);
    expect($response1->headers->get('X-Idempotent-Replay'))->toBeNull();

    // Verify record in idempotency_keys table
    expect(IdempotencyKey::query()->where('key', $key)->exists())->toBeTrue();

    // Second request (replay)
    $response2 = $this->actingAs($user)
        ->withHeaders(['Idempotency-Key' => $key])
        ->postJson('/api/v1/customer/profile', $payload);

    $response2->assertOk();
    expect($response2->headers->get('X-Idempotent-Replay'))->toBe('1');
    expect($response2->json())->toEqual($response1->json());
});

it('rejects duplicate Idempotency-Key if payload was changed with IDEMPOTENCY_KEY_REUSED', function () {
    $user = User::factory()->customer()->create();
    $key = 'idem_mismatch_'.uniqid();

    $payloadOriginal = [
        'name' => 'Alice Original',
        'email' => $user->email,
        'phone' => '+2348011112233',
    ];

    $payloadDifferent = [
        'name' => 'Alice Changed',
        'email' => $user->email,
        'phone' => '+2348099998877',
    ];

    // First request
    $this->actingAs($user)
        ->withHeaders(['Idempotency-Key' => $key])
        ->postJson('/api/v1/customer/profile', $payloadOriginal)
        ->assertOk();

    // Second request with different body using same key
    $response = $this->actingAs($user)
        ->withHeaders(['Idempotency-Key' => $key])
        ->postJson('/api/v1/customer/profile', $payloadDifferent);

    $response->assertStatus(409);
    expect($response->json('error.code'))->toBe('IDEMPOTENCY_KEY_REUSED');
});
