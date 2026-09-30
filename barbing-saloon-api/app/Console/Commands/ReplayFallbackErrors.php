<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ErrorGroup;
use App\Models\ErrorEvent;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ReplayFallbackErrors extends Command
{
    protected $signature = 'errors:replay';
    protected $description = 'Replay fallback error log entries into the error_groups and error_events tables';

    public function handle(): int
    {
        $logDir = storage_path('logs');
        $files = File::glob("{$logDir}/error-fallback-*.log");

        if (empty($files)) {
            $this->info('No fallback error logs found.');
            return self::SUCCESS;
        }

        $replayedCount = 0;

        foreach ($files as $filePath) {
            $this->info("Processing {$filePath}...");
            $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            if (! $lines) {
                File::delete($filePath);
                continue;
            }

            foreach ($lines as $line) {
                $data = json_decode($line, true);
                if (! is_array($data)) {
                    continue;
                }

                $errorCode = $data['error_code'] ?? 'SERVER_ERROR';
                $exceptionClass = $data['exception_class'] ?? 'Exception';
                $file = $data['file'] ?? 'unknown';
                $lineNum = (int) ($data['line'] ?? 0);
                $routeUri = $data['route_uri'] ?? 'console';
                $timestamp = isset($data['timestamp']) ? Carbon::parse($data['timestamp']) : Carbon::now();

                $fingerprint = sha1("{$errorCode}|{$exceptionClass}|{$file}:{$lineNum}|{$routeUri}");

                $group = ErrorGroup::query()->where('fingerprint', $fingerprint)->first();
                if (! $group) {
                    $group = ErrorGroup::create([
                        'fingerprint' => $fingerprint,
                        'error_code' => $errorCode,
                        'category' => 'infra',
                        'severity' => 'error',
                        'exception_class' => $exceptionClass,
                        'sample_message' => $data['message'] ?? 'Fallback error',
                        'source' => 'api',
                        'status' => 'open',
                        'occurrences' => 1,
                        'first_seen_at' => $timestamp,
                        'last_seen_at' => $timestamp,
                        'last_request_id' => $data['request_id'] ?? null,
                    ]);
                } else {
                    $group->increment('occurrences');
                    $group->update([
                        'last_seen_at' => $timestamp,
                        'last_request_id' => $data['request_id'] ?? $group->last_request_id,
                    ]);
                }

                ErrorEvent::create([
                    'group_id' => $group->id,
                    'request_id' => $data['request_id'] ?? 'req_fallback',
                    'occurred_at' => $timestamp,
                    'http_status' => 500,
                    'route_uri' => $routeUri,
                    'exception_class' => $exceptionClass,
                    'message' => $data['message'] ?? '',
                    'file' => $file,
                    'line' => $lineNum,
                    'context' => ['fallback_reason' => $data['fallback_reason'] ?? 'unknown'],
                ]);

                $replayedCount++;
            }

            // Archive or remove the processed log
            File::delete($filePath);
        }

        $this->info("Replayed {$replayedCount} fallback errors successfully.");
        return self::SUCCESS;
    }
}
