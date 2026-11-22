<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Responses\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class BlockMaliciousIngress
{
    /**
     * Patterns matching path traversal, sensitive dotfiles, and automated vulnerability scanners.
     */
    protected const BLOCKED_PATH_PATTERNS = [
        // Directory / Path traversal attempts
        '/\.\.(\/|\\\\)/',
        '/%2e%2e/i',
        '/\%2f/i',
        '/\%5c/i',

        // Probing for environment, git, and configuration files
        '/(^|\/)\.(env|git|svn|hg|bzr|aws|docker|kube|ssh|ds_store)/i',
        '/\.(env|bak|swp|old|dist|local)($|\/)/i',

        // Probing for unauthorized administration or scanner scripts
        '/(wp-admin|wp-login|xmlrpc|phpmyadmin|pma|adminer|phpinfo|eval-stdin|web\.config)/i',
    ];

    /**
     * Dangerous query string signatures commonly used by automated exploit scanners.
     */
    protected const BLOCKED_QUERY_PATTERNS = [
        '/union\s+select/i',
        '/select\s+.*\s+from\s+information_schema/i',
        '/concat\(.*0x/i',
        '/<script[\s>]/i',
        '/javascript:\s*/i',
        '/(etc\/passwd|win\.ini)/i',
        '/\/bin\/(sh|bash)/i',
    ];

    /**
     * Permitted HTTP methods on ingress.
     */
    protected const ALLOWED_METHODS = [
        'GET',
        'POST',
        'PUT',
        'PATCH',
        'DELETE',
        'OPTIONS',
        'HEAD',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // 1. Method whitelist check
        $method = strtoupper($request->method());
        if (! in_array($method, self::ALLOWED_METHODS, true)) {
            $this->recordBlockedProbe($request, 'disallowed_method');

            return ApiResponse::error('HTTP method not allowed.', [], 405, 'METHOD_NOT_ALLOWED');
        }

        // 2. URI path length check (prevent buffer overflow / ReDoS attempts)
        $rawUri = (string) $request->getRequestUri();
        if (strlen($rawUri) > 2048) {
            $this->recordBlockedProbe($request, 'excessive_uri_length');

            return ApiResponse::error('Request URI is excessively long.', [], 414, 'URI_TOO_LONG');
        }

        // 3. Scan URI path for traversal and probe patterns
        $path = $request->path();
        $decodedPath = rawurldecode($path);
        foreach (self::BLOCKED_PATH_PATTERNS as $pattern) {
            if (preg_match($pattern, $path) || preg_match($pattern, $decodedPath) || preg_match($pattern, $rawUri)) {
                $this->recordBlockedProbe($request, 'path_probe:'.$pattern);

                return ApiResponse::error('Access denied by security shield.', [], 400, 'MALICIOUS_PROBE_BLOCKED');
            }
        }

        // 4. Scan query string for SQLi / LFI exploit patterns
        $queryString = rawurldecode((string) $request->getQueryString());
        if ($queryString !== '') {
            foreach (self::BLOCKED_QUERY_PATTERNS as $pattern) {
                if (preg_match($pattern, $queryString)) {
                    $this->recordBlockedProbe($request, 'query_exploit:'.$pattern);

                    return ApiResponse::error('Malicious request parameter detected.', [], 400, 'MALICIOUS_QUERY_BLOCKED');
                }
            }
        }

        return $next($request);
    }

    protected function recordBlockedProbe(Request $request, string $reason): void
    {
        try {
            Cache::increment('security_probes_blocked_total');
            Cache::increment('security_probes_blocked_24h');
            Log::warning('Malicious ingress request blocked by security shield.', [
                'reason' => $reason,
                'ip' => $request->ip(),
                'method' => $request->method(),
                'uri' => $request->getRequestUri(),
                'user_agent' => $request->userAgent(),
            ]);
        } catch (\Throwable) {
            // Failsafe
        }
    }
}
