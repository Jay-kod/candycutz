<?php

declare(strict_types=1);

use App\Domain\Identity\Services\ConsentService;
use App\Models\AuditLog;
use App\Models\User;
use App\Models\UserConsent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['consent.enforce' => true]);
});

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function consentRegistrationPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Consent Customer',
        'email' => 'consent-customer@example.com',
        'phone' => '+2348012345678',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'terms_accepted' => true,
    ], $overrides);
}

it('rejects registration when the terms checkbox was not accepted', function () {
    $response = $this->postJson('/api/v1/auth/register', consentRegistrationPayload([
        'terms_accepted' => false,
    ]));

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['terms_accepted']);

    expect(User::where('email', 'consent-customer@example.com')->exists())->toBeFalse();
});

it('records an auditable consent row when a customer registers', function () {
    $response = $this->postJson('/api/v1/auth/register', consentRegistrationPayload());

    $response->assertCreated();

    $user = User::where('email', 'consent-customer@example.com')->firstOrFail();
    $consent = UserConsent::where('user_id', $user->id)->where('type', 'terms')->firstOrFail();

    expect($consent->type)->toBe('terms')
        ->and($consent->source)->toBe('register')
        ->and($consent->version)->toBe(app(ConsentService::class)->currentTermsVersion())
        ->and($consent->accepted_at)->not->toBeNull()
        ->and($consent->ip_address)->not->toBeNull()
        ->and($consent->metadata['privacy_version'])->not->toBeNull();

    expect(UserConsent::where('user_id', $user->id)->where('type', 'privacy')->exists())->toBeTrue();

    $audit = AuditLog::where('user_id', $user->id)
        ->where('action', 'consent.terms_accepted')
        ->first();

    expect($audit)->not->toBeNull();
});

it('rejects registration that submits a stale terms version', function () {
    $response = $this->postJson('/api/v1/auth/register', consentRegistrationPayload([
        'terms_version' => '2020-01-01',
    ]));

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['terms_version']);

    expect(User::where('email', 'consent-customer@example.com')->exists())->toBeFalse();
});

it('blocks a customer without a current consent from account endpoints', function () {
    $user = User::factory()->customer()->create();

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/account/profile');

    $response->assertStatus(403)
        ->assertJsonPath('error.code', 'CONSENT_REQUIRED')
        ->assertJsonPath('error.action', 'accept_terms');

    expect($response->json('error.details.context.terms_version'))
        ->toBe(app(ConsentService::class)->currentTermsVersion());
});

it('allows a customer who accepted the current terms', function () {
    $user = User::factory()->customer()->create();
    $version = app(ConsentService::class)->currentTermsVersion();

    UserConsent::create([
        'user_id' => $user->id,
        'type' => 'terms',
        'version' => $version,
        'accepted_at' => now(),
        'source' => 'register',
    ]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/account/profile')
        ->assertOk();
});

it('does not apply the consent gate to non-customer roles', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin, 'sanctum')
        ->getJson('/api/v1/account/profile')
        ->assertOk();
});

it('records consent when a customer accepts through the login gate', function () {
    $user = User::factory()->customer()->create();
    $service = app(ConsentService::class);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/account/consents/accept', [
            'type' => 'terms',
            'version' => $service->currentTermsVersion(),
        ])
        ->assertOk()
        ->assertJsonPath('success', true);

    $consent = UserConsent::where('user_id', $user->id)->firstOrFail();
    expect($consent->source)->toBe('login_gate');

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/account/profile')
        ->assertOk();
});

it('rejects acceptance of a stale terms version', function () {
    $user = User::factory()->customer()->create();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/account/consents/accept', [
            'type' => 'terms',
            'version' => '2020-01-01',
        ])
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'CONSENT_VERSION_STALE');

    expect(UserConsent::where('user_id', $user->id)->where('type', 'terms')->count())->toBe(0);
});

it('keeps consent evidence idempotent across repeated acceptance', function () {
    $user = User::factory()->customer()->create();
    $version = app(ConsentService::class)->currentTermsVersion();

    $first = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/account/consents/accept', ['type' => 'terms', 'version' => $version])
        ->assertOk();

    $second = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/account/consents/accept', ['type' => 'terms', 'version' => $version])
        ->assertOk();

    expect(UserConsent::where('user_id', $user->id)->where('type', 'terms')->count())->toBe(1)
        ->and($second->json('data.accepted_at'))->toBe($first->json('data.accepted_at'))
        ->and(AuditLog::where('user_id', $user->id)->where('action', 'consent.terms_accepted')->count())->toBe(1);
});

it('exposes the consent status on the authenticated user payload', function () {
    $user = User::factory()->customer()->create();

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/auth/me')
        ->assertOk()
        ->assertJsonPath('data.consent_required', true)
        ->assertJsonPath('data.consent.terms_accepted', false);
});

it('reports accepted and pending customers to admins', function () {
    $admin = User::factory()->admin()->create();
    $accepted = User::factory()->customer()->create(['name' => 'Accepted Customer']);
    User::factory()->customer()->create(['name' => 'Pending Customer']);

    $version = app(ConsentService::class)->currentTermsVersion();
    UserConsent::create([
        'user_id' => $accepted->id,
        'type' => 'terms',
        'version' => $version,
        'accepted_at' => now(),
        'source' => 'register',
    ]);

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/v1/admin/consents')
        ->assertOk();

    expect($response->json('data.stats.accepted'))->toBe(1)
        ->and($response->json('data.stats.pending'))->toBe(1);

    $statuses = collect($response->json('data.users'))->pluck('terms_status', 'name');
    expect($statuses['Accepted Customer'])->toBe('accepted')
        ->and($statuses['Pending Customer'])->toBe('pending');
});

it('exports the consent report as csv evidence', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->customer()->create(['email' => 'export-me@example.com']);

    $response = $this->actingAs($admin, 'sanctum')
        ->get('/api/v1/admin/consents/export')
        ->assertOk();

    expect($response->headers->get('content-type'))->toContain('text/csv');

    $csv = $response->streamedContent();
    expect($csv)->toContain('User ID')
        ->and($csv)->toContain('export-me@example.com')
        ->and($csv)->toContain('pending');
});

it('records cookie preferences for guests by consent id', function () {
    $consentId = (string) Str::uuid();

    $this->postJson('/api/v1/consents/cookies', [
        'consent_id' => $consentId,
        'preferences' => true,
        'analytics' => false,
        'marketing' => false,
    ])->assertOk();

    $consent = UserConsent::where('consent_id', $consentId)->firstOrFail();

    expect($consent->user_id)->toBeNull()
        ->and($consent->type)->toBe('cookies')
        ->and($consent->source)->toBe('cookie_banner')
        ->and($consent->metadata['preferences'])->toBeTrue()
        ->and($consent->metadata['analytics'])->toBeFalse();
});

it('requires a consent id from guests posting cookie preferences', function () {
    $this->postJson('/api/v1/consents/cookies', [
        'analytics' => true,
    ])->assertStatus(422);
});

it('keeps public catalogue endpoints available while the gate is on', function () {
    $this->getJson('/api/v1/services')->assertOk();
});
