# Candycutz — Comprehensive System Audit & Recovery Architecture
**Document Version**: 1.0.0-recovery  
**Date**: September 2026  
**Auditor**: Senior Software Architect & Multi-Disciplinary Engineering Team  
**Scope**: Full Repository Forensic Inspection (`barbing-saloon-api`, `barbing-saloon-web`, `candycutz-customer-app`, `candycutz-barber-app`, Infrastructure & Database)

---

## Executive Summary

Candycutz is a commercial grooming platform operating in Keffi, Nasarawa State, Nigeria. The platform was designed to serve three user cohorts (Customers, Barbers, and Super Admin) across web and mobile surfaces powered by a central backend. 

A forensic audit of the entire repository reveals that the system currently suffers from **architectural fragmentation, conflicting authentication models, inconsistent route contracts, duplicated domain logic, and a divergence between mobile and web clients**. 

Crucially, **two separate Expo mobile applications were created (`candycutz-customer-app` and `candycutz-barber-app`)**, violating the core requirement of a unified, role-based Expo mobile client. Furthermore, the web client and mobile clients call different API endpoints for identical operations (e.g., `/public/services` vs `/services`), and authentication tokens frequently invalidate one another due to destructive session handling (`$user->tokens()->delete()`).

This document provides the definitive forensic audit of the platform, answers all 30 foundational architectural questions, and defines the Phase-by-Phase Recovery Plan to stabilize Candycutz into a production-grade, single-VPS platform.

---

## 1. System Technology Stack Baseline

| Component | Technology | Version | Active Location / Configuration |
|---|---|---|---|
| **Runtime / OS** | Windows / Linux VPS Target | PHP 8.2.12 / Node v22.15.0 | XAMPP local / Docker Ubuntu 22.04 target |
| **Backend Framework** | Laravel | 11.0 (`laravel/framework: ^11.0`) | `barbing-saloon-api/` |
| **Database** | MySQL | 8.0 / MariaDB 10.4 | `candycutz_db` (XAMPP Port 3306) |
| **API Authentication** | Laravel Sanctum | ^4.0 | Bearer Tokens (`personal_access_tokens`) |
| **Permissions / RBAC** | Spatie Laravel Permission | ^6.0 + Enums | `App\Core\Enums\UserRole` (`customer`, `barber`, `admin`, `super_admin`) |
| **Web Client** | Vue 3 + Vite | Vue 3.5.0, Vite 8.0.16 | `barbing-saloon-web/` (SPA via Pinia, Vue Router 4.4, TailwindCSS 3.4) |
| **Mobile Apps (Current)** | React Native / Expo | Expo SDK 51 (~57.0.20), React 19.2.3, RN 0.86.3 | Fragmented into two separate apps: `candycutz-customer-app/` and `candycutz-barber-app/` |
| **Mobile Navigation** | Expo Router | ~57.0.19 | File-based routing (`app/` directory) |
| **Mobile State / API** | TanStack Query + Zustand + Axios | TanStack Query v5.28, Zustand v4.5.2 | `expo-secure-store` for token persistence |
| **Payments** | Stripe (Partial Mock) | Custom Provider | `App\Services\Payment\StripePaymentProvider` (`stripe/stripe-php` missing from `composer.json`) |
| **Transactional Email** | Brevo / Laravel Mail | SMTP / Blade Templates | `App\Services\Notification\BrevoNotificationAdapter` (Driver: `log` in `.env`) |
| **Infrastructure** | Docker Compose | Version 3.9 (Incomplete) | `docker-compose.yml` (MySQL + PHP-CLI + Vite Dev Server; lacks Nginx, Redis, Queue worker) |

---

## 2. Detailed Architectural Audit

