<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Cancelled — CandyCutz</title>
    <style>
        body { margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #0A0A0C; color: #E5E7EB; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #0A0A0C; padding: 30px 0; }
        .main-card { max-width: 600px; margin: 0 auto; background-color: #121217; border-radius: 12px; border: 1px solid #272732; overflow: hidden; }
        .header { background: linear-gradient(180deg, #1C1C24 0%, #121217 100%); padding: 36px 30px 24px; text-align: center; border-bottom: 1px solid #272732; }
        .gold-brand { color: #D4AF37; font-size: 26px; font-weight: 800; letter-spacing: 2px; text-transform: uppercase; margin: 0; }
        .tagline { color: #9CA3AF; font-size: 13px; text-transform: uppercase; letter-spacing: 1.5px; margin-top: 6px; }
        .content { padding: 32px 30px; }
        .headline { font-size: 20px; font-weight: 700; color: #FFFFFF; margin-top: 0; margin-bottom: 12px; }
        .summary-pill { display: inline-block; background-color: rgba(239, 68, 68, 0.15); color: #EF4444; border: 1px solid rgba(239, 68, 68, 0.4); padding: 4px 14px; border-radius: 9999px; font-size: 12px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 20px; }
        .details-box { background-color: #191922; border-radius: 8px; border-left: 4px solid #EF4444; padding: 18px 20px; margin: 24px 0; }
        .row { display: flex; justify-content: space-between; padding: 8px 0; font-size: 14px; }
        .lbl { color: #9CA3AF; font-weight: 500; }
        .val { color: #FFFFFF; font-weight: 600; text-align: right; }
        .btn-gold { display: block; width: fit-content; margin: 28px auto 10px; background: linear-gradient(135deg, #D4AF37 0%, #B89428 100%); color: #0A0A0C !important; font-weight: 700; font-size: 14px; padding: 14px 28px; border-radius: 8px; text-decoration: none; text-align: center; }
        .footer { padding: 24px 30px; text-align: center; font-size: 12px; color: #6B7280; border-top: 1px solid #22222E; background-color: #0E0E12; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="main-card">
            <div class="header">
                <h1 class="gold-brand">CANDYCUTZ</h1>
                <div class="tagline">Luxury Grooming & Barbing Saloon</div>
            </div>
            <div class="content">
                <div class="summary-pill">Appointment Cancelled</div>
                <h2 class="headline">Hello {{ $customer_name }},</h2>
                <p style="color: #D1D5DB; font-size: 14px; line-height: 1.6; margin: 0 0 16px;">
                    This email is to notify you that appointment <strong>{{ $booking_reference }}</strong> has been cancelled.
                </p>

                <div class="details-box">
                    <div class="row">
                        <span class="lbl">Reference:</span>
                        <span class="val">{{ $booking_reference }}</span>
                    </div>
                    <div class="row">
                        <span class="lbl">Reason:</span>
                        <span class="val">{{ $reason }}</span>
                    </div>
                </div>

                <p style="color: #9CA3AF; font-size: 13px; line-height: 1.5;">
                    If you made an online deposit or prepayment, your refund will be processed in accordance with our cancellation policy. If you would like to reschedule for a future time slot, please tap below.
                </p>

                <a href="{{ config('app.url') }}/booking" class="btn-gold">Book New Appointment</a>
            </div>
            <div class="footer">
                <p style="margin: 0 0 6px;">&copy; {{ date('Y') }} CandyCutz Nigeria. All rights reserved.</p>
                <p style="margin: 0;">Angwan Kare, BCG, Keffi 961101, Nasarawa State, Nigeria</p>
            </div>
        </div>
    </div>
</body>
</html>
