@extends('mail.layouts.candycutz-luxury')

@section('title', ($title ?? 'Notification') . ' — CandyCutz')

@section('preheader', Str::limit(strip_tags($message ?? 'You have a new update from CandyCutz Luxury Grooming.'), 90))

@section('content')
    <div class="summary-pill">
        🔔 Account Notification
    </div>

    <h2 class="headline" style="font-size: 20px; font-weight: 700; color: #FFFFFF; margin-top: 0; margin-bottom: 12px; line-height: 1.4;">
        {{ $title ?? 'Notification' }}
    </h2>

    <div class="body-text" style="color: #D1D5DB; font-size: 14px; line-height: 1.6; margin: 0 0 20px;">
        {!! nl2br(e($message ?? '')) !!}
    </div>

    <div class="details-box" style="background-color: #181822; border-radius: 10px; border-left: 4px solid #D4AF37; padding: 14px 18px; margin: 20px 0;">
        <table class="details-table" role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width: 100%; border-collapse: collapse;">
            <tr>
                <td class="lbl" align="left" style="color: #9CA3AF; font-size: 12px; padding: 6px 0;">Recipient</td>
                <td class="val" align="right" style="color: #FFFFFF; font-weight: 600; font-size: 12px; padding: 6px 0;">{{ $recipient->email ?? $recipient }}</td>
            </tr>
            <tr>
                <td class="lbl" align="left" style="color: #9CA3AF; font-size: 12px; padding: 6px 0;">Date</td>
                <td class="val" align="right" style="color: #FFFFFF; font-weight: 600; font-size: 12px; padding: 6px 0;">{{ now()->format('M d, Y • g:i A') }}</td>
            </tr>
        </table>
    </div>

    <div style="text-align: center; margin-top: 24px;">
        <a href="{{ config('app.frontend_url', config('app.url')) }}/dashboard" class="btn-gold" style="display: inline-block; background: linear-gradient(135deg, #D4AF37 0%, #B89428 100%); background-color: #D4AF37; color: #0A0A0C !important; font-weight: 700; font-size: 13px; padding: 12px 28px; border-radius: 8px; text-decoration: none; text-align: center; box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);">
            Open CandyCutz Portal
        </a>
    </div>
@endsection
