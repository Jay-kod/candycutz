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

## Known Broken — Security Defects

Each defect is referenced from `ARCHITECTURE.md` §5 with verified file:line locations.

### 1. Password Exposure
`User` model has **no `$hidden` property**. Password hashes, `remember_token`, and `provider_id` are included in any JSON serialization.
- **File:** [`app/Models/User.php`](file:///c:/xampp/htdocs/1/candycutz/barbing-saloon-api/app/Models/User.php) — no `$hidden` declaration exists
- **Severity:** Critical — leaks credentials to any authenticated client

### 2. Global `strip_tags()` Destroys CMS Content
`SanitizeInput` middleware runs `strip_tags()` on **all** request input, silently stripping HTML from TipTap editor content (blog posts, CMS pages).
- **File:** [`app/Core/Http/Middleware/SanitizeInput.php:25`](file:///c:/xampp/htdocs/1/candycutz/barbing-saloon-api/app/Core/Http/Middleware/SanitizeInput.php#L25) — `$input[$key] = trim(strip_tags($value))`
- **Registered at:** [`bootstrap/app.php:17`](file:///c:/xampp/htdocs/1/candycutz/barbing-saloon-api/bootstrap/app.php#L17)
- **Applied at:** [`routes/api.php:9`](file:///c:/xampp/htdocs/1/candycutz/barbing-saloon-api/routes/api.php#L9)
- **Severity:** High — production data corruption

### 3. CORS: Hardcoded Private IP Addresses
CORS config includes hardcoded private IP `10.252.94.238` (dev machine).
- **File:** [`config/cors.php:14-15`](file:///c:/xampp/htdocs/1/candycutz/barbing-saloon-api/config/cors.php#L14-L15)
- **Also:** `supports_credentials: true` at [`config/cors.php:27`](file:///c:/xampp/htdocs/1/candycutz/barbing-saloon-api/config/cors.php#L27)
- **Severity:** Medium — overly permissive CORS with credential support

### 4. Token Lifetime: Never Expires
No `sanctum.expiration` config file found. Tokens issued to customers, barbers, and admins persist indefinitely.
- **File:** No `config/sanctum.php` published (using package defaults)
- **Severity:** High — stolen tokens are permanent

### 5. Mass Assignment: Privilege Fields in `$fillable`
`User::$fillable` includes `role`, `status`, `is_active` — allowing any request to escalate privileges via mass assignment.
- **File:** [`app/Models/User.php:18-30`](file:///c:/xampp/htdocs/1/candycutz/barbing-saloon-api/app/Models/User.php#L18-L30) — `$fillable` includes `'role'`, `'status'`, `'is_active'`
- **Also:** `array_merge($request->all(), $validated)` at [`AppointmentApiController.php:115`](file:///c:/xampp/htdocs/1/candycutz/barbing-saloon-api/app/Core/Http/Controllers/Api/V1/AppointmentApiController.php#L115) and [`:212`](file:///c:/xampp/htdocs/1/candycutz/barbing-saloon-api/app/Core/Http/Controllers/Api/V1/AppointmentApiController.php#L212)
- **Severity:** Critical — unauthenticated privilege escalation vector

### 6. Upload Mishandling
`HasSecureUploads` trait names every uploaded file `.webp` regardless of actual MIME type. No re-encoding, no EXIF stripping.
- **File:** [`app/Core/Traits/HasSecureUploads.php:26`](file:///c:/xampp/htdocs/1/candycutz/barbing-saloon-api/app/Core/Traits/HasSecureUploads.php#L26) — `$fileName = Str::uuid() . '-' . time() . '.webp'`
- **Severity:** Medium — potential for malicious file upload

### 7. Payment Receipts on Public Disk
Receipt uploads use `public_path('uploads/receipts/')` — world-readable without authentication.
- **Files:** [`CustomerController.php:240`](file:///c:/xampp/htdocs/1/candycutz/barbing-saloon-api/app/Modules/Customer/Controllers/CustomerController.php#L240), [`ReceiptApiController.php:28`](file:///c:/xampp/htdocs/1/candycutz/barbing-saloon-api/app/Core/Http/Controllers/Api/V1/ReceiptApiController.php#L28)
- **Severity:** Medium — financial documents exposed publicly

### 8. Super Admin Bypass Without Audit
`CheckRole` middleware unconditionally passes `super_admin` requests without logging.
- **File:** [`app/Core/Http/Middleware/CheckRole.php:25`](file:///c:/xampp/htdocs/1/candycutz/barbing-saloon-api/app/Core/Http/Middleware/CheckRole.php#L25) — `if ($role === 'super_admin')`
- **Severity:** High — privileged access leaves no trail

### 9. Webhook Behind Throttle
The Stripe webhook endpoint is behind `throttle:120,1`, which drops legitimate gateway retry attempts.
- **File:** [`routes/api.php:9`](file:///c:/xampp/htdocs/1/candycutz/barbing-saloon-api/routes/api.php#L9) — `middleware(['throttle:120,1', ...])`
- **Severity:** Medium — payment confirmation failures under load

### 10. Unused `spatie/laravel-permission`
Installed as a dependency with **zero usages** in `app/`. Security-adjacent dead weight.
- **File:** [`composer.json:9`](file:///c:/xampp/htdocs/1/candycutz/barbing-saloon-api/composer.json#L9) — `"spatie/laravel-permission": "^6.0"`
- **Severity:** Low — unused attack surface

---

## Known Broken — Structural Defects

### Duplicate API Mount Points
Routes are mounted twice: `/api/v1/*` and `/api/*` (alias).
- **File:** [`routes/api.php:9,13`](file:///c:/xampp/htdocs/1/candycutz/barbing-saloon-api/routes/api.php#L9-L13)

### Duplicate Controllers
Appointment cancel reachable at 4 paths across 2 controllers. Only `CustomerController` calls `Gate::authorize`.

### Raw SQL in Migrations
9 `.sql` files coexist with 30 PHP migration classes in `database/migrations/`. `migrate:fresh` does not reproduce production schema.

### No Test Harness
No automated tests. `phpunit.xml` and Pest scaffolding exist but no characterisation tests. The `PROJECT_STATE.md` claim of "277-assertion Master Test Matrix (100% PASS)" was false.
