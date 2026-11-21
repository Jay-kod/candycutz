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

Ensure the Laravel queue worker is running in production (e.g. via Supervisor or systemd) to process `SendExpoPush` and `SendBrevoEmail` jobs:

```bash
php artisan queue:work --queue=default,high --tries=3 --timeout=90
```

---

## 3. Web Server & Reverse Proxy Configuration (SSE)

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
