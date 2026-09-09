<?php

declare(strict_types=1);

namespace App\Core\Http\Controllers\Api\V1;

use App\Core\Http\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class HealthApiController
{
    public function health(): JsonResponse
    {
        $dbStatus = 'disconnected';
        try {
            DB::connection()->getPdo();
            $dbStatus = 'connected';
        } catch (Throwable) {
            $dbStatus = 'disconnected';
        }

        $storageStatus = is_writable(storage_path('framework')) ? 'writable' : 'readonly';

        $isHealthy = $dbStatus === 'connected';

        $data = [
            'status' => $isHealthy ? 'healthy' : 'degraded',
            'timestamp' => now()->toIso8601String(),
            'services' => [
                'database' => $dbStatus,
                'storage' => $storageStatus,
                'api_version' => 'v1',
                'flagship' => 'Keffi, Nasarawa State, Nigeria',
            ],
        ];

        return ApiResponse::success($data, $isHealthy ? 'System healthy' : 'System degraded', $isHealthy ? 200 : 503);
    }
}
