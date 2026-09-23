<?php

use App\Http\Middleware\ApiGateMiddleware;
use App\Http\Middleware\CheckRole;
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
        ]);

        $middleware->api(append: [
            CheckAppVersion::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return ApiResponse::error('Unauthenticated.', [], 401, 'UNAUTHENTICATED');
            }
        });

        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return ApiResponse::error($e->getMessage() ?: 'Forbidden.', [], 403, 'FORBIDDEN_ROLE');
            }
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return ApiResponse::error('The requested resource was not found.', [], 404, 'RESOURCE_NOT_FOUND');
            }
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return ApiResponse::error('The given data was invalid.', $e->errors(), 422, 'VALIDATION_FAILED');
            }
        });

        $exceptions->render(function (HttpException $e, Request $request) {
            if ($e->getStatusCode() === 503 && ($request->is('api/*') || $request->expectsJson())) {
                return response()->json([
                    'success' => false,
                    'message' => 'The server is temporarily undergoing maintenance. Please check back shortly.',
                    'code' => 'MAINTENANCE_MODE',
                    'maintenance' => true,
                    'retry_after' => $e->getHeaders()['Retry-After'] ?? null,
                ], 503);
            }
        });
    })->create();
