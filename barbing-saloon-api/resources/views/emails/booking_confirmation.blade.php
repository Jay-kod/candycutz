<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed — CandyCutz</title>
    <style>
        body { margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #0A0A0C; color: #E5E7EB; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #0A0A0C; padding: 30px 0; }
        .main-card { max-width: 600px; margin: 0 auto; background-color: #121217; border-radius: 12px; border: 1px solid #272732; overflow: hidden; }
        .header { background: linear-gradient(180deg, #1C1C24 0%, #121217 100%); padding: 36px 30px 24px; text-align: center; border-bottom: 1px solid #272732; }
        .gold-brand { color: #D4AF37; font-size: 26px; font-weight: 800; letter-spacing: 2px; text-transform: uppercase; margin: 0; }
        .tagline { color: #9CA3AF; font-size: 13px; text-transform: uppercase; letter-spacing: 1.5px; margin-top: 6px; }
        .content { padding: 32px 30px; }
        .headline { font-size: 20px; font-weight: 700; color: #FFFFFF; margin-top: 0; margin-bottom: 12px; }
        .summary-pill { display: inline-block; background-color: rgba(212, 175, 55, 0.15); color: #D4AF37; border: 1px solid rgba(212, 175, 55, 0.4); padding: 4px 14px; border-radius: 9999px; font-size: 12px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 20px; }
        .details-box { background-color: #191922; border-radius: 8px; border-left: 4px solid #D4AF37; padding: 18px 20px; margin: 24px 0; }
        .row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #22222E; font-size: 14px; }
        .row:last-child { border-bottom: none; }
        .lbl { color: #9CA3AF; font-weight: 500; }
        .val { color: #FFFFFF; font-weight: 600; text-align: right; }
        .total-row { display: flex; justify-content: space-between; padding-top: 14px; margin-top: 8px; border-top: 1px dashed #3F3F4E; font-size: 16px; }
        .total-lbl { color: #FFFFFF; font-weight: 700; }
        .total-val { color: #D4AF37; font-weight: 800; font-size: 18px; }
        .location-box { background-color: #16161F; border-radius: 8px; padding: 16px 20px; font-size: 13px; color: #9CA3AF; line-height: 1.6; border: 1px solid #272732; margin-top: 20px; }
        .location-title { color: #D4AF37; font-weight: 600; margin-bottom: 4px; display: block; }
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
                <div class="summary-pill">Appointment Confirmed</div>
                <h2 class="headline">Hello {{ $customer_name }} {{ $customer_username }},</h2>
                <p style="color: #D1D5DB; font-size: 14px; line-height: 1.6; margin: 0 0 16px;">
                    Your booking has been secured. Your master stylist is scheduled and ready to provide an exceptional grooming experience.
                </p>

                <div class="details-box">
                    <div class="row">
                        <span class="lbl">Booking Reference</span>
                        <span class="val" style="letter-spacing: 1px; color: #D4AF37;">{{ $booking_reference }}</span>
                    </div>
                    <div class="row">
                        <span class="lbl">Date & Time</span>
                        <span class="val">{{ $appointment_date }} at {{ $start_time }}</span>
                    </div>
                    <div class="row">
                        <span class="lbl">Service Type</span>
                        <span class="val">{{ $appointment_type }}</span>
                    </div>
                    <div class="row">
                        <span class="lbl">Stylist / Barber</span>
                        <span class="val">{{ $barber_name }}</span>
                    </div>
                    <div class="total-row">
                        <span class="total-lbl">Total Payable</span>
                        <span class="total-val">&#8358;{{ $grand_total }}</span>
                    </div>
                </div>

                <div class="location-box">
                    <span class="location-title">Physical Branch Location:</span>
                    {{ $shop_address }}<br>
                    <a href="https://maps.app.goo.gl/RtpPCeBRobajKmwS7" target="_blank" style="color: #D4AF37; text-decoration: underline; display: inline-block; margin-top: 6px;">Open in Google Maps &rarr;</a>
                </div>

                <a href="{{ config('app.url') }}/dashboard" class="btn-gold">View in Dashboard</a>
            </div>
            <div class="footer">
                <p style="margin: 0 0 6px;">&copy; {{ date('Y') }} CandyCutz Nigeria. All rights reserved.</p>
                <p style="margin: 0;">Angwan Kare, BCG, Keffi 961101, Nasarawa State, Nigeria</p>
            </div>
        </div>
    </div>
</body>
</html>
