@extends('mail.layouts.candycutz-luxury')

@section('title', 'Session Complete — CandyCutz')

@section('preheader')
Your CandyCutz session ({{ $bookingCode ?? ($appointment->verification_code ?? '') }}) is complete. Thank you for visiting!
@endsection

@section('content')
    @php
        $cName = $customerName ?? $customer_name ?? 'Valued Customer';
        $code = $bookingCode ?? ($appointment->verification_code ?? 'N/A');
        $service = $serviceName ?? 'Grooming Service';
        $barber = $barberName ?? 'Master Stylist';
        $date = $appointmentDate ?? now()->format('M d, Y');
        $time = $appointmentTime ?? 'Scheduled Time';
        $completedAt = $completedAtFormatted ?? now()->format('M d, Y · g:i A');
        $total = $totalFormatted ?? null;
        $payRef = $paymentReference ?? $code;
        $frontendBase = rtrim((string) config('app.frontend_url', config('app.url', 'http://localhost:5173')), '/');
        $reviewsLink = $reviewsUrl ?? ($frontendBase . '/customer/dashboard/reviews');
        $bookingsLink = $bookingsUrl ?? ($frontendBase . '/customer/dashboard/bookings');
    @endphp

    <div class="summary-pill pill-success">✓ Session Verified &amp; Completed</div>
    <h2 class="headline">Thank You, {{ $cName }}</h2>
    <p class="body-text">
        Your <strong style="color: #FFFFFF;">{{ $service }}</strong> session with <strong style="color: #FFFFFF;">{{ $barber }}</strong> has been verified and completed. We hope you love your fresh look.
    </p>

    <div class="receipt-card" style="background-color: #131620; border-radius: 14px; border: 1px solid #262B3D; padding: 18px; margin: 20px 0;">
        <p style="margin: 0 0 10px; font-size: 10px; font-weight: 700; letter-spacing: 1.6px; text-transform: uppercase; color: #34D399;">
            Session Completion Summary
        </p>
        <table class="details-table" role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td class="lbl" align="left">Booking Code</td>
                <td class="val" align="right" style="font-family: 'Courier New', Courier, monospace; color: #F7D774;">{{ $code }}</td>
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
                <td class="lbl" align="left">Scheduled Slot</td>
                <td class="val" align="right">{{ $date }} · {{ $time }}</td>
            </tr>
            <tr>
                <td class="lbl" align="left">Completed At</td>
                <td class="val" align="right">{{ $completedAt }} WAT</td>
            </tr>
            @if(!empty($total))
                <tr>
                    <td class="lbl" align="left">Receipt Ref</td>
                    <td class="val" align="right" style="font-family: 'Courier New', Courier, monospace;">{{ $payRef }}</td>
                </tr>
                <tr>
                    <td class="total-lbl" align="left">Total Settled</td>
                    <td class="total-val" align="right">&#8358;{{ $total }}</td>
                </tr>
            @endif
        </table>
    </div>

    <p class="body-text" style="color: #94A3B8; font-size: 12px;">
        Your feedback helps us maintain the highest grooming standard in Keffi. Take 20 seconds to rate your barber and share your experience.
    </p>

    <div style="text-align: center; margin-top: 22px;">
        <a href="{{ $reviewsLink }}" class="btn-gold" style="display: inline-block; background-color: #D4AF37; color: #090A0E !important; font-weight: 700; font-size: 13px; padding: 12px 26px; border-radius: 10px; text-decoration: none; margin: 4px;">
            Leave a Review
        </a>
        <a href="{{ $bookingsLink }}" class="btn-outline" style="display: inline-block; background-color: #181B26; border: 1px solid #353B50; color: #E2E8F0 !important; font-weight: 600; font-size: 12px; padding: 11px 20px; border-radius: 10px; text-decoration: none; margin: 4px;">
            View Booking History
        </a>
    </div>
@endsection
