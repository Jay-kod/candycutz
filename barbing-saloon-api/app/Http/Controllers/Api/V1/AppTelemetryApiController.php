<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Responses\ApiResponse;
use App\Models\AppCrash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AppTelemetryApiController
{
    /**
     * Ingest an unhandled application exception / crash from client.
     */
    public function reportCrash(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'error_message' => 'required|string|max:5000',
            'stack_trace' => 'nullable|string|max:50000',
            'component_stack' => 'nullable|string|max:20000',
            'app_version' => 'nullable|string|max:32',
            'platform' => 'nullable|string|max:20',
            'device_info' => 'nullable|array',
        ]);

        try {
            $crash = AppCrash::create([
                'user_id' => $request->user()?->id,
                'error_message' => $validated['error_message'],
                'stack_trace' => $validated['stack_trace'] ?? null,
                'component_stack' => $validated['component_stack'] ?? null,
                'app_version' => $validated['app_version'] ?? $request->header('X-App-Version'),
                'platform' => $validated['platform'] ?? $request->header('X-App-Platform', 'unknown'),
                'device_info' => $validated['device_info'] ?? null,
            ]);

            Log::error('Client App Crash Captured', [
                'id' => $crash->id,
                'user_id' => $crash->user_id,
                'error' => $crash->error_message,
                'version' => $crash->app_version,
            ]);

            return ApiResponse::success([
                'crash_id' => $crash->id,
            ], 'Crash telemetry recorded successfully.', 201);
        } catch (\Throwable $e) {
            Log::warning('Failed to persist app crash: '.$e->getMessage());

            return ApiResponse::error('Unable to record crash telemetry.', [], 500);
        }
    }
}