### 2.1 Current Architecture & System Diagram
```
                              [CLIENTS]
                                  │
         ┌────────────────────────┼────────────────────────┐
         ▼                        ▼                        ▼
  Vue 3 Web Client         Customer App (Expo)      Barber App (Expo)
(barbing-saloon-web)    (candycutz-customer-app)  (candycutz-barber-app)
   Port 5173/5174            Port 8081 / LAN           Port 8082 / LAN
         │                        │                        │
         │ Calls /api/*           │ Calls /api/v1/*        │ Calls /api/v1/*
         │ (expects public/cust)  │ (expects RESTful)      │ (expects barber/sched)
         │                        │                        │
         └────────────────────────┼────────────────────────┘
                                  ▼
                   ┌─────────────────────────────┐
                   │   Laravel 11 Backend API    │
                   │    (barbing-saloon-api)     │
                   │        Port 8000            │
                   └──────────────┬──────────────┘
                                  │
                 ┌────────────────┴────────────────┐
                 ▼                                 ▼
         MySQL Database                     Local Filesystem
         (candycutz_db)                  (storage/app/public)
```

### 2.2 Current Website Architecture (`barbing-saloon-web`)
- **Structure**: Modular Single Page Application (SPA) using Vue 3 Composition API (`<script setup>`).
- **Module Partitioning**: Grouped under `src/modules/`: `admin/`, `auth/`, `barber/`, `customer/`, `public/`, `superadmin/`.
- **Router**: `src/router/index.js` concatenates routes from all modules into one flat list using HTML5 history mode (`createWebHistory()`).
- **Bootstrap Disconnect**: In `src/main.js`, `await auth.fetchUser()` is invoked before `app.use(router)` and `registerRouteGuards(router)`. If the backend is slow or returns 401, the bootstrap hangs or prematurely triggers fallback routing.
- **Axios Hardcoded Redirects**: In `src/core/api/axios.js`, any 401 response from non-auth endpoints executes:
  ```javascript
  window.location.href = '/customer/login';
  ```
  This creates a catastrophic user experience: if an Admin or Barber session expires or encounters a transient 401, they are forcefully redirected via full page reload to the *Customer* login page rather than their respective portal login (`/admin/login` or `/barber/login`).
- **API Base URL**: `import.meta.env.VITE_API_BASE_URL` is configured as `/api` in `barbing-saloon-web/.env`. It communicates via relative paths when served behind the same origin, or proxies to port 8000.

### 2.3 Current Mobile Architecture (`candycutz-customer-app` & `candycutz-barber-app`)
- **The Dual-App Fracture**: Contrary to Section 2 and Section 15 of the target specification ("ONE EXPO MOBILE APP with role-based navigation"), two independent Expo projects exist in the repository root.
  1. `candycutz-customer-app`: Contains screens for customer exploration, booking stepper, appointment list, and customer profile.
  2. `candycutz-barber-app`: Contains screens for daily chair queues, appointment check-ins, weekly schedule hours, and walk-in registration.
- **Shared Code Duplication**: Both apps independently bundle identical design tokens (`src/constants/theme.ts`), identical configuration helpers, identical icons (`lucide-react-native`), and duplicate versions of React 19.2.3 and React Native 0.86.3.
- **Route Guard Absence**: Neither app implements navigation guards. If an unauthenticated user opens either app, tabs load in an uninitialized zero-state.
- **Modal Pop Dismissal Defect**: In `candycutz-customer-app/app/auth/login.tsx`, successful login executes `router.back()`. If the user launched the app directly into the login screen (cold start), `router.back()` has no navigation stack history and fails to transition to the main customer tab.

### 2.4 Current Laravel Backend Architecture (`barbing-saloon-api`)
- **Kernel Bootstrapping**: Handled via Laravel 11's `bootstrap/app.php`. Custom procedural routing has been retired, routing standard HTTP requests to `routes/web.php` and `routes/api.php`.
- **Dual Prefixing in `ModuleServiceProvider`**:
  ```php
  Route::prefix('api')->middleware($middleware)->group($routes);
  Route::prefix('api/v1')->middleware($middleware)->group($routes);
  ```
  While this allows `/api` and `/api/v1` to hit the same controller logic, the routes registered are actor-prefixed (e.g. `/api/v1/public/services`, `/api/v1/customer/bookings`), whereas the mobile clients expect RESTful canonical domain routes (e.g. `/api/v1/services`, `/api/v1/appointments`).
