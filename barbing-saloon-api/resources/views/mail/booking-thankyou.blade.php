@extends('mail.layouts.candycutz-luxury')

@section('title', 'Thank You for Visiting — CandyCutz')

@section('content')
    @php
        $cName = $customerName ?? $customer_name ?? 'Valued Customer';
        $service = $serviceName ?? 'Grooming Service';
        $barber = $barberName ?? 'Master Stylist';
    @endphp

    <div class="summary-pill pill-success">💈 Visit Completed</div>
    <h2 class="headline">Hello {{ $cName }},</h2>
    <p class="body-text">
        We hope you enjoyed your {{ $service }} with {{ $barber }} at CandyCutz today. Your grooming experience and satisfaction are our top priority.
    </p>

    <div class="details-box">
        <table class="details-table" role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td class="lbl" align="left">Service</td>
                <td class="val" align="right">{{ $service }}</td>
            </tr>
            <tr>
                <td class="lbl" align="left">Stylist</td>
                <td class="val" align="right">{{ $barber }}</td>
            </tr>
        </table>
    </div>

    <p class="body-text" style="color: #9CA3AF; font-size: 12px;">
        Your feedback helps us continuously elevate our craft. We invite you to leave a short review or share your experience with the community.
    </p>

    <div style="text-align: center; margin-top: 24px;">
        <a href="{{ config('app.url') }}/reviews" class="btn-gold">Leave a Review</a>
    </div>
@endsection
