# Candycutz — Single-VPS Production Deployment Specification

## 1. Infrastructure Mission & Philosophy

Candycutz is deployed on **ONE VPS**. It deliberately avoids premature complexity:
- **No Kubernetes**: Docker Compose provides simple, predictable container orchestration.
- **No Multi-Cloud Microservices**: All backend processes run as co-located services on a high-speed internal Docker network (`candycutz-net`).
- **No Complex Load Balancers**: Nginx runs natively inside Docker as the high-performance reverse proxy and SSL terminator.
- **Non-DevOps Maintainable**: Operations are fully scriptable with standard Docker commands and straightforward backup/restore runbooks.

---

## 2. Target Hardware & VPS Specifications

| Specification | Minimum Required | Recommended Production |
|---|---|---|
| **Cloud Provider** | Hetzner / DigitalOcean / Linode / AWS EC2 | Hetzner Cloud (CPX21) or DigitalOcean Droplet |
| **OS** | Ubuntu 22.04 LTS x86_64 | Ubuntu 22.04 LTS x86_64 |
| **vCPUs** | 2 vCPUs | 4 vCPUs |
| **Memory (RAM)** | 4 GB | 8 GB |
| **Storage (SSD)** | 40 GB NVMe | 80 GB NVMe |
| **Bandwidth** | 1 TB/month | 2 TB/month |
| **Static IP** | 1 Dedicated IPv4 + IPv6 | 1 Dedicated IPv4 + IPv6 |

---

## 3. Production Container Topology

```text
Host VPS (Ubuntu 22.04 LTS)
│
├── Nginx Container (candycutz-nginx)
│   ├── Listens on Ports 80 and 443
│   ├── SSL Termination (Certbot / Let's Encrypt automated renewal)
│   ├── Reverse proxies /api/* -> candycutz-app:9000 (FastCGI)
│   └── Serves static pre-built Vue 3 files directly from /var/www/web/dist
│
├── App Container (candycutz-app)
│   ├── PHP 8.2-FPM with OPcache & required extensions (pdo_mysql, redis, bcmath, gd)
│   ├── Executes Laravel 11 API Kernel
│   └── Mounts shared storage volume (/var/www/storage)
│
├── Database Container (candycutz-db)
│   ├── MySQL 8.0 Server with InnoDB engine
│   ├── Persistent data stored in host-mounted volume (db-data)
│   └── Configured for UTF8MB4 collation
│
├── Cache Container (candycutz-redis)
│   ├── Redis 7.2 In-Memory Key-Value Store
│   └── Provides caching, session handling, and queue brokering
│
├── Queue Worker Container (candycutz-worker)
│   ├── Background worker running `php artisan queue:work --tries=3 --timeout=90`
│   └── Asynchronously processes Brevo emails and Expo push notifications
│
└── Scheduler Container (candycutz-scheduler)
    └── Executes `php artisan schedule:run` every minute for appointment reminders
```

---

## 4. Production Domain & DNS Mapping

| Domain / Subdomain | Target | Destination in Docker |
|---|---|---|
| `candycutz.com` | VPS IPv4 | Nginx -> Static Vue 3 Web Dist (Customer Experience) |
| `www.candycutz.com` | CNAME -> `candycutz.com` | Nginx -> 301 Redirect to `https://candycutz.com` |
| `admin.candycutz.com` | VPS IPv4 | Nginx -> Static Vue 3 Web Dist (Admin Route Entry) |
| `api.candycutz.com` | VPS IPv4 | Nginx -> FastCGI Proxy to `candycutz-app:9000` |

---

## 5. Security & Hardening Checklist

1. **Firewall (UFW)**:
   - Only allow ports `22` (SSH), `80` (HTTP), and `443` (HTTPS).
   - MySQL (`3306`) and Redis (`6379`) are bound **exclusively to the internal Docker network** and are never exposed to public internet.
2. **Fail2ban**:
   - Installed on the VPS host to block brute-force SSH attempts.
3. **Secret Isolation**:
   - Production secrets are stored in `/var/www/candycutz/.env` with `chmod 600` permissions owned by `www-data`.
   - Never commit `.env` to Git.
4. **Automated SSL**:
   - Nginx uses Let's Encrypt certificates managed via Certbot with automatic 60-day renewal.