- **Middleware Role Choking**:
  In `ModuleServiceProvider.php`:
  ```php
  'Auth' => ['api', 'throttle:10,1', ...],
  'Customer' => ['api', 'auth:sanctum', 'check.role:customer', ...],
  'Barber' => ['api', 'auth:sanctum', 'check.role:barber', ...],
  'Admin' => ['api', 'auth:sanctum', 'check.role:admin,super_admin', ...],
  ```
  - `throttle:10,1` on Auth routes allows only 10 requests per minute across all authentication endpoints. A single user testing login, failing password once, and requesting `auth/me` exhausts 30% of the minute budget immediately.
  - Applying `check.role:customer` to the entire Customer route module prevents Admin/Super Admin from inspecting customer appointments via standard shared endpoints.
- **Dual Service Locations**:
  Business logic is split haphazardly between:
  - `app/Services/` (`Booking/BookingService.php`, `Payment/PaymentService.php`, `Notification/BrevoNotificationAdapter.php`)
  - `app/Modules/` (`Customer/Services/CustomerService.php`, `Admin/Services/AdminService.php`, `Barber/Services/BarberService.php`)
  As an egregious example: `app/Services/Booking/BookingService.php` contains pessimistic row locking and atomic multi-service creation, but `CustomerController` calls `CustomerService::createBooking`, which completely ignores `BookingService` and uses an un-locked, legacy single-service insertion!

---

## 3. Authentication Architecture Forensic Audit

### 3.1 Website Authentication Flow
1. User enters email/identity and password on `/customer/login`, `/barber/login`, or `/admin/login`.
2. Frontend calls `POST /api/auth/login`.
3. Backend `AuthService::login` validates credentials against `users` table via `Hash::check()`.
4. **Catastrophic Token Invalidation**:
   ```php
   $user->tokens()->delete();
   $token = $user->createToken('auth-token')->plainTextToken;
   ```
   Calling `$user->tokens()->delete()` purges **ALL** Sanctum tokens across every active device. When a customer logs into the mobile app, their web session is instantly revoked. When an admin logs in on a laptop, their tablet session is instantly revoked.
5. The plain-text token is returned in `{ data: { token: "...", user: {...} } }`.
6. Web client stores token in browser `localStorage.getItem('candycutz_auth_token')`.
7. Web requests attach `Authorization: Bearer <token>`. Session cookies are bypassed (`withCredentials: false` in `axios.js`).

### 3.2 Mobile Authentication Flow
1. User logs in via `candycutz-customer-app` or `candycutz-barber-app`.
2. Client posts to `http://10.252.94.238:8000/api/v1/auth/login`.
3. Backend issues a Sanctum token (purging any web token in the process).
4. Mobile app stores token in hardware-encrypted storage via `expo-secure-store` under key `candycutz_auth_token`.
5. Mobile Axios interceptor attaches `Authorization: Bearer <token>`.

### 3.3 Social Authentication (Google & Apple) Audit
- **Frontend Status**:
  - `barbing-saloon-web` contains `useSocialAuth.js`, which renders Google Identity Services (`gsi/client`) and Apple ID JS (`appleid.auth.js`).
  - Upon receiving credentials, it posts `{ provider: 'google', id_token: '...' }` to `POST /api/auth/social-login`.
- **Backend Status**:
  - **The route `/api/auth/social-login` DOES NOT EXIST in `barbing-saloon-api`**.
  - `AuthController.php` does not have a `socialLogin()` method.
  - `composer.json` includes `firebase/php-jwt`, but it is completely unused. No JWT verification for Google or Apple public keys exists.
  - **Verdict**: Social authentication is 100% non-functional and returns HTTP 404.

