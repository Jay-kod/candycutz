<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Gate\Actions\GetGateLogs;
use App\Domain\Gate\Actions\GetGateMetrics;
use App\Http\Resources\ApiGateLogResource;
use App\Http\Responses\ApiResponse;
use App\Models\ApiGateLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GateApiController
{
    public function metrics(GetGateMetrics $action): JsonResponse
    {
        return ApiResponse::success($action->execute(), 'Gate metrics loaded');
    }

    public function logs(Request $request, GetGateLogs $action): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 50);
        $paginator = $action->execute($request->all(), $perPage);

        return ApiResponse::success([
            'items' => ApiGateLogResource::collection($paginator->items()),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ], 'Gate logs loaded');
    }

    public function flush(Request $request): JsonResponse
    {
        $keepDays = (int) $request->input('keep_days', 7);
        $deleted = ApiGateLog::where('created_at', '<', now()->subDays($keepDays))->delete();

        return ApiResponse::success([
            'pruned_records' => $deleted,
            'retained_days' => $keepDays,
        ], 'Gate telemetry historical logs pruned.');
    }
}
