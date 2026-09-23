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
    public function execute(UploadedFile $file, string $directory = 'uploads'): string
    {
        $this->validateMime($file);

        // Process through Intervention Image if available and GD extension loaded
        if (class_exists(ImageManager::class) && extension_loaded('gd')) {
            try {
                $manager = new ImageManager(new Driver);
                $image = $manager->read($file->getRealPath());
                $encoded = $image->toWebp(90);
                $filename = Str::random(40).'.webp';
                $path = trim($directory, '/').'/'.$filename;
                Storage::disk('public')->put($path, (string) $encoded);
                return $path;
            } catch (\Exception $e) {
                throw ValidationException::withMessages([
                    'image' => ['The uploaded file is not a valid image.'],
                ]);
            }
        }

        $extension = $file->guessExtension() ?: 'jpg';
        $filename = Str::random(40).'.'.$extension;
        $path = trim($directory, '/').'/'.$filename;
        Storage::disk('public')->putFileAs(trim($directory, '/'), $file, $filename);

        return $path;
    }

    protected function validateMime(UploadedFile $file): void
    {
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
        $actualMime = MimeTypes::getDefault()->guessMimeType($file->getRealPath());

        if (! in_array($actualMime, $allowedMimes, true) || ! in_array($file->getMimeType(), $allowedMimes, true)) {
            throw ValidationException::withMessages([
                'image' => ['Image must be a valid JPG, PNG, or WEBP.'],
            ]);
        }

        if ($file->getSize() > 5120 * 1024) {
            throw ValidationException::withMessages([
                'image' => ['Image may not be greater than 5MB.'],
            ]);
        }
    }
}
