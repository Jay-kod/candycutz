@extends('mail.layouts.candycutz-luxury')

@section('title', 'Appointment Reminder — CandyCutz')

@section('preheader')
Your CandyCutz session starts in {{ $minutesBefore ?? 30 }} minutes. Booking Code: {{ $bookingCode ?? ($appointment->verification_code ?? '') }}.
@endsection

@section('content')
    @php
        $cName = $customerName ?? $customer_name ?? 'Valued Customer';
        $code = $bookingCode ?? $booking_reference ?? ($appointment->verification_code ?? 'N/A');
        $date = $appointmentDate ?? $appointment_date ?? now()->format('M d, Y');
        $time = $appointmentTime ?? $start_time ?? 'Scheduled Time';
        $service = $serviceName ?? $appointment_type ?? 'Grooming Service';
        $barber = $barberName ?? $barber_name ?? 'Master Stylist';
        $mins = (int) ($minutesBefore ?? 30);
        $modeLabel = $appointmentTypeLabel ?? 'In-Shop · Keffi Main Hub';
        $frontendBase = rtrim((string) config('app.frontend_url', config('app.url', 'http://localhost:5173')), '/');
        $bookingsLink = $bookingsUrl ?? ($frontendBase . '/customer/dashboard/bookings');
        $codesLink = $codesUrl ?? ($frontendBase . '/customer/dashboard/my-codes');
    @endphp

    <div class="summary-pill pill-warning">⏰ Starts in {{ $mins }} Minutes</div>
    <h2 class="headline">Hello {{ $cName }},</h2>
    <p class="body-text">
        Your CandyCutz grooming session with <strong style="color: #FFFFFF;">{{ $barber }}</strong> begins in approximately <strong style="color: #FBBF24;">{{ $mins }} minutes</strong> at <strong style="color: #FFFFFF;">{{ $time }} (WAT)</strong>. Your chair is being prepared for your arrival.
    </p>

    <!-- Prominent Booking Code Box -->
    <div class="booking-code-card" style="background-color: #171612; border: 1px solid #8E7125; border-radius: 14px; padding: 18px; text-align: center; margin: 18px 0;">
        <p class="booking-code-label" style="color: #E6C15A; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; margin: 0 0 8px;">
            Your Booking Verification Code
        </p>
        <div class="booking-code-value" style="display: inline-block; background-color: #0B0D12; border: 1px dashed rgba(230, 193, 90, 0.65); border-radius: 10px; padding: 9px 20px; font-family: 'Courier New', Courier, monospace; font-size: 24px; font-weight: 800; letter-spacing: 5px; color: #F7D774;">
            {{ $code }}
        </div>
        <p class="booking-code-hint" style="color: #94A3B8; font-size: 11px; line-height: 1.5; margin: 10px 0 0;">
            Keep this code handy to present to your barber upon session completion.
        </p>
    </div>

    <div class="details-box" style="background-color: #151822; border-radius: 12px; border: 1px solid #242838; border-left: 4px solid #F59E0B; padding: 16px 18px; margin: 18px 0;">
        <table class="details-table" role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td class="lbl" align="left">Scheduled Time</td>
                <td class="val" align="right">{{ $date }} · {{ $time }} (WAT)</td>
            </tr>
            <tr>
                <td class="lbl" align="left">Service</td>
                <td class="val" align="right">{{ $service }}</td>
            </tr>
            <tr>
                <td class="lbl" align="left">Master Barber</td>
                <td class="val" align="right">{{ $barber }}</td>
            </tr>
            <tr>
                <td class="lbl" align="left">Session Type</td>
                <td class="val" align="right">{{ $modeLabel }}</td>
            </tr>
        </table>
    </div>

    <div class="location-box" style="background-color: #141720; border-radius: 10px; padding: 14px 16px; font-size: 12px; color: #94A3B8; line-height: 1.6; border: 1px solid #242838; margin-top: 18px;">
        <span class="location-title" style="color: #E6C15A; font-weight: 700; margin-bottom: 4px; display: block;">Saloon Location</span>
        Angwan Kare, BCG, Beside Angwan Kare BCG Gas Station, beside Wealths Khort Apartments, Keffi 961101, Nasarawa State, Nigeria.<br />
        <a href="https://maps.app.goo.gl/RtpPCeBRobajKmwS7" target="_blank" style="color: #E6C15A; text-decoration: underline; display: inline-block; margin-top: 6px;">Open in Google Maps &rarr;</a>
    </div>

    <div style="text-align: center; margin-top: 22px;">
        <a href="{{ $bookingsLink }}" class="btn-gold" style="display: inline-block; background-color: #D4AF37; color: #090A0E !important; font-weight: 700; font-size: 13px; padding: 12px 26px; border-radius: 10px; text-decoration: none; margin: 4px;">
            View My Booking
        </a>
        <a href="{{ $codesLink }}" class="btn-outline" style="display: inline-block; background-color: #181B26; border: 1px solid #353B50; color: #E2E8F0 !important; font-weight: 600; font-size: 12px; padding: 11px 20px; border-radius: 10px; text-decoration: none; margin: 4px;">
            View Booking Code
        </a>
    </div>
@endsection