### 3.4 Password Reset & Account Lifecycle Audit
- `barbing-saloon-web/src/modules/auth/api/auth.api.js` defines:
  - `forgotPassword: (email) => client.post('/auth/forgot-password', { email })`
  - `resetPassword: (data) => client.post('/auth/reset-password', data)`
- Neither `/auth/forgot-password` nor `/auth/reset-password` is registered in `app/Modules/Auth/routes.php`.
- In `candycutz-customer-app`, there is no forgot password link or screen.
- **Account Deactivation**: `users.is_active` and `users.status` exist in the database, and `AuthService::login` blocks deactivated users (`Account is currently inactive or suspended`). However, self-service deactivation routes and Super Admin restore endpoints lack standardized lifecycle event dispatching.

---

## 4. Routing Architecture Forensic Audit

### 4.1 Route Disconnect Matrix

| Intended Action | Client Making Call | URL Called | Backend Actual URL | Status / Result |
|---|---|---|---|---|
| Fetch Services | Web Client | `GET /api/public/services` | `GET /api/public/services` | **200 OK** |
| Fetch Services | Mobile Customer App | `GET /api/v1/services` | `GET /api/v1/public/services` | **404 Not Found** |
| Fetch Barbers | Web Client | `GET /api/public/barbers` | `GET /api/public/barbers` | **200 OK** |
| Fetch Barbers | Mobile Customer App | `GET /api/v1/barbers` | `GET /api/v1/public/barbers` | **404 Not Found** |
| Check Availability | Web Client | `GET /api/public/available-slots` | `GET /api/public/available-slots` | **200 OK** |
| Check Availability | Mobile Customer App | `GET /api/v1/availability` | `GET /api/public/available-slots` | **404 Not Found** |
| Create Booking | Web Client | `POST /api/customer/bookings` | `POST /api/customer/bookings` | **201 Created** |
| Create Booking | Mobile Customer App | `POST /api/v1/appointments` | `POST /api/customer/bookings` | **404 Not Found** |
| Fetch Service Zones | Mobile Customer App | `GET /api/v1/service-zones` | Not exposed in any route file | **404 Not Found** |
| Google / Apple Login | Web Client | `POST /api/auth/social-login` | Not registered | **404 Not Found** |
| Forgot Password | Web Client | `POST /api/auth/forgot-password` | Not registered | **404 Not Found** |
| Stripe Webhook | Stripe Service | `POST /api/v1/payments/webhook` | Not registered | **404 Not Found** |

### 4.2 CORS & Session Configuration Conflict
1. In `barbing-saloon-api/public/index.php`:
   ```php
   header('Access-Control-Allow-Origin: *');
   header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
   header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Idempotency-Key');
   ```
2. In `barbing-saloon-api/config/cors.php`:
   ```php
   'allowed_origins' => ['http://localhost:5173'],
   'supports_credentials' => true,
   ```
3. **The Conflict**:
   - `public/index.php` emits an uncontrolled raw PHP wildcard header (`*`).
   - If a browser sends a request with `withCredentials: true`, the W3C CORS specification forbids `Access-Control-Allow-Origin: *`. Browsers immediately block the response with a CORS violation error.
   - If `barbing-saloon-web` is served on port `5174` (or any staging/production domain), `config/cors.php` rejects the origin because only `http://localhost:5173` was whitelisted.

---

## 5. Answers to the 30 Architectural Hard Questions

### 1. Why is website authentication currently failing?
Website authentication fails intermittently due to:
- Token purge collision: Whenever the same user logs into the mobile app, the backend executes `$user->tokens()->delete()`, instantly destroying the website token. The next website action receives a 401.
- In `axios.js`, any 401 hard-redirects the browser to `/customer/login`, even for Barbers and Admins.
- Strict auth rate limiting (`throttle:10,1`) rapidly locks out users during normal page refreshes and profile fetches.
- Social authentication (`/auth/social-login`) and password resets (`/auth/forgot-password`) are completely unhandled by the backend router.

