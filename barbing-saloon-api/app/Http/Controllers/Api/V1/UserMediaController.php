<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserMediaController
{
    public function show(User $user, string $type, string $filename): StreamedResponse
    {
        $field = match ($type) {
            'avatar' => 'avatar',
            'cover' => 'cover_image',
            default => abort(404),
        };
        $path = "user-media/{$user->id}/{$type}/{$filename}";
        if ($user->{$field} !== $path) {
            abort(404);
        }

        $disk = Storage::disk('local');
        if (! $disk->exists($path)) {
            abort(404);
        }

        return $disk->response($path, null, [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
