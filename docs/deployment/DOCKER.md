# Candycutz — Docker Architecture & Operations Manual

## 1. Container Topology Overview

The production Docker Compose definition orchestrates five co-located services on the internal `candycutz-net` bridge network:

```text
┌─────────────────────────────────────────────────────────────┐
│                       candycutz-net                         │
│                                                             │
│   [candycutz-nginx] (Ports 80, 443)                         │
│          │                                                  │
│          ├──► [candycutz-app] (PHP-FPM:9000)                │
│          │          │                                       │
│          │          ├──► [candycutz-db] (MySQL:3306)        │
│          │          │                                       │
│          │          └──► [candycutz-redis] (Redis:6379)     │
│          │                     ▲                            │
│   [candycutz-worker] ──────────┘                            │
│   (queue:work --tries=3)                                    │
│                                                             │
│   [candycutz-scheduler] (cron: schedule:run)                │
└─────────────────────────────────────────────────────────────┘
```

---

## 2. Service Definitions

### 1. `candycutz-nginx`
- **Image**: `nginx:1.25-alpine`
- **Purpose**: Terminates SSL certificates, serves static web assets directly, caches images, and proxies `/api/*` to PHP-FPM via FastCGI.
- **Exposed Ports**: `80` (HTTP), `443` (HTTPS).

### 2. `candycutz-app`
- **Build**: `./backend/laravel/Dockerfile` (PHP 8.2-FPM Alpine)
- **Extensions**: `pdo_mysql`, `redis`, `bcmath`, `mbstring`, `opcache`, `gd`.
- **Purpose**: Executes Laravel 11 HTTP requests.

### 3. `candycutz-db`
- **Image**: `mysql:8.0`
- **Purpose**: Primary transactional database.
- **Volume**: `db-data:/var/lib/mysql` (persisted on host filesystem).
- **Environment**: UTF8MB4 character set and collation.

### 4. `candycutz-redis`
- **Image**: `redis:7.2-alpine`
- **Purpose**: In-memory cache, session management, and queue message broker.
- **Persistence**: AOF (Append Only File) enabled for durability.

### 5. `candycutz-worker`
- **Build**: Shares identical Dockerfile with `app`.
- **Command**: `php artisan queue:work redis --queue=default,emails,push --tries=3 --timeout=90`
- **Purpose**: Asynchronous processing of transactional emails, reminders, and push notifications.

### 6. `candycutz-scheduler`
- **Build**: Shares identical Dockerfile with `app`.
- **Command**: Periodic loop executing `php artisan schedule:run` every 60 seconds.

---

## 3. Essential Operational Commands

### Starting & Stopping
```bash
# Start all services in the background
docker compose up -d

# Stop all services safely (graceful shutdown)
docker compose down

# Restart all services
docker compose restart

# Rebuild containers after code changes or Dockerfile edits
docker compose up -d --build
```

### Viewing Logs
```bash
# Tail logs across all services
docker compose logs -f

# View only backend application logs
docker compose logs -f app

# View only queue worker logs
docker compose logs -f worker

# View Nginx access & error logs
docker compose logs -f nginx
```

### Database Operations
```bash
# Run pending database migrations
docker compose exec app php artisan migrate --force

# Roll back the last migration step
docker compose exec app php artisan migrate:rollback

# Run database seeders
docker compose exec app php artisan db:seed --class=KeffiOperationsSeeder --force

# Access MySQL interactive CLI
docker compose exec db mysql -u candycutz -p candycutz_db
```

### Cache & Optimization
```bash
# Clear all application caches
docker compose exec app php artisan optimize:clear

# Warm route, config, and view caches for maximum production speed
docker compose exec app php artisan optimize

# Restart queue worker after code deployment
docker compose exec app php artisan queue:restart
```
