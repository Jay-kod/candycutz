<?php

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;

it('sends email verification notification with 6-digit otp', function () {
    Notification::fake();
    $user = User::factory()->create(['email_verified_at' => null]);

    $this->actingAs($user)
        ->postJson('/api/v1/auth/email/verification-notification')
        ->assertOk()
        ->assertJsonPath('success', true);

    Notification::assertSentTo($user, VerifyEmail::class);
    expect(Cache::has("email_verification_otp:{$user->id}"))->toBeTrue();
});

it('retrieves email verification status for user', function () {
    $unverified = User::factory()->create(['email_verified_at' => null]);
    $verified = User::factory()->create(['email_verified_at' => now()]);

    $this->actingAs($unverified)
        ->getJson('/api/v1/auth/email/status')
        ->assertOk()
        ->assertJsonPath('data.is_email_verified', false);

    $this->actingAs($verified)
        ->getJson('/api/v1/auth/email/status')
        ->assertOk()
        ->assertJsonPath('data.is_email_verified', true);
});

it('verifies email with valid 6-digit otp code', function () {
    $user = User::factory()->create(['email_verified_at' => null]);
    Cache::put("email_verification_otp:{$user->id}", '847291', now()->addMinutes(15));

    $this->actingAs($user)
        ->postJson('/api/v1/auth/email/verify-code', ['code' => '847291'])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.is_email_verified', true);

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
    expect(Cache::has("email_verification_otp:{$user->id}"))->toBeFalse();
});

it('rejects invalid or expired 6-digit otp code', function () {
    $user = User::factory()->create(['email_verified_at' => null]);
    Cache::put("email_verification_otp:{$user->id}", '847291', now()->addMinutes(15));

    $this->actingAs($user)
        ->postJson('/api/v1/auth/email/verify-code', ['code' => '000000'])
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'INVALID_VERIFICATION_CODE');

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});
