<?php

use App\Http\Middleware\ApiGateMiddleware;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\EnsureUserActive;
use App\Http\Middleware\ForceHttps;
use App\Http\Middleware\LogApiRequest;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Responses\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Http\Middleware\CheckAppVersion;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'check.role' => CheckRole::class,
            'force.https' => ForceHttps::class,
            'security.headers' => SecurityHeaders::class,
            'log.api.request' => LogApiRequest::class,
            'api.gate' => ApiGateMiddleware::class,
            'check.app.version' => CheckAppVersion::class,
            'ensure.active' => EnsureUserActive::class,
            'request.context' => \App\Http\Middleware\RequestContextMiddleware::class,
        ]);

        $middleware->api(prepend: [
            \App\Http\Middleware\RequestContextMiddleware::class,
        ]);

        $middleware->api(append: [
            CheckAppVersion::class,
            EnsureUserActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->shouldRenderJsonWhen(fn ($request) => $request->is('api/*') || $request->expectsJson());

        $exceptions->render(function (\Throwable $e, Request $r) {
            if ($r->is('api/*') || $r->expectsJson()) {
                return app(\App\Support\Errors\ApiExceptionRenderer::class)->render($e, $r);
            }
            return null;
        });

        $exceptions->report(function (\Throwable $e) {
            app(\App\Support\Errors\ErrorReporter::class)->report($e);
        });

        $exceptions->context(function () {
            return app()->bound(\App\Support\RequestContext::class)
                ? app(\App\Support\RequestContext::class)->toArray()
                : [];
        });

        $exceptions->throttle(function (\Throwable $e) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(30)->by(
                get_class($e).'|'.$e->getFile().$e->getLine()
            );
        });
    })->create();
