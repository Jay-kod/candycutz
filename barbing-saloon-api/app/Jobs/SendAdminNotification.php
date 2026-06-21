<?php

namespace App\Jobs;

use App\Core\Enums\UserRole;
use App\Mail\AdminNotification;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendAdminNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Appointment $appointment;
    public string $action;

    public function __construct(Appointment $appointment, string $action = 'new_booking')
    {
        $this->appointment = $appointment;
        $this->action = $action;
    }

    public function handle(): void
    {
        $adminEmails = collect(User::where('role', UserRole::admin)->orWhere('role', UserRole::super_admin)->get())
            ->pluck('email')
            ->toArray();

        if (!empty($adminEmails)) {
            Mail::to($adminEmails)
                ->send(new AdminNotification($this->appointment, $this->action));
        }
    }
}
