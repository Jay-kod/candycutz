<?php

declare(strict_types=1);

namespace App\Http\Responses;

use App\Support\Errors\ErrorCode;
use App\Support\RequestContext;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

class ApiResponse
{
    private static function resolveRequestId(): string
    {
        if (app()->bound(RequestContext::class)) {
            return app(RequestContext::class)->requestId();
        }

        $header = request()?->header('X-Request-Id');
        if (is_string($header) && preg_match('/^req_[A-Za-z0-9]{10,40}$/', $header)) {
            return $header;
        }

        return RequestContext::generateRequestId();
    }

    public static function success(mixed $data = null, string $message = 'Success', int $status = 200, array $headers = []): JsonResponse
    {
        $requestId = self::resolveRequestId();
        $headers['X-Request-Id'] = $requestId;

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'request_id' => $requestId,
        ], $status, $headers);
    }

    /**
     * @param  array<string, mixed>  $errors
     * @param  array<string, mixed>  $context
     * @param  array<string, string>  $headers
     */
    public static function error(
        string $message = 'Error',
        array $errors = [],
        int $status = 400,
        string|ErrorCode|null $errorCode = null,
        ?string $action = null,
        ?bool $retryable = null,
        ?int $retryAfter = null,
        array $context = [],
        array $headers = []
    ): JsonResponse {
        $requestId = self::resolveRequestId();
        $headers['X-Request-Id'] = $requestId;

        if ($retryAfter !== null) {
            $headers['Retry-After'] = (string) $retryAfter;
        }

        $codeEnum = null;
        if ($errorCode instanceof ErrorCode) {
            $codeEnum = $errorCode;
            $codeString = $errorCode->value;
        } elseif (is_string($errorCode)) {
            $codeEnum = ErrorCode::tryFrom($errorCode);
            $codeString = $errorCode;
        } else {
            $codeEnum = match ($status) {
                401 => ErrorCode::AUTH_TOKEN_INVALID,
                403 => ErrorCode::AUTH_FORBIDDEN_ROLE,
                404 => ErrorCode::RESOURCE_NOT_FOUND,
                405 => ErrorCode::METHOD_NOT_ALLOWED,
                409 => ErrorCode::BOOKING_SLOT_TAKEN,
                422 => ErrorCode::VALIDATION_FAILED,
                426 => ErrorCode::APP_UPDATE_REQUIRED,
                429 => ErrorCode::RATE_LIMITED,
                502 => ErrorCode::EXTERNAL_SERVICE_ERROR,
                503 => ErrorCode::DB_UNAVAILABLE,
                default => ($status >= 500 ? ErrorCode::SERVER_ERROR : null),
            };
            $codeString = $codeEnum ? $codeEnum->value : ($status >= 500 ? 'SERVER_ERROR' : 'BAD_REQUEST');
        }

        $category = $codeEnum ? $codeEnum->category() : 'system';
        $resolvedAction = $action ?? ($codeEnum ? $codeEnum->action() : ($status >= 500 ? 'contact_support' : 'fix_input'));
        $resolvedRetryable = $retryable ?? ($codeEnum ? $codeEnum->retryable() : false);

        // In production, never leak raw exception messages or SQL details on 5xx errors
        if ($status >= 500 && app()->environment('production')) {
            $message = 'An unexpected server error occurred. Please contact support with reference: '.$requestId;
        }

        $errorObject = [
            'code' => $codeString,
            'message' => $message,
            'category' => $category,
            'action' => $resolvedAction,
            'retryable' => $resolvedRetryable,
            'retry_after' => $retryAfter,
            'details' => [
                'field_errors' => ! empty($errors) ? $errors : (object) [],
                'context' => ! empty($context) ? $context : (object) [],
            ],
            'request_id' => $requestId,
            'docs' => '/docs/errors#'.$codeString,
        ];

        $payload = [
            'success' => false,
            'message' => $message,
            'error' => $errorObject,
            'request_id' => $requestId,
            'errors' => ! empty($errors) ? $errors : (object) [],
            'code' => $status,
        ];

        return response()->json($payload, $status, $headers);
    }

    public static function paginated(LengthAwarePaginator $data, string $message = 'Success', int $status = 200, array $headers = []): JsonResponse
    {
        $requestId = self::resolveRequestId();
        $headers['X-Request-Id'] = $requestId;

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data->items(),
            'meta' => [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
            ],
            'request_id' => $requestId,
        ], $status, $headers);
    }
}
