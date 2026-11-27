@extends('mail.layouts.candycutz-luxury')

@section('title', 'Booking Confirmed — CandyCutz')

@section('content')
    @php
        $cName = $customer_name ?? $customerName ?? 'Valued Customer';
        $ref = $booking_reference ?? ($appointment->booking_reference ?? 'N/A');
        $date = $appointment_date ?? $appointmentDate ?? 'Scheduled Date';
        $time = $start_time ?? $appointmentTime ?? 'Scheduled Time';
        $service = $appointment_type ?? $serviceName ?? 'Grooming Service';
        $barber = $barber_name ?? $barberName ?? 'Master Stylist';
        $total = isset($grand_total) ? $grand_total : (isset($appointment->grand_total) ? number_format((float)$appointment->grand_total, 2) : null);
        $address = $shop_address ?? 'Angwan Kare, BCG, Beside Angwan Kare BCG Gas Station, beside Wealths Khort Apartments, BCG, Keffi 961101, Nasarawa, Nigeria';
    @endphp

    <div class="summary-pill">✓ Appointment Confirmed</div>
    <h2 class="headline">Hello {{ $cName }} {{ $customer_username ?? '' }},</h2>
    <p class="body-text">
        Your booking has been secured. Your master stylist is scheduled and ready to provide an exceptional grooming experience.
    </p>

    <div class="details-box">
        <table class="details-table" role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td class="lbl" align="left">Booking Reference</td>
                <td class="val" align="right" style="letter-spacing: 1px; color: #D4AF37; font-family: monospace;">{{ $ref }}</td>
            </tr>
            <tr>
                <td class="lbl" align="left">Date &amp; Time</td>
                <td class="val" align="right">{{ $date }} at {{ $time }}</td>
            </tr>
            <tr>
                <td class="lbl" align="left">Service Type</td>
                <td class="val" align="right">{{ $service }}</td>
            </tr>
            <tr>
                <td class="lbl" align="left">Stylist / Barber</td>
                <td class="val" align="right">{{ $barber }}</td>
            </tr>
            @if($total)
                <tr>
                    <td class="total-lbl" align="left">Total Payable</td>
                    <td class="total-val" align="right">&#8358;{{ $total }}</td>
                </tr>
            @endif
        </table>
    </div>

    <div class="location-box">
        <span class="location-title">Physical Branch Location:</span>
        {{ $address }}<br>
        <a href="https://maps.app.goo.gl/RtpPCeBRobajKmwS7" target="_blank" style="color: #D4AF37; text-decoration: underline; display: inline-block; margin-top: 6px;">Open in Google Maps &rarr;</a>
    </div>

    <div style="text-align: center; margin-top: 24px;">
        <a href="{{ config('app.url') }}/dashboard" class="btn-gold">View in Dashboard</a>
    </div>
@endsection
