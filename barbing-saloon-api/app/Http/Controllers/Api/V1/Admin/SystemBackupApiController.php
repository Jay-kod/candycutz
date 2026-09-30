<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Responses\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
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
        $dir = $this->getBackupDirectory();
        $timestamp = date('Y-m-d_His');
        $customName = $request->input('filename');

        $params = [];
        if ($customName) {
            $sanitised = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', (string) $customName);
            if (! str_ends_with($sanitised, '.sql') && ! str_ends_with($sanitised, '.sqlite')) {
                $sanitised .= '.sql';
            }
            $params['--filename'] = $sanitised;
        }

        try {
            $exitCode = Artisan::call('db:backup', $params);
            $output = Artisan::output();

            if ($exitCode !== 0) {
                return ApiResponse::error('Database backup failed: '.$output, [], 500, 'BACKUP_FAILED');
            }

            // Find the most recently created file
            $files = File::files($dir);
            $latestFile = null;
            $latestMtime = 0;
            foreach ($files as $f) {
                if ($f->getMTime() > $latestMtime) {
                    $latestMtime = $f->getMTime();
                    $latestFile = $f;
                }
            }

            return ApiResponse::success([
                'filename' => $latestFile ? $latestFile->getFilename() : null,
                'size_formatted' => $latestFile ? $this->formatBytes($latestFile->getSize()) : null,
                'created_at' => now()->toIso8601String(),
                'output' => trim($output),
            ], 'Database snapshot generated successfully');
        } catch (Throwable $e) {
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

    public function download(string $filename): BinaryFileResponse|JsonResponse
    {
        $safeFilename = basename($filename);
        $dir = $this->getBackupDirectory();
        $filePath = $dir.DIRECTORY_SEPARATOR.$safeFilename;

        if (! File::exists($filePath)) {
            return ApiResponse::error("Backup file not found: {$safeFilename}", [], 404, 'RESOURCE_NOT_FOUND');
        }

        return response()->download($filePath, $safeFilename);
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
