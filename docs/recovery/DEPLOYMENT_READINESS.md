# CANDYCUTZ — DEPLOYMENT READINESS & PRODUCTION RUNBOOK

**Date:** 2026-10-23  
**Status:** Ready for Single-VPS Staging & Production Deployment  
**Architecture:** Single VPS running Nginx, PHP 8.2-FPM, MySQL 8, Redis, Supervisor, and Queue Workers  
**Reference:** `ARCHITECTURE.md` §10, §11  

---

## 1. PRODUCTION ENVIRONMENT PREREQUISITES

### 1.1 VPS Host Requirements
- **OS:** Ubuntu 22.04 LTS (x86_64 or arm64)
- **CPU / RAM:** Minimum 2 vCPU, 4GB RAM (8GB recommended for production with Redis)
- **Storage:** 40GB+ NVMe SSD
- **Network Ports:** `80` (HTTP -> HTTPS redirect), `443` (HTTPS TLS 1.3)
- **Firewall (UFW):**
  - Allow `22` (SSH with key-based authentication only)
  - Allow `80`, `443`
  - Deny `3306` (MySQL), `6379` (Redis) from public ingress

### 1.2 Required Software Packages
```bash
sudo apt update && sudo apt install -y \
  nginx \
  php8.2-fpm php8.2-cli php8.2-mysql php8.2-redis php8.2-mbstring \
  php8.2-xml php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath \
  mysql-server \
  redis-server \
  supervisor \
  certbot python3-certbot-nginx
```

---

## 2. PRODUCTION NGINX CONFIGURATION

Single Nginx server block routing requests to the Laravel API and serving the Vue 3 SPA:

```nginx
server {
    listen 80;
    server_name candycutz.ng api.candycutz.ng;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name candycutz.ng;

    ssl_certificate /etc/letsencrypt/live/candycutz.ng/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/candycutz.ng/privkey.pem;

    root /var/www/candycutz/barbing-saloon-web/dist;
    index index.html;

    # Gzip / Brotli Compression
    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml text/javascript;

    # Vue SPA HTML5 History Mode
    location / {
        try_files $uri $uri/ /index.html;
    }

    # API Proxy to Laravel
    location /api/ {
        proxy_pass http://127.0.0.1:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

    # Storage Media Proxy
    location /storage/ {
        proxy_pass http://127.0.0.1:8000;
        proxy_set_header Host $host;
    }
}

server {
    listen 443 ssl http2;
    server_name api.candycutz.ng;

    ssl_certificate /etc/letsencrypt/live/api.candycutz.ng/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/api.candycutz.ng/privkey.pem;

    root /var/www/candycutz/barbing-saloon-api/public;
    index index.php;

    client_max_body_size 10M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

---

## 3. SUPERVISOR DAEMON SUPERVISION

### 3.1 Laravel Queue Worker (`/etc/supervisor/conf.d/candycutz-worker.conf`)
```ini
[program:candycutz-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/candycutz/barbing-saloon-api/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/supervisor/candycutz-worker.log
stopwaitsecs=3600
```

### 3.2 Cron / Task Scheduler (`crontab -u www-data -e`)
```cron
* * * * * cd /var/www/candycutz/barbing-saloon-api && php artisan schedule:run >> /dev/null 2>&1
```

---

## 4. DEPLOYMENT & VERIFICATION CHECKLIST

1. **Database Migrations:**
   ```bash
   cd /var/www/candycutz/barbing-saloon-api
   php artisan migrate --force
   ```
2. **Framework Optimization:**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   php artisan event:cache
   ```
3. **Web Production Build:**
   ```bash
   cd /var/www/candycutz/barbing-saloon-web
   npm ci
   npm run build
   ```
4. **Mobile Client Release (EAS Build):**
   ```bash
   cd /var/www/candycutz/MOBILE
   eas build --platform android --profile production
   eas build --platform ios --profile production
   ```
5. **Liveness Verification:**
   ```bash
   curl -I https://api.candycutz.ng/api/v1/health
   # Expected: HTTP/2 200 OK
   curl -I https://api.candycutz.ng/api/v1/health/ready
   # Expected: HTTP/2 200 OK
   ```
