<?php

namespace App\Core\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

trait HasAuditLog
{
    public function logAction(string $action, Model $target, array $oldValue = [], array $newValue = []): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'user_role' => auth()->user()?->role?->value ?? null,
            'action' => $action,
            'module' => class_basename(static::class),
            'target_type' => $target::class,
            'target_id' => $target->getKey(),
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}