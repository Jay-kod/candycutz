<?php

namespace App\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $roles)
    {
        $allowed = array_map('trim', explode(',', $roles));
        $role = auth()->user()?->role?->value ?? auth()->user()?->role;

        if (! auth()->check() || ! in_array($role, $allowed, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden',
                'code' => 403,
            ], 403);
        }

        return $next($request);
    }
}