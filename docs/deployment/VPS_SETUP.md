# Candycutz — Turnkey VPS Provisioning & Setup Runbook

## 1. Initial VPS Provisioning (Ubuntu 22.04 LTS)

Connect to your freshly provisioned VPS via SSH:
```bash
ssh root@<YOUR_VPS_IP>
```

### 1.1 System Updates & Basic Packages
```bash
apt-get update && apt-get upgrade -y
apt-get install -y curl git ufw fail2ban unzip htop certbot python3-certbot-nginx
```

### 1.2 Firewall Hardening (UFW)
```bash
ufw default deny incoming
ufw default allow outgoing
ufw allow 22/tcp
ufw allow 80/tcp
ufw allow 443/tcp
ufw --force enable
ufw status verbose
```

### 1.3 Install Docker Engine & Docker Compose
```bash
curl -fsSL https://get.docker.com -o get-docker.sh
sh get-docker.sh
usermod -aG docker ubuntu 2>/dev/null || true
docker --version
docker compose version
```

---

## 2. Project Deployment

### 2.1 Clone Repository
```bash
mkdir -p /var/www
cd /var/www
git clone https://github.com/Jay-kod/candycutz.git
cd /var/www/candycutz
```

### 2.2 Configure Production Environment File
```bash
cp .env.example .env
nano .env
```
Ensure the following production values are configured:
```ini
APP_NAME=CandyCutz
APP_ENV=production
APP_KEY=base64:GENERATE_VIA_COMMAND_BELOW
APP_DEBUG=false
APP_URL=https://api.candycutz.com
FRONTEND_URL=https://candycutz.com

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=candycutz_db
DB_USERNAME=candycutz
DB_PASSWORD=YOUR_STRONG_DATABASE_PASSWORD_HERE

REDIS_HOST=redis
REDIS_PORT=6379
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

STRIPE_KEY=pk_live_...
STRIPE_SECRET=sk_live_...
STRIPE_WEBHOOK_SECRET=whsec_...

MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=your_brevo_smtp_login
MAIL_PASSWORD=your_brevo_smtp_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=concierge@candycutz.com
MAIL_FROM_NAME="CandyCutz Luxury Grooming"
```

---

## 3. Build & Orchestration

### 3.1 Build Frontend Static Assets
```bash
cd /var/www/candycutz/barbing-saloon-web
npm install
npm run build
cd /var/www/candycutz
```

### 3.2 Start Docker Services
```bash
docker compose up -d --build
```

### 3.3 Initialize Laravel Backend
```bash
# Generate unique encryption key
docker compose exec app php artisan key:generate

# Run production database migrations
docker compose exec app php artisan migrate --force

# Seed baseline business operational records (Keffi flagship)
docker compose exec app php artisan db:seed --class=KeffiOperationsSeeder --force

# Create public storage symlink
docker compose exec app php artisan storage:link

# Optimize Laravel routes, configs, and views
docker compose exec app php artisan optimize
```

---

## 4. SSL Certificate Setup (Let's Encrypt)

```bash
certbot certonly --standalone -d candycutz.com -d www.candycutz.com -d api.candycutz.com -d admin.candycutz.com
```
Certbot will issue certificates into `/etc/letsencrypt/live/candycutz.com/`, which are mounted into the Nginx container.

---

## 5. Verification & Sanity Check

```bash
# Verify container health
docker compose ps

# Check API health endpoint
curl -I https://api.candycutz.com/api/health

# Inspect live application logs
docker compose logs -f app
```
All services should return `HTTP/2 200` with clean JSON responses.
