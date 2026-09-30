<?php

declare(strict_types=1);

namespace App\Support\Errors;

use App\Models\ErrorEvent;
use App\Models\ErrorGroup;
use App\Support\RequestContext;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use PDOException;
use Throwable;

class ErrorReporter
{
    private const SENSITIVE_REGEX = '/(password|token|secret|authorization|cookie|card|cvv|id_token|otp|api_key|receipt)/i';
    private const MAX_TRACE_BYTES = 16384;
    private const MAX_BODY_BYTES = 4096;
    private const MAX_EVENTS_PER_HOUR = 20;

    public function report(Throwable $e, ?Request $request = null, ?ErrorCode $errorCode = null): void
    {
        try {
            $this->processReport($e, $request, $errorCode);
        } catch (Throwable $fallbackEx) {
            $this->writeFallbackLog($e, $request, $errorCode, $fallbackEx->getMessage());
        }
    }

    private function processReport(Throwable $e, ?Request $request, ?ErrorCode $errorCode): void
    {
        if ($e instanceof \Illuminate\Http\Exceptions\HttpResponseException) {
            return;
        }

        $request = $request ?? (request() instanceof Request ? request() : null);

        $resolvedCode = $errorCode ?? $this->resolveErrorCode($e);
        $category = $resolvedCode->category();
        $severity = $resolvedCode->severity();

        $context = app()->bound(RequestContext::class) ? app(RequestContext::class) : null;
        $requestId = $context?->requestId() ?? RequestContext::generateRequestId();

        $normFile = $this->normalisePath($e->getFile());
        $line = $e->getLine();
        $exceptionClass = get_class($e);
        $routeUri = $request?->route()?->uri() ?? $request?->path() ?? 'console';

        $fingerprint = sha1($resolvedCode->value.'|'.$exceptionClass.'|'.$normFile.':'.$line.'|'.$routeUri);
        $sampleMessage = $this->normaliseMessage($e->getMessage() ?: $resolvedCode->userMessage());

        $source = app()->runningInConsole() ? 'console' : 'api';
        $now = Carbon::now();

        // 1. Group handling with storm control & regression detection
        $group = ErrorGroup::query()->where('fingerprint', $fingerprint)->first();

        if (! $group) {
            $group = new ErrorGroup([
                'fingerprint' => $fingerprint,
                'error_code' => $resolvedCode->value,
                'category' => $category,
                'severity' => $severity,
                'exception_class' => $exceptionClass,
                'sample_message' => $sampleMessage,
                'source' => $source,
                'status' => 'open',
                'occurrences' => 1,
                'first_seen_at' => $now,
                'last_seen_at' => $now,
                'last_request_id' => $requestId,
                'affected_users_count' => ($context?->userId() ? 1 : 0),
            ]);
            $group->save();
            $shouldStoreEvent = true;
        } else {
            $isRegressed = ($group->status === 'resolved');
            $group->occurrences++;
            $group->last_seen_at = $now;
            $group->last_request_id = $requestId;

            if ($isRegressed) {
                $group->status = 'open';
                $group->regressed_at = $now;
            }

            if ($context?->userId()) {
                // Increment affected user if this user hasn't been seen for this group
                $alreadyCounted = ErrorEvent::query()
                    ->where('group_id', $group->id)
                    ->where('user_id', $context->userId())
                    ->exists();
                if (! $alreadyCounted) {
                    $group->affected_users_count++;
                }
            }

            $group->save();

            // Storm control: at most 20 events per hour
            $recentEventsCount = ErrorEvent::query()
                ->where('group_id', $group->id)
                ->where('occurred_at', '>=', $now->copy()->subHour())
                ->count();

            $shouldStoreEvent = ($recentEventsCount < self::MAX_EVENTS_PER_HOUR);
        }

        if (! $shouldStoreEvent) {
            return;
        }

        // 2. Prepare event data
        $ip = $request?->ip() ?? $context?->ip();
        $ipHash = $ip ? hash_hmac('sha256', $ip, (string) config('app.key', 'candycutz_salt')) : null;

        $sanitisedContext = $this->extractAndSanitiseContext($e, $request);
        $truncatedTrace = $this->formatCollapsedTrace($e);

        $event = new ErrorEvent([
            'group_id' => $group->id,
            'request_id' => $requestId,
            'occurred_at' => $now,
            'http_status' => $resolvedCode->status(),
            'method' => $request?->method() ?? $context?->method(),
            'route_uri' => $routeUri,
            'url_redacted' => $this->redactUrl($request?->fullUrl()),
            'user_id' => $context?->userId() ?? $request?->user()?->id,
            'role' => $context?->role() ?? ($request?->user()?->role instanceof \BackedEnum ? $request->user()->role->value : (string) ($request?->user()?->role ?? '')),
            'client' => $context?->client(),
            'app_version' => $context?->appVersion(),
            'device_label' => $context?->deviceId(),
            'ip_hash' => $ipHash,
            'user_agent' => $request?->userAgent(),
            'exception_class' => $exceptionClass,
            'message' => $e->getMessage() ?: $resolvedCode->userMessage(),
            'file' => $normFile,
            'line' => $line,
            'trace' => $truncatedTrace,
            'context' => $sanitisedContext,
            'previous' => $e->getPrevious() ? get_class($e->getPrevious()).': '.$e->getPrevious()->getMessage() : null,
            'duration_ms' => $context?->durationMs(),
            'memory_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
        ]);

        $event->save();
    }

