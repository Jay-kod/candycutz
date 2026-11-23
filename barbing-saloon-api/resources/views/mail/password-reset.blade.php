@extends('mail.layouts.candycutz-luxury')

@section('title', 'Password Reset Request — CandyCutz')

@section('preheader', 'Secure password reset request for your CandyCutz account.')

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
        We received a request to reset your password for your CandyCutz account. Click the button below to establish a new password:
    </p>

    <div style="text-align: center; margin: 28px 0;">
        <a href="{{ $resetUrl }}" class="btn-gold" style="display: inline-block; background: linear-gradient(135deg, #D4AF37 0%, #B89428 100%); background-color: #D4AF37; color: #0A0A0C !important; font-weight: 700; font-size: 14px; padding: 14px 34px; border-radius: 8px; text-decoration: none; text-align: center; box-shadow: 0 4px 18px rgba(212, 175, 55, 0.35);">
            Reset Password
        </a>
    </div>

    <div class="details-box" style="background-color: #181822; border-radius: 10px; border-left: 4px solid #F59E0B; padding: 14px 18px; margin: 24px 0;">
        <p style="color: #9CA3AF; font-size: 12px; line-height: 1.6; margin: 0;">
            <strong style="color: #FFFFFF;">Security Note:</strong> This link will expire in 60 minutes. If you did not initiate this request, no action is required and your account remains fully secure.
        </p>
    </div>
@endsection
