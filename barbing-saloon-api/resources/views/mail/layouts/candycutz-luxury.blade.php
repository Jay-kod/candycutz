<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>@yield('title', 'CandyCutz — Luxury Grooming & Barbing Saloon')</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
    <style type="text/css">
        body, table, td, a {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }
        table, td {
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }
        img {
            -ms-interpolation-mode: bicubic;
            border: 0;
            height: auto;
            line-height: 100%;
            outline: none;
            text-decoration: none;
        }
        body {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            height: 100% !important;
            background-color: #0A0A0C !important;
            color: #E5E7EB;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        .wrapper {
            width: 100% !important;
            background-color: #0A0A0C;
            padding: 30px 10px;
        }
        .main-card {
            max-width: 600px;
            margin: 0 auto;
            background-color: #121217;
            border-radius: 16px;
            border: 1px solid #272732;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.6);
        }
        .header {
            background-color: #16161F;
            padding: 32px 24px 22px;
            text-align: center;
            border-bottom: 1px solid #272732;
        }
        .gold-brand {
            color: #D4AF37;
            font-size: 24px;
            font-weight: 800;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin: 0;
            line-height: 1.2;
        }
        .tagline {
            color: #9CA3AF;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-top: 6px;
            font-weight: 500;
        }
        .content {
            padding: 28px 24px;
        }
        .summary-pill {
            display: inline-block;
            background-color: rgba(212, 175, 55, 0.15);
            color: #D4AF37;
            border: 1px solid rgba(212, 175, 55, 0.4);
            padding: 4px 14px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 16px;
        }
        .pill-danger {
            background-color: rgba(239, 68, 68, 0.15);
            color: #EF4444;
            border-color: rgba(239, 68, 68, 0.4);
        }
        .pill-warning {
            background-color: rgba(245, 158, 11, 0.15);
            color: #F59E0B;
            border-color: rgba(245, 158, 11, 0.4);
        }
        .pill-success {
            background-color: rgba(16, 185, 129, 0.15);
            color: #10B981;
            border-color: rgba(16, 185, 129, 0.4);
        }
        .headline {
            font-size: 18px;
            font-weight: 700;
            color: #FFFFFF;
            margin-top: 0;
            margin-bottom: 12px;
            line-height: 1.4;
        }
        .body-text {
            color: #D1D5DB;
            font-size: 13px;
            line-height: 1.6;
            margin: 0 0 16px;
        }
        .details-box {
            background-color: #181822;
            border-radius: 10px;
            border-left: 4px solid #D4AF37;
            padding: 16px 18px;
            margin: 20px 0;
            border-top: 1px solid #232330;
            border-right: 1px solid #232330;
            border-bottom: 1px solid #232330;
        }
        .details-box-danger {
            border-left-color: #EF4444;
        }
        table.details-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }
        table.details-table td.lbl {
            color: #9CA3AF;
            font-weight: 500;
            font-size: 13px;
            padding: 8px 0;
            border-bottom: 1px solid #232330;
            text-align: left;
        }
        table.details-table td.val {
            color: #FFFFFF;
            font-weight: 600;
            font-size: 13px;
            padding: 8px 0;
            border-bottom: 1px solid #232330;
            text-align: right;
        }
        table.details-table tr:last-child td {
            border-bottom: none;
        }
        table.details-table td.total-lbl {
            color: #FFFFFF;
            font-weight: 700;
            font-size: 14px;
            padding: 12px 0 4px;
            border-top: 1px dashed #3F3F4E;
            text-align: left;
        }
        table.details-table td.total-val {
            color: #D4AF37;
            font-weight: 800;
            font-size: 16px;
            padding: 12px 0 4px;
            border-top: 1px dashed #3F3F4E;
            text-align: right;
        }
        .location-box {
            background-color: #15151D;
            border-radius: 8px;
            padding: 14px 16px;
            font-size: 12px;
            color: #9CA3AF;
            line-height: 1.6;
            border: 1px solid #242430;
            margin-top: 18px;
        }
        .location-title {
            color: #D4AF37;
            font-weight: 600;
            margin-bottom: 4px;
            display: block;
        }
        .btn-gold {
            display: inline-block;
            background: linear-gradient(135deg, #D4AF37 0%, #B89428 100%);
            background-color: #D4AF37;
            color: #0A0A0C !important;
            font-weight: 700;
            font-size: 13px;
            padding: 12px 28px;
            border-radius: 8px;
            text-decoration: none;
            text-align: center;
            box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
        }
        .footer {
            padding: 22px 24px;
            text-align: center;
            font-size: 11px;
            color: #6B7280;
            border-top: 1px solid #22222E;
            background-color: #0E0E12;
            line-height: 1.6;
        }
        .footer a {
            color: #9CA3AF;
            text-decoration: none;
        }
        .footer a:hover {
            color: #D4AF37;
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #0A0A0C; color: #E5E7EB;">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" class="wrapper" style="background-color: #0A0A0C; width: 100%;">
        <tr>
            <td align="center" style="padding: 30px 10px;">
                <!--[if (gte mso 9)|(IE)]>
                <table align="center" border="0" cellspacing="0" cellpadding="0" width="600">
                <tr>
                <td align="center" valign="top" width="600">
                <![endif]-->
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" class="main-card" style="max-width: 600px; background-color: #121217; border-radius: 16px; border: 1px solid #272732; overflow: hidden;">
                    <tr>
                        <td class="header" style="background-color: #16161F; padding: 32px 24px 22px; text-align: center; border-bottom: 1px solid #272732;">
                            <h1 class="gold-brand" style="color: #D4AF37; font-size: 24px; font-weight: 800; letter-spacing: 3px; text-transform: uppercase; margin: 0;">CANDYCUTZ</h1>
                            <div class="tagline" style="color: #9CA3AF; font-size: 11px; text-transform: uppercase; letter-spacing: 2px; margin-top: 6px;">Luxury Grooming &amp; Barbing Saloon</div>
                        </td>
                    </tr>
                    <tr>
                        <td class="content" style="padding: 28px 24px;">
                            @yield('content')
                        </td>
                    </tr>
                    <tr>
                        <td class="footer" style="padding: 22px 24px; text-align: center; font-size: 11px; color: #6B7280; border-top: 1px solid #22222E; background-color: #0E0E12; line-height: 1.6;">
                            <p style="margin: 0 0 6px;">&copy; {{ date('Y') }} CandyCutz Nigeria. All rights reserved.</p>
                            <p style="margin: 0 0 6px;">Angwan Kare, BCG, Beside Angwan Kare BCG Gas Station, Keffi 961101, Nasarawa State, Nigeria</p>
                            <p style="margin: 0;">Questions? Contact concierge at <a href="mailto:support@candycutz.com" style="color: #9CA3AF;">support@candycutz.com</a></p>
                        </td>
                    </tr>
                </table>
                <!--[if (gte mso 9)|(IE)]>
                </td>
                </tr>
                </table>
                <![endif]-->
            </td>
        </tr>
    </table>
</body>
</html>
