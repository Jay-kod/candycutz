<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Responses\ApiResponse;
use App\Support\Errors\ErrorReporter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientErrorApiController
{
    public function __construct(
        private readonly ErrorReporter $reporter
    ) {}

    public function store(Request $request): JsonResponse
    {
        // Enforce max body size ~32 KB
        $rawContent = (string) $request->getContent();
        if (strlen($rawContent) > 32768) {
            return ApiResponse::error('Payload exceeds 32KB maximum limit', [], 413, 'UPLOAD_INVALID');
        }

        $input = $request->all();
        $items = [];

        // Support either a single error payload or a batch array of errors
        if (isset($input['events']) && is_array($input['events'])) {
            $items = $input['events'];
        } elseif (isset($input['errors']) && is_array($input['errors'])) {
            $items = $input['errors'];
        } elseif (is_array($input) && ! empty($input) && isset($input['message'])) {
            $items = [$input];
        }

        // Batch capped at 20 items
        if (count($items) > 20) {
            $items = array_slice($items, 0, 20);
        }

        $ingestedCount = 0;
        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }
            $this->reporter->reportClientEvent($item, $request);
            $ingestedCount++;
        }

        return ApiResponse::success([
            'ingested_count' => $ingestedCount,
        ], 'Client errors recorded successfully', 201);
    }
}
