<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class UserMediaController
{
    public function show(User $user, string $type, string $filename): Response
    {
        $field = match ($type) {
            'avatar' => 'avatar',
            'cover' => 'cover_image',
            default => abort(404),
        };
        $path = "user-media/{$user->id}/{$type}/{$filename}";
        $storedValue = ltrim(str_replace('storage/', '', (string) $user->{$field}), '/');

        $isThumbnail = str_starts_with($filename, 'thumb_');
        $baseFilename = $isThumbnail ? substr($filename, 6) : $filename;
        $baseStoredPath = "user-media/{$user->id}/{$type}/{$baseFilename}";

        if ($storedValue !== $path && $storedValue !== $baseStoredPath) {
            abort(404);
        }

        $headers = [
            'Cache-Control' => 'public, max-age=604800, immutable',
            'X-Content-Type-Options' => 'nosniff',
        ];

        $pathsToTry = $isThumbnail ? [$path, $baseStoredPath] : [$path];

        foreach ($pathsToTry as $candidatePath) {
            if (Storage::disk('local')->exists($candidatePath)) {
                return Storage::disk('local')->response($candidatePath, null, $headers);
            }

            if (file_exists(storage_path('app/private/'.$candidatePath))) {
                return response()->file(storage_path('app/private/'.$candidatePath), $headers);
            }

            if (file_exists(storage_path('app/'.$candidatePath))) {
                return response()->file(storage_path('app/'.$candidatePath), $headers);
            }

            if (Storage::disk('public')->exists($candidatePath)) {
                return Storage::disk('public')->response($candidatePath, null, $headers);
            }

            if (file_exists(public_path('storage/'.$candidatePath))) {
                return response()->file(public_path('storage/'.$candidatePath), $headers);
            }
        }

        abort(404);
    }
}
