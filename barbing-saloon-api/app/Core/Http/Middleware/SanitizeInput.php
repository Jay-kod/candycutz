<?php

namespace App\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SanitizeInput
{
    public function handle(Request $request, Closure $next)
    {
        $request->merge($this->sanitizeArray($request->all()));

        return $next($request);
    }

    protected function sanitizeArray(array $input): array
    {
        foreach ($input as $key => $value) {
            if (in_array($key, ['password', 'password_confirmation'], true)) {
                continue;
            }

            if (is_string($value)) {
                $input[$key] = trim(strip_tags($value));
            }

            if (is_array($value)) {
                $input[$key] = $this->sanitizeArray($value);
            }
        }

        return $input;
    }
}