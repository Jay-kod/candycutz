@extends('mail.layouts.candycutz-luxury')

@section('title', 'CandyCutz — Resend Connection Test')

@section('preheader', '✓ Connection verified • CandyCutz transactional email transport is fully operational.')

@section('content')
    <div class="summary-pill pill-success" style="background-color: rgba(16, 185, 129, 0.15); color: #10B981; border: 1px solid rgba(16, 185, 129, 0.4); padding: 5px 16px; border-radius: 9999px; font-size: 11px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; display: inline-block; margin-bottom: 16px;">
        ✓ Resend Connected &amp; Verified
    </div>

    <h2 class="headline" style="font-size: 20px; font-weight: 700; color: #FFFFFF; margin-top: 0; margin-bottom: 12px; line-height: 1.4;">
        Transactional Mailer Online
    </h2>

    <p class="body-text" style="color: #D1D5DB; font-size: 13px; line-height: 1.6; margin: 0 0 16px;">
        Your CandyCutz Resend transactional email integration is operating cleanly and reliably. All appointment bookings, receipts, client reminders, and administrator alerts are configured for high-deliverability dispatch.
    </p>

    <div class="details-box" style="background-color: #181822; border-radius: 10px; border-left: 4px solid #10B981; padding: 16px 18px; margin: 20px 0; border-top: 1px solid #232330; border-right: 1px solid #232330; border-bottom: 1px solid #232330;">
        <table class="details-table" role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width: 100%; border-collapse: collapse;">
            <tr>
                <td class="lbl" align="left" style="color: #9CA3AF; font-weight: 500; font-size: 12px; padding: 8px 0; border-bottom: 1px solid #232330;">Mail Transport</td>
                <td class="val" align="right" style="color: #FFFFFF; font-weight: 600; font-size: 12px; padding: 8px 0; border-bottom: 1px solid #232330; font-family: monospace;">Resend API / HTTPS</td>
            </tr>
            <tr>
                <td class="lbl" align="left" style="color: #9CA3AF; font-weight: 500; font-size: 12px; padding: 8px 0; border-bottom: 1px solid #232330;">Channel Status</td>
                <td class="val" align="right" style="color: #10B981; font-weight: 700; font-size: 12px; padding: 8px 0; border-bottom: 1px solid #232330;">● 100% Operational</td>
            </tr>
            <tr>
                <td class="lbl" align="left" style="color: #9CA3AF; font-weight: 500; font-size: 12px; padding: 8px 0; border-bottom: 1px solid #232330;">Delivered To</td>
                <td class="val" align="right" style="color: #D4AF37; font-weight: 600; font-size: 12px; padding: 8px 0; border-bottom: 1px solid #232330; font-family: monospace;">{{ $recipient }}</td>
            </tr>
            <tr>
                <td class="lbl" align="left" style="color: #9CA3AF; font-weight: 500; font-size: 12px; padding: 8px 0; border-bottom: 1px solid #232330;">Sender Identity</td>
                <td class="val" align="right" style="color: #FFFFFF; font-weight: 600; font-size: 12px; padding: 8px 0; border-bottom: 1px solid #232330;">{{ $fromName }} &lt;{{ $fromAddress }}&gt;</td>
            </tr>
            <tr>
                <td class="lbl" align="left" style="color: #9CA3AF; font-weight: 500; font-size: 12px; padding: 8px 0; border-bottom: 1px solid #232330;">Dispatched At</td>
                <td class="val" align="right" style="color: #FFFFFF; font-weight: 600; font-size: 12px; padding: 8px 0; border-bottom: 1px solid #232330;">{{ $dispatchedAt }}</td>
            </tr>
            <tr>
                <td class="lbl" align="left" style="color: #9CA3AF; font-weight: 500; font-size: 12px; padding: 8px 0;">Environment</td>
                <td class="val" align="right" style="color: #9CA3AF; font-weight: 600; font-size: 12px; padding: 8px 0;">{{ ucfirst($environment) }}</td>
            </tr>
        </table>
    </div>

    <div class="location-box" style="background-color: #15151D; border-radius: 8px; padding: 14px 16px; font-size: 12px; color: #9CA3AF; line-height: 1.6; border: 1px solid #242430; margin-top: 18px;">
        <span class="location-title" style="color: #D4AF37; font-weight: 600; margin-bottom: 4px; display: block;">📍 Physical Saloon Location</span>
        Angwan Kare, BCG, Beside Angwan Kare BCG Gas Station, beside Wealths Khort Apartments, BCG, Keffi 961101, Nasarawa State, Nigeria.<br>
        <a href="https://maps.app.goo.gl/RtpPCeBRobajKmwS7" target="_blank" style="color: #D4AF37; text-decoration: underline; display: inline-block; margin-top: 6px; font-weight: 600;">Open Saloon in Google Maps &rarr;</a>
    </div>

    <p class="body-text" style="font-size: 12px; margin-top: 16px; color: #9CA3AF;">
        This automated test message was generated from your CandyCutz Administration Console to verify external API communication.
    </p>

    <div style="text-align: center; margin-top: 24px;">
        <a href="{{ $adminUrl }}" class="btn-gold" style="display: inline-block; background: linear-gradient(135deg, #D4AF37 0%, #B89428 100%); background-color: #D4AF37; color: #0A0A0C !important; font-weight: 700; font-size: 13px; padding: 12px 28px; border-radius: 8px; text-decoration: none; text-align: center; box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);">
            Open Admin Console
        </a>
    </div>
@endsection
