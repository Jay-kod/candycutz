<?php

use App\Http\Middleware\ApiGateMiddleware;
use App\Http\Middleware\CheckAppVersion;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\ClientGateMiddleware;
use App\Http\Middleware\EnsureUserActive;
use App\Http\Middleware\FeatureGateMiddleware;
use App\Http\Middleware\ForceHttps;
use App\Http\Middleware\IdempotencyMiddleware;
use App\Http\Middleware\LogApiRequest;
use App\Http\Middleware\RequestContextMiddleware;
use App\Http\Middleware\SecurityHeaders;
use App\Support\Errors\ApiExceptionRenderer;
use App\Support\Errors\ErrorReporter;
use App\Support\RequestContext;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'check.role' => CheckRole::class,
            'force.https' => ForceHttps::class,
            'security.headers' => SecurityHeaders::class,
            'log.api.request' => LogApiRequest::class,
            'api.gate' => ApiGateMiddleware::class,
            'check.app.version' => CheckAppVersion::class,
            'ensure.active' => EnsureUserActive::class,
            'request.context' => RequestContextMiddleware::class,
            'idempotent' => IdempotencyMiddleware::class,
            'client.gate' => ClientGateMiddleware::class,
            'feature' => FeatureGateMiddleware::class,
        ]);

        $middleware->api(prepend: [
            RequestContextMiddleware::class,
            ClientGateMiddleware::class,
        ]);

        $middleware->api(append: [
            CheckAppVersion::class,
            EnsureUserActive::class,
            IdempotencyMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->shouldRenderJsonWhen(fn ($request) => $request->is('api/*') || $request->expectsJson());

        $exceptions->render(function (Throwable $e, Request $r) {
            if ($r->is('api/*') || $r->expectsJson()) {
                return app(ApiExceptionRenderer::class)->render($e, $r);
            }

            return null;
        });

        $exceptions->report(function (Throwable $e) {
            app(ErrorReporter::class)->report($e);
        });

        $exceptions->context(function () {
            return app()->bound(RequestContext::class)
                ? app(RequestContext::class)->toArray()
                : [];
        });

        $exceptions->throttle(function (Throwable $e) {
            return Limit::perMinute(30)->by(
                get_class($e).'|'.$e->getFile().$e->getLine()
            );
        });
    })->create();
