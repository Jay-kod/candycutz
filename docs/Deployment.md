# Candycutz — Deployment & Infrastructure Runbook

## 1. Environment Segregation
Candycutz maintains three isolated operational environments:
- **Local (Development)**: Local developer machines running PHP 8.2+, XAMPP / Docker, Node.js 20, Vite.
- **Staging**: Exact production replica for integration testing, closed customer beta, and barber staff training.
- **Production**: Hardened, autoscaled cloud infrastructure with automated database backups and CDN caching.

---

## 2. Infrastructure Architecture & Docker Deployment

```mermaid
graph TD
    INTERNET["Internet Traffic"] --> NGINX["Nginx Edge Proxy<br/>(TLS 1.3 / SSL Termination / HTTP/2)"]
    NGINX -->|/api/*| PHP_FPM["PHP 8.2-FPM<br/>(Laravel 11 Kernel)"]
    NGINX -->|/* (Static Assets)| VUE_WEB["Vue 3 Web Bundle<br/>(Static Dist / PWA)"]
    PHP_FPM --> MYSQL[("MySQL 8.0 Database<br/>(InnoDB / utf8mb4)")]
    PHP_FPM --> REDIS[("Redis In-Memory<br/>(Cache & Queues)")]
    QUEUE_WORKER["Laravel Queue Worker<br/>(Brevo & Push Jobs)"] --> REDIS
    SCHEDULER["Laravel Crontab Scheduler<br/>(2h Reminders & Cleanup)"] --> PHP_FPM
```

---

## 3. Production Environment Configuration (`.env.production`)

```ini
APP_NAME="CandyCutz"
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://candycutz.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=candycutz_prod
DB_USERNAME=candycutz_user
DB_PASSWORD=...

# Cache & Sessions
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=...
REDIS_PORT=6379

# Storage
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
AWS_DEFAULT_REGION=af-south-1
AWS_BUCKET=candycutz-media-prod

# Stripe Payments
STRIPE_KEY=pk_live_...
STRIPE_SECRET=sk_live_...
STRIPE_WEBHOOK_SECRET=whsec_...

# Brevo Transactional Email
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=concierge@candycutz.com
MAIL_FROM_NAME="CandyCutz Grooming"

# Expo Push Notifications
EXPO_ACCESS_TOKEN=...
```

---

## 4. Production Deployment Procedures

### 4.1 Backend Deployment Pipeline
```bash
# 1. Enter application directory
cd /var/www/candycutz/barbing-saloon-api

# 2. Pull latest release
git pull origin main

# 3. Install composer dependencies (optimized, no dev)
composer install --no-dev --optimize-autoloader

# 4. Run database migrations
php artisan migrate --force

# 5. Cache configurations, routes, and events
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 6. Restart queue workers
php artisan queue:restart

# 7. Reload PHP-FPM
sudo systemctl reload php8.2-fpm
```

### 4.2 Web Client Build & Sync
```bash
cd /var/www/candycutz/barbing-saloon-web
npm ci
npm run build
# Dist directory served by Nginx root
```

### 4.3 Scheduler (Cron Configuration)
Add to system crontab (`crontab -e`):
```cron
* * * * * cd /var/www/candycutz/barbing-saloon-api && php artisan schedule:run >> /dev/null 2>&1
```
The scheduler executes:
- 2-hour appointment reminder email & push alerts.
- Expired slot lock releases (every 5 minutes).
- Public cache pruning.
