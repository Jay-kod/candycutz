@extends('mail.layouts.candycutz-luxury')

@section('title', ($isPaid ?? false) ? 'Official Payment Receipt & Booking Code — CandyCutz' : 'Booking Confirmed — CandyCutz')

@section('preheader')
Your CandyCutz booking code is {{ $bookingCode ?? ($appointment->verification_code ?? 'CC-BOOKING') }}. Keep this code ready for your session verification.
@endsection

@section('content')
    @php
        $cName = $customerName ?? $customer_name ?? 'Valued Customer';
        $code = $bookingCode ?? $booking_reference ?? ($appointment->verification_code ?? 'N/A');
        $date = $appointmentDate ?? $appointment_date ?? 'Scheduled Date';
        $time = $appointmentTime ?? $start_time ?? 'Scheduled Time';
        $service = $serviceName ?? $appointment_type ?? 'Grooming Service';
        $duration = $serviceDuration ?? 30;
        $barber = $barberName ?? $barber_name ?? 'Master Stylist';
        $modeLabel = $appointmentTypeLabel ?? 'In-Shop · Keffi Main Hub';
        $paid = $isPaid ?? false;
        $total = $totalFormatted ?? (isset($grand_total) ? $grand_total : (isset($appointment->grand_total) ? number_format((float)$appointment->grand_total, 2) : '0.00'));
        $subtotal = $subtotalFormatted ?? $total;
        $receiptNo = $receiptNumber ?? ('RCP-' . str_pad((string) ($appointment->id ?? 1), 6, '0', STR_PAD_LEFT));
        $payRef = $paymentReference ?? $code;
        $payMethod = $paymentMethodLabel ?? 'Online Payment';
        $paidAt = $paidAtFormatted ?? now()->format('M d, Y · g:i A');
        $frontendBase = rtrim((string) config('app.frontend_url', config('app.url', 'http://localhost:5173')), '/');
        $bookingsLink = $bookingsUrl ?? ($frontendBase . '/customer/dashboard/bookings');
        $codesLink = $codesUrl ?? ($frontendBase . '/customer/dashboard/my-codes');
    @endphp

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 14px;">
        <tr>
            <td align="left" valign="middle">
                <span class="summary-pill {{ $paid ? 'pill-success' : '' }}" style="margin-bottom: 0;">
                    {{ $paid ? '✓ Payment Verified · Booking Confirmed' : '✓ Appointment Confirmed' }}
                </span>
            </td>
            <td align="right" valign="middle" style="font-family: 'Courier New', Courier, monospace; font-size: 11px; color: #94A3B8;">
                {{ $receiptNo }}
            </td>
        </tr>
    </table>

    <h2 class="headline">Hello {{ $cName }},</h2>
    <p class="body-text">
        @if($paid)
            Thank you for choosing CandyCutz. Your payment has been verified and your grooming chair is reserved. Below are your <strong style="color: #FFFFFF;">Booking Verification Code</strong> and <strong style="color: #FFFFFF;">Official Payment Receipt</strong>.
        @else
            Your appointment at CandyCutz has been confirmed. Please keep your <strong style="color: #FFFFFF;">Booking Verification Code</strong> below ready for your session.
        @endif
    </p>

    <!-- Prominent Booking & Service Verification Code Passcard -->
    <div class="booking-code-card" style="background-color: #171612; border: 1px solid #8E7125; border-radius: 14px; padding: 20px 18px; text-align: center; margin: 20px 0;">
        <p class="booking-code-label" style="color: #E6C15A; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; margin: 0 0 10px;">
            Your Booking &amp; Service Verification Code
        </p>
        <div class="booking-code-value" style="display: inline-block; background-color: #0B0D12; border: 1px dashed rgba(230, 193, 90, 0.65); border-radius: 10px; padding: 10px 22px; font-family: 'Courier New', Courier, monospace; font-size: 26px; font-weight: 800; letter-spacing: 5px; color: #F7D774;">
            {{ $code }}
        </div>
        <p class="booking-code-hint" style="color: #94A3B8; font-size: 11px; line-height: 1.5; margin: 12px 0 0;">
            Present this code to your barber when your haircut session is complete to verify and close your appointment.
        </p>
    </div>

    <!-- Appointment Details -->
    <div class="details-box" style="background-color: #151822; border-radius: 12px; border: 1px solid #242838; border-left: 4px solid #D4AF37; padding: 16px 18px; margin: 18px 0;">
        <p style="margin: 0 0 8px; font-size: 10px; font-weight: 700; letter-spacing: 1.6px; text-transform: uppercase; color: #E6C15A;">
            Appointment Schedule
        </p>
        <table class="details-table" role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td class="lbl" align="left">Service</td>
                <td class="val" align="right">{{ $service }} ({{ $duration }} mins)</td>
            </tr>
            <tr>
                <td class="lbl" align="left">Master Barber</td>
                <td class="val" align="right">{{ $barber }}</td>
            </tr>
            <tr>
                <td class="lbl" align="left">Date &amp; Time</td>
                <td class="val" align="right">{{ $date }} · {{ $time }} (WAT)</td>
            </tr>
            <tr>
                <td class="lbl" align="left">Session Type</td>
                <td class="val" align="right">{{ $modeLabel }}</td>
            </tr>
        </table>
    </div>

    <!-- Official Payment Receipt Ledger -->
    <div class="receipt-card" style="background-color: #131620; border-radius: 14px; border: 1px solid #262B3D; padding: 18px; margin: 20px 0;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 10px;">
            <tr>
                <td align="left" valign="middle" style="font-size: 10px; font-weight: 700; letter-spacing: 1.6px; text-transform: uppercase; color: #E6C15A;">
                    Official Payment Receipt
                </td>
                <td align="right" valign="middle">
                    <span style="display: inline-block; padding: 3px 10px; border-radius: 9999px; font-size: 10px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; {{ $paid ? 'background-color: rgba(16,185,129,0.16); color: #34D399; border: 1px solid rgba(16,185,129,0.4);' : 'background-color: rgba(245,158,11,0.16); color: #FBBF24; border: 1px solid rgba(245,158,11,0.4);' }}">
                        {{ $paid ? 'PAID' : 'PENDING' }}
                    </span>
                </td>
            </tr>
        </table>

        <table class="details-table" role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td class="lbl" align="left">Receipt Number</td>
                <td class="val" align="right" style="font-family: 'Courier New', Courier, monospace;">{{ $receiptNo }}</td>
            </tr>
            <tr>
                <td class="lbl" align="left">Payment Reference</td>
                <td class="val" align="right" style="font-family: 'Courier New', Courier, monospace;">{{ $payRef }}</td>
            </tr>
            <tr>
                <td class="lbl" align="left">Payment Method</td>
                <td class="val" align="right">{{ $payMethod }}</td>
            </tr>
            <tr>
                <td class="lbl" align="left">Paid On</td>
                <td class="val" align="right">{{ $paidAt }} WAT</td>
            </tr>
            <tr>
                <td class="lbl" align="left">Service Subtotal</td>
                <td class="val" align="right">&#8358;{{ $subtotal }}</td>
            </tr>
            @if(!empty($travelFeeFormatted))
                <tr>
                    <td class="lbl" align="left">Home Service Travel Fee</td>
                    <td class="val" align="right">&#8358;{{ $travelFeeFormatted }}</td>
                </tr>
            @endif
            @if(!empty($discountFormatted))
                <tr>
                    <td class="lbl" align="left">Discount</td>
                    <td class="val" align="right" style="color: #34D399;">-&#8358;{{ $discountFormatted }}</td>
                </tr>
            @endif
            <tr>
                <td class="total-lbl" align="left">{{ $paid ? 'Total Amount Paid' : 'Total Payable' }}</td>
                <td class="total-val" align="right">&#8358;{{ $total }}</td>
            </tr>
        </table>
    </div>

    <div class="location-box" style="background-color: #141720; border-radius: 10px; padding: 14px 16px; font-size: 12px; color: #94A3B8; line-height: 1.6; border: 1px solid #242838; margin-top: 18px;">
        <span class="location-title" style="color: #E6C15A; font-weight: 700; margin-bottom: 4px; display: block;">Physical Saloon Location</span>
        Angwan Kare, BCG, Beside Angwan Kare BCG Gas Station, beside Wealths Khort Apartments, Keffi 961101, Nasarawa State, Nigeria.<br />
        <a href="https://maps.app.goo.gl/RtpPCeBRobajKmwS7" target="_blank" style="color: #E6C15A; text-decoration: underline; display: inline-block; margin-top: 6px;">Open in Google Maps &rarr;</a>
    </div>

    <p class="body-text" style="font-size: 12px; margin-top: 16px; color: #94A3B8;">
        We recommend arriving 5 minutes ahead of your slot. A reminder will be sent 30 minutes before your session begins.
    </p>

    <div style="text-align: center; margin-top: 22px;">
        <a href="{{ $bookingsLink }}" class="btn-gold" style="display: inline-block; background-color: #D4AF37; color: #090A0E !important; font-weight: 700; font-size: 13px; padding: 12px 26px; border-radius: 10px; text-decoration: none; margin: 4px;">
            View Booking &amp; Receipt
        </a>
        <a href="{{ $codesLink }}" class="btn-outline" style="display: inline-block; background-color: #181B26; border: 1px solid #353B50; color: #E2E8F0 !important; font-weight: 600; font-size: 12px; padding: 11px 20px; border-radius: 10px; text-decoration: none; margin: 4px;">
            Open My Passcodes
        </a>
    </div>
@endsection
