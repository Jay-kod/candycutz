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
            background-color: #07080C !important;
            color: #E5E7EB;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        .wrapper {
            width: 100% !important;
            background-color: #07080C;
            padding: 28px 12px;
        }
        .main-card {
            max-width: 620px;
            margin: 0 auto;
            background-color: #101218;
            border-radius: 18px;
            border: 1px solid #262936;
            border-top: 3px solid #D4AF37;
            overflow: hidden;
            box-shadow: 0 18px 48px rgba(0, 0, 0, 0.65);
        }
        .header {
            background: linear-gradient(180deg, #171922 0%, #11131A 100%);
            background-color: #151720;
            padding: 30px 26px 22px;
            text-align: center;
            border-bottom: 1px solid #242734;
        }
        .gold-brand {
            color: #E6C15A;
            font-size: 24px;
            font-weight: 800;
            letter-spacing: 4px;
            text-transform: uppercase;
            margin: 0;
            line-height: 1.2;
        }
        .tagline {
            color: #9CA3AF;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 2.5px;
            margin-top: 7px;
            font-weight: 600;
        }
        .content {
            padding: 28px 26px;
        }
        .summary-pill {
            display: inline-block;
            background-color: rgba(212, 175, 55, 0.14);
            color: #E6C15A;
            border: 1px solid rgba(212, 175, 55, 0.42);
            padding: 5px 14px;
            border-radius: 9999px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            margin-bottom: 16px;
        }
        .pill-danger {
            background-color: rgba(239, 68, 68, 0.14);
            color: #F87171;
            border-color: rgba(239, 68, 68, 0.42);
        }
        .pill-warning {
            background-color: rgba(245, 158, 11, 0.15);
            color: #FBBF24;
            border-color: rgba(245, 158, 11, 0.42);
        }
        .pill-success {
            background-color: rgba(16, 185, 129, 0.14);
            color: #34D399;
            border-color: rgba(16, 185, 129, 0.42);
        }
        .headline {
            font-size: 20px;
            font-weight: 700;
            color: #FFFFFF;
            margin-top: 0;
            margin-bottom: 10px;
            line-height: 1.35;
        }
        .body-text {
            color: #CBD5E1;
            font-size: 13px;
            line-height: 1.65;
            margin: 0 0 16px;
        }
        .booking-code-card {
            background: linear-gradient(145deg, #1B1811 0%, #12141B 100%);
            background-color: #171612;
            border: 1px solid #8E7125;
            border-radius: 14px;
            padding: 20px 18px;
            text-align: center;
            margin: 20px 0;
        }
        .booking-code-label {
            color: #E6C15A;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin: 0 0 8px;
        }
        .booking-code-value {
            display: inline-block;
            background-color: #0B0D12;
            border: 1px dashed rgba(230, 193, 90, 0.6);
            border-radius: 10px;
            padding: 10px 22px;
            font-family: 'Courier New', Courier, monospace;
            font-size: 26px;
            font-weight: 800;
            letter-spacing: 5px;
            color: #F7D774;
        }
        .booking-code-hint {
            color: #94A3B8;
            font-size: 11px;
            line-height: 1.5;
            margin: 10px 0 0;
        }
        .details-box {
            background-color: #151822;
            border-radius: 12px;
            border: 1px solid #242838;
            border-left: 4px solid #D4AF37;
            padding: 16px 18px;
            margin: 18px 0;
        }
        .details-box-danger {
            border-left-color: #EF4444;
        }
        .receipt-card {
            background-color: #131620;
            border-radius: 14px;
            border: 1px solid #262B3D;
            padding: 18px;
            margin: 20px 0;
        }
        .receipt-divider {
            border-top: 1px dashed #33394F;
            margin: 14px 0;
        }
        table.details-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }
        table.details-table td.lbl {
            color: #94A3B8;
            font-weight: 500;
            font-size: 12px;
            padding: 8px 0;
            border-bottom: 1px solid #202434;
            text-align: left;
        }
        table.details-table td.val {
            color: #F8FAFC;
            font-weight: 600;
            font-size: 12px;
            padding: 8px 0;
            border-bottom: 1px solid #202434;
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
            border-top: 1px dashed #3D445C;
            text-align: left;
        }
        table.details-table td.total-val {
            color: #F7D774;
            font-weight: 800;
            font-size: 17px;
            padding: 12px 0 4px;
            border-top: 1px dashed #3D445C;
            text-align: right;
        }
        .location-box {
            background-color: #141720;
            border-radius: 10px;
            padding: 14px 16px;
            font-size: 12px;
            color: #94A3B8;
            line-height: 1.6;
            border: 1px solid #242838;
            margin-top: 18px;
        }
        .location-title {
            color: #E6C15A;
            font-weight: 700;
            margin-bottom: 4px;
            display: block;
        }
        .btn-gold {
            display: inline-block;
            background: linear-gradient(135deg, #E6C15A 0%, #C59B27 100%);
            background-color: #D4AF37;
            color: #090A0E !important;
            font-weight: 700;
            font-size: 13px;
            padding: 12px 28px;
            border-radius: 10px;
            text-decoration: none;
            text-align: center;
            box-shadow: 0 4px 16px rgba(212, 175, 55, 0.3);
        }
        .btn-outline {
            display: inline-block;
            background-color: #181B26;
            border: 1px solid #353B50;
            color: #E2E8F0 !important;
            font-weight: 600;
            font-size: 12px;
            padding: 10px 20px;
            border-radius: 10px;
            text-decoration: none;
            text-align: center;
        }
        .footer {
            padding: 22px 26px;
            text-align: center;
            font-size: 11px;
            color: #64748B;
            border-top: 1px solid #202330;
            background-color: #0B0C11;
            line-height: 1.6;
        }
        .footer a {
            color: #94A3B8;
            text-decoration: none;
        }
        .footer a:hover {
            color: #E6C15A;
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #07080C; color: #E5E7EB;">
    @hasSection('preheader')
        <div style="display: none; max-height: 0px; overflow: hidden; mso-hide: all; font-size: 1px; line-height: 1px; color: #07080C; opacity: 0;">
            @yield('preheader')
            &#8203;&zwnj;&nbsp;&#8203;&zwnj;&nbsp;&#8203;&zwnj;&nbsp;&#8203;&zwnj;&nbsp;&#8203;&zwnj;&nbsp;&#8203;&zwnj;&nbsp;&#8203;&zwnj;&nbsp;&#8203;&zwnj;&nbsp;&#8203;&zwnj;&nbsp;&#8203;&zwnj;
        </div>
    @endif
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" class="wrapper" style="background-color: #07080C; width: 100%;">
        <tr>
            <td align="center" style="padding: 28px 12px;">
                <!--[if (gte mso 9)|(IE)]>
                <table align="center" border="0" cellspacing="0" cellpadding="0" width="620">
                <tr>
                <td align="center" valign="top" width="620">
                <![endif]-->
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" class="main-card" style="max-width: 620px; background-color: #101218; border-radius: 18px; border: 1px solid #262936; border-top: 3px solid #D4AF37; overflow: hidden; box-shadow: 0 18px 48px rgba(0, 0, 0, 0.65);">
                    <tr>
                        <td class="header" style="background-color: #151720; padding: 30px 26px 22px; text-align: center; border-bottom: 1px solid #242734;">
                            <h1 class="gold-brand" style="color: #E6C15A; font-size: 24px; font-weight: 800; letter-spacing: 4px; text-transform: uppercase; margin: 0;">CANDYCUTZ</h1>
                            <div class="tagline" style="color: #9CA3AF; font-size: 10px; text-transform: uppercase; letter-spacing: 2.5px; margin-top: 7px;">Luxury Grooming &amp; Barbing Saloon</div>
                        </td>
                    </tr>
                    <tr>
                        <td class="content" style="padding: 28px 26px;">
                            @yield('content')
                        </td>
                    </tr>
                    <tr>
                        <td class="footer" style="padding: 22px 26px; text-align: center; font-size: 11px; color: #64748B; border-top: 1px solid #202330; background-color: #0B0C11; line-height: 1.6;">
                            <p style="margin: 0 0 6px;">&copy; {{ date('Y') }} CandyCutz Grooming Ltd. All rights reserved.</p>
                            <p style="margin: 0 0 6px;">Angwan Kare, BCG, Beside Angwan Kare BCG Gas Station, Keffi 961101, Nasarawa State, Nigeria</p>
                            <p style="margin: 0;">Questions? Contact concierge at <a href="mailto:concierge@candycutz.com" style="color: #94A3B8;">concierge@candycutz.com</a></p>
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
