@extends('mail.layouts.candycutz-luxury')

@section('title', 'Appointment Cancelled — CandyCutz')

@section('content')
    @php
        $cName = $customerName ?? $customer_name ?? 'Valued Customer';
        $ref = $booking_reference ?? ($appointment->booking_reference ?? 'N/A');
        $date = $appointmentDate ?? $appointment_date ?? 'Scheduled Date';
        $time = $appointmentTime ?? $start_time ?? 'Scheduled Time';
        $service = $serviceName ?? $appointment_type ?? 'Grooming Service';
        $reasonText = $reason ?? ($appointment->cancellation_reason ?? 'Cancelled by request');
    @endphp

    <div class="summary-pill pill-danger">✕ Appointment Cancelled</div>
    <h2 class="headline">Hello {{ $cName }},</h2>
    <p class="body-text">
        This email confirms that your booking <strong>{{ $ref }}</strong> has been cancelled as requested.
    </p>

    <div class="details-box details-box-danger">
        <table class="details-table" role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td class="lbl" align="left">Booking Reference</td>
                <td class="val" align="right" style="letter-spacing: 1px; color: #EF4444; font-family: monospace;">{{ $ref }}</td>
            </tr>
            <tr>
                <td class="lbl" align="left">Cancelled Slot</td>
                <td class="val" align="right">{{ $date }} at {{ $time }}</td>
            </tr>
            <tr>
                <td class="lbl" align="left">Service</td>
                <td class="val" align="right">{{ $service }}</td>
            </tr>
            <tr>
                <td class="lbl" align="left">Reason</td>
                <td class="val" align="right">{{ $reasonText }}</td>
            </tr>
        </table>
    </div>

    <p class="body-text" style="color: #9CA3AF; font-size: 12px;">
        If you made an online prepayment or deposit, your refund will be processed in accordance with our cancellation policy. If you would like to reschedule for a future date, click below.
    </p>

    <div style="text-align: center; margin-top: 24px;">
        <a href="{{ config('app.url') }}/booking" class="btn-gold">Book New Appointment</a>
    </div>
@endsection