### 2. Why is mobile authentication currently failing?
Mobile authentication fails because:
- The mobile app targets hard-coded local network IPs (`http://10.252.94.238:8000/api/v1`) which change whenever the development Wi-Fi network reassigns DHCP leases.
- In `candycutz-customer-app/app/auth/login.tsx`, login completion calls `router.back()` without checking if there is a back stack, causing an unresponsive screen on direct cold-start launches.
- Registration in the mobile app submits `username`, but `RegisterRequest` in Laravel rejects or discards the username, failing validation or producing incomplete profiles.

### 3. Are web sessions and mobile API authentication incorrectly mixed?
Yes. Laravel Sanctum's configuration (`config/sanctum.php`) defines stateful domains (`localhost:5173`), but the frontend uses `localStorage` Bearer tokens with `withCredentials: false`. Simultaneously, `public/index.php` injects raw CORS headers while `config/cors.php` attempts cookie credential support (`supports_credentials => true`). The system must standardize strictly: **Bearer tokens via Sanctum for both web SPA and mobile clients**, eliminating fragile cross-domain cookie session issues entirely.

### 4. Is Sanctum configured correctly for the intended architecture?
No. Sanctum's `stateful` domain list is missing development port variations (`localhost:5174`, `127.0.0.1:5173`, `127.0.0.1:8000`), local LAN IPs, and production subdomains (`api.candycutz.com`, `admin.candycutz.com`). Furthermore, tokens are generated without client-specific device names or abilities (e.g. `web-client`, `mobile-client`), causing single-session purges to kill multi-device logins.

### 5. Is CORS correctly configured?
No. Raw headers in `public/index.php` collide with Laravel's `HandleCors` middleware. `config/cors.php` only whitelists `http://localhost:5173`, failing whenever Vite starts on port 5174 or when requests originate from mobile Expo packs or production domains.

### 6. Are frontend URLs hard-coded?
Yes.
- `barbing-saloon-web`: Has hardcoded redirects to `/customer/login` in `axios.js`.
- `candycutz-customer-app`: `.env` has hardcoded `EXPO_PUBLIC_API_URL=http://10.252.94.238:8000/api/v1`.
- `candycutz-barber-app`: `.env` has hardcoded `EXPO_PUBLIC_API_URL=http://10.252.94.238:8000/api/v1`.
- Physical address of the Keffi flagship store is duplicated across 14 separate files.

### 7. Are API URLs environment-specific?
No. There is no automated resolution between localhost, LAN IP (Expo Go on physical devices), Android Emulator (`10.0.2.2`), iOS Simulator (`localhost`), and production (`api.candycutz.com`).

### 8. Are Google and Apple callbacks correct?
No. On the web, the popup handler sends the raw ID token to `/auth/social-login`, which does not exist. On mobile, Google and Apple SDKs are completely uninstalled and unconfigured.

### 9. Can one Google/Apple identity accidentally create multiple users?
Currently yes. Because there is no database constraint linking `provider_id` + `auth_provider` uniquely to a single `user_id`, nor is there account resolution logic to associate a verified Google email with an existing password-based account.

### 10. Are website and mobile routes incorrectly coupled?
No, they are decoupled, but they are **inconsistently routed**. The website routes to `/api/public/*` and `/api/customer/*`, whereas the mobile app routes to `/api/v1/*` without actor prefixes. The backend must provide a unified, canonical REST API v1 that satisfies both clients.

### 11. Are API routes conflicting with web routes?
In production, static file proxying in `public/index.php` intercepts non-API routes:
```php
if (strpos($requestUri, '/api') !== 0 ...) { readfile(__DIR__ . '/index.html'); exit; }
```
If an API route is defined without the `/api` or `/api/v1` prefix, it is silently eaten by the SPA index fallback and returns HTML instead of JSON.

