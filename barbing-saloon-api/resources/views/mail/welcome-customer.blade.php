@extends('mail.layouts.candycutz-luxury')

@section('title', 'Welcome to CandyCutz — Luxury Grooming')

@section('preheader', 'Welcome to CandyCutz! Your luxury grooming journey starts here.')

@section('content')
    <div class="summary-pill pill-success" style="background-color: rgba(16, 185, 129, 0.15); color: #10B981; border: 1px solid rgba(16, 185, 129, 0.4); padding: 5px 16px; border-radius: 9999px; font-size: 11px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; display: inline-block; margin-bottom: 16px;">
        ✓ Welcome to the Club
    </div>

    <h2 class="headline" style="font-size: 20px; font-weight: 700; color: #FFFFFF; margin-top: 0; margin-bottom: 12px; line-height: 1.4;">
        Welcome to CandyCutz, {{ $userName }}
    </h2>

    <p class="body-text" style="color: #D1D5DB; font-size: 14px; line-height: 1.6; margin: 0 0 16px;">
        Thank you for joining CandyCutz. We are thrilled to welcome you to Nigeria's premier luxury grooming and barbing destination. Your account is active and ready for scheduling your next signature experience.
    </p>

    <div class="details-box" style="background-color: #181822; border-radius: 10px; border-left: 4px solid #D4AF37; padding: 18px 20px; margin: 20px 0; border-top: 1px solid #232330; border-right: 1px solid #232330; border-bottom: 1px solid #232330;">
        <h3 style="color: #D4AF37; font-size: 13px; text-transform: uppercase; letter-spacing: 1.5px; margin: 0 0 14px; font-weight: 700;">
            What Awaits You at CandyCutz:
        </h3>
        
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; vertical-align: top; width: 28px;">
                    <span style="font-size: 16px;">✂️</span>
                </td>
                <td style="padding: 8px 0; vertical-align: top;">
                    <strong style="color: #FFFFFF; font-size: 13px;">Master Stylists &amp; Barbers</strong>
                    <div style="color: #9CA3AF; font-size: 12px; margin-top: 2px;">Handpicked artisans dedicated to precision cutting, beard detailing, and executive care.</div>
                </td>
            </tr>
            <tr>
                <td style="padding: 8px 0; vertical-align: top; width: 28px;">
                    <span style="font-size: 16px;">📅</span>
                </td>
                <td style="padding: 8px 0; vertical-align: top;">
                    <strong style="color: #FFFFFF; font-size: 13px;">Instant Online Booking</strong>
                    <div style="color: #9CA3AF; font-size: 12px; margin-top: 2px;">Select your preferred stylist, pick a time that suits your calendar, and secure your chair in seconds.</div>
                </td>
            </tr>
            <tr>
                <td style="padding: 8px 0; vertical-align: top; width: 28px;">
                    <span style="font-size: 16px;">💳</span>
                </td>
                <td style="padding: 8px 0; vertical-align: top;">
                    <strong style="color: #FFFFFF; font-size: 13px;">Cashless Convenience</strong>
                    <div style="color: #9CA3AF; font-size: 12px; margin-top: 2px;">Smooth checkouts with instant card payments, USSD, and verified bank transfers.</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="location-box" style="background-color: #15151D; border-radius: 8px; padding: 14px 16px; font-size: 12px; color: #9CA3AF; line-height: 1.6; border: 1px solid #242430; margin-top: 18px;">
        <span class="location-title" style="color: #D4AF37; font-weight: 600; margin-bottom: 4px; display: block;">📍 Physical Saloon Location</span>
        Angwan Kare, BCG, Beside Angwan Kare BCG Gas Station, beside Wealths Khort Apartments, BCG, Keffi 961101, Nasarawa State, Nigeria.<br>
        <a href="https://maps.app.goo.gl/RtpPCeBRobajKmwS7" target="_blank" style="color: #D4AF37; text-decoration: underline; display: inline-block; margin-top: 6px; font-weight: 600;">Open Saloon in Google Maps &rarr;</a>
    </div>

    <div style="text-align: center; margin-top: 28px;">
        <a href="{{ $servicesUrl }}" class="btn-gold" style="display: inline-block; background: linear-gradient(135deg, #D4AF37 0%, #B89428 100%); background-color: #D4AF37; color: #0A0A0C !important; font-weight: 700; font-size: 13px; padding: 13px 32px; border-radius: 8px; text-decoration: none; text-align: center; box-shadow: 0 4px 20px rgba(212, 175, 55, 0.35);">
            Book Your First Appointment
        </a>
    </div>
@endsection
