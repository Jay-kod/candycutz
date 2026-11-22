<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('terminates all other active sessions across devices when user changes password', function () {
    $user = User::factory()->customer()->create([
        'password' => Hash::make('CurrentPassword123!'),
    ]);

    // Create 3 active tokens simulating 3 devices
    $tokenA = $user->createToken('desktop-chrome:Windows', ['*'], now()->addDays(30));
    $tokenB = $user->createToken('mobile-ios:iPhone', ['*'], now()->addDays(90));
    $tokenC = $user->createToken('tablet-safari:iPad', ['*'], now()->addDays(30));

    expect($user->tokens()->count())->toBe(3);

    // Device A changes password
    $response = $this->withToken($tokenA->plainTextToken)->postJson('/api/v1/auth/change-password', [
        'current_password' => 'CurrentPassword123!',
        'new_password' => 'NewHardenedPassword123!',
        'new_password_confirmation' => 'NewHardenedPassword123!',
    ]);

    $response->assertOk();

    // Token A remains active
    expect($user->tokens()->where('id', $tokenA->accessToken->id)->exists())->toBeTrue();

    // Token B and Token C have been terminated
    expect($user->tokens()->where('id', $tokenB->accessToken->id)->exists())->toBeFalse();
    expect($user->tokens()->where('id', $tokenC->accessToken->id)->exists())->toBeFalse();
    expect($user->tokens()->count())->toBe(1);

    // Device B attempts to access protected endpoint -> 401 Unauthorized
    $this->app['auth']->forgetGuards();
    $this->withToken($tokenB->plainTextToken)
        ->getJson('/api/v1/auth/me')
        ->assertStatus(401);
});

it('terminates ALL tokens across all devices simultaneously when an account is suspended', function () {
    $user = User::factory()->customer()->create([
        'is_active' => true,
        'status' => 'active',
    ]);

    $token1 = $user->createToken('web:Laptop', ['*'], now()->addDays(30));
    $token2 = $user->createToken('app:Phone', ['*'], now()->addDays(90));
    $token3 = $user->createToken('web:Workstation', ['*'], now()->addDays(30));

    expect($user->tokens()->count())->toBe(3);

    // Admin suspends user
    $user->is_active = false;
    $user->status = 'suspended';
    $user->save();

    // Request from token1 hits EnsureUserActive middleware
    $res = $this->withToken($token1->plainTextToken)->getJson('/api/v1/auth/me');
    $res->assertStatus(403);
    $res->assertJsonPath('error.code', 'AUTH_ACCOUNT_SUSPENDED');

    // ALL tokens for this suspended user are wiped immediately
    expect($user->tokens()->count())->toBe(0);
});

it('allows user to revoke all other active sessions on demand via revoke-others endpoint', function () {
    $user = User::factory()->customer()->create();

    $currentSession = $user->createToken('active:CurrentBrowser', ['*'], now()->addDays(30));
    $staleSession1 = $user->createToken('other:OldDevice1', ['*'], now()->addDays(30));
    $staleSession2 = $user->createToken('other:OldDevice2', ['*'], now()->addDays(30));

    expect($user->tokens()->count())->toBe(3);

    $response = $this->withToken($currentSession->plainTextToken)
        ->postJson('/api/v1/auth/sessions/revoke-others');

    $response->assertOk();
    expect($response->json('data.revoked_count'))->toBe(2);

    expect($user->tokens()->where('id', $currentSession->accessToken->id)->exists())->toBeTrue();
    expect($user->tokens()->where('id', $staleSession1->accessToken->id)->exists())->toBeFalse();
    expect($user->tokens()->where('id', $staleSession2->accessToken->id)->exists())->toBeFalse();
    expect($user->tokens()->count())->toBe(1);
});

it('rejects expired Sanctum tokens and prunes them via sanctum:prune-expired command', function () {
    $user = User::factory()->customer()->create([
        'is_active' => true,
        'status' => 'active',
    ]);

    $expiredToken = $user->createToken('web:Expired', ['*'], now()->subDays(2));

    $response = $this->withToken($expiredToken->plainTextToken)->getJson('/api/v1/auth/me');
    $response->assertStatus(401);
    expect(in_array($response->json('error.code'), ['AUTH_TOKEN_INVALID', 'AUTH_TOKEN_EXPIRED'], true))->toBeTrue();

    // Verify sanctum:prune-expired removes expired tokens
    \Illuminate\Support\Facades\Artisan::call('sanctum:prune-expired', ['--hours' => 0]);
    expect($user->tokens()->where('id', $expiredToken->accessToken->id)->exists())->toBeFalse();
});
