<?php

namespace App\Core\Traits;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HasSecureUploads
{
    public function uploadFile(UploadedFile $file, string $folder): string
    {
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $mimeType = $file->getMimeType();

        if (! in_array($mimeType, $allowedMimeTypes, true)) {
            throw new HttpResponseException(response()->json(['success' => false, 'message' => 'Invalid file type', 'code' => 422], 422));
        }

        if ($file->getSize() > 2 * 1024 * 1024) {
            throw new HttpResponseException(response()->json(['success' => false, 'message' => 'File exceeds 2MB limit', 'code' => 422], 422));
        }

        $folder = trim($folder, '/');
        $fileName = Str::uuid() . '-' . time() . '.webp';

        return $file->storePubliclyAs($folder, $fileName, 'public');
    }

    public function deleteFile(string $path): void
    {
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    public function buildUserPath(string $type, int $userId): string
    {
        return "users/{$userId}/{$type}/";
    }

    public function buildResourcePath(string $type, int $resourceId): string
    {
        return "{$type}/{$resourceId}/";
    }
}