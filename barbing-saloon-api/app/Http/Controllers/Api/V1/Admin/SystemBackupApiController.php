<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Responses\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class SystemBackupApiController
{
    private function getBackupDirectory(): string
    {
        $dir = storage_path('app/backups');
        if (! File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        return $dir;
    }

    public function index(): JsonResponse
    {
        $dir = $this->getBackupDirectory();
        $files = File::files($dir);

        $backups = [];
        foreach ($files as $file) {
            $filename = $file->getFilename();
            // Skip hidden or temporary files
            if (str_starts_with($filename, '.')) {
                continue;
            }

            $bytes = $file->getSize();
            $ext = strtolower($file->getExtension());

            $backups[] = [
                'filename' => $filename,
                'size_bytes' => $bytes,
                'size_formatted' => $this->formatBytes($bytes),
                'created_at' => Carbon::createFromTimestamp($file->getMTime())->toIso8601String(),
                'extension' => $ext,
                'is_valid' => in_array($ext, ['sql', 'sqlite', 'gz'], true),
            ];
        }

        // Sort latest first
        usort($backups, fn ($a, $b) => strcmp($b['created_at'], $a['created_at']));

        return ApiResponse::success([
            'backups' => $backups,
            'total_count' => count($backups),
            'storage_directory' => 'storage/app/backups',
        ], 'System backups retrieved successfully');
    }

    public function create(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'filename' => 'nullable|string|max:120',
        ]);

        $dir = $this->getBackupDirectory();
        $driver = DB::connection()->getDriverName();
        $extension = match ($driver) {
            'mysql' => 'sql',
            'sqlite' => 'sqlite',
            default => null,
        };

        if ($extension === null) {
            return ApiResponse::error("Database driver [{$driver}] is not supported for backups.", [], 422, 'BACKUP_UNSUPPORTED_DRIVER');
        }

        $customName = trim((string) ($validated['filename'] ?? ''));
        if ($customName !== '') {
            $stem = pathinfo($customName, PATHINFO_FILENAME);
            $sanitisedStem = trim((string) preg_replace('/[^a-zA-Z0-9_-]+/', '_', $stem), '_-');
            if ($sanitisedStem === '') {
                return ApiResponse::error('Enter a filename containing letters or numbers.', [], 422, 'VALIDATION_FAILED');
            }
            $filename = $sanitisedStem.'.'.$extension;
        } else {
            $filename = 'candycutz_backup_'.now()->format('Y-m-d_His_u').'.'.$extension;
        }

        $destination = $dir.DIRECTORY_SEPARATOR.$filename;
        if (File::exists($destination)) {
            return ApiResponse::error('A backup already exists with that filename. Choose a different name.', [], 409, 'BACKUP_ALREADY_EXISTS');
        }

        try {
            $exitCode = Artisan::call('db:backup', [
                '--filename' => $filename,
                '--directory' => $dir,
            ]);
            $output = Artisan::output();

            if ($exitCode !== 0 || ! File::exists($destination) || File::size($destination) === 0) {
                $message = trim($output) ?: 'The backup command did not produce a valid file.';
                Log::error('Database snapshot generation failed.', [
                    'connection' => $driver,
                    'filename' => $filename,
                    'command_output' => $message,
                ]);

                return ApiResponse::error('Database backup failed: '.$message, [], 500, 'BACKUP_FAILED');
            }

            return ApiResponse::success([
                'filename' => $filename,
                'size_bytes' => File::size($destination),
                'size_formatted' => $this->formatBytes(File::size($destination)),
                'created_at' => Carbon::createFromTimestamp(File::lastModified($destination))->toIso8601String(),
                'output' => trim($output),
            ], 'Database snapshot generated successfully');
        } catch (Throwable $e) {
            Log::error('Database snapshot generation threw an exception.', [
                'connection' => $driver,
                'filename' => $filename,
                'exception' => $e,
            ]);

            return ApiResponse::error('Failed generating backup: '.$e->getMessage(), [], 500, 'BACKUP_FAILED');
        }
    }

    public function restore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'filename' => 'required|string',
            'confirm' => 'required|accepted',
        ]);

        $safeFilename = basename($validated['filename']);
        $dir = $this->getBackupDirectory();
        $filePath = $dir.DIRECTORY_SEPARATOR.$safeFilename;

        if (! File::exists($filePath)) {
            return ApiResponse::error("Backup file not found: {$safeFilename}", [], 404, 'RESOURCE_NOT_FOUND');
        }

        try {
            $exitCode = Artisan::call('db:restore', [
                'file' => $filePath,
            ]);
            $output = Artisan::output();

            if ($exitCode !== 0) {
                return ApiResponse::error('Database restoration failed: '.$output, [], 500, 'RESTORE_FAILED');
            }

            return ApiResponse::success([
                'restored_file' => $safeFilename,
                'restored_at' => now()->toIso8601String(),
                'output' => trim($output),
            ], 'Database restored successfully from backup snapshot');
        } catch (Throwable $e) {
            return ApiResponse::error('Database restore error: '.$e->getMessage(), [], 500, 'RESTORE_FAILED');
        }
    }

    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'backup_file' => 'required|file|max:102400', // 100MB max
        ]);

        $uploadedFile = $request->file('backup_file');
        $origName = $uploadedFile->getClientOriginalName();
        $safeName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $origName);

        $ext = strtolower($uploadedFile->getClientOriginalExtension());
        if (! in_array($ext, ['sql', 'sqlite', 'gz'], true)) {
            return ApiResponse::error('Invalid backup format. Only .sql, .sqlite, and .gz are allowed.', [], 422, 'UPLOAD_INVALID');
        }

        $dir = $this->getBackupDirectory();
        $finalFilename = 'upload_'.date('Y-m-d_His').'_'.$safeName;
        $uploadedFile->move($dir, $finalFilename);

        $savedPath = $dir.DIRECTORY_SEPARATOR.$finalFilename;

        return ApiResponse::success([
            'filename' => $finalFilename,
            'size_bytes' => File::size($savedPath),
            'size_formatted' => $this->formatBytes(File::size($savedPath)),
            'created_at' => now()->toIso8601String(),
        ], 'Backup file uploaded and registered successfully');
    }

    public function download(string $filename): StreamedResponse|BinaryFileResponse|JsonResponse
    {
        $safeFilename = basename($filename);
        $dir = $this->getBackupDirectory();
        $filePath = $dir.DIRECTORY_SEPARATOR.$safeFilename;

        if (! File::exists($filePath)) {
            return ApiResponse::error("Backup file not found: {$safeFilename}", [], 404, 'RESOURCE_NOT_FOUND');
        }

        return response()->streamDownload(function () use ($filePath) {
            $stream = fopen($filePath, 'rb');
            if ($stream !== false) {
                fpassthru($stream);
                fclose($stream);
            }
        }, $safeFilename, [
            'Content-Type' => 'application/octet-stream',
            'Content-Length' => (string) File::size($filePath),
        ]);
    }

    public function destroy(string $filename): JsonResponse
    {
        $safeFilename = basename($filename);
        $dir = $this->getBackupDirectory();
        $filePath = $dir.DIRECTORY_SEPARATOR.$safeFilename;

        if (! File::exists($filePath)) {
            return ApiResponse::error("Backup file not found: {$safeFilename}", [], 404, 'RESOURCE_NOT_FOUND');
        }

        File::delete($filePath);

        return ApiResponse::success([
            'deleted_file' => $safeFilename,
        ], 'Backup file deleted successfully');
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' B';
        }
        if ($bytes < 1048576) {
            return round($bytes / 1024, 2).' KB';
        }
        if ($bytes < 1073741824) {
            return round($bytes / 1048576, 2).' MB';
        }

        return round($bytes / 1073741824, 2).' GB';
    }
}
