<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Inter, -apple-system, sans-serif; color: #333; background-color: #f9f9f9; }
        .email-container { max-width: 600px; margin: 0 auto; background-color: #fff; border: 1px solid #ddd; }
        .header { background-color: #0D0D0D; color: #C9A84C; padding: 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { padding: 30px; }
        .content h2 { color: #0D0D0D; margin-top: 0; }
        .alert { background-color: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 20px 0; }
        .appointment-details { background-color: #f5f5f5; padding: 20px; border-left: 4px solid #C9A84C; margin: 20px 0; }
        .detail-row { margin: 10px 0; }
        .detail-label { font-weight: bold; color: #0D0D0D; }
        .button { display: inline-block; background-color: #C9A84C; color: #0D0D0D; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin-top: 20px; font-weight: bold; }
        .footer { background-color: #f5f5f5; padding: 20px; text-align: center; color: #666; font-size: 12px; border-top: 1px solid #ddd; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>CandyCutz Admin</h1>
            <p>Booking Alert</p>
        </div>
        <div class="content">
            @if($action === 'new_booking')
                <h2>📅 New Booking Received</h2>
            @elseif($action === 'cancellation')
                <h2>❌ Booking Cancelled</h2>
            @elseif($action === 'completed')
                <h2>✓ Booking Completed</h2>
            @elseif($action === 'no_show')
                <h2>⚠️ No Show</h2>
            @else
                <h2>🔔 Booking Update</h2>
            @endif
            
            <p>A booking action requires your attention:</p>
            
            <div class="appointment-details">
                <div class="detail-row">
                    <span class="detail-label">Customer:</span> {{ $customerName }} ({{ $customerEmail }})
                </div>
                <div class="detail-row">
                    <span class="detail-label">Date & Time:</span> {{ $appointmentDate }} at {{ $appointmentTime }}
                </div>
                <div class="detail-row">
                    <span class="detail-label">Service:</span> {{ $serviceName }}
                </div>
                <div class="detail-row">
                    <span class="detail-label">Barber:</span> {{ $barberName }}
                </div>
            </div>
            
            <p>Log in to the admin panel to view more details and take action if needed.</p>
            
            <a href="#" class="button">View in Admin</a>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} CandyCutz. All rights reserved.</p>
            <p>This is an automated admin notification.</p>
        </div>
    </div>
</body>
</html>
