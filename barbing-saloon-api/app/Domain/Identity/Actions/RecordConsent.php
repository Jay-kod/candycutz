<?php

declare(strict_types=1);

namespace App\Domain\Identity\Actions;

use App\Models\AuditLog;
use App\Models\User;
use App\Models\UserConsent;

class RecordConsent
{
    /**
     * Record a consent decision. Repeating the same user + type + version is
     * idempotent and returns the original evidence row unchanged.
     *
     * @param  array<string, mixed>  $metadata
     */
    public function execute(
        User $user,
        string $type,
        string $version,
        string $source,
        array $metadata = []
    ): UserConsent {
        $consent = UserConsent::query()->firstOrCreate(
            [
                'user_id' => $user->id,
                'type' => $type,
                'version' => $version,
            ],
            [
                'accepted_at' => now(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'source' => $source,
                'metadata' => $metadata,
            ]
        );

        if ($consent->wasRecentlyCreated) {
            AuditLog::query()->create([
                'user_id' => $user->id,
                'user_role' => $user->role->value ?? $user->role,
                'action' => "consent.{$type}_accepted",
                'module' => 'Consent',
                'target_type' => User::class,
                'target_id' => $user->id,
                'old_value' => [],
                'new_value' => [
                    'type' => $type,
                    'version' => $version,
                    'source' => $source,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }

        return $consent;
    }
}
