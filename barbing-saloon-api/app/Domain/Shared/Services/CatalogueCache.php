<?php

declare(strict_types=1);

namespace App\Domain\Shared\Services;

use Closure;
use Illuminate\Support\Facades\Cache;

/**
 * High-performance versioned caching engine for public catalogue data.
 *
 * Implements atomic version-stamping cache invalidation compatible with all
 * cache drivers (Redis, file, database, array) without requiring driver-specific tagging.
 */
class CatalogueCache
{
    /** Default TTL for catalogue endpoints: 1 hour */
    public const TTL_SECONDS = 3600;

    /**
     * Cache or retrieve data for a catalogue entity using version-isolated keys.
     *
     * @template T
     *
     * @param  string  $entity  e.g. 'services', 'categories', 'barbers', 'gallery', 'blog', 'testimonials'
     * @param  string  $key  Entity-specific subkey (e.g. query params hash or slug)
     * @param  Closure(): T  $callback
     * @return T
     */
    public static function remember(string $entity, string $key, Closure $callback, int $ttl = self::TTL_SECONDS): mixed
    {
        $version = static::getVersion($entity);
        $fullKey = "catalogue:{$entity}:v{$version}:{$key}";

        return Cache::remember($fullKey, $ttl, $callback);
    }

    /**
     * Get the current active cache version for an entity.
     */
    public static function getVersion(string $entity): int
    {
        return (int) Cache::get("catalogue_version:{$entity}", 1);
    }

    /**
     * Atomically invalidate all cached entries for an entity across all filters and keys.
     */
    public static function bust(string $entity): void
    {
        try {
            if (! Cache::has("catalogue_version:{$entity}")) {
                Cache::forever("catalogue_version:{$entity}", 2);
            } else {
                Cache::increment("catalogue_version:{$entity}");
            }
        } catch (\Throwable) {
            Cache::forever("catalogue_version:{$entity}", (int) (microtime(true) * 1000));
        }
    }

    /**
     * Invalidate all catalogue entity caches at once.
     */
    public static function bustAll(): void
    {
        foreach (['services', 'categories', 'barbers', 'gallery', 'testimonials', 'blog', 'service_zones', 'working_hours', 'settings'] as $entity) {
            static::bust($entity);
        }
    }
}
