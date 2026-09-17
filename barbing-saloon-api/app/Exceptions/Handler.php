<?php

namespace App\Exceptions;

use App\Http\Responses\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
        AuthenticationException::class,
        AuthorizationException::class,
        ValidationException::class,
    ];

    public function register(): void
    {
        $this->renderable(function (AuthenticationException $exception) {
            return ApiResponse::error('Invalid credentials', [], Response::HTTP_UNAUTHORIZED);
        });

        $this->renderable(function (ValidationException $exception) {
            return ApiResponse::error('The given data was invalid.', $exception->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        });

        $this->renderable(function (ThrottleRequestsException $exception) {
            return ApiResponse::error('Too many requests', [], Response::HTTP_TOO_MANY_REQUESTS);
        });

        $this->renderable(function (ModelNotFoundException $exception) {
            return ApiResponse::error('Resource not found', [], Response::HTTP_NOT_FOUND);
        });

        // Database errors - hide details
        $this->renderable(function (QueryException $exception) {
            if (app()->environment('production')) {
                return ApiResponse::error('Database error. Please try again.', [], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return null;
        });
    }

    public function render($request, Throwable $exception)
    {
        // For JSON requests, always return standardized error response
        if ($request->expectsJson()) {
            if (app()->environment('production')) {
                return ApiResponse::error('Something went wrong', [], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            // In non-production, parent will handle it with more details
        }

        return parent::render($request, $exception);
    }
}
