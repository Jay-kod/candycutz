@extends('mail.layouts.candycutz-luxury')

@section('title', 'Booking Cancelled — CandyCutz')

@section('content')
    @php
        $cName = $customer_name ?? $customerName ?? 'Valued Customer';
        $ref = $booking_reference ?? ($appointment->booking_reference ?? 'N/A');
        $reasonText = $reason ?? ($appointment->cancellation_reason ?? 'Cancelled by request');
    @endphp

    <div class="summary-pill pill-danger">✕ Appointment Cancelled</div>
    <h2 class="headline">Hello {{ $cName }},</h2>
    <p class="body-text">
        This email confirms that appointment <strong>{{ $ref }}</strong> has been cancelled.
    </p>

    <div class="details-box details-box-danger">
        <div class="row">
            <span class="lbl">Reference</span>
            <span class="val" style="letter-spacing: 1px; color: #EF4444;">{{ $ref }}</span>
        </div>
        <div class="row">
            <span class="lbl">Reason</span>
            <span class="val">{{ $reasonText }}</span>
        </div>
    </div>

    <p class="body-text" style="color: #9CA3AF; font-size: 12px;">
        If you made an online deposit or prepayment, your refund will be processed in accordance with our cancellation policy. If you would like to reschedule for a future time slot, please tap below.
    </p>

    <a href="{{ config('app.url') }}/booking" class="btn-gold">Book New Appointment</a>
@endsection
