<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Responses\ApiResponse;
use App\Models\IdempotencyKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdempotencyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Only enforce on state-mutating HTTP methods
        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return $next($request);
        }

        $idempotencyKey = $request->header('Idempotency-Key') ?? $request->header('X-Idempotency-Key');

        if (! $idempotencyKey || ! is_string($idempotencyKey)) {
            return $next($request);
        }

        $cleanKey = substr(trim($idempotencyKey), 0, 64);
        if ($cleanKey === '') {
            return $next($request);
        }

        $userId = $request->user()?->id;
        $route = $request->route()?->uri() ?? $request->path();
        $requestHash = hash('sha256', (string) $request->getContent());

        // Check if an unexpired idempotency record exists
        $existing = IdempotencyKey::query()
            ->where('key', $cleanKey)
            ->where('route', $route)
            ->where('user_id', $userId)
            ->where('expires_at', '>', now())
            ->first();

        if ($existing) {
            // If request hash matches, return stored response replay
            if ($existing->request_hash === $requestHash) {
                return response(
                    $existing->response_body,
                    $existing->response_status,
                    [
                        'Content-Type' => 'application/json',
                        'X-Idempotent-Replay' => '1',
                        'X-Idempotency-Key' => $cleanKey,
                    ]
                );
            }

            // Key was reused with a different request payload
            return ApiResponse::error(
                'Idempotency key reused with different request payload.',
                [],
                409,
                'IDEMPOTENCY_KEY_REUSED'
            );
        }

        $response = $next($request);

        // Cache responses between 200 and 499 (never cache 5xx server errors to allow retries)
        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 500) {
            try {
                IdempotencyKey::create([
                    'key' => $cleanKey,
                    'user_id' => $userId,
                    'route' => $route,
                    'request_hash' => $requestHash,
                    'response_status' => $response->getStatusCode(),
                    'response_body' => $response->getContent(),
                    'response_headers' => ['Content-Type' => 'application/json'],
                    'expires_at' => now()->addHours(48),
                ]);
            } catch (\Throwable) {
                // Silently ignore insert races
            }
        }

        $response->headers->set('X-Idempotency-Key', $cleanKey);

        return $response;
    }
}
