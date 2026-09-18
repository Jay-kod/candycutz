<?php

declare(strict_types=1);

namespace App\Domain\Shared\Actions;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Mime\MimeTypes;

class SecureImageUpload
{
    public function execute(UploadedFile $file, string $directory = 'uploads'): string
    {
        $this->validateMime($file);

        // Process through Intervention Image (which automatically strips EXIF by default when re-encoding)
        $manager = new ImageManager(new Driver());
        
        try {
            $image = $manager->read($file->getRealPath());
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'image' => ['The uploaded file is not a valid image.'],
            ]);
        }
        
        // Re-encode to webp to neutralize payloads and strip metadata
        $encoded = $image->toWebp(90);
        
        $filename = Str::random(40) . '.webp';
        $path = trim($directory, '/') . '/' . $filename;
        
        Storage::disk('public')->put($path, (string) $encoded);
        
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
