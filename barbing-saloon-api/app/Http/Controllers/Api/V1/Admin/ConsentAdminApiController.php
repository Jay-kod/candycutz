<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domain\Identity\Actions\GetConsentReport;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ConsentAdminApiController
{
    public function index(Request $request, GetConsentReport $action): JsonResponse
    {
        $filters = $request->validate([
            'status' => ['nullable', 'in:all,accepted,pending'],
            'search' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        return ApiResponse::success($action->execute($filters), 'Consent report loaded');
    }

    public function export(Request $request, GetConsentReport $action): StreamedResponse
    {
        $filters = $request->validate([
            'status' => ['nullable', 'in:all,accepted,pending'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $rows = $action->export($filters);
        $filename = 'consent-report-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            fputcsv($handle, [
                'User ID',
                'Name',
                'Email',
                'Phone',
                'Terms status',
                'Terms version',
                'Accepted at',
                'Source',
                'IP address',
                'User agent',
            ]);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row['id'],
                    $this->csvCell($row['name']),
                    $this->csvCell($row['email']),
                    $this->csvCell($row['phone']),
                    $row['terms_status'],
                    $row['terms_version'],
                    $row['accepted_at'],
                    $row['source'],
                    $row['ip_address'],
                    $this->csvCell($row['user_agent']),
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /**
     * Prevent spreadsheet formula injection in exported evidence.
     */
    private function csvCell(mixed $value): string
    {
        $string = (string) ($value ?? '');

        if ($string !== '' && preg_match('/^[=+\-@\t\r]/', $string) === 1) {
            return "'".$string;
        }

        return $string;
    }
}
