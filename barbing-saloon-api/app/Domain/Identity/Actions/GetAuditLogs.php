<?php

namespace App\Domain\Identity\Actions;

use App\Models\Appointment;
use App\Models\Barber;
use App\Models\BlogPost;
use App\Models\Gallery;
use App\Models\Holiday;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Setting;
use App\Models\Testimonial;
use App\Models\WorkingHour;
use App\Models\User;
use App\Core\Enums\AppointmentStatus;
use App\Core\Enums\BlogStatus;
use App\Core\Enums\GalleryCategory;
use Illuminate\Http\UploadedFile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class GetAuditLogs
{

    public function execute(): array
    {
        if (\Illuminate\Support\Facades\Schema::hasTable('audit_logs')) {
            $logs = \Illuminate\Support\Facades\DB::table('audit_logs')
                ->leftJoin('users', 'audit_logs.user_id', '=', 'users.id')
                ->select(
                    'audit_logs.id',
                    'audit_logs.action',
                    \Illuminate\Support\Facades\DB::raw("COALESCE(audit_logs.target_type, audit_logs.module, 'system') as entity_type"),
                    'audit_logs.ip_address',
                    'audit_logs.created_at',
                    'users.name as user_name'
                )
                ->latest('audit_logs.id')
                ->limit(100)
                ->get();

            if ($logs->isNotEmpty()) {
                return $logs->all();
            }
        }

        $recentAppts = Appointment::with(['customer', 'service', 'barber.user'])->latest()->limit(25)->get();

        return $recentAppts->map(fn ($a) => [
            'id' => $a->id,
            'user_name' => $a->customer?->name ?? 'System',
            'action' => 'booking_' . ($a->status instanceof \App\Core\Enums\AppointmentStatus ? $a->status->value : $a->status),
            'entity_type' => 'appointment',
            'ip_address' => '127.0.0.1',
            'created_at' => $a->created_at?->toIso8601String() ?? now()->toIso8601String(),
        ])->all();
    }
}
