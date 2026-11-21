<?php

declare(strict_types=1);

namespace App\Domain\System\Actions;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class LogReportExport
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function execute(?User $user, array $data, Request $request): array
    {
        $action = (string) ($data['action'] ?? 'report_pdf_download');
        $reportId = (string) ($data['report_id'] ?? ('REF-CCZ-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -4))));
        $range = (string) ($data['range'] ?? '7d');
        $rangeLabel = (string) ($data['range_label'] ?? match ($range) {
            '30d' => '30 Days',
            'month' => 'This Month',
            'all' => 'All Time',
            default => '7 Days',
        });
        $title = (string) ($data['title'] ?? 'Operations Dossier');
        $metadata = (array) ($data['metadata'] ?? []);
        $checksum = (string) ($data['checksum'] ?? ('SHA256:'.hash('sha256', $reportId.'-'.$range.'-'.date('Ymd'))));

        $userRole = 'admin';
        if ($user) {
            $roleVal = $user->role;
            $userRole = $roleVal instanceof \BackedEnum ? (string) $roleVal->value : (string) $roleVal;
        }

        $auditLog = AuditLog::create([
            'user_id' => $user?->id,
            'user_role' => $userRole,
            'action' => $action,
            'module' => 'reports',
            'target_type' => 'operational_report',
            'target_id' => null,
            'old_value' => null,
            'new_value' => [
                'report_id' => $reportId,
                'range' => $range,
                'range_label' => $rangeLabel,
                'title' => $title,
                'metadata' => $metadata,
                'checksum' => $checksum,
            ],
            'ip_address' => $request->ip() ?: '127.0.0.1',
            'user_agent' => $request->userAgent() ?: 'CandyCutz Web Management',
        ]);

        $actionLabel = match ($action) {
            'report_pdf_download', 'download_pdf' => 'PDF Document Download',
            'report_csv_export', 'export_csv' => 'CSV Spreadsheet Export',
            'report_print', 'print' => 'Physical Report Printed',
            'report_brief_copied', 'copy_brief' => 'Executive Briefing Copied',
            'report_saved', 'save' => 'Report Snapshot Saved',
            default => ucwords(str_replace('_', ' ', $action)),
        };

        return [
            'id' => $auditLog->id,
            'report_id' => $reportId,
            'action' => $action,
            'action_label' => $actionLabel,
            'range' => $range,
            'range_label' => $rangeLabel,
            'title' => $title,
            'user_name' => $user ? $user->name : 'System Administrator',
            'user_role' => $auditLog->user_role,
            'user_id' => $auditLog->user_id,
            'ip_address' => $auditLog->ip_address,
            'user_agent' => $auditLog->user_agent,
            'metadata' => $metadata,
            'checksum' => $checksum,
            'created_at' => $auditLog->created_at?->toIso8601String() ?? now()->toIso8601String(),
            'formatted_date' => $auditLog->created_at?->format('M d, Y • h:i A') ?? now()->format('M d, Y • h:i A'),
            'verified' => true,
        ];
    }
}
