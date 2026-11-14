<?php

declare(strict_types=1);

namespace App\Domain\Shared\Actions;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Intervention\Image\ImageManager;
use Symfony\Component\Mime\MimeTypes;

class SecureImageUpload
{
    public function execute(UploadedFile $file, string $directory = 'uploads', string $disk = 'public'): string
    {
        $this->validateMime($file);

        $actualMime = $this->getMimeType($file);
        $isIco = in_array($actualMime, ['image/x-icon', 'image/vnd.microsoft.icon', 'image/x-ico'], true)
            || strtolower($file->getClientOriginalExtension()) === 'ico';

        $realPath = $file->getRealPath();
        $rawContents = $realPath ? @file_get_contents($realPath) : null;
        $canReadContents = is_string($rawContents) && strlen($rawContents) > 0;

        // Process through GD auto-WebP pipeline if GD extension loaded, imagewebp available, and not an ICO file
        if (! $isIco && extension_loaded('gd') && function_exists('imagewebp') && $canReadContents) {
            $img = @imagecreatefromstring($rawContents);
            if ($img !== false) {
                try {
                    $width = imagesx($img);
                    $height = imagesy($img);

                    imagealphablending($img, false);
                    imagesavealpha($img, true);

                    // Scale down primary image if exceeding 1920px max dimension
                    if ($width > 1920 || $height > 1920) {
                        $scale = min(1920 / $width, 1920 / $height);
                        $newWidth = (int) round($width * $scale);
                        $newHeight = (int) round($height * $scale);
                        $scaled = imagescale($img, $newWidth, $newHeight);
                        if ($scaled !== false) {
                            imagedestroy($img);
                            $img = $scaled;
                            imagealphablending($img, false);
                            imagesavealpha($img, true);
                        }
                    }

                    // Encode primary image to WebP (quality 82)
                    ob_start();
                    imagewebp($img, null, 82);
                    $encodedMain = ob_get_clean();

                    // Generate responsive companion thumbnail (480px width)
                    $thumbWidth = min(480, imagesx($img));
                    $thumbHeight = (int) round(imagesy($img) * ($thumbWidth / imagesx($img)));
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

                    imagedestroy($img);

                    $filename = Str::random(40).'.webp';
                    $path = trim($directory, '/').'/'.$filename;
                    $thumbPath = trim($directory, '/').'/thumb_'.$filename;

                    Storage::disk($disk)->put($path, (string) $encodedMain);
                    Storage::disk($disk)->put($thumbPath, (string) $encodedThumb);

                    if (! app()->runningUnitTests()) {
                        $publicStoragePath = public_path('storage/'.$path);
                        $publicThumbPath = public_path('storage/'.$thumbPath);
                        if (! file_exists($publicStoragePath)) {
                            @mkdir(dirname($publicStoragePath), 0755, true);
                            @file_put_contents($publicStoragePath, (string) $encodedMain);
                            @file_put_contents($publicThumbPath, (string) $encodedThumb);
                        }
                    }

                    return $path;
                } catch (\Throwable) {
                    if (isset($img) && is_resource($img)) {
                        @imagedestroy($img);
                    }
                }
            }
        }

        $extension = $file->guessExtension() ?: ($isIco ? 'ico' : 'jpg');
        $filename = Str::random(40).'.'.$extension;
        $path = trim($directory, '/').'/'.$filename;
        Storage::disk($disk)->putFileAs(trim($directory, '/'), $file, $filename);

        if (! app()->runningUnitTests() && $isReadable) {
            $publicStoragePath = public_path('storage/'.$path);
            if (! file_exists($publicStoragePath)) {
                @mkdir(dirname($publicStoragePath), 0755, true);
                @copy($realPath, $publicStoragePath);
            }
        }

        return $path;
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
