# CandyCutz — Notification Engine Deployment & Operations Guide

## 1. Database Migrations

Run database migrations to ensure all notification tracking and preference tables exist:

```bash
cd barbing-saloon-api
php artisan migrate --force
```

### Tables Created / Enhanced:
- `notifications`: Enhanced with `category`, `data` (JSON), `action_url`, `priority`, `idempotency_key`, `expires_at`.
- `notification_deliveries`: Tracks individual channel deliveries (`in_app`, `push`, `email`).
- `notification_broadcasts`: Stores broadcast campaign configurations, targets, and delivery counts.
- `user_notification_preferences`: Stores category x channel preferences and quiet hours.
- `notification_reads`: Authoritative per-user read receipts for broadcast announcements.

---

## 2. Queue Workers & Background Processing

Ensure the Laravel queue worker is running in production (e.g. via Supervisor or systemd) to process `SendExpoPush`, `SendNotificationEmail`, and transactional booking mailables (`SendBookingConfirmation`, `SendBookingCancellation`, `SendBookingReminder`, `SendBookingThankYou`, `SendAdminNotification`):

```bash
php artisan queue:work --queue=default,high --tries=3 --timeout=90
```

For Supervisor (`/etc/supervisor/conf.d/candycutz-worker.conf`):
```ini
[program:candycutz-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/candycutz/barbing-saloon-api/artisan queue:work --queue=default,high --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/candycutz-worker.log
stopwaitsecs=3600
```

---

## 3. Resend Transactional Email Setup

### Required Environment Variables
Add the following to your deployment environment (Railway, cPanel, Docker, or VPS `.env`):

```env
MAIL_MAILER=resend
RESEND_API_KEY=re_123456789_abcdefghijklmnopqrstuvwxyz
MAIL_FROM_ADDRESS="no-reply@mail.candycutz.com"
MAIL_FROM_NAME="Candycutz"
```

> [!IMPORTANT]
> The web and mobile apps must never hold or see the `RESEND_API_KEY`. Email dispatch is handled strictly through the Laravel backend API.

### Domain Verification in Resend (DNS Records)
1. Sign in to your [Resend Dashboard](https://resend.com/domains) and click **Add Domain** (e.g., `mail.candycutz.com` or `candycutz.com`).
2. Add the generated DNS records at your DNS host (Cloudflare, Namecheap, Route53, cPanel Zone Editor):
   - **DKIM (TXT):** Name: `resend._domainkey`, Value: Provided by Resend.
   - **SPF (TXT):** Name: `@` (or subdomain), Value: `v=spf1 include:amazonses.com ~all`.
   - **DMARC (TXT):** Name: `_dmarc`, Value: `v=DMARC1; p=none; rua=mailto:dmarc@candycutz.com`.
   - **MX (optional for receiving):** Set priority `10` pointing to `feedback-smtp.us-east-1.amazonses.com` (or Resend MX).
3. Click **Verify Domain** in Resend until the status displays as **Verified**.
4. Match `MAIL_FROM_ADDRESS` in `.env` to the verified domain (e.g. `no-reply@mail.candycutz.com`).

### Rotating the Resend API Key
1. Generate a new API key in the Resend Dashboard with **Sending access** (Restricted to your verified domain).
2. Update `RESEND_API_KEY` in your environment config (Railway Variables / cPanel Environment / `.env`).
3. If cached, refresh application config cache:
   ```bash
   php artisan config:cache
   ```
4. Restart queue workers gracefully so workers pick up the new key:
   ```bash
   php artisan queue:restart
   ```
5. Run a manual test via tinker or the Admin CMS to confirm outbound delivery:
   ```bash
   php artisan tinker --execute="Illuminate\Support\Facades\Mail::raw('Test after rotation', fn(\$m) => \$m->to('admin@candycutz.com')->subject('Resend Key Rotation Test'));"
   ```
6. Revoke the old API key in the Resend Dashboard.

---

## 4. Web Server & Reverse Proxy Configuration (SSE)

For Server-Sent Events (`/api/v1/notifications/stream`) to stream events smoothly without proxy buffering, configure your Nginx block:

```nginx
location /api/v1/notifications/stream {
    proxy_pass http://127.0.0.1:8000;
    proxy_http_version 1.1;
    proxy_set_header Connection '';
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;

    # Disable proxy buffering for persistent SSE connections
    proxy_buffering off;
    proxy_cache off;
    chunked_transfer_encoding off;
    proxy_read_timeout 120s;
}
```

---

## 4. Frontend Build & Asset Deployment

Build the Vue 3 PWA assets and sync them to `barbing-saloon-api/public`:

```bash
cd barbing-saloon-web
npm run build
```

This runs the responsive design linter, builds Vite chunks, and triggers `sync-to-public.mjs` to keep the Laravel web root in sync.
