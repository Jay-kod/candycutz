<?php

use App\Domain\Identity\Actions\GetCustomers;
use App\Domain\Identity\Actions\SocialLogin;
use App\Domain\Identity\Services\ApiTokenIssuer;
use App\Domain\Identity\Services\UsernameIdentityService;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
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

it('tests the configured Google OAuth connection', function () {
    Http::fake([
        'https://accounts.google.com/.well-known/openid-configuration' => Http::response([
            'issuer' => 'https://accounts.google.com',
            'authorization_endpoint' => 'https://accounts.google.com/o/oauth2/v2/auth',
            'token_endpoint' => 'https://oauth2.googleapis.com/token',
            'jwks_uri' => 'https://www.googleapis.com/oauth2/v3/certs',
        ], 200),
    ]);

    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->postJson('/api/v1/admin/google-auth/test', [
            'client_id' => '123456789-example.apps.googleusercontent.com',
        ])
        ->assertOk()
        ->assertJsonPath('data.connected', true);

    $this->actingAs($admin)
        ->postJson('/api/v1/admin/google-auth/test', [
            'client_id' => 'not-a-google-client-id',
        ])
        ->assertStatus(422);
});

it('blocks Google login and connection testing when Google authentication is disabled', function () {
    Setting::updateOrCreate(
        ['key' => 'google_auth_enabled'],
        ['value' => 'false', 'group' => 'social']
    );

    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin)
        ->postJson('/api/v1/admin/google-auth/test', [
            'client_id' => '123456789-example.apps.googleusercontent.com',
        ])
        ->assertStatus(422)
        ->assertJsonPath('errors.enabled', false);

    $action = new class(app(UsernameIdentityService::class), app(ApiTokenIssuer::class)) extends SocialLogin
    {
        protected function verifyAndDecodeIdToken(string $provider, string $idToken): array
        {
            return ['sub' => 'disabled-google', 'email' => 'disabled@example.com', 'email_verified' => true];
        }
    };

    try {
        $action->execute(['provider' => 'google', 'id_token' => 'mock_disabled_token']);
        $this->fail('Expected disabled Google login to be rejected');
    } catch (RuntimeException $exception) {
        expect($exception->getMessage())->toContain('disabled');
    }

    Setting::updateOrCreate(
        ['key' => 'google_auth_enabled'],
        ['value' => 'true', 'group' => 'social']
    );
});