    private function resolveErrorCode(Throwable $e): ErrorCode
    {
        if ($e instanceof AppException) {
            return $e->getErrorCode();
        }

        if ($e instanceof QueryException) {
            $state = $e->getCode();
            return match ($state) {
                '2002', '2006', 'HY000' => ErrorCode::DB_UNAVAILABLE,
                '40001', '1213' => ErrorCode::DB_DEADLOCK,
                '23000', '1062', '1452' => ErrorCode::DB_CONSTRAINT_VIOLATION,
                '42S22', '42S02' => ErrorCode::SCHEMA_DRIFT,
                default => ErrorCode::DB_UNAVAILABLE,
            };
        }

        return ErrorCode::SERVER_ERROR;
    }

    private function normalisePath(string $file): string
    {
        $base = base_path();
        return str_replace([$base.DIRECTORY_SEPARATOR, $base.'/'], '', $file);
    }

    private function normaliseMessage(string $message): string
    {
        // Strip UUIDs
        $msg = preg_replace('/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/i', '<uuid>', $message) ?? $message;
        // Strip standalone numeric IDs
        return preg_replace('/\b\d+\b/', '<num>', $msg) ?? $msg;
    }

    private function redactUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        return preg_replace_callback('/([?&])([^=]+)=([^&]*)/', function ($matches) {
            if (preg_match(self::SENSITIVE_REGEX, $matches[2])) {
                return $matches[1].$matches[2].'=[REDACTED]';
            }
            return $matches[0];
        }, $url);
    }

    /**
     * @return array<string, mixed>
     */
    public function redactData(mixed $data): mixed
    {
        if (is_array($data)) {
            $redacted = [];
            foreach ($data as $key => $value) {
                if (is_string($key) && preg_match(self::SENSITIVE_REGEX, $key)) {
                    $redacted[$key] = '[REDACTED]';
                } else {
                    $redacted[$key] = $this->redactData($value);
                }
            }
            return $redacted;
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function extractAndSanitiseContext(Throwable $e, ?Request $request): array
    {
        $context = [];

        if ($e instanceof AppException) {
            $context['custom'] = $this->redactData($e->getCustomContext());
            $context['details'] = $this->redactData($e->getDetails());
        }

        if ($request) {
            // Request payload sanitized
            $allInput = $request->all();
            foreach ($request->allFiles() as $key => $file) {
                if ($file instanceof UploadedFile) {
                    $allInput[$key] = [
                        'filename' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                        'mime' => $file->getMimeType(),
                    ];
                }
            }

            $sanitisedInput = $this->redactData($allInput);
            $jsonInput = json_encode($sanitisedInput);
            if ($jsonInput && strlen($jsonInput) > self::MAX_BODY_BYTES) {
                $sanitisedInput = ['keys' => array_keys($allInput), 'truncated' => true];
            }
            $context['request_input'] = $sanitisedInput;
            $context['headers'] = $this->redactData($request->headers->all());
        }

        return $context;
    }

    private function formatCollapsedTrace(Throwable $e): string
    {
        $frames = [];
        $base = base_path();

        foreach ($e->getTrace() as $index => $frame) {
            $file = isset($frame['file']) ? str_replace([$base.DIRECTORY_SEPARATOR, $base.'/'], '', $frame['file']) : '[internal]';
            $line = $frame['line'] ?? '?';
            $call = ($frame['class'] ?? '').($frame['type'] ?? '').($frame['function'] ?? '');

            // Collapse vendor frames into brief summaries
            if (str_starts_with($file, 'vendor/')) {
                $frames[] = "#{$index} [vendor] {$call}";
            } else {
                $frames[] = "#{$index} {$file}:{$line} {$call}";
            }
        }

        $fullTrace = implode("\n", $frames);
        if (strlen($fullTrace) > self::MAX_TRACE_BYTES) {
            return substr($fullTrace, 0, self::MAX_TRACE_BYTES)."\n... [trace truncated]";
        }

        return $fullTrace;
    }

    private function writeFallbackLog(Throwable $e, ?Request $request, ?ErrorCode $errorCode, string $cause): void
    {
        $date = Carbon::now()->format('Y-m-d');
        $filePath = storage_path("logs/error-fallback-{$date}.log");

        $payload = [
            'timestamp' => Carbon::now()->toIso8601String(),
            'fallback_reason' => $cause,
            'error_code' => $errorCode?->value ?? ($e instanceof AppException ? $e->getErrorCode()->value : 'SERVER_ERROR'),
            'exception_class' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $this->normalisePath($e->getFile()),
            'line' => $e->getLine(),
            'request_id' => app()->bound(RequestContext::class) ? app(RequestContext::class)->requestId() : RequestContext::generateRequestId(),
            'route_uri' => $request?->route()?->uri() ?? $request?->path() ?? 'console',
        ];

        File::append($filePath, json_encode($payload)."\n");
    }
}