### 12. Are mobile route guards correctly implemented?
No. Neither mobile application contains navigation route guards. Protected tabs mount unconditionally, rendering null states or throwing exceptions when token storage is empty.

### 13. Can a customer access barber functionality?
On the web, `registerRouteGuards` checks `to.meta.roles`. On mobile, `candycutz-customer-app` does not have barber screens, but a customer token sent to `/api/v1/barber/*` is blocked by Laravel's `check.role:barber` middleware. However, direct endpoint validation must remain authoritative.

### 14. Can a barber access customer-only functionality?
On the backend, `check.role:customer` explicitly blocks barbers. Barbers cannot book personal haircuts unless they switch roles or maintain a distinct customer profile.

### 15. Can an unauthorized user access CMS functionality?
No. Laravel's `check.role:admin,super_admin` middleware guards the `/admin` routes. However, error envelopes on unauthorized access return raw HTML 403 pages instead of structured JSON when requested by API clients without `Accept: application/json`.

### 16. What happens if a user's role changes while logged in?
The role is read from the database on every authenticated request via `$request->user()->role`. Role changes take effect immediately on the backend. However, the frontend stores the user object in Pinia / Zustand; if the user's role is demoted, client UI remains visible until an API call triggers a 403.

### 17. What happens when an account is deactivated?
`AuthService::login` blocks new logins for users with `is_active = 0` or `status = 'deactivated'`. However, active Sanctum tokens are not revoked upon administrative deactivation, allowing an already-logged-in deactivated user to continue making requests until token expiry.

### 18. What happens when authentication expires?
- Web: `axios.js` catches 401, clears localStorage, and hard-redirects to `/customer/login`.
- Mobile: `client.ts` catches 401, deletes token from `SecureStore`, but does not redirect the navigation stack to the login screen, leaving the user on a frozen screen.

### 19. What happens when the network disappears during booking?
- Web: Displays a toast error, leaves form state intact.
- Mobile: Mutation throws network error; no offline queue or idempotent retry token is sent. If the request reached the server before the network dropped, a duplicate submission will create a duplicate booking.

### 20. What happens if two customers select the same appointment?
- In `CustomerService::createBooking` (currently called by web): Double-booking occurs because `SlotHelper` performs an un-locked check.
- In `BookingService::createBooking` (dormant service): Pessimistic row locking (`lockForUpdate()`) rejects the second request with a clean concurrency exception.

### 21. What happens if Stripe succeeds but booking creation fails?
In the current code, Stripe `PaymentIntent` creation happens *before* booking confirmation. If the customer is charged but the server crashes before `Appointment::create`, the money is deducted with no booking recorded. The system must create the appointment in `pending_payment` status *first*, attach the payment intent, and confirm atomically upon webhook receipt.

### 22. What happens if Stripe webhook delivery is delayed?
Because there is no Stripe webhook listener registered in `routes/api.php`, webhooks fail with HTTP 404. Appointments remain indefinitely in `pending` status.

### 23. What happens if Brevo is unavailable?
In `BrevoNotificationAdapter.php`, email dispatch is wrapped in a `try...catch` block logging a warning. The HTTP request does not crash, but because notifications run synchronously (`QUEUE_CONNECTION=sync`), Brevo connection timeouts delay API response times by up to 30 seconds.

### 24. What happens if Redis is unavailable?
Currently, Redis is not configured (`CACHE_STORE=file`, `SESSION_DRIVER=file`, `QUEUE_CONNECTION=sync`). The platform is running entirely on local filesystem drivers.

### 25. What happens if a queue worker stops?
Because `QUEUE_CONNECTION=sync`, no background worker exists. If queues are enabled without a supervisor or Docker queue container, all background emails and notifications stall indefinitely.

