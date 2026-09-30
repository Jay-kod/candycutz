<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Responses\ApiResponse;
use Closure;
use Illuminate\Http\Request;
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
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if (! $user->is_active || in_array($user->status, ['deactivated', 'suspended'], true)) {
            return ApiResponse::error(
                'Your account is currently inactive or suspended. Please contact support.',
                [],
                403,
                \App\Support\Errors\ErrorCode::AUTH_ACCOUNT_SUSPENDED
            );
        }

        return $next($request);
    }
}
