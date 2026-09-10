# Candycutz — Production Release & Operations Handbook

## 1. Executive Summary
This document provides the definitive step-by-step procedures for building and submitting the Candycutz unified mobile application to the Apple App Store and Google Play Store, managing the single-VPS Docker production environment, executing zero-downtime deployments, and operating disaster recovery drills.

---

## 2. Mobile App Store Production Release (EAS)

### 2.1 Pre-Flight Prerequisites
1. Ensure the Expo CLI and EAS CLI are installed:
   ```bash
   npm install -g eas-cli
   eas --version  # Must be >= 12.0.0
   ```
2. Authenticate with the authoritative Expo organization account:
   ```bash
   eas login
   eas whoami
   ```
3. Verify project configuration in `candycutz-mobile-app`:
   - `app.json`: `bundleIdentifier: "com.candycutz.app"`, `package: "com.candycutz.app"`, `version: "1.0.0"`.
   - `eas.json`: Valid `build.production` profile with `app-bundle` for Android and non-simulator archive for iOS.

---

### 2.2 Building Production Binaries
In the `candycutz-mobile-app/` directory:

#### Android App Bundle (.aab)
```bash
# Triggers remote cloud build with automatic keystore generation
eas build --platform android --profile production
```
- **Output**: Android App Bundle (`.aab`) optimized for Google Play dynamic feature delivery and size compression.
- **Local Download**: The EAS build link will output a `.aab` download link once build completes.

#### iOS Production Archive
```bash
# Triggers Apple distribution build with automatic provisioning profiles
eas build --platform ios --profile production
```
- **Output**: Signed `.ipa` archive pushed directly to Apple Transporter / App Store Connect.

#### Parallel Cross-Platform Build
```bash
eas build --platform all --profile production
```

---

### 2.3 Store Review Test Credentials
Both Apple and Google review teams require functional test credentials to test role-based authentication and services. Provide these details in the **App Review Information** fields:

| Field | Value |
|---|---|
| **Demo Customer Account** | `test.customer@candycutz.com` / `Password123!` |
| **Demo Barber Account** | `test.barber@candycutz.com` / `Password123!` |
| **Reviewer Notes** | "CandyCutz is a commercial grooming platform in Keffi, Nigeria. Grooming appointments are strictly in-person physical salon services or home mobile visits exempt from In-App Purchase commissions under Apple Guideline 3.1.5(a). In-app account deactivation is accessible via Profile > Security." |
| **Support URL** | `https://candycutz.com/contact` |
| **Privacy Policy URL** | `https://candycutz.com/privacy` |
| **Account Deletion URL** | `https://candycutz.com/account-deletion` |

---

### 2.4 Submitting to App Stores

#### Google Play Store
```bash
# Uploads .aab to the Internal Testing track (or Production track)
eas submit -p android --profile production
```

#### Apple App Store (TestFlight)
```bash
# Uploads archive directly to App Store Connect TestFlight
eas submit -p ios --profile production
```

---

## 3. Single-VPS Production Deployment & Updates

### 3.1 Initial Machine Provisioning
On the Ubuntu 22.04 LTS VPS (Host: `138.68.x.x` or domain `candycutz.com`):

1. **Install Docker Engine & Docker Compose V2**:
   ```bash
   curl -fsSL https://get.docker.com | sh
   sudo usermod -aG docker $USER
   sudo systemctl enable docker
   ```

2. **Clone the Repository**:
   ```bash
   git clone https://github.com/Jay-kod/candycutz.git /var/www/candycutz
   cd /var/www/candycutz
   ```

3. **Configure Environment Secrets**:
   ```bash
   cp .env.example .env
   # Edit production keys: APP_KEY, DB_PASSWORD, REDIS_PASSWORD, STRIPE_SECRET, BREVO_KEY
   nano .env
   ```

4. **Launch Unified Production Containers**:
   ```bash
   docker compose up -d --build
   ```

5. **Initialize Database and Warming**:
   ```bash
   docker compose exec app php artisan migrate --force
   docker compose exec app php artisan db:seed --force
   docker compose exec app php artisan config:cache
   docker compose exec app php artisan route:cache
   docker compose exec app php artisan view:cache
   ```

---

### 3.2 Automated Zero-Downtime Deployments
To deploy code updates from GitHub `main`, execute the turnkey deployment script:
```bash
bash infrastructure/scripts/deploy.sh
```
This automated script sequentially performs:
1. Git pull on `main`.
2. Compiles the latest Vue 3 web client bundle (`npm run build`).
3. Executes pending database migrations (`php artisan migrate --force`).
4. Warms OPcache and Laravel configuration/route caches.
5. Gracefully restarts the background queue worker (`php artisan queue:restart`).

---

### 3.3 Database Backup & Disaster Recovery Schedule

#### Automated Daily Backup
Add the backup script to the host `crontab`:
```bash
# Run backup nightly at 02:00 UTC
0 2 * * * /var/www/candycutz/infrastructure/scripts/backup_db.sh >> /var/log/candycutz_backup.log 2>&1
```

#### Manual Database Recovery
In the event of database corruption or hardware failover:
```bash
# Syntax: ./restore_db.sh <path_to_gzipped_sql_file>
bash infrastructure/scripts/restore_db.sh /var/backups/candycutz/candycutz_db_2026-09-10_020000.sql.gz
```

---

## 4. Production Health Checks & Audit Endpoints

| Resource | URL | Expected Response | Alert Trigger |
|---|---|---|---|
| **API Healthcheck** | `https://candycutz.com/api/health` | `HTTP 200 {"status":"ok"}` | Status != ok or latency > 1500ms |
| **Web Homepage** | `https://candycutz.com/` | `HTTP 200` (HTML payload) | HTTP 5xx or SSL expiration |
| **Privacy Policy** | `https://candycutz.com/privacy` | `HTTP 200` (Vue SPA) | HTTP 404 |
| **Account Deletion** | `https://candycutz.com/account-deletion` | `HTTP 200` (Vue SPA) | HTTP 404 |
| **Redis Health** | `docker exec candycutz-redis redis-cli ping` | `PONG` | Connection refused |
| **Queue Worker** | `docker exec candycutz-worker ps aux` | `php artisan queue:work` running | Process absent |
