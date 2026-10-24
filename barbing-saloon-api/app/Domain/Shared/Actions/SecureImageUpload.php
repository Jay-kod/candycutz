<?php

declare(strict_types=1);

namespace App\Domain\Shared\Actions;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Symfony\Component\Mime\MimeTypes;

class SecureImageUpload
{
    public function execute(UploadedFile $file, string $directory = 'uploads', string $disk = 'public'): string
    {
        $this->validateMime($file);

        $actualMime = MimeTypes::getDefault()->guessMimeType($file->getRealPath());
        $isIco = in_array($actualMime, ['image/x-icon', 'image/vnd.microsoft.icon', 'image/x-ico'], true)
            || strtolower($file->getClientOriginalExtension()) === 'ico';

        // Process through Intervention Image if available, GD extension loaded, and not an ICO file
        if (! $isIco && class_exists(ImageManager::class) && extension_loaded('gd')) {
            try {
                $manager = new ImageManager(new Driver);
                $image = $manager->read($file->getRealPath());
                $encoded = $image->toWebp(90);
                $filename = Str::random(40).'.webp';
                $path = trim($directory, '/').'/'.$filename;
                Storage::disk($disk)->put($path, (string) $encoded);

                $publicStoragePath = public_path('storage/'.$path);
                if (! file_exists($publicStoragePath)) {
                    @mkdir(dirname($publicStoragePath), 0755, true);
                    @file_put_contents($publicStoragePath, (string) $encoded);
                }

                return $path;
            } catch (\Exception $e) {
                throw ValidationException::withMessages([
                    'image' => ['The uploaded file is not a valid image.'],
                ]);
            }
        }

        $extension = $file->guessExtension() ?: ($isIco ? 'ico' : 'jpg');
        $filename = Str::random(40).'.'.$extension;
        $path = trim($directory, '/').'/'.$filename;
        Storage::disk($disk)->putFileAs(trim($directory, '/'), $file, $filename);

        $publicStoragePath = public_path('storage/'.$path);
        if (! file_exists($publicStoragePath)) {
            @mkdir(dirname($publicStoragePath), 0755, true);
            @copy($file->getRealPath(), $publicStoragePath);
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
        $actualMime = MimeTypes::getDefault()->guessMimeType($file->getRealPath());

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
}
