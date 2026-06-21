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
            <h1>CandyCutz</h1>
            <p>Premium Barbing Saloon</p>
        </div>
        <div class="content">
            <h2>Thank You for Visiting! 💈</h2>
            <p>Hi {{ $customerName }},</p>
            <p>We hope you enjoyed your {{ $serviceName }} with {{ $barberName }} at CandyCutz. Your satisfaction is our priority!</p>
            
            <div class="appointment-details">
                <div class="detail-row">
                    If you'd like to share your experience, we'd love to hear from you.
                </div>
            </div>
            
            <p>Leave a review and help us improve your next visit. We look forward to seeing you again soon!</p>
            
            <a href="#" class="button">Leave a Review</a>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} CandyCutz. All rights reserved.</p>
            <p>Questions? Contact us at support@candycutz.local</p>
        </div>
    </div>
</body>
</html>
