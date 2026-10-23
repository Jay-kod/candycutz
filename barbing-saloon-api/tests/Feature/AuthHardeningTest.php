<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Identity\Actions\ManageUsers;
use App\Domain\Identity\Actions\UpdateBarberStatus;
use App\Domain\Shared\Enums\UserRole;
use App\Models\Barber;
use App\Models\User;
use Database\Seeders\UserSeeder;
use Database\Seeders\WebUserSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('does not mass assign role, status, or activity attributes', function () {
    $privilegeFields = [
        'role' => UserRole::super_admin->value,
        'status' => 'suspended',
        'is_active' => false,
    ];

    $constructed = new User($privilegeFields);
    expect($constructed->role)->toBeNull();
    expect($constructed->status)->toBeNull();
    expect($constructed->is_active)->toBeNull();

    $user = User::factory()->customer()->create();
    $user->fill($privilegeFields);

    expect($user->role)->toBe(UserRole::customer);
    expect($user->status)->toBe('active');
    expect($user->is_active)->toBeTrue();
});

it('keeps registration privileges server controlled when extra privilege fields are submitted', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Privilege Probe',
        'email' => 'privilege-probe@example.com',
        'phone' => '+2348012345678',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'role' => UserRole::super_admin->value,
        'status' => 'suspended',
        'is_active' => false,
    ]);

    $response->assertCreated();

    $user = User::where('email', 'privilege-probe@example.com')->firstOrFail();
    expect($user->role)->toBe(UserRole::customer);
    expect($user->status)->toBe('active');
    expect($user->is_active)->toBeTrue();
});

it('issues a scoped expiring web token during registration', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Scoped Registration',
        'email' => 'scoped-registration@example.com',
        'phone' => '+2348012345678',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'device_name' => 'Chrome',
    ]);

    $response->assertCreated();
    $user = User::where('email', 'scoped-registration@example.com')->firstOrFail();
    $token = $user->tokens()->latest()->firstOrFail();

    expect($token->expires_at)->not->toBeNull();
    expect($token->expires_at->between(now()->addDays(29), now()->addDays(31)))->toBeTrue();
    expect($token->abilities)->toBe(['client:web']);
});

it('keeps customer privileges unchanged when extra privilege fields are submitted to profile update', function () {
    $user = User::factory()->customer()->create();

    $this->actingAs($user)->postJson('/api/v1/customer/profile', [
        'name' => 'Updated Customer',
        'email' => $user->email,
        'role' => UserRole::super_admin->value,
        'status' => 'suspended',
        'is_active' => false,
    ])->assertOk();

    $user->refresh();
    expect($user->role)->toBe(UserRole::customer);
    expect($user->status)->toBe('active');
    expect($user->is_active)->toBeTrue();
});

it('keeps barber privileges unchanged when extra privilege fields are submitted to account update', function () {
    $barber = Barber::factory()->create();
    $user = User::findOrFail($barber->user_id);

    $this->actingAs($user)->putJson('/api/v1/barbers/account', [
        'name' => 'Updated Barber',
        'role' => UserRole::super_admin->value,
        'status' => 'suspended',
        'is_active' => false,
    ])->assertOk();

    $user->refresh();
    expect($user->role)->toBe(UserRole::barber);
    expect($user->status)->toBe('active');
    expect($user->is_active)->toBeTrue();
});

it('denies customers access to the privileged user update endpoint', function () {
    $customer = User::factory()->customer()->create();
    $target = User::factory()->customer()->create();

    $this->actingAs($customer)->patchJson("/api/v1/super-admin/users/{$target->id}", [
        'name' => $target->name,
        'email' => $target->email,
        'role' => UserRole::super_admin->value,
        'status' => 'suspended',
        'is_active' => false,
    ])->assertForbidden();

    $target->refresh();
    expect($target->role)->toBe(UserRole::customer);
    expect($target->status)->toBe('active');
    expect($target->is_active)->toBeTrue();
});

it('allows a super admin to assign roles through the privileged user-management flow', function () {
    $superAdmin = User::factory()->superAdmin()->create();

    $created = $this->actingAs($superAdmin)->postJson('/api/v1/super-admin/users', [
        'name' => 'Managed Barber',
        'email' => 'managed-barber@example.com',
        'password' => 'Password123!',
        'role' => UserRole::barber->value,
    ]);

    $created->assertCreated();
    $managedUser = User::where('email', 'managed-barber@example.com')->firstOrFail();
    expect($managedUser->role)->toBe(UserRole::barber);
    expect($managedUser->status)->toBe('active');
    expect($managedUser->is_active)->toBeTrue();

    $this->actingAs($superAdmin)->patchJson("/api/v1/super-admin/users/{$managedUser->id}", [
        'name' => $managedUser->name,
        'email' => $managedUser->email,
        'role' => UserRole::admin->value,
    ])->assertOk();

    expect($managedUser->fresh()->role)->toBe(UserRole::admin);

    $this->actingAs($superAdmin)->patchJson("/api/v1/super-admin/users/{$managedUser->id}/deactivate")
        ->assertOk();
    expect($managedUser->fresh()->is_active)->toBeFalse();

    $this->actingAs($superAdmin)->patchJson("/api/v1/super-admin/users/{$managedUser->id}/activate")
        ->assertOk();
    expect($managedUser->fresh()->is_active)->toBeTrue();
});

it('requires super-admin authorization inside user-management actions', function () {
    $customer = User::factory()->customer()->create();

    $this->actingAs($customer);

    expect(fn () => app(ManageUsers::class)->storeUser([
        'name' => 'Injected Admin',
        'email' => 'injected-admin@example.com',
        'password' => 'Password123!',
        'role' => UserRole::super_admin->value,
    ]))->toThrow(AuthorizationException::class);
});

