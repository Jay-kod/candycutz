<?php

declare(strict_types=1);

namespace App\Domain\Shared\Actions;

use App\Jobs\OptimizeUploadedImageJob;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Mime\MimeTypes;

class SecureImageUpload
{
    public function execute(UploadedFile $file, string $directory = 'uploads', string $disk = 'public'): string
    {
        $this->validateMime($file);

        $rawPath = $this->storeRaw($file, $directory, $disk);
        $actualMime = $this->getMimeType($file);
        $isIco = $this->isIco($file, $actualMime);

        if (! $isIco && extension_loaded('gd') && function_exists('imagewebp')) {
            $converted = $this->optimizeStoredFile($disk, $rawPath, 1920, 82, true, deleteOriginal: true);
            if ($converted !== null) {
                return $converted['path'];
            }
        }

        return $rawPath;
    }

    /**
     * Dispatch background WebP optimization AFTER the model/setting row has been saved with $rawPath.
     */
    public function dispatchOptimization(
        string $rawPath,
        string $disk,
        string $modelClass,
        int|string $modelKey,
        string $attribute,
        bool $prefixStorage = true,
        ?int $actorUserId = null,
        ?string $cacheEntity = null,
        int $maxDim = 1920,
        int $quality = 82,
        bool $generateThumbnail = true,
        ?string $settingKey = null,
    ): void {
        if (strtolower(pathinfo($rawPath, PATHINFO_EXTENSION)) === 'ico') {
            return;
        }

        OptimizeUploadedImageJob::dispatch(
            disk: $disk,
            rawPath: $rawPath,
            modelClass: $modelClass,
            modelKey: $modelKey,
            attribute: $attribute,
            prefixStorage: $prefixStorage,
            actorUserId: $actorUserId,
            cacheEntity: $cacheEntity,
            maxDim: $maxDim,
            quality: $quality,
            generateThumbnail: $generateThumbnail,
            settingKey: $settingKey,
        )->afterCommit();
    }

    /**
     * Store raw uploaded image with minimal latency (<50ms) — pair with dispatchOptimization() after saving the DB row.
     */
    public function storeFast(UploadedFile $file, string $directory = 'uploads', string $disk = 'public'): string
    {
        $this->validateMime($file);

        return $this->storeRaw($file, $directory, $disk);
    }

    public function storeRaw(UploadedFile $file, string $directory = 'uploads', string $disk = 'public'): string
    {
        $actualMime = $this->getMimeType($file);
        $isIco = $this->isIco($file, $actualMime);
        $extension = strtolower((string) ($file->guessExtension() ?: $file->getClientOriginalExtension() ?: ($isIco ? 'ico' : 'jpg')));
        $filename = Str::random(40).'.'.$extension;
        $dir = trim($directory, '/');
        $path = $dir.'/'.$filename;

        Storage::disk($disk)->putFileAs($dir, $file, $filename);

        if ($disk === 'public' && ! app()->runningUnitTests()) {
            $realPath = $file->getRealPath();
            $publicStoragePath = public_path('storage/'.$path);
            if ($realPath && ! file_exists($publicStoragePath)) {
                @mkdir(dirname($publicStoragePath), 0755, true);
                @copy($realPath, $publicStoragePath);
            }
        }

        return $path;
    }

