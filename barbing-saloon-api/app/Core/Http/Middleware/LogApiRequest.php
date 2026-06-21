<?php

namespace App\Core\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;

class LogApiRequest
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (! $request->isMethod('get')) {
            $routeName = $request->route()?->getName();
            $module = $routeName ? explode('.', $routeName)[0] : explode('/', trim($request->path(), '/'))[1] ?? 'api';

            AuditLog::create([
                'user_id' => auth()->id(),
                'user_role' => auth()->user()?->role?->value ?? null,
                'action' => $request->method() . ' ' . $request->path(),
                'module' => $module,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return $response;
    }
}