<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\ApiGateLog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ApiGateLog
 */
class ApiGateLogResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'method' => $this->method,
            'path' => $this->path,
            'client_type' => $this->client_type,
            'status_code' => $this->status_code,
            'duration_ms' => $this->duration_ms,
            'query_count' => $this->query_count,
            'query_duration_ms' => $this->query_duration_ms,
            'memory_kb' => (int) round($this->memory_bytes / 1024),
            'user_name' => $this->user?->name,
            'user_role' => $this->user_role,
            'ip_address' => $this->ip_address,
            'is_slow' => (bool) $this->is_slow,
            'budget_exceeded' => (bool) $this->budget_exceeded,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
