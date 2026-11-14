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
        if ($storedValue !== $path) {
            abort(404);
        }

        if (Storage::disk('local')->exists($path)) {
            return Storage::disk('local')->response($path, null, [
                'Cache-Control' => 'private, no-store',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        }

        if (file_exists(storage_path('app/'.$path))) {
            return response()->file(storage_path('app/'.$path), [
                'Cache-Control' => 'private, no-store',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        }

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->response($path, null, [
                'Cache-Control' => 'private, no-store',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        }

        if (file_exists(public_path('storage/'.$path))) {
            return response()->file(public_path('storage/'.$path), [
                'Cache-Control' => 'private, no-store',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        }

        abort(404);
    }
}
