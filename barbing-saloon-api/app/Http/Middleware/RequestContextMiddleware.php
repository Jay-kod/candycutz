<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\RequestContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RequestContextMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $context = RequestContext::fromRequest($request);
        app()->instance(RequestContext::class, $context);

        Log::shareContext([
            'request_id' => $context->requestId(),
            'client' => $context->client(),
            'app_version' => $context->appVersion(),
        ]);

        $response = $next($request);

        $response->headers->set('X-Request-Id', $context->requestId());

        return $response;
    }
}
