<?php

namespace App\Core\Traits;

use App\Core\Http\Response\ApiResponse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

trait HasApiResponse
{
    public function success(mixed $data, string $message, int $status)
    {
        return ApiResponse::success($data, $message, $status);
    }

    public function error(string $message, array $errors, int $status)
    {
        return ApiResponse::error($message, $errors, $status);
    }

    public function paginated(LengthAwarePaginator $data, string $message)
    {
        return ApiResponse::paginated($data, $message);
    }
}