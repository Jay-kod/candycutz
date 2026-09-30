<?php

declare(strict_types=1);

namespace App\Support\Errors;

use App\Http\Responses\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class ApiExceptionRenderer
{
    public function render(Throwable $e, Request $request): Response
    {
        if ($e instanceof HttpResponseException) {
            return $e->getResponse();
        }

        // 1. Domain Typed Exceptions
        if ($e instanceof AppException) {
            $code = $e->getErrorCode();
            return ApiResponse::error(
                message: $e->getMessage(),
                errors: $e->getDetails(),
                status: $code->status(),
                errorCode: $code,
                context: $e->getCustomContext()
            );
        }

        // 2. Validation
        if ($e instanceof ValidationException) {
            return ApiResponse::error(
                message: 'The given data was invalid.',
                errors: $e->errors(),
                status: 422,
                errorCode: ErrorCode::VALIDATION_FAILED
            );
        }

        // 3. Authentication
        if ($e instanceof AuthenticationException) {
            return ApiResponse::error(
                message: 'Unauthenticated.',
                errors: [],
                status: 401,
                errorCode: ErrorCode::AUTH_TOKEN_INVALID
            );
        }

        // 4. Authorization & Access Denied
        if ($e instanceof AuthorizationException || $e instanceof AccessDeniedHttpException) {
            return ApiResponse::error(
                message: $e->getMessage() ?: 'Forbidden.',
                errors: [],
                status: 403,
                errorCode: ErrorCode::AUTH_FORBIDDEN_ROLE
            );
        }

        // 5. Rate Limiting
        if ($e instanceof ThrottleRequestsException) {
            $retryAfter = null;
            foreach ($e->getHeaders() as $header => $val) {
                if (strtolower($header) === 'retry-after') {
                    $retryAfter = (int) $val;
                    break;
                }
            }

            return ApiResponse::error(
                message: 'Too many requests. Please slow down.',
                errors: [],
                status: 429,
                errorCode: ErrorCode::RATE_LIMITED,
                retryAfter: $retryAfter
            );
        }

        // 6. Not Found
        if ($e instanceof ModelNotFoundException) {
            $modelName = class_basename($e->getModel());
            return ApiResponse::error(
                message: "The requested {$modelName} was not found.",
                errors: [],
                status: 404,
                errorCode: ErrorCode::RESOURCE_NOT_FOUND
            );
        }

        if ($e instanceof NotFoundHttpException) {
            return ApiResponse::error(
                message: 'The requested resource was not found.',
                errors: [],
                status: 404,
                errorCode: ErrorCode::RESOURCE_NOT_FOUND
            );
        }

        // 7. Method Not Allowed
        if ($e instanceof MethodNotAllowedHttpException) {
            return ApiResponse::error(
                message: 'The method is not allowed for this route.',
                errors: [],
                status: 405,
                errorCode: ErrorCode::METHOD_NOT_ALLOWED
            );
        }

        // 8. CSRF / Token Mismatch
        if ($e instanceof TokenMismatchException) {
            return ApiResponse::error(
                message: 'Your session token has expired. Please refresh and try again.',
                errors: [],
                status: 419,
                errorCode: ErrorCode::AUTH_TOKEN_EXPIRED
            );
        }

        // 9. Database / Query Exceptions
        if ($e instanceof QueryException) {
            $state = (string) $e->getCode();
            $sqlCode = $e->errorInfo[1] ?? null;

            if (in_array($state, ['2002', '2006', 'HY000'], true) || str_contains($e->getMessage(), 'Connection refused')) {
                $code = ErrorCode::DB_UNAVAILABLE;
            } elseif (in_array((string) $sqlCode, ['1213', '40001'], true) || in_array($state, ['40001'], true)) {
                $code = ErrorCode::DB_DEADLOCK;
            } elseif (in_array((string) $sqlCode, ['1062', '1452'], true) || $state === '23000') {
                $code = ErrorCode::DB_CONSTRAINT_VIOLATION;
            } elseif (in_array((string) $sqlCode, ['1054', '1146'], true) || in_array($state, ['42S22', '42S02'], true)) {
                $code = ErrorCode::SCHEMA_DRIFT;
            } else {
                $code = ErrorCode::DB_UNAVAILABLE;
            }

            return ApiResponse::error(
                message: app()->environment('production') ? $code->userMessage() : $e->getMessage(),
                errors: [],
                status: $code->status(),
                errorCode: $code
            );
        }

        // 10. Outbound HTTP Request Exceptions
        if ($e instanceof RequestException) {
            return ApiResponse::error(
                message: 'An external service error occurred. Please try again later.',
                errors: [],
                status: 502,
                errorCode: ErrorCode::EXTERNAL_SERVICE_ERROR
            );
        }

        // 11. Generic HttpException
        if ($e instanceof HttpException) {
            $status = $e->getStatusCode();
            $code = match ($status) {
                503 => ErrorCode::FEATURE_DISABLED,
                default => ($status >= 500 ? ErrorCode::SERVER_ERROR : null),
            };

            return ApiResponse::error(
                message: $e->getMessage() ?: ($code ? $code->userMessage() : 'HTTP Error'),
                errors: [],
                status: $status,
                errorCode: $code
            );
        }

        // 12. Fallback unclassified Throwable -> SERVER_ERROR
        return ApiResponse::error(
            message: app()->environment('production') ? ErrorCode::SERVER_ERROR->userMessage() : $e->getMessage(),
            errors: [],
            status: 500,
            errorCode: ErrorCode::SERVER_ERROR
        );
    }
}
