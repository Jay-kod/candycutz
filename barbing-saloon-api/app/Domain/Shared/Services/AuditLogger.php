<?php

declare(strict_types=1);

namespace App\Domain\Shared\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class AuditLogger
{
    /**
     * Safely records an administrative or security audit event.
     *
     * @param  array<string, mixed>  $oldValue
     * @param  array<string, mixed>  $newValue
     */
    public static function log(
        string $action,
        string $module,
        ?Model $target = null,
        array $oldValue = [],
        array $newValue = [],
        ?string $targetType = null,
        ?int $targetId = null
    ): ?AuditLog {
        try {
            $user = auth()->user();

            $userRole = null;
            if ($user) {
                $userRole = $user->role instanceof \BackedEnum ? $user->role->value : (string) $user->role;
            }

            return AuditLog::create([
                'user_id' => $user?->id,
                'user_role' => $userRole ?? 'system',
                'action' => $action,
                'module' => $module,
                'target_type' => $target ? class_basename($target) : $targetType,
                'target_id' => $target ? $target->getKey() : $targetId,
                'old_value' => ! empty($oldValue) ? $oldValue : null,
                'new_value' => ! empty($newValue) ? $newValue : null,
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'CLI/System',
            ]);
        } catch (\Throwable $e) {
            Log::warning("Could not write audit log for [{$action}]: ".$e->getMessage());

            return null;
        }
    }
}