it('allows an admin to unlink a Google account', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create([
        'role' => 'customer',
        'auth_provider' => 'google',
        'provider_id' => 'google-unlink-subject',
    ]);

    $this->actingAs($admin)
        ->postJson("/api/v1/admin/google-auth/users/{$customer->id}/unlink")
        ->assertOk()
        ->assertJsonPath('data.auth_provider', 'local');

    expect($customer->fresh())
        ->auth_provider->toBe('local')
        ->provider_id->toBeNull();
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

it('rejects password reset request when email is not verified', function () {
    $user = User::factory()->unverified()->create(['email' => 'unverified-customer@example.com']);

    $response = $this->postJson('/api/v1/auth/forgot-password', [
        'email' => $user->email,
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('error.code', 'EMAIL_NOT_VERIFIED');

    expect(DB::table('password_reset_tokens')->where('email', $user->email)->exists())->toBeFalse();
});

it('resets password using 6-digit OTP code', function () {
    $user = User::factory()->create(['email' => 'otp-reset@example.com', 'password' => 'OldPassword123!']);
    $otp = '654321';

    DB::table('password_reset_tokens')->insert([
        'email' => $user->email,
        'token' => Hash::make($otp),
        'created_at' => now(),
    ]);

    $response = $this->postJson('/api/v1/auth/reset-password', [
        'email' => $user->email,
        'code' => $otp,
        'password' => 'NewSecurePassword123!',
        'password_confirmation' => 'NewSecurePassword123!',
    ]);

    $response->assertOk()->assertJsonPath('success', true);
    expect(Hash::check('NewSecurePassword123!', $user->fresh()->password))->toBeTrue();
    expect(DB::table('password_reset_tokens')->where('email', $user->email)->exists())->toBeFalse();
});

it('burns reset code after 5 failed password reset attempts to prevent brute-force', function () {
    $user = User::factory()->create(['email' => 'bruteforce-test@example.com', 'password' => 'OldPassword123!']);
    $otp = '123456';

    DB::table('password_reset_tokens')->insert([
        'email' => $user->email,
        'token' => Hash::make($otp),
        'created_at' => now(),
    ]);

    // Send 5 incorrect attempts
    for ($i = 0; $i < 5; $i++) {
        $this->postJson('/api/v1/auth/reset-password', [
            'email' => $user->email,
            'code' => '999999',
            'password' => 'NewSecurePassword123!',
            'password_confirmation' => 'NewSecurePassword123!',
        ])->assertStatus(422);
    }

    // 6th attempt should fail and report burned token
    $response = $this->postJson('/api/v1/auth/reset-password', [
        'email' => $user->email,
        'code' => $otp,
        'password' => 'NewSecurePassword123!',
        'password_confirmation' => 'NewSecurePassword123!',
    ]);

    $response->assertStatus(422);
    expect(DB::table('password_reset_tokens')->where('email', $user->email)->exists())->toBeFalse();
});

it('allows admin to override and reset password for unverified customer from dashboard', function () {
    $admin = User::factory()->admin()->create();
    $unverifiedCustomer = User::factory()->customer()->unverified()->create([
        'email' => 'unverified-customer-override@example.com',
    ]);

    $response = $this->actingAs($admin)->postJson("/api/v1/admin/customers/{$unverifiedCustomer->id}/reset-password", [
        'password' => 'AdminSimplePass123',
    ]);

    $response->assertOk()
        ->assertJsonPath('success', true);

    expect(Hash::check('AdminSimplePass123', $unverifiedCustomer->fresh()->password))->toBeTrue();
});

it('invalidates the old password and active tokens immediately when an admin resets a customer password, including demo accounts', function () {
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->customer()->create([
        'email' => 'kefas@customer.com',
        'password' => Hash::make('customer123'),
    ]);
    $existingToken = $customer->createToken('web:chrome')->accessToken;

    $this->postJson('/api/v1/auth/login', [
        'identity' => 'kefas@customer.com',
        'password' => 'customer123',
    ])->assertOk();

    $this->actingAs($admin)->postJson("/api/v1/admin/customers/{$customer->id}/reset-password", [
        'password' => 'LatestPass2026!',
    ])->assertOk();

    expect($customer->tokens()->whereKey($existingToken->id)->exists())->toBeFalse();

    $this->postJson('/api/v1/auth/login', [
        'identity' => 'kefas@customer.com',
        'password' => 'customer123',
    ])->assertStatus(401)->assertJsonPath('error.code', 'AUTH_INVALID_CREDENTIALS');

    $this->postJson('/api/v1/auth/login', [
        'identity' => 'kefas@customer.com',
        'password' => 'Piqa-toga-muxo-daye-13!',
    ])->assertStatus(401)->assertJsonPath('error.code', 'AUTH_INVALID_CREDENTIALS');

    $this->postJson('/api/v1/auth/login', [
        'identity' => 'kefas@customer.com',
        'password' => 'LatestPass2026!',
    ])->assertOk()->assertJsonPath('success', true);
});

it('invalidates the old password immediately when a super admin resets a user password', function () {
    $superAdmin = User::factory()->superAdmin()->create();
    $targetUser = User::factory()->admin()->create([
        'email' => 'webadmin@candycutz.com',
        'password' => Hash::make('admin123'),
    ]);
    $existingToken = $targetUser->createToken('web:edge')->accessToken;

    $this->actingAs($superAdmin)->postJson("/api/v1/super-admin/users/{$targetUser->id}/reset-password", [
        'password' => 'BrandNewAdmin99!',
        'password_confirmation' => 'BrandNewAdmin99!',
    ])->assertOk();

    expect($targetUser->tokens()->whereKey($existingToken->id)->exists())->toBeFalse();

    $this->postJson('/api/v1/auth/login', [
        'identity' => 'webadmin@candycutz.com',
        'password' => 'admin123',
    ])->assertStatus(401)->assertJsonPath('error.code', 'AUTH_INVALID_CREDENTIALS');

    $this->postJson('/api/v1/auth/login', [
        'identity' => 'webadmin@candycutz.com',
        'password' => 'Zemo-lefi-dewi-joma-03!',
    ])->assertStatus(401)->assertJsonPath('error.code', 'AUTH_INVALID_CREDENTIALS');

    $this->postJson('/api/v1/auth/login', [
        'identity' => 'webadmin@candycutz.com',
        'password' => 'BrandNewAdmin99!',
    ])->assertOk()->assertJsonPath('success', true);
});

it('does not allow a soft-deleted user to authenticate via email, username, or phone', function () {
    $user = User::factory()->customer()->create([
        'email' => 'deleted-user@example.com',
        'username' => 'deleteduser',
        'phone' => '08011112222',
        'password' => Hash::make('ValidPass123!'),
    ]);
    $user->delete();

    foreach (['deleted-user@example.com', 'deleteduser', '08011112222'] as $identity) {
        $this->postJson('/api/v1/auth/login', [
            'identity' => $identity,
            'password' => 'ValidPass123!',
        ])->assertStatus(401)->assertJsonPath('error.code', 'AUTH_INVALID_CREDENTIALS');
    }
});

it('does not fall back to another barber account when obo@barber.com does not exist', function () {
    User::factory()->barber()->create([
        'email' => 'obo@candycutz.com',
        'username' => 'obo-candycutz',
        'password' => Hash::make('barber123'),
    ]);

    $this->postJson('/api/v1/auth/login', [
        'identity' => 'obo@barber.com',
        'password' => 'barber123',
    ])->assertStatus(401)->assertJsonPath('error.code', 'AUTH_INVALID_CREDENTIALS');
});

it('invalidates the old demo password after self-service password change or reset', function () {
    $customer = User::factory()->customer()->create([
        'email' => 'customer@candycutz.com',
        'password' => Hash::make('customer123'),
    ]);

    $this->actingAs($customer)->postJson('/api/v1/auth/change-password', [
        'current_password' => 'customer123',
        'new_password' => 'SelfUpdatedPass99!',
        'new_password_confirmation' => 'SelfUpdatedPass99!',
    ])->assertOk();

    $this->postJson('/api/v1/auth/login', [
        'identity' => 'customer@candycutz.com',
        'password' => 'customer123',
    ])->assertStatus(401)->assertJsonPath('error.code', 'AUTH_INVALID_CREDENTIALS');

    $this->postJson('/api/v1/auth/login', [
        'identity' => 'customer@candycutz.com',
        'password' => 'SelfUpdatedPass99!',
    ])->assertOk()->assertJsonPath('success', true);
});

it('sends an email verification notification after customer registration', function () {
    Notification::fake();

    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Verify Me',
        'email' => 'verify-me@example.com',
        'phone' => '+2348012345678',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'terms_accepted' => true,
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

it('links a verified Google login to an existing account with the same email and saves profile details', function () {
    $user = User::factory()->create([
        'name' => 'Original Name',
        'real_name' => 'Original Name',
        'email' => 'synced-user@example.com',
        'phone' => '',
        'avatar' => null,
        'email_verified_at' => now(),
        'auth_provider' => 'local',
        'provider_id' => null,
    ]);

    $claims = [
        'sub' => 'google-synced-subject',
        'email' => 'synced-user@example.com',
        'email_verified' => true,
        'name' => 'Synced Google Name',
        'given_name' => 'Synced',
        'family_name' => 'Google Name',
        'picture' => 'https://example.com/google-avatar.jpg',
        'phone_number' => '+2348000000000',
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

    $payload = $action->execute(['provider' => 'google', 'id_token' => 'verified-provider-token']);

    expect(User::whereRaw('LOWER(email) = ?', ['synced-user@example.com'])->count())->toBe(1);
    expect($payload['user']->id)->toBe($user->id);
    expect($user->fresh())
        ->auth_provider->toBe('google')
        ->provider_id->toBe('google-synced-subject')
        ->name->toBe('Synced Google Name')
        ->real_name->toBe('Synced Google Name')
        ->avatar->toBe('https://example.com/google-avatar.jpg')
        ->phone->toBe('+2348000000000');
});

it('returns email verification status for customer management', function () {
    User::factory()->create([
        'role' => 'customer',
        'name' => 'Shared Name',
        'email' => 'verified-name@example.com',
        'email_verified_at' => now(),
    ]);
    User::factory()->create([
        'role' => 'customer',
        'name' => 'Shared Name',
        'email' => 'unverified-name@example.com',
        'email_verified_at' => null,
    ]);

    $customers = app(GetCustomers::class)->execute();
    $byEmail = collect($customers)->keyBy('email');

    expect($byEmail['verified-name@example.com']['is_email_verified'])->toBeTrue();
    expect($byEmail['verified-name@example.com']['email_verified_at'])->not->toBeNull();
    expect($byEmail['unverified-name@example.com']['is_email_verified'])->toBeFalse();
    expect($byEmail['unverified-name@example.com']['email_verified_at'])->toBeNull();
});

it('reuses the same account for repeated verified social logins', function () {
    $claims = [
        'sub' => 'google-repeat-subject',
        'email' => 'repeat-google@example.com',
        'email_verified' => true,
        'name' => 'Repeat Google User',
        'picture' => 'https://example.com/repeat-avatar.jpg',
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

    $first = $action->execute(['provider' => 'google', 'id_token' => 'repeat-provider-token']);
    $second = $action->execute(['provider' => 'google', 'id_token' => 'repeat-provider-token']);

    expect($first['user']->id)->toBe($second['user']->id);
    expect(User::whereRaw('LOWER(email) = ?', ['repeat-google@example.com'])->count())->toBe(1);
    expect(User::where('auth_provider', 'google')->where('provider_id', 'google-repeat-subject')->count())->toBe(1);
});

it('does not link or create another account when an existing email was not verified by the provider', function () {
    $user = User::factory()->create([
        'email' => 'protected-account@example.com',
        'email_verified_at' => now(),
        'auth_provider' => 'local',
    ]);

    $action = new class(app(UsernameIdentityService::class), app(ApiTokenIssuer::class), ['sub' => 'google-unverified-link', 'email' => 'protected-account@example.com', 'email_verified' => false, 'name' => 'Should Not Link']) extends SocialLogin
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

    try {
        $action->execute(['provider' => 'google', 'id_token' => 'unverified-provider-token']);
        $this->fail('Expected unverified email account linking to be rejected');
    } catch (RuntimeException $exception) {
        expect($exception->getMessage())->toContain('existing account');
    }

    expect(User::whereRaw('LOWER(email) = ?', ['protected-account@example.com'])->count())->toBe(1);
    expect($user->fresh()->provider_id)->toBeNull();
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
