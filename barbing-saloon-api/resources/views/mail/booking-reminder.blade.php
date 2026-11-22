@extends('mail.layouts.candycutz-luxury')

@section('title', 'Appointment Reminder — CandyCutz')

@section('content')
    @php
        $cName = $customerName ?? $customer_name ?? 'Valued Customer';
        $ref = $booking_reference ?? ($appointment->booking_reference ?? 'N/A');
        $date = $appointmentDate ?? $appointment_date ?? 'Tomorrow';
        $time = $appointmentTime ?? $start_time ?? 'Scheduled Time';
        $service = $serviceName ?? $appointment_type ?? 'Grooming Service';
        $barber = $barberName ?? $barber_name ?? 'Master Stylist';
    @endphp

    <div class="summary-pill pill-warning">⏰ Appointment Reminder</div>
    <h2 class="headline">Hello {{ $cName }},</h2>
    <p class="body-text">
        This is a friendly reminder that your appointment at CandyCutz is coming up! Your master stylist is looking forward to seeing you.
    </p>

    <div class="details-box">
        <table class="details-table" role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td class="lbl" align="left">Booking Reference</td>
                <td class="val" align="right" style="letter-spacing: 1px; color: #D4AF37; font-family: monospace;">{{ $ref }}</td>
            </tr>
            <tr>
                <td class="lbl" align="left">Scheduled Time</td>
                <td class="val" align="right">{{ $time }} ({{ $date }})</td>
            </tr>
            <tr>
                <td class="lbl" align="left">Service</td>
                <td class="val" align="right">{{ $service }}</td>
            </tr>
            <tr>
                <td class="lbl" align="left">Barber</td>
                <td class="val" align="right">{{ $barber }}</td>
            </tr>
        </table>
    </div>

    <div class="location-box">
        <span class="location-title">Saloon Location:</span>
        Angwan Kare, BCG, Beside Angwan Kare BCG Gas Station, beside Wealths Khort Apartments, BCG, Keffi 961101, Nasarawa State, Nigeria.<br>
        <a href="https://maps.app.goo.gl/RtpPCeBRobajKmwS7" target="_blank" style="color: #D4AF37; text-decoration: underline; display: inline-block; margin-top: 6px;">Open in Google Maps &rarr;</a>
    </div>

    <p class="body-text" style="font-size: 12px; margin-top: 16px; color: #9CA3AF;">
        Please remember to arrive 5 minutes early. If you need to reschedule, please visit your dashboard in advance.
    </p>

    <div style="text-align: center; margin-top: 24px;">
        <a href="{{ config('app.url') }}/dashboard" class="btn-gold">Manage Booking</a>
    </div>
@endsection
