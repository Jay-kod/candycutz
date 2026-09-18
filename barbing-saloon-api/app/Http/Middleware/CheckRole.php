<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = auth()->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
                'code' => 401,
            ], 401);
        }

        $role = $user->role?->value ?? $user->role;

        // Super Admin has full administrative access to admin endpoints
        if ($role === 'super_admin') {
            return $next($request);
        }

        $allowed = [];
        foreach ($roles as $r) {
            foreach (explode(',', (string) $r) as $part) {
                $trimmed = trim($part);
                if ($trimmed !== '') {
                    $allowed[] = $trimmed;
                }
            }
        }

        if (! in_array($role, $allowed, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden',
                'code' => 403,
            ], 403);
        }

        return $next($request);
    }
}
