<?php

namespace App\Jobs;

use App\Mail\BookingReminder;
use App\Models\Appointment;
use App\Support\Errors\ErrorReporter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendBookingReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Appointment $appointment;

    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment;
    }

    public function handle(): void
    {
        $email = $this->appointment->customer?->email;
        if (! $email) {
            return;
        }

        try {
            Mail::to($email)
                ->send(new BookingReminder($this->appointment));
        } catch (\Throwable $e) {
            Log::error('Failed sending booking reminder email', [
                'recipient' => $email,
                'mailable' => BookingReminder::class,
                'appointment_id' => $this->appointment->id,
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
