<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Responses\ApiResponse;
use App\Support\Errors\ErrorCode;
use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

/**
 * Reject requests from deactivated or suspended users.
 *
 * CheckRole verifies the user has the right role but never checks account status.
 * This middleware closes that gap: any authenticated user whose account is inactive
 * or suspended gets a 403 before the request reaches a controller.
 */
class EnsureUserActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user() ?? auth('sanctum')->user();

        if (! $user) {
            return $next($request);
        }

        $currentToken = $user->currentAccessToken();
        $user->refresh();

        if (! $request->user()) {
            $request->setUserResolver(fn () => $user);
        }

        if (! $user->is_active || in_array($user->status, ['deactivated', 'suspended'], true)) {
            // Revoke the current token immediately so it cannot be reused
            if ($currentToken && method_exists($currentToken, 'delete')) {
                $currentToken->delete();
            }

            return ApiResponse::error(
                'Your account is currently inactive or suspended. Please contact support.',
                [],
                403,
                ErrorCode::AUTH_ACCOUNT_SUSPENDED,
                action: 'logout'
            );
        }

        // Sliding token expiration check
        $token = $user->currentAccessToken();
        if ($token instanceof PersonalAccessToken && $token->expires_at) {
            if ($token->expires_at->isPast()) {
                $token->delete();

                return ApiResponse::error(
                    'Your session has expired. Please log in again.',
                    [],
                    401,
                    ErrorCode::AUTH_TOKEN_EXPIRED,
                    action: 'logout'
                );
            }

            // Extend if less than 50% of the lifetime remains, at most once per hour
            $totalDays = str_starts_with((string) $token->name, 'mobile') ? 90 : 30;
            $remainingSeconds = now()->diffInSeconds($token->expires_at, false);
            $halfLifeSeconds = ($totalDays * 86400) / 2;

            if ($remainingSeconds < $halfLifeSeconds) {
                $lastUpdatedSeconds = $token->updated_at ? now()->diffInSeconds($token->updated_at) : 3600;
                if ($lastUpdatedSeconds >= 3600) {
                    $token->forceFill(['expires_at' => now()->addDays($totalDays)])->save();
                }
            }
        }

        return $next($request);
    }
}
