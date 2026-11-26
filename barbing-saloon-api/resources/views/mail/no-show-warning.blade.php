@extends('mail.layouts.candycutz-luxury')

@section('title', 'No-Show Warning — CandyCutz')

@section('preheader')
Your CandyCutz appointment ({{ $bookingCode ?? '' }}) will be marked as a no-show in {{ $minutesRemaining ?? 5 }} minutes unless you check in or extend your grace period.
@endsection

@section('content')
    @php
        $cName = $customerName ?? 'Valued Customer';
        $code = $bookingCode ?? ($appointment->verification_code ?? 'N/A');
        $date = $appointmentDate ?? now()->format('M d, Y');
        $time = $appointmentTime ?? 'Scheduled Time';
        $service = $serviceName ?? 'Grooming Service';
        $barber = $barberName ?? 'Master Stylist';
        $mins = (int) ($minutesRemaining ?? 5);
        $extendable = (bool) ($canExtend ?? true);
        $frontendBase = rtrim((string) config('app.frontend_url', config('app.url', 'http://localhost:5173')), '/');
        $bookingsLink = $bookingsUrl ?? ($frontendBase . '/customer/dashboard/bookings');
    @endphp

    <div class="summary-pill pill-warning">⚠️ Urgent Arrival Warning · {{ $mins }} Min Left</div>
    <h2 class="headline">Hello {{ $cName }},</h2>
    <p class="body-text">
        Your appointment for <strong style="color: #FFFFFF;">{{ $service }}</strong> with <strong style="color: #FFFFFF;">{{ $barber }}</strong> was scheduled for <strong style="color: #FBBF24;">{{ $time }} (WAT)</strong>. You have approximately <strong style="color: #FBBF24;">{{ $mins }} minute(s)</strong> remaining before your booking is automatically marked as a <strong style="color: #F87171;">No-Show</strong>.
    </p>

    <div class="details-box details-box-danger" style="background-color: #151822; border-radius: 12px; border: 1px solid #242838; border-left: 4px solid #EF4444; padding: 16px 18px; margin: 18px 0;">
        <table class="details-table" role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td class="lbl" align="left">Booking Reference</td>
                <td class="val" align="right" style="font-family: monospace; color: #FBBF24;">{{ $code }}</td>
            </tr>
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
        </table>
    </div>

    @if($extendable)
        <p class="body-text" style="color: #E2E8F0;">
            Running late due to traffic or an urgent delay? You can request a <strong style="color: #FBBF24;">one-time grace period extension</strong> with a valid reason directly from your bookings dashboard before the deadline expires.
        </p>
    @else
        <p class="body-text" style="color: #9CA3AF; font-size: 13px;">
            Please arrive immediately and check in with your barber to avoid a no-show record and potential deposit forfeiture.
        </p>
    @endif

    <div style="text-align: center; margin-top: 22px;">
        <a href="{{ $bookingsLink }}" class="btn-gold" style="display: inline-block; background-color: #D4AF37; color: #090A0E !important; font-weight: 700; font-size: 13px; padding: 12px 26px; border-radius: 10px; text-decoration: none; margin: 4px;">
            {{ $extendable ? 'Extend Grace Period / View Booking' : 'View My Booking' }}
        </a>
    </div>
@endsection
