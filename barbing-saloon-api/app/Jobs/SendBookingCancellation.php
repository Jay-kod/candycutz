<?php

namespace App\Jobs;

use App\Mail\BookingCancellation;
use App\Models\Appointment;
use App\Support\Errors\ErrorReporter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendBookingCancellation implements ShouldQueue
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
                ->send(new BookingCancellation($this->appointment));
        } catch (\Throwable $e) {
            Log::error('Failed sending booking cancellation email', [
                'recipient' => $email,
                'mailable' => BookingCancellation::class,
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
