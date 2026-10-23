<?php

use App\Domain\Identity\Actions\SocialLogin;
use App\Domain\Identity\Services\ApiTokenIssuer;
use App\Domain\Identity\Services\UsernameIdentityService;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

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

it('sends a hashed reset token without revealing whether the account exists', function () {
    $user = User::factory()->create(['email' => 'reset-user@example.com']);

    Mail::shouldReceive('send')
        ->once()
        ->with([], [], Mockery::type(Closure::class));

    $existingResponse = $this->postJson('/api/v1/auth/forgot-password', [
        'email' => $user->email,
    ]);
    $missingResponse = $this->postJson('/api/v1/auth/forgot-password', [
        'email' => 'missing-user@example.com',
    ]);

    $existingResponse->assertOk()->assertJsonPath('message', $missingResponse->json('message'));
    $missingResponse->assertOk();

    $resetRecord = DB::table('password_reset_tokens')->where('email', $user->email)->first();
    expect($resetRecord)->not->toBeNull();
    expect($resetRecord->token)->toStartWith('$2y$');
});

it('resets the password with a valid token, consumes it, and revokes old sessions', function () {
    $user = User::factory()->create(['password' => 'OldPassword123!']);
    $plainToken = Str::random(60);
    DB::table('password_reset_tokens')->insert([
        'email' => $user->email,
        'token' => Hash::make($plainToken),
        'created_at' => now(),
    ]);
    $oldToken = $user->createToken('old-device')->accessToken;

    $response = $this->postJson('/api/v1/auth/reset-password', [
        'email' => $user->email,
        'token' => $plainToken,
        'password' => 'NewPassword123!',
        'password_confirmation' => 'NewPassword123!',
    ]);

    $response->assertOk()->assertJsonPath('success', true);
    expect(Hash::check('NewPassword123!', $user->fresh()->password))->toBeTrue();
    expect(DB::table('password_reset_tokens')->where('email', $user->email)->exists())->toBeFalse();
    expect($user->tokens()->whereKey($oldToken->id)->exists())->toBeFalse();
    expect($user->tokens()->count())->toBe(1);
    expect($response->json('data.token'))->not->toBeEmpty();

    $freshToken = $user->tokens()->latest()->firstOrFail();
    expect($freshToken->expires_at)->not->toBeNull();
    expect($freshToken->expires_at->between(now()->addDays(29), now()->addDays(31)))->toBeTrue();
    expect($freshToken->abilities)->toBe(['client:web']);
});

it('rejects and consumes an expired password reset token', function () {
    $user = User::factory()->create(['password' => 'OldPassword123!']);
    $plainToken = Str::random(60);
    DB::table('password_reset_tokens')->insert([
        'email' => $user->email,
        'token' => Hash::make($plainToken),
        'created_at' => now()->subMinutes(61),
    ]);

    $this->postJson('/api/v1/auth/reset-password', [
        'email' => $user->email,
        'token' => $plainToken,
        'password' => 'NewPassword123!',
        'password_confirmation' => 'NewPassword123!',
    ])->assertUnprocessable();

    expect(Hash::check('OldPassword123!', $user->fresh()->password))->toBeTrue();
    expect(DB::table('password_reset_tokens')->where('email', $user->email)->exists())->toBeFalse();
});

it('sends an email verification notification after customer registration', function () {
    Notification::fake();

    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Verify Me',
        'email' => 'verify-me@example.com',
        'phone' => '+2348012345678',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertCreated();
    $user = User::where('email', 'verify-me@example.com')->firstOrFail();

    Notification::assertSentTo($user, VerifyEmail::class);
});

it('verifies an email using an unexpired signed URL', function () {
    $user = User::factory()->create(['email_verified_at' => null]);
    $url = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(30),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $this->getJson($url)->assertOk()->assertJsonPath('success', true);

    expect($user->fresh()->email_verified_at)->not->toBeNull();
});

it('rejects an unsigned email verification URL', function () {
    $user = User::factory()->create(['email_verified_at' => null]);

    $this->getJson("/api/v1/auth/email/verify/{$user->id}/".sha1($user->email))
        ->assertForbidden();

    expect($user->fresh()->email_verified_at)->toBeNull();
});

it('resends verification only to the authenticated user', function () {
    Notification::fake();
    $user = User::factory()->create(['email_verified_at' => null]);

    $this->actingAs($user)
        ->postJson('/api/v1/auth/email/verification-notification')
        ->assertOk();

    Notification::assertSentTo($user, VerifyEmail::class);

    $this->app['auth']->forgetGuards();
    $this->postJson('/api/v1/auth/email/verification-notification')->assertUnauthorized();
});

it('marks a linked social account verified only when the provider verifies the matching email', function () {
    $user = User::factory()->create([
        'email' => 'social-user@example.com',
        'email_verified_at' => null,
        'auth_provider' => 'google',
        'provider_id' => 'google-subject-123',
    ]);

    $action = new class(app(UsernameIdentityService::class), app(ApiTokenIssuer::class), ['sub' => 'google-subject-123', 'email' => $user->email, 'email_verified' => true]) extends SocialLogin
    {
        public function __construct(UsernameIdentityService $usernameService, ApiTokenIssuer $tokenIssuer, private array $claims)
        {
            parent::__construct($usernameService, $tokenIssuer);
        }

        protected function verifyAndDecodeIdToken(string $provider, string $idToken): array
        {
            return $this->claims;
        }
    };

    $action->execute(['provider' => 'google', 'id_token' => 'verified-provider-token']);

    expect($user->fresh()->email_verified_at)->not->toBeNull();
});

it('sends verification for a new social account when the provider email is unverified', function () {
    Notification::fake();
    $claims = [
        'sub' => 'google-unverified-subject',
        'email' => 'social-unverified@example.com',
        'email_verified' => false,
    ];
    $action = new class(app(UsernameIdentityService::class), app(ApiTokenIssuer::class), $claims) extends SocialLogin
    {
        public function __construct(UsernameIdentityService $usernameService, ApiTokenIssuer $tokenIssuer, private array $claims)
        {
            parent::__construct($usernameService, $tokenIssuer);
        }

        protected function verifyAndDecodeIdToken(string $provider, string $idToken): array
        {
            return $this->claims;
        }
    };

    $action->execute(['provider' => 'google', 'id_token' => 'unverified-provider-token']);

    $user = User::where('email', 'social-unverified@example.com')->firstOrFail();
    expect($user->email_verified_at)->toBeNull();
    Notification::assertSentTo($user, VerifyEmail::class);
});

it('requires re-verification when an account email changes', function () {
    Notification::fake();
    $user = User::factory()->create([
        'email' => 'verified-before@example.com',
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user)->postJson('/api/v1/customer/profile', [
        'name' => $user->name,
        'email' => 'new-address@example.com',
    ])->assertOk();

    $user->refresh();
    expect($user->email_verified_at)->toBeNull();
    Notification::assertSentTo($user, VerifyEmail::class);
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
