<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'check.role' => App\Core\Http\Middleware\CheckRole::class,
            'sanitize.input' => App\Core\Http\Middleware\SanitizeInput::class,
            'force.https' => App\Core\Http\Middleware\ForceHttps::class,
            'security.headers' => App\Core\Http\Middleware\SecurityHeaders::class,
            'log.api.request' => App\Core\Http\Middleware\LogApiRequest::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return \App\Core\Http\Response\ApiResponse::error('Unauthenticated.', [], 401, 'UNAUTHENTICATED');
            }
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return \App\Core\Http\Response\ApiResponse::error($e->getMessage() ?: 'Forbidden.', [], 403, 'FORBIDDEN_ROLE');
            }
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return \App\Core\Http\Response\ApiResponse::error('The requested resource was not found.', [], 404, 'RESOURCE_NOT_FOUND');
            }
        });

        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return \App\Core\Http\Response\ApiResponse::error('The given data was invalid.', $e->errors(), 422, 'VALIDATION_FAILED');
            }
        });
    })->create();