### 26. Can the VPS be safely restarted?
Currently no, because there is no systemd service or Docker compose file with `restart: always` for Nginx, PHP-FPM, MySQL, and Redis.

### 27. Can the database be backed up and restored?
There are no automated backup scripts, cron jobs, or database snapshot runbooks in the repository.

### 28. Can the system be debugged without SSH guesswork?
No. Structured logging is missing, audit logs are fragmented, and runtime errors are buried in `storage/logs/laravel.log`.

### 29. Can the mobile app continue using the API after future API changes?
Yes, provided that all mobile traffic is routed strictly through `/api/v1/` with documented contracts and semantic versioning.

### 30. Can Candycutz expand to additional branches without redesigning the entire database?
Yes. The database already contains `businesses`, `branches`, `service_zones`, and `addresses` tables with foreign keys on appointments.

---

## 6. Duplication & Technical Debt

### 6.1 Major Code Duplication
1. **Two Separate Mobile Apps**: `candycutz-customer-app` and `candycutz-barber-app` duplicate 80% of their infrastructure, styling tokens, types, and dependencies.
2. **Booking Logic Duplication**: `app/Services/Booking/BookingService.php` vs `app/Modules/Customer/Services/CustomerService.php`.
3. **Scratch Files in Production Root**: 22 ad-hoc test scripts (`test_login.php`, `seed_demo_data.php`, `check_db.php`, `fix_all_passwords.php`, etc.) clutter `barbing-saloon-api/`.
4. **Physical Business Address Duplication**: The Keffi address string is copy-pasted as a raw string across 14 backend and frontend files instead of querying `Branch::first()`.

### 6.2 Technical Debt
1. Missing `stripe/stripe-php` in `composer.json`.
2. Missing Stripe webhook controller and route.
3. Missing `/auth/social-login`, `/auth/forgot-password`, `/auth/reset-password` routes.
4. Missing canonical RESTful aliases for mobile clients (`/api/v1/services`, `/api/v1/barbers`, `/api/v1/appointments`).
5. Missing unified Expo mobile app architecture.

---

## 7. Strategic Monorepo & Architectural Direction

### 7.1 Non-Negotiable Core Principle
```text
ONE PLATFORM
ONE GITHUB REPOSITORY
ONE LARAVEL BACKEND
ONE DATABASE
ONE CMS
ONE VPS
ONE EXPO MOBILE APP
ONE AUTHENTICATION AUTHORITY
```

### 7.2 Safe Repository Monorepo Structure
To avoid breaking Git history or invalidating existing dependencies while moving toward clean monorepo organization:
```
candycutz/
├── apps/
│   ├── web/                     # Current barbing-saloon-web (Vue 3 client)
│   └── mobile/                  # Unified Expo Mobile App (Customer + Barber roles)
├── backend/
│   └── laravel/                 # Current barbing-saloon-api (Laravel 11 backend)
├── infrastructure/
│   ├── docker/                  # Nginx, PHP-FPM, MySQL, Redis compose definitions
│   └── scripts/                 # Backup, deploy, and seed utilities
├── docs/                        # Complete living documentation
├── AGENTS.md                    # Agent constraints & rules
├── PROJECT_STATE.md             # Real-time state tracker
├── BUILD_PLAN.md                # Milestone dependency matrix
├── DECISIONS.md                 # Architectural decision records
└── docker-compose.yml           # Unified VPS orchestration
```

*Note: Monorepo folder migrations will occur only in controlled phases after API contracts, authentication, and mobile convergence are finalized, preventing path disruption during active coding.*

---

## 8. Step-by-Step Recovery Plan

### Phase 0: System Audit (COMPLETED)
- Forensic codebase audit completed and documented in `docs/recovery/SYSTEM_AUDIT.md`.
- Brain files synchronized: `AGENTS.md`, `PROJECT_STATE.md`, `BUILD_PLAN.md`, `DECISIONS.md`.

