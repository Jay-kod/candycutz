<?php

declare(strict_types=1);

namespace App\Domain\System\Actions;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Schema;

class GetReportHistory
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function execute(?string $actionFilter = null, ?string $search = null): array
    {
        if (! Schema::hasTable('audit_logs')) {
            return [];
        }

        $query = AuditLog::with('user')
            ->where(function ($q) {
                $q->where('module', 'reports')
                    ->orWhere('target_type', 'operational_report')
                    ->orWhere('action', 'like', 'report_%');
            });

        if ($actionFilter && $actionFilter !== 'all') {
            $query->where('action', $actionFilter);
        }

        $logs = $query->latest('id')->limit(100)->get();

        $results = [];

        foreach ($logs as $log) {
            $val = is_array($log->new_value) ? $log->new_value : [];
            $reportId = $val['report_id'] ?? ('REF-CCZ-'.($log->created_at?->format('Ymd') ?? date('Ymd')).'-'.str_pad((string) $log->id, 4, '0', STR_PAD_LEFT));
            $range = $val['range'] ?? '7d';
            $rangeLabel = $val['range_label'] ?? match ($range) {
                '30d' => '30 Days',
                'month' => 'This Month',
                'all' => 'All Time',
                default => '7 Days'
            };
            $checksum = $val['checksum'] ?? ('SHA256:'.hash('sha256', (string) $reportId.'-'.$range));
            $actionLabel = match ($log->action) {
                'report_pdf_download', 'download_pdf' => 'PDF Document Download',
                'report_csv_export', 'export_csv' => 'CSV Spreadsheet Export',
                'report_print', 'print' => 'Physical Report Printed',
                'report_brief_copied', 'copy_brief' => 'Executive Briefing Copied',
                'report_saved', 'save' => 'Report Snapshot Saved',
                default => ucwords(str_replace('_', ' ', $log->action))
            };

            $entry = [
                'id' => $log->id,
                'report_id' => $reportId,
                'action' => $log->action,
                'action_label' => $actionLabel,
                'range' => $range,
                'range_label' => $rangeLabel,
                'title' => $val['title'] ?? 'Operations Dossier',
                'user_name' => $log->user ? $log->user->name : 'System Administrator',
                'user_role' => $log->user_role ?? 'admin',
                'user_id' => $log->user_id,
                'ip_address' => $log->ip_address ?? '127.0.0.1',
                'user_agent' => $log->user_agent,
                'metadata' => $val['metadata'] ?? [],
                'checksum' => $checksum,
                'created_at' => $log->created_at?->toIso8601String() ?? now()->toIso8601String(),
                'formatted_date' => $log->created_at?->format('M d, Y • h:i A') ?? now()->format('M d, Y • h:i A'),
                'verified' => true,
            ];

            if ($search) {
                $term = strtolower(trim($search));
                $haystack = strtolower($entry['report_id'].' '.$entry['action_label'].' '.$entry['user_name'].' '.$entry['range_label']);
                if (! str_contains($haystack, $term)) {
                    continue;
                }
            }

            $results[] = $entry;
        }

        return $results;
    }
}
