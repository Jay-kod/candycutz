<?php

declare(strict_types=1);

namespace App\Domain\Shared\Services;

use App\Models\User;
use Illuminate\Support\Facades\URL;

class UserMediaUrl
{
    public function for(?User $user, ?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $path)) {
            return $path;
        }

        $normalizedPath = ltrim($path, '/');
        if (str_starts_with($normalizedPath, 'storage/')) {
            $normalizedPath = substr($normalizedPath, strlen('storage/'));
        }

        $prefix = $user ? "user-media/{$user->id}/" : '';
        if ($prefix !== '' && str_starts_with($normalizedPath, $prefix)) {
            $relativePath = substr($normalizedPath, strlen($prefix));
            $parts = explode('/', $relativePath, 2);
            if (count($parts) === 2 && in_array($parts[0], ['avatar', 'cover'], true)) {
                return URL::temporarySignedRoute(
                    'user-media.show',
                    now()->addMinutes(30),
                    ['user' => $user->id, 'type' => $parts[0], 'filename' => basename($parts[1])]
                );
            }
        }

        return url('storage/'.$normalizedPath);
    }
}
