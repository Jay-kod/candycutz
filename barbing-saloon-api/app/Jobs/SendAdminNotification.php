<?php

namespace App\Jobs;

use App\Domain\Shared\Enums\UserRole;
use App\Mail\AdminNotification;
use App\Models\Appointment;
use App\Models\User;
use App\Support\Errors\ErrorReporter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
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

        if (! empty($adminEmails)) {
            try {
                Mail::to($adminEmails)
                    ->send(new AdminNotification($this->appointment, $this->action));
            } catch (\Throwable $e) {
                Log::error('Failed sending admin notification email', [
                    'recipients' => $adminEmails,
                    'mailable' => AdminNotification::class,
                    'appointment_id' => $this->appointment->id,
                    'action' => $this->action,
                    'error' => $e->getMessage(),
                ]);

                try {
                    app(ErrorReporter::class)->report($e);
                } catch (\Throwable) {
                    // Safeguard against reporter failure
                }

                throw $e;
            }
        }
    }
}