    /**
     * Convert a stored raw image on disk into an optimized WebP (+ optional 480px thumbnail).
     *
     * @return array{path: string, thumb_path: ?string, original_bytes: int, webp_bytes: int}|null
     */
    public function optimizeStoredFile(
        string $disk,
        string $rawPath,
        int $maxDim = 1920,
        int $quality = 82,
        bool $generateThumbnail = true,
        bool $deleteOriginal = true,
    ): ?array {
        if (! extension_loaded('gd') || ! function_exists('imagewebp')) {
            return null;
        }

        if (! Storage::disk($disk)->exists($rawPath)) {
            return null;
        }

        $rawContents = Storage::disk($disk)->get($rawPath);
        if (! is_string($rawContents) || $rawContents === '') {
            return null;
        }

        $originalBytes = strlen($rawContents);
        $img = @imagecreatefromstring($rawContents);
        if ($img === false) {
            return null;
        }

        try {
            $width = imagesx($img);
            $height = imagesy($img);
            $isAlreadyWebp = str_ends_with(strtolower($rawPath), '.webp')
                && ((new \finfo(FILEINFO_MIME_TYPE))->buffer($rawContents) === 'image/webp');

            imagealphablending($img, false);
            imagesavealpha($img, true);

            if ($isAlreadyWebp && $width <= $maxDim && $height <= $maxDim) {
                $encodedThumb = null;
                if ($generateThumbnail) {
                    $thumbWidth = min(480, $width);
                    $thumbHeight = max(1, (int) round($height * ($thumbWidth / max(1, $width))));
                    $thumbImg = imagescale($img, $thumbWidth, $thumbHeight);
                    if ($thumbImg !== false) {
                        imagealphablending($thumbImg, false);
                        imagesavealpha($thumbImg, true);
                        ob_start();
                        imagewebp($thumbImg, null, 80);
                        $encodedThumb = ob_get_clean();
                        imagedestroy($thumbImg);
                    } else {
                        $encodedThumb = $rawContents;
                    }
                }

                imagedestroy($img);

                $dir = trim(dirname($rawPath), '.');
                $prefix = $dir !== '' ? $dir.'/' : '';
                $rawBase = basename($rawPath);
                $thumbPath = $generateThumbnail
                    ? ($prefix.(str_starts_with($rawBase, 'thumb_') ? $rawBase : 'thumb_'.$rawBase))
                    : null;

                if ($thumbPath !== null && is_string($encodedThumb)) {
                    Storage::disk($disk)->put($thumbPath, $encodedThumb);
                    if ($disk === 'public' && ! app()->runningUnitTests()) {
                        $publicThumbPath = public_path('storage/'.$thumbPath);
                        @mkdir(dirname($publicThumbPath), 0755, true);
                        @file_put_contents($publicThumbPath, $encodedThumb);
                    }
                }

                return [
                    'path' => $rawPath,
                    'thumb_path' => $thumbPath,
                    'original_bytes' => $originalBytes,
                    'webp_bytes' => $originalBytes,
                ];
            }

            if ($width > $maxDim || $height > $maxDim) {
                $scale = min($maxDim / $width, $maxDim / $height);
                $newWidth = max(1, (int) round($width * $scale));
                $newHeight = max(1, (int) round($height * $scale));
                $scaled = imagescale($img, $newWidth, $newHeight);
                if ($scaled !== false) {
                    imagedestroy($img);
                    $img = $scaled;
                    imagealphablending($img, false);
                    imagesavealpha($img, true);
                }
            }

            ob_start();
            imagewebp($img, null, $quality);
            $encodedMain = ob_get_clean();

            if (! is_string($encodedMain) || $encodedMain === '') {
                imagedestroy($img);

                return null;
            }

            $encodedThumb = null;
            if ($generateThumbnail) {
                $curWidth = imagesx($img);
                $thumbWidth = min(480, $curWidth);
                $thumbHeight = max(1, (int) round(imagesy($img) * ($thumbWidth / max(1, $curWidth))));
                $thumbImg = imagescale($img, $thumbWidth, $thumbHeight);
                if ($thumbImg !== false) {
                    imagealphablending($thumbImg, false);
                    imagesavealpha($thumbImg, true);
                    ob_start();
                    imagewebp($thumbImg, null, 80);
                    $encodedThumb = ob_get_clean();
                    imagedestroy($thumbImg);
                } else {
                    $encodedThumb = $encodedMain;
                }
            }

            imagedestroy($img);

            $dir = trim(dirname($rawPath), '.');
            $prefix = $dir !== '' ? $dir.'/' : '';
            $filename = Str::random(40).'.webp';
            $webpPath = $prefix.$filename;
            $thumbPath = $generateThumbnail ? ($prefix.'thumb_'.$filename) : null;

            Storage::disk($disk)->put($webpPath, $encodedMain);
            if ($thumbPath !== null && is_string($encodedThumb)) {
                Storage::disk($disk)->put($thumbPath, $encodedThumb);
            }

            if ($disk === 'public' && ! app()->runningUnitTests()) {
                $publicStoragePath = public_path('storage/'.$webpPath);
                @mkdir(dirname($publicStoragePath), 0755, true);
                @file_put_contents($publicStoragePath, $encodedMain);

                if ($thumbPath !== null && is_string($encodedThumb)) {
                    $publicThumbPath = public_path('storage/'.$thumbPath);
                    @file_put_contents($publicThumbPath, $encodedThumb);
                }
            }

            if ($deleteOriginal && $webpPath !== $rawPath) {
                Storage::disk($disk)->delete($rawPath);
                if ($disk === 'public' && ! app()->runningUnitTests()) {
                    $oldPublic = public_path('storage/'.$rawPath);
                    if (file_exists($oldPublic)) {
                        @unlink($oldPublic);
                    }
                }
            }

            return [
                'path' => $webpPath,
                'thumb_path' => $thumbPath,
                'original_bytes' => $originalBytes,
                'webp_bytes' => strlen($encodedMain),
            ];
        } catch (\Throwable) {
            @imagedestroy($img);

            return null;
        }
    }

    protected function validateMime(UploadedFile $file): void
    {
        $allowedMimes = [
            'image/jpeg',
            'image/png',
            'image/webp',
            'image/x-icon',
            'image/vnd.microsoft.icon',
            'image/x-ico',
        ];
        $actualMime = $this->getMimeType($file);

        if ((! in_array($actualMime, $allowedMimes, true) || ! in_array($file->getMimeType(), $allowedMimes, true)) && strtolower($file->getClientOriginalExtension()) !== 'ico') {
            throw ValidationException::withMessages([
                'image' => ['Image must be a valid JPG, PNG, WEBP, or ICO.'],
            ]);
        }

        if ($file->getSize() > 5120 * 1024) {
            throw ValidationException::withMessages([
                'image' => ['Image may not be greater than 5MB.'],
            ]);
        }
    }

    protected function isIco(UploadedFile $file, ?string $actualMime): bool
    {
        return in_array($actualMime, ['image/x-icon', 'image/vnd.microsoft.icon', 'image/x-ico'], true)
            || strtolower($file->getClientOriginalExtension()) === 'ico';
    }

    protected function getMimeType(UploadedFile $file): ?string
    {
        $realPath = $file->getRealPath();
        if ($realPath && @file_exists($realPath)) {
            try {
                return MimeTypes::getDefault()->guessMimeType($realPath) ?: $file->getMimeType();
            } catch (\Throwable) {
                return $file->getMimeType();
            }
        }

        return $file->getMimeType();
    }
}
