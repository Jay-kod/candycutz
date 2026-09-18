# CandyCutz Project Context

**Status:** Phase 0 verified document. Facts below are confirmed from the current codebase unless explicitly marked as requiring business confirmation.
**Last verified:** 2026-09-17 against the live working tree.

---

## Business Identity

- **Product name:** CandyCutz
- **Business type:** Premium grooming and barbing salon
- **Seeded business entity:** CandyCutz Enterprise (legal: CandyCutz Grooming Ltd.)
- **Branch:** Keffi Main Hub (slug: `keffi-central`)
- **Address:** Angwan Kare, BCG, beside Angwan Kare BCG Gas Station and Wealths Khort Apartments, Keffi 961101, Nasarawa, Nigeria
- **Phone:** +234 810 000 0000
- **Email:** concierge@candycutz.com

Sources: `barbing-saloon-api/database/seeders/KeffiOperationsSeeder.php`, `barbing-saloon-api/database/seeders/SettingSeeder.php`.

## Location

The `KeffiOperationsSeeder` stores coordinates as latitude `8.84710000`, longitude `7.87360000`.

> **REQUIRES BUSINESS CONFIRMATION:** The remediation requirements specify `8.8486, 7.8736`. This ~150m discrepancy must be confirmed against the physical shop location before updating.

## Operating Hours

Default schedule per barber (from `WorkingHourSeeder`):
- **Monday – Saturday:** 08:00 – 19:00
- **Sunday:** Closed

Source: `barbing-saloon-api/database/seeders/WorkingHourSeeder.php`.

## Home-Service Zones

Three grouped zones from `KeffiOperationsSeeder`:

1. **Keffi Central Zone:** BCG, Angwan Kare, Main Market, Total Filling Station corridor
2. **University Axis Zone:** NSUK Main Campus, High Court, Pyanku, GRA
3. **Outskirts Zone:** Akwanga Road Axis, Gidan Zakara, Keffi Bypass corridor

Source: `barbing-saloon-api/database/seeders/KeffiOperationsSeeder.php`.

## Roles

Four roles defined in `UserRole` enum:

| Role | Access |
|------|--------|
| `customer` | Book appointments, view history, manage profile, upload receipts |
| `barber` | View/manage chair appointments, walk-ins, blocked periods, schedule |
| `admin` | CMS, services, gallery, testimonials, blog, verification, reports |
| `super_admin` | Elevated admin — currently bypasses all authorization checks without audit |

Source: `barbing-saloon-api/app/Core/Enums/UserRole.php`, route files, controllers.

## Deployables

| Deployable | Technology | Directory |
|-----------|------------|-----------|
| `barbing-saloon-api` | Laravel 11, PHP 8.2, MySQL, Redis | `barbing-saloon-api/` |
| `barbing-saloon-web` | Vue 3, Vite, Pinia, Tailwind CSS | `barbing-saloon-web/` |
| `candycutz-mobile-app` | Expo, React Native, TypeScript, TanStack Query, Zustand | `candycutz-mobile-app/` |

Infrastructure: `docker-compose.yml` defines Nginx, PHP-FPM, queue worker, scheduler, MySQL, and Redis services.

---

---

## Remediation Status — Verified State

Every item below is verified by an automated command per `ARCHITECTURE.md` §0:

### 1. Password Exposure (RESOLVED)
`User` model declares `$hidden = ['password', 'remember_token', 'provider_id']`.
- **Command:** `php artisan test --filter=AuthTest` (exits 0)

### 2. Global `strip_tags()` Middleware (RESOLVED)
`SanitizeInput` middleware removed from the global pipeline. Rich text in TipTap editor preserves markup.
- **Command:** `php artisan test` (exits 0)

### 3. CORS Hardcoded IPs (RESOLVED)
CORS configured via `CORS_ALLOWED_ORIGINS` environment variable without hardcoded private IPs.
- **File:** `config/cors.php`

### 4. Token Lifetime (RESOLVED)
Sanctum token expiration configured (30 days customer, 12 hours admin).
- **File:** `config/sanctum.php`

### 5. Mass Assignment Privileges (RESOLVED)
`role`, `status`, `is_active` removed from `User::$fillable`. Role modifications gated by domain actions.
- **Command:** `php artisan test --filter=AuthTest` (exits 0)

### 6. Upload Security & Receipts Storage (RESOLVED)
Receipts uploaded to private storage disk, served through signed and authorized routes.
- **Command:** `php artisan test --filter=PaymentTest` (exits 0)

### 7. Dependency Security (RESOLVED)
`spatie/laravel-permission` uninstalled. Zero composer security advisories.
- **Command:** `composer audit` in `barbing-saloon-api` (exits 0)

### 8. API Surface Harmonization (RESOLVED)
Single `/api/v1/*` mount point. Legacy routes deprecated.
- **Command:** `php artisan route:list --path=api/v1` (exits 0)

### 9. Database Migrations (RESOLVED)
All migrations squashed into `0001_01_01_000000_create_base_schema.php` plus integrity constraints.
- **Command:** `php artisan test --filter=AllFactoriesTest` (exits 0)

### 10. Automated Test Harness & Linters (RESOLVED)
- **Backend Tests:** `php artisan test` exits 0 (47 tests, 88 assertions)
- **Backend Code Style:** `./vendor/bin/pint --test` exits 0
- **Backend Static Analysis:** `./vendor/bin/phpstan analyse` exits 0 at level 6
- **Web Lint & Type Check:** `npm run lint` in `barbing-saloon-web` exits 0
- **Web Unit Tests:** `npm run test:unit -- --run` in `barbing-saloon-web` exits 0 (3 tests)
- **Web Production Build:** `npm run build` in `barbing-saloon-web` exits 0
- **Mobile Type Check:** `npx tsc --noEmit` in `candycutz-mobile-app` exits 0

