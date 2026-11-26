<?php

declare(strict_types=1);

namespace App\Domain\Identity\Services;

use App\Models\Setting;
use App\Models\User;
use App\Models\UserConsent;
use InvalidArgumentException;

class ConsentService
{
    public function currentTermsVersion(): string
    {
        return $this->setting('terms_version', (string) config('consent.terms_version'));
    }

    public function currentPrivacyVersion(): string
    {
        return $this->setting('privacy_version', (string) config('consent.privacy_version'));
    }

    public function currentCookiePolicyVersion(): string
    {
        return $this->setting('cookie_policy_version', (string) config('consent.cookie_policy_version'));
    }

    public function hasAccepted(User $user, string $type, ?string $version = null): bool
    {
        $version ??= $this->versionFor($type);

        return UserConsent::query()
            ->where('user_id', $user->id)
            ->where('type', $type)
            ->where('version', $version)
            ->exists();
    }

    /**
     * @return array<string, mixed>
     */
    public function statusFor(User $user): array
    {
        $role = $user->role->value ?? $user->role;
        $enforced = (bool) config('consent.enforce')
            && in_array($role, (array) config('consent.enforce_roles', ['customer']), true);

        $termsVersion = $this->currentTermsVersion();
        $terms = UserConsent::query()
            ->where('user_id', $user->id)
            ->where('type', 'terms')
            ->orderByDesc('accepted_at')
            ->first();

        $acceptedCurrent = $terms !== null && $terms->version === $termsVersion;

        return [
            'required' => $enforced && ! $acceptedCurrent,
            'terms_version' => $termsVersion,
            'terms_accepted' => $acceptedCurrent,
            'terms_accepted_at' => $terms?->accepted_at?->toIso8601String(),
            'terms_accepted_version' => $terms?->version,
            'privacy_version' => $this->currentPrivacyVersion(),
            'documents' => config('consent.documents'),
        ];
    }

    public function versionFor(string $type): string
    {
        return match ($type) {
            'terms' => $this->currentTermsVersion(),
            'privacy' => $this->currentPrivacyVersion(),
            'cookies' => $this->currentCookiePolicyVersion(),
            default => throw new InvalidArgumentException("Unknown consent type: {$type}"),
        };
    }

    private function setting(string $key, string $fallback): string
    {
        $value = Setting::query()->where('key', $key)->value('value');

        return is_string($value) && $value !== '' ? $value : $fallback;
    }
}
