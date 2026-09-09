# Candycutz — Notifications & Brevo Debugging Runbook

## 1. Problem: Booking Confirmation Emails Are Not Delivered

### What it looks like:
An appointment is confirmed, but neither the customer nor the barber receives an email.

### Root Cause:
Common causes:
1. `QUEUE_CONNECTION=redis` is set, but the background queue worker is stopped or crashed.
2. `MAIL_MAILER=log` is set in `.env`, writing emails to `storage/logs/laravel.log` instead of sending them.
3. Brevo SMTP authentication failed (wrong login/password or port blocked).

### How to Diagnose:
1. Check queued jobs in Redis:
   ```bash
   docker compose exec app php artisan queue:failed
   ```
2. Check if the worker container is running:
   ```bash
   docker compose ps worker
   docker compose logs -f worker
   ```
3. Check Laravel log for Brevo SMTP errors:
   ```bash
   docker compose exec app grep -i "brevo" storage/logs/laravel.log
   ```

### How to Fix:
1. Ensure `.env` has active Brevo SMTP credentials:
   ```ini
   MAIL_MAILER=smtp
   MAIL_HOST=smtp-relay.brevo.com
   MAIL_PORT=587
   MAIL_USERNAME=your_brevo_smtp_login
   MAIL_PASSWORD=your_brevo_smtp_master_key
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS=concierge@candycutz.com
   ```
2. Retry any failed queued jobs:
   ```bash
   docker compose exec app php artisan queue:retry all
   ```

---

## 2. Problem: API Takes 30 Seconds to Respond to Bookings

### What it looks like:
When booking an appointment, the button spins for 20-30 seconds before succeeding.

### Root Cause:
`QUEUE_CONNECTION=sync` was active. Laravel was trying to connect to Brevo SMTP synchronously inside the HTTP request cycle. Network latency or SMTP timeouts blocked the HTTP response.

### How to Fix:
1. Set `QUEUE_CONNECTION=redis` (or `QUEUE_CONNECTION=database` during local testing).
2. Ensure the queue worker is running in the background:
   ```bash
   docker compose exec -d app php artisan queue:work --tries=3
   ```
The HTTP endpoint will respond in under 100ms, and the email will be dispatched asynchronously in the background.
