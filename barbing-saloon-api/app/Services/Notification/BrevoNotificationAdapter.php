<?php

declare(strict_types=1);

namespace App\Services\Notification;

use App\Models\Appointment;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class BrevoNotificationAdapter
{
    /**
     * Send booking confirmation email via Brevo SMTP / API.
     */
    public function sendBookingConfirmation(Appointment $appointment): bool
    {
        $customer = $appointment->customer;
        $barber = $appointment->barber?->user;

        $subject = "Booking Confirmed: {$appointment->booking_reference} — CandyCutz";
        $data = [
            'booking_reference' => $appointment->booking_reference,
            'customer_name' => $customer->real_name ?: $customer->name,
            'customer_username' => $customer->username ? "@{$customer->username}" : '',
            'barber_name' => $barber?->real_name ?: $barber?->name ?: 'Master Stylist',
            'appointment_date' => $appointment->appointment_date,
            'start_time' => $appointment->start_time,
            'appointment_type' => ($appointment->appointment_type === 'home_service') ? 'Home Service' : 'In-Shop',
            'grand_total' => number_format((float) $appointment->grand_total, 2),
            'shop_address' => 'Angwan Kare, BCG, Beside Angwan Kare BCG Gas Station, beside Wealths Khort Apartments, BCG, Keffi 961101, Nasarawa, Nigeria',
        ];

        try {
            // Attempt standard Laravel mailer configured for Brevo
            Mail::send('emails.booking_confirmation', $data, function ($message) use ($customer, $subject) {
                $message->to($customer->email, $customer->real_name ?: $customer->name)
                        ->subject($subject);
            });
            return true;
        } catch (Exception $e) {
            Log::warning("Brevo email dispatch failed for appointment {$appointment->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send cancellation notice.
     */
    public function sendBookingCancellation(Appointment $appointment): bool
    {
        $customer = $appointment->customer;
        $subject = "Appointment Cancelled: {$appointment->booking_reference} — CandyCutz";
        
        $data = [
            'booking_reference' => $appointment->booking_reference,
            'customer_name' => $customer->real_name ?: $customer->name,
            'reason' => $appointment->cancellation_reason ?: 'Cancelled by client request',
        ];

        try {
            Mail::send('emails.booking_cancellation', $data, function ($message) use ($customer, $subject) {
                $message->to($customer->email, $customer->real_name ?: $customer->name)
                        ->subject($subject);
            });
            return true;
        } catch (Exception $e) {
            Log::warning("Brevo cancellation email failed: " . $e->getMessage());
            return false;
        }
    }
}
