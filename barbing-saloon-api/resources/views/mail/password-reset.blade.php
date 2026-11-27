@extends('mail.layouts.candycutz-luxury')

@section('title', 'Password Reset Code — CandyCutz')

@section('preheader', 'Your 6-digit password reset code for your CandyCutz account.')

@section('content')
    <div class="summary-pill pill-warning">
        🔒 Security Request
    </div>

    <h2 class="headline" style="font-size: 20px; font-weight: 700; color: #FFFFFF; margin-top: 0; margin-bottom: 12px; line-height: 1.4;">
        Reset Your Account Password
    </h2>

    <p class="body-text" style="color: #D1D5DB; font-size: 14px; line-height: 1.6; margin: 0 0 16px;">
        Hello {{ $userName ?? 'Valued Client' }},
    </p>

    <p class="body-text" style="color: #D1D5DB; font-size: 14px; line-height: 1.6; margin: 0 0 20px;">
        We received a request to reset your password for your CandyCutz account. Enter the 6-digit OTP code below on the password reset page:
    </p>

    <div style="text-align: center; margin: 28px 0; background: #161a22; border: 1px solid #2d3748; border-radius: 12px; padding: 24px 16px;">
        <p style="font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px; color: #94a3b8; margin: 0 0 10px 0; font-weight: 600;">Your 6-Digit Password Reset Code</p>
        <div style="display: inline-block; font-family: monospace, 'Courier New', Courier; font-size: 34px; font-weight: 800; letter-spacing: 8px; color: #ff6700; text-shadow: 0 0 15px rgba(255,103,0,0.3); -webkit-user-select: all; user-select: all;">{{ $otp ?? '******' }}</div>
        <div style="margin-top: 14px;">
            <span style="display: inline-block; font-size: 11px; color: #ff6700; background: rgba(255,103,0,0.12); border: 1px solid rgba(255,103,0,0.3); border-radius: 8px; padding: 6px 14px; font-weight: 700; -webkit-user-select: all; user-select: all;">
                📋 Tap / Double-Click to Copy: {{ $otp ?? '******' }}
            </span>
        </div>
        <p style="font-size: 11px; color: #64748b; margin: 10px 0 0 0;">Valid for 15 minutes</p>
    </div>

    <p style="font-size: 13px; color: #94a3b8; line-height: 1.6; text-align: center; margin-bottom: 20px;">
        Alternatively, click the button below to open the reset form directly with your code:
    </p>

    <div style="text-align: center; margin: 24px 0;">
        <a href="{{ $resetUrl }}" class="btn-gold" style="display: inline-block; background: linear-gradient(135deg, #D4AF37 0%, #B89428 100%); background-color: #D4AF37; color: #0A0A0C !important; font-weight: 700; font-size: 14px; padding: 14px 34px; border-radius: 8px; text-decoration: none; text-align: center; box-shadow: 0 4px 18px rgba(212, 175, 55, 0.35);">
            Reset Password
        </a>
    </div>

    <div class="details-box" style="background-color: #181822; border-radius: 10px; border-left: 4px solid #F59E0B; padding: 14px 18px; margin: 24px 0;">
        <p style="color: #9CA3AF; font-size: 12px; line-height: 1.6; margin: 0;">
            <strong style="color: #FFFFFF;">Security Note:</strong> This OTP code will expire in 15 minutes. If you did not initiate this request, no action is required and your account remains fully secure.
        </p>
    </div>
@endsection