### Phase 1: Architecture Lock & Contract Specifications
- Author canonical API v1 contract in `docs/api/API_SPEC.md`.
- Author unified Authentication Specification in `docs/authentication/AUTHENTICATION_SPEC.md`.
- Author Routing Specification in `docs/routing/ROUTING_SPEC.md`.
- Author Docker & VPS Deployment Specification in `docs/deployment/DEPLOYMENT_SPEC.md`.

### Phase 2: Authentication Recovery
- Implement `socialLogin` in `AuthController.php` supporting Google ID tokens and Apple tokens.
- Implement `/auth/forgot-password` and `/auth/reset-password` endpoints with transactional email tokens.
- Remove destructive `$user->tokens()->delete()` from `AuthService::login`; replace with client-scoped token naming (`web_token`, `mobile_token`).
- Fix `RegisterRequest` and `AuthService::register` to validate and persist unique `@username`.
- Fix CORS configuration: remove raw PHP headers from `public/index.php`, configure `config/cors.php` for credentials and dynamic origin matching.

### Phase 3: Routing & API Harmonization
- Register canonical RESTful endpoints under `/api/v1/`:
  - `GET /api/v1/services` -> `ServicesController::index`
  - `GET /api/v1/barbers` -> `BarbersController::index`
  - `GET /api/v1/availability` -> `AvailabilityController::index`
  - `GET /api/v1/appointments` & `POST /api/v1/appointments` -> `AppointmentsController`
  - `GET /api/v1/service-zones` -> `ServiceZonesController::index`
  - `POST /api/v1/payments/webhook` -> `PaymentWebhookController`
- Maintain 100% backward compatibility with existing Vue 3 endpoints (`/api/public/*`, `/api/customer/*`, `/api/barber/*`, `/api/admin/*`).
- Fix `axios.js` 401 handler: redirect users based on route context (`/admin/login`, `/barber/login`, or `/customer/login`).

### Phase 4: Single Unified Expo Mobile App Convergence
- Consolidate `candycutz-customer-app` and `candycutz-barber-app` into **ONE** Expo application under `candycutz-mobile-app` (or converged inside `candycutz-customer-app`).
- Implement Role-Based Root Navigation in `app/_layout.tsx`:
  - `role === 'customer'` -> Mount `(customer)` tab layout (`Home`, `Explore`, `Book`, `Appointments`, `Profile`).
  - `role === 'barber'` -> Mount `(barber)` tab layout (`Dashboard`, `Appointments`, `Schedule`, `Walk-In`, `Profile`).
  - Unauthenticated -> Mount `(auth)` layout (`Login`, `Register`, `ForgotPassword`).
- Connect unified mobile client to canonical `/api/v1/*` endpoints.

### Phase 5: Concurrency, Payments & Notification Hardening
- Require `stripe/stripe-php` in `barbing-saloon-api`.
- Connect `CustomerController::storeBooking` to `BookingService::createBooking` with pessimistic row locking (`SELECT FOR UPDATE`).
- Implement Stripe webhook endpoint with idempotency checks in `payment_transactions`.
- Configure asynchronous queue processing (`QUEUE_CONNECTION=database` or `redis`) for Brevo emails.

### Phase 6: Docker & Single-VPS Infrastructure
- Author production-grade `Dockerfile` for Laravel using PHP-FPM 8.2 with OPcache.
- Author production Nginx configuration with SSL, reverse proxying `/api` to PHP-FPM and serving pre-built Vue 3 dist assets statically.
- Author `docker-compose.yml` with: `nginx`, `app` (PHP-FPM), `db` (MySQL 8.0), `redis` (Cache & Queues), and `worker` (Queue processor).
- Create automated database backup script (`scripts/backup_db.sh`).

### Phase 7: End-to-End Verification & Launch Handover
- Execute automated end-to-end test suite verifying Customer Web, Customer Mobile, Barber Mobile, CMS Admin, Payments, and Notifications.
- Deliver developer deployment and debugging runbooks.
