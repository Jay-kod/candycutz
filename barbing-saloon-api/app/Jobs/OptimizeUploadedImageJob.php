<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domain\Content\Services\SettingsService;
use App\Domain\Notification\NotificationDispatcher;
use App\Domain\Shared\Actions\SecureImageUpload;
use App\Domain\Shared\Services\CatalogueCache;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class OptimizeUploadedImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 60;

    public bool $failOnTimeout = true;

    public function __construct(
        public readonly string $disk,
        public readonly string $rawPath,
        public readonly string $modelClass,
        public readonly int|string $modelKey,
        public readonly string $attribute,
        public readonly bool $prefixStorage = true,
        public readonly ?int $actorUserId = null,
        public readonly ?string $cacheEntity = null,
        public readonly int $maxDim = 1920,
        public readonly int $quality = 82,
        public readonly bool $generateThumbnail = true,
        public readonly ?string $settingKey = null,
    ) {}

    public function handle(SecureImageUpload $uploader): void
    {
        $expectedValue = $this->prefixStorage ? '/storage/'.$this->rawPath : $this->rawPath;

        $result = $uploader->optimizeStoredFile(
            disk: $this->disk,
            rawPath: $this->rawPath,
            maxDim: $this->maxDim,
            quality: $this->quality,
            generateThumbnail: $this->generateThumbnail,
            deleteOriginal: false,
        );

        if ($result === null) {
            return;
        }

        $newPath = $result['path'];
        $newValue = $this->prefixStorage ? '/storage/'.$newPath : $newPath;

        $matchesCurrent = false;
        if ($this->settingKey !== null) {
            $matchesCurrent = Setting::where('key', $this->settingKey)
                ->where('value', $expectedValue)
                ->exists();
            if ($matchesCurrent && $newPath !== $this->rawPath) {
                Setting::where('key', $this->settingKey)
                    ->where('value', $expectedValue)
                    ->update(['value' => $newValue]);
            }
            if ($matchesCurrent) {
                app(SettingsService::class)->clearCache();
            }
        } elseif (class_exists($this->modelClass)) {
            /** @var class-string<Model> $cls */
            $cls = $this->modelClass;
            $matchesCurrent = $cls::whereKey($this->modelKey)
                ->where($this->attribute, $expectedValue)
                ->exists();
            if ($matchesCurrent && $newPath !== $this->rawPath) {
                $cls::whereKey($this->modelKey)
                    ->where($this->attribute, $expectedValue)
                    ->update([$this->attribute => $newValue]);
            }
        }

        if ($matchesCurrent) {
            if ($newPath !== $this->rawPath) {
                Storage::disk($this->disk)->delete($this->rawPath);
                if ($this->disk === 'public' && ! app()->runningUnitTests()) {
                    $oldPublic = public_path('storage/'.$this->rawPath);
                    if (file_exists($oldPublic)) {
                        @unlink($oldPublic);
                    }
                }
            }

            if ($this->cacheEntity !== null) {
                CatalogueCache::bust($this->cacheEntity);
            }

            if ($this->actorUserId !== null) {
                try {
                    NotificationDispatcher::mediaOptimized(
                        userId: $this->actorUserId,
                        entity: $this->cacheEntity ?: strtolower(class_basename($this->modelClass)),
                        entityId: (int) $this->modelKey,
                        originalBytes: $result['original_bytes'],
                        webpBytes: $result['webp_bytes'],
                        newPath: $newValue,
                    );
                } catch (\Throwable $e) {
                    Log::warning("mediaOptimized notification failed: {$e->getMessage()}");
                }
            }
        } else {
            // A newer upload superseded or deleted this raw image while in queue — discard stale WebP artifacts
            if ($newPath !== $this->rawPath) {
                Storage::disk($this->disk)->delete($newPath);
            }
            if ($result['thumb_path'] !== null) {
                Storage::disk($this->disk)->delete($result['thumb_path']);
            }
        }
    }
}
