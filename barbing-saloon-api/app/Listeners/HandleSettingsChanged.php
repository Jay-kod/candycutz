<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Domain\Shared\Services\CatalogueCache;
use App\Events\SettingsChanged;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Reacts to system settings changes — busts cached settings
 * and logs changes for the audit trail.
 */
class HandleSettingsChanged implements ShouldQueue
{
    public int $tries = 3;

    public int $backoff = 5;

    public function handle(SettingsChanged $event): void
    {
        $actorName = $event->actor?->name ?? 'System';

        Log::info("Setting changed: {$event->key}", [
            'key' => $event->key,
            'old_value' => $event->oldValue,
            'new_value' => $event->newValue,
            'actor' => $actorName,
        ]);

        // Bust all cached settings so the next read gets fresh data
        Cache::forget('system_settings');
        Cache::forget("setting:{$event->key}");
        CatalogueCache::bust('settings');
    }
}