it('requires admin authorization inside the barber status action', function () {
    $barber = Barber::factory()->create();
    $owner = User::findOrFail($barber->user_id);

    $this->actingAs($owner);

    expect(fn () => app(UpdateBarberStatus::class)->execute($barber->id, 'suspended'))
        ->toThrow(AuthorizationException::class);
});

it('preserves explicit roles and account status when user seeders run', function () {
    app(UserSeeder::class)->run();
    app(WebUserSeeder::class)->run();

    expect(User::where('email', 'superadmin@candycutz.com')->firstOrFail()->role)->toBe(UserRole::super_admin);
    expect(User::where('email', 'admin@candycutz.com')->firstOrFail()->role)->toBe(UserRole::admin);
    expect(User::where('email', 'obo@candycutz.com')->firstOrFail()->role)->toBe(UserRole::barber);
    expect(User::where('email', 'jay@candycutz.com')->firstOrFail()->role)->toBe(UserRole::customer);

    foreach (User::whereIn('email', [
        'superadmin@candycutz.com',
        'admin@candycutz.com',
        'obo@candycutz.com',
        'jay@candycutz.com',
    ])->get() as $seededUser) {
        expect($seededUser->status)->toBe('active');
        expect($seededUser->is_active)->toBeTrue();
    }
});

it('returns AUTH_INVALID_CREDENTIALS with 401 for wrong credentials (AUTH-4)', function () {
    $user = User::factory()->create([
        'email' => 'user@example.com',
        'password' => Hash::make('CorrectPassword123!'),
        'is_active' => true,
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'identity' => 'user@example.com',
        'password' => 'WrongPassword!',
    ]);

    $response->assertStatus(401);
    expect($response->json('error.code'))->toBe('AUTH_INVALID_CREDENTIALS');
});

it('returns AUTH_ACCOUNT_SUSPENDED with 403 for suspended user on login (AUTH-4)', function () {
    $user = User::factory()->create([
        'email' => 'suspended@example.com',
        'password' => Hash::make('Password123!'),
        'is_active' => false,
        'status' => 'suspended',
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'identity' => 'suspended@example.com',
        'password' => 'Password123!',
    ]);

    $response->assertStatus(403);
    expect($response->json('error.code'))->toBe('AUTH_ACCOUNT_SUSPENDED');
});

it('generates client and device named token on login with abilities and expiry', function () {
    $user = User::factory()->customer()->create([
        'email' => 'customer@example.com',
        'password' => Hash::make('Secret12345!'),
        'is_active' => true,
    ]);

    $response = $this->withHeaders([
        'X-Client' => 'mobile-ios',
    ])->postJson('/api/v1/auth/login', [
        'identity' => 'customer@example.com',
        'password' => 'Secret12345!',
        'device_name' => 'iPhone 15 Pro',
    ]);

    $response->assertOk();
    $tokenString = $response->json('data.token');
    expect($tokenString)->not->toBeNull();

    $tokenRecord = $user->tokens()->latest()->first();
    expect($tokenRecord)->not->toBeNull();
    expect($tokenRecord->name)->toBe('mobile-ios:iPhone 15 Pro');
    expect($tokenRecord->abilities)->toContain('client:mobile');
    expect($tokenRecord->expires_at)->not->toBeNull();
    expect($tokenRecord->abilities)->not->toContain('*');
    expect($tokenRecord->expires_at->between(now()->addDays(29), now()->addDays(31)))->toBeTrue();
});

it('lists active user sessions and allows revoking individual sessions', function () {
    $user = User::factory()->customer()->create();

    // Create 2 tokens
    $token1 = $user->createToken('web:Chrome Mac', ['*'], now()->addDays(30));
    $token2 = $user->createToken('mobile-ios:iPhone', ['*'], now()->addDays(90));

    // Request session list acting as token1
    $response = $this->withToken($token1->plainTextToken)
        ->getJson('/api/v1/auth/sessions');

    $response->assertOk();
    $sessions = $response->json('data');
    expect(count($sessions))->toBe(2);

    // Revoke token2
    $revokeResponse = $this->withToken($token1->plainTextToken)
        ->deleteJson("/api/v1/auth/sessions/{$token2->accessToken->id}");

    $revokeResponse->assertOk();

    // Verify token2 is deleted, token1 remains
    expect($user->tokens()->where('id', $token2->accessToken->id)->exists())->toBeFalse();
    expect($user->tokens()->where('id', $token1->accessToken->id)->exists())->toBeTrue();
});

it('revokes suspended user token immediately on authenticated request (SEC-5/A5)', function () {
    $user = User::factory()->customer()->create([
        'is_active' => true,
        'status' => 'active',
    ]);

    $token = $user->createToken('web:Browser', ['*'], now()->addDays(30));

    // Initially active user request succeeds
    $this->withToken($token->plainTextToken)
        ->getJson('/api/v1/auth/me')
        ->assertOk();

    // Admin suspends user
    $user->is_active = false;
    $user->status = 'suspended';
    $user->save();

    // Next request is rejected with 403 AUTH_ACCOUNT_SUSPENDED
    $response = $this->withToken($token->plainTextToken)
        ->getJson('/api/v1/auth/me');

    $response->assertStatus(403);
    expect($response->json('error.code'))->toBe('AUTH_ACCOUNT_SUSPENDED');

    // Token has been revoked in the database
    expect($user->tokens()->where('id', $token->accessToken->id)->exists())->toBeFalse();
});
