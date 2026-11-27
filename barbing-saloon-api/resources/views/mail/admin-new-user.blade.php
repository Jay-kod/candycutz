@extends('mail.layouts.candycutz-luxury')

@section('title', 'CandyCutz Admin — New User Alert')

@section('preheader', 'New user joined: ' . $user->name . ' (' . $user->email . ') via ' . ($providerLabel ?? 'Registration') . '.')

@section('content')
    <div class="summary-pill">
        👤 New Client Registration
    </div>

    <h2 class="headline" style="font-size: 20px; font-weight: 700; color: #FFFFFF; margin-top: 0; margin-bottom: 12px; line-height: 1.4;">
        New Customer Joined CandyCutz
    </h2>

    <p class="body-text" style="color: #D1D5DB; font-size: 13px; line-height: 1.6; margin: 0 0 16px;">
        A new user account was just registered on the CandyCutz platform. Below are the registration and authentication details:
    </p>

    <div class="details-box" style="background-color: #181822; border-radius: 10px; border-left: 4px solid #D4AF37; padding: 16px 18px; margin: 20px 0; border-top: 1px solid #232330; border-right: 1px solid #232330; border-bottom: 1px solid #232330;">
        <table class="details-table" role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width: 100%; border-collapse: collapse;">
            <tr>
                <td class="lbl" align="left" style="color: #9CA3AF; font-weight: 500; font-size: 12px; padding: 8px 0; border-bottom: 1px solid #232330;">Customer Name</td>
                <td class="val" align="right" style="color: #FFFFFF; font-weight: 600; font-size: 12px; padding: 8px 0; border-bottom: 1px solid #232330;">{{ $user->name }}</td>
            </tr>
            <tr>
                <td class="lbl" align="left" style="color: #9CA3AF; font-weight: 500; font-size: 12px; padding: 8px 0; border-bottom: 1px solid #232330;">Username</td>
                <td class="val" align="right" style="color: #D4AF37; font-weight: 600; font-size: 12px; padding: 8px 0; border-bottom: 1px solid #232330; font-family: monospace;">{{ '@' . $user->username }}</td>
            </tr>
            <tr>
                <td class="lbl" align="left" style="color: #9CA3AF; font-weight: 500; font-size: 12px; padding: 8px 0; border-bottom: 1px solid #232330;">Email Address</td>
                <td class="val" align="right" style="color: #FFFFFF; font-weight: 600; font-size: 12px; padding: 8px 0; border-bottom: 1px solid #232330;">{{ $user->email }}</td>
            </tr>
            <tr>
                <td class="lbl" align="left" style="color: #9CA3AF; font-weight: 500; font-size: 12px; padding: 8px 0; border-bottom: 1px solid #232330;">Registration Source</td>
                <td class="val" align="right" style="color: #FFFFFF; font-weight: 600; font-size: 12px; padding: 8px 0; border-bottom: 1px solid #232330;">{{ $providerLabel }}</td>
            </tr>
            <tr>
                <td class="lbl" align="left" style="color: #9CA3AF; font-weight: 500; font-size: 12px; padding: 8px 0; border-bottom: 1px solid #232330;">Email Verification</td>
                <td class="val" align="right" style="color: {{ $user->hasVerifiedEmail() ? '#10B981' : '#F59E0B' }}; font-weight: 700; font-size: 12px; padding: 8px 0; border-bottom: 1px solid #232330;">
                    {{ $user->hasVerifiedEmail() ? '✓ Verified' : '⏳ Pending' }}
                </td>
            </tr>
            <tr>
                <td class="lbl" align="left" style="color: #9CA3AF; font-weight: 500; font-size: 12px; padding: 8px 0;">Registered At</td>
                <td class="val" align="right" style="color: #9CA3AF; font-weight: 600; font-size: 12px; padding: 8px 0;">{{ $registeredAt }}</td>
            </tr>
        </table>
    </div>

    <p class="body-text" style="color: #9CA3AF; font-size: 12px; margin: 16px 0 24px;">
        You can view customer appointments, transaction history, or manage their account permissions directly from the management console.
    </p>

    <div style="text-align: center; margin-top: 24px;">
        <a href="{{ $adminUrl }}" class="btn-gold" style="display: inline-block; background: linear-gradient(135deg, #D4AF37 0%, #B89428 100%); background-color: #D4AF37; color: #0A0A0C !important; font-weight: 700; font-size: 13px; padding: 12px 28px; border-radius: 8px; text-decoration: none; text-align: center; box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);">
            View Customer Directory
        </a>
    </div>
@endsection
