<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Identity\Actions\RecordConsent;
use App\Domain\Identity\Services\ConsentService;
use App\Http\Responses\ApiResponse;
use App\Models\AuditLog;
use App\Models\UserConsent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConsentApiController
{
    public function accept(Request $request, ConsentService $consentService, RecordConsent $recordConsent): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:terms'],
            'version' => ['nullable', 'string', 'max:40'],
        ]);

        $type = $validated['type'];
        $currentVersion = $consentService->versionFor($type);
        $submittedVersion = $validated['version'] ?? $currentVersion;

        if ($submittedVersion !== $currentVersion) {
            return ApiResponse::error(
                'The policy has changed. Please review and accept the current version.',
                ['current_version' => $currentVersion],
                422,
                'CONSENT_VERSION_STALE'
            );
        }

        $consent = $recordConsent->execute(
            $request->user(),
            $type,
            $currentVersion,
            'login_gate',
            [
                'documents' => ['terms', 'privacy'],
                'privacy_version' => $consentService->currentPrivacyVersion(),
            ]
        );

        $recordConsent->execute(
            $request->user(),
            'privacy',
            $consentService->currentPrivacyVersion(),
            'login_gate',
            [
                'document' => 'privacy',
                'bundled_with' => 'terms',
            ]
        );

        return ApiResponse::success([
            'type' => $consent->type,
            'version' => $consent->version,
            'accepted_at' => $consent->accepted_at?->toIso8601String(),
        ], 'Consent recorded');
    }

    public function status(Request $request, ConsentService $consentService): JsonResponse
    {
        return ApiResponse::success($consentService->statusFor($request->user()), 'Consent status loaded');
    }

    /**
     * Record the cookie banner decision. Works for signed-in customers and for
     * guests, who are identified by the UUID stored in their browser.
     */
    public function storeCookieConsent(Request $request, ConsentService $consentService): JsonResponse
    {
        $validated = $request->validate([
            'consent_id' => ['nullable', 'uuid'],
            'necessary' => ['sometimes', 'boolean'],
            'preferences' => ['sometimes', 'boolean'],
            'analytics' => ['sometimes', 'boolean'],
            'marketing' => ['sometimes', 'boolean'],
        ]);

        $user = $request->user() ?? $request->user('sanctum');
        $consentId = $validated['consent_id'] ?? null;

        if (! $user && $consentId === null) {
            return ApiResponse::error(
                'A consent_id is required for guests.',
                ['consent_id' => ['A consent_id is required for guests.']],
                422,
                'VALIDATION_FAILED'
            );
        }

        $version = $consentService->currentCookiePolicyVersion();
        $preferences = [
            'necessary' => true,
            'preferences' => (bool) ($validated['preferences'] ?? false),
            'analytics' => (bool) ($validated['analytics'] ?? false),
            'marketing' => (bool) ($validated['marketing'] ?? false),
        ];

        $query = UserConsent::query()
            ->where('type', 'cookies')
            ->where('version', $version);

        if ($user) {
            $query->where('user_id', $user->id);
        } else {
            $query->whereNull('user_id')->where('consent_id', $consentId);
        }

        $consent = $query->first();
        $changed = $consent === null || $consent->metadata !== $preferences;

        if ($consent === null) {
            $consent = UserConsent::query()->create([
                'user_id' => $user?->id,
                'consent_id' => $user ? null : $consentId,
                'type' => 'cookies',
                'version' => $version,
                'accepted_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'source' => 'cookie_banner',
                'metadata' => $preferences,
            ]);
        } elseif ($changed) {
            $consent->update([
                'metadata' => $preferences,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        if ($changed && $user) {
            AuditLog::query()->create([
                'user_id' => $user->id,
                'user_role' => $user->role->value ?? $user->role,
                'action' => 'consent.cookies_updated',
                'module' => 'Consent',
                'target_type' => UserConsent::class,
                'target_id' => $consent->id,
                'old_value' => [],
                'new_value' => $preferences + ['version' => $version],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return ApiResponse::success([
            'consent_id' => $consent->consent_id,
            'version' => $consent->version,
            'preferences' => $consent->metadata,
            'accepted_at' => $consent->accepted_at?->toIso8601String(),
        ], 'Cookie preferences recorded');
    }
}
