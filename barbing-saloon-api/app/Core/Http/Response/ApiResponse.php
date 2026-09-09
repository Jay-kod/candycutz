<?php

declare(strict_types=1);

namespace App\Core\Http\Response;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function success(mixed $data = null, string $message = 'Success', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    public static function error(string $message = 'Error', array $errors = [], int $status = 400, ?string $errorCode = null): JsonResponse
    {
        $code = $errorCode ?? match ($status) {
            401 => 'UNAUTHENTICATED',
            403 => 'FORBIDDEN_ROLE',
            404 => 'RESOURCE_NOT_FOUND',
            409 => 'IDEMPOTENCY_COLLISION',
            422 => 'VALIDATION_FAILED',
            429 => 'RATE_LIMIT_EXCEEDED',
            500 => 'SERVER_ERROR',
            default => 'BAD_REQUEST',
        };

        $payload = [
            'success' => false,
            'message' => $message,
            'error' => [
                'code' => $code,
                'message' => $message,
                'details' => $errors,
            ],
            'code' => $status,
        ];

        if (! empty($errors)) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }

    public static function paginated(LengthAwarePaginator $data, string $message = 'Success', int $status = 200): JsonResponse
    {
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
        ], $status);
    }
}