@extends('mail.layouts.candycutz-luxury')

@section('title', 'CandyCutz Admin — Booking Alert')

@section('content')
    @php
        $act = $action ?? 'update';
        $badgeClass = match($act) {
            'cancellation' => 'pill-danger',
            'completed' => 'pill-success',
            'no_show' => 'pill-warning',
            default => '',
        };
        $badgeText = match($act) {
            'new_booking' => '📅 New Booking',
            'cancellation' => '❌ Booking Cancelled',
            'completed' => '✓ Booking Completed',
            'no_show' => '⚠️ No Show',
            default => '🔔 Booking Update',
        };
    @endphp

    <div class="summary-pill {{ $badgeClass }}">{{ $badgeText }}</div>
    <h2 class="headline">Admin Booking Notification</h2>
    <p class="body-text">
        A customer booking action requires administrator attention:
    </p>

    <div class="details-box">
        <table class="details-table" role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td class="lbl" align="left">Customer</td>
                <td class="val" align="right">{{ $customerName }} ({{ $customerEmail }})</td>
            </tr>
            <tr>
                <td class="lbl" align="left">Date &amp; Time</td>
                <td class="val" align="right">{{ $appointmentDate }} at {{ $appointmentTime }}</td>
            </tr>
            <tr>
                <td class="lbl" align="left">Service</td>
                <td class="val" align="right">{{ $serviceName }}</td>
            </tr>
            <tr>
                <td class="lbl" align="left">Barber</td>
                <td class="val" align="right">{{ $barberName }}</td>
            </tr>
        </table>
    </div>

    <p class="body-text" style="color: #9CA3AF; font-size: 12px;">
        Log in to the admin console to inspect schedule slots, view payment details, or manage customer assignments.
    </p>

    <div style="text-align: center; margin-top: 24px;">
        <a href="{{ config('app.url') }}/admin/appointments" class="btn-gold">View in Admin Panel</a>
    </div>
@endsection
