# CandyCutz — Target Architecture

**Status:** Target state. This document is authoritative. Where the codebase disagrees with this document, the codebase is wrong.
**Supersedes:** `docs/architecture/ARCHITECTURE.md`, `docs/Architecture.md`, `docs/Architecture-Essentials.md`, `docs/architecture/ARCHITECTURE_ESSENTIALS.md`
**Scope:** `barbing-saloon-api` (Laravel 11), `barbing-saloon-web` (Vue 3), `candycutz-mobile-app` (Expo)

---

## 0. Why this document exists

The repository currently contains four architecture documents, 28 files under `docs/`, and a `PROJECT_STATE.md` asserting nine completed phases and a "277-assertion Master Test Matrix (100% PASS)". No automated test harness exists in the repository. The documentation describes a system that was never built.

The single most damaging consequence is that **every future AI-assisted change is grounded in a false context**. An agent reading `PROJECT_STATE.md` believes routing is harmonised, believes tests pass, and therefore builds on top of duplication instead of removing it.

The rule going forward: **a document may only assert what a command can prove.** If `PROJECT_STATE.md` says tests pass, `php artisan test` must exit 0.

---

## 1. System shape

Three deployable units, one authority.

```
┌─────────────────────┐   ┌──────────────────────┐   ┌────────────────────┐
│ barbing-saloon-web  │   │ candycutz-mobile-app │   │   Stripe / Paystack│
│ Vue 3 SPA (PWA)     │   │ Expo / React Native  │   │   webhooks         │
│ public + 4 portals  │   │ customer + barber    │   └─────────┬──────────┘
└──────────┬──────────┘   └──────────┬───────────┘             │
           │                         │                         │
           │      HTTPS · Bearer token · JSON envelope         │
           └────────────┬────────────┴─────────────────────────┘
                        ▼
        ┌───────────────────────────────────────────┐
        │  barbing-saloon-api — Laravel 11          │
        │  SINGLE API SURFACE: /api/v1/*            │
        │                                           │
        │  HTTP layer → Application layer → Domain  │
        └───────────────┬───────────────────────────┘
                        │
        ┌───────────────┼────────────────┬─────────────┐
        ▼               ▼                ▼             ▼
    MySQL 8         Redis            S3 / local     Brevo
   (InnoDB)    (cache/queue/session)   storage      (email)
```

**Non-negotiable:** the API owns all business rules. No business logic lives in Vue or Expo. Clients render state and submit intent; they never compute price, availability, or permission.

---

## 2. The API surface — one, not three

### 2.1 Current state (broken)

Mount point
Registered by
Controllers

`/api/v1/*`
`routes/api.php` → `api_v1.php`
`App\\Core\\Http\\Controllers\\Api\\V1\\*`

`/api/*` (alias)
`routes/api.php` → same file again
same

`/api/customer/*`
`ModuleServiceProvider`
`App\\Modules\\Customer\\Controllers\\*`

`/api/barber/*`
`ModuleServiceProvider`
`App\\Modules\\Barber\\Controllers\\*`

`/api/admin/*`
`ModuleServiceProvider`
`App\\Modules\\Admin\\Controllers\\*`

`/api/superadmin/*`
`ModuleServiceProvider`
`App\\Modules\\SuperAdmin\\Controllers\\*`

`/api/auth/*`
both `api_v1.php` **and** `Modules/Auth/routes.php`
`AuthController` + `AuthApiController`

Cancelling an appointment is possible at `/api/v1/appointments/{id}/cancel`, `/api/appointments/{id}/cancel`, `/api/customer/bookings/{id}/cancel` and `/api/customer/appointments/{id}/cancel` — four routes, two controllers, two response shapes, and only one of them (`CustomerController`) calls `Gate::authorize`.

### 2.2 Target state

**`/api/v1/*` is the only mount point.** `ModuleServiceProvider` is deleted. The alias group in `routes/api.php` is deleted.

Routes are organised by resource, not by role. Role determines *what you may do to a resource*, never *which URL you call*.

```
routes/
  api.php            # mounts v1 only
  api/
    v1/
      auth.php       # login, register, logout, me, password reset, social
      catalogue.php  # services, categories, barbers, zones, gallery, blog, testimonials
      booking.php    # appointments, availability, walk-ins
      payments.php   # checkout, receipts, webhooks
      account.php    # profile, addresses, notifications, wishlist, device tokens
      admin.php      # CMS, settings, verification, reports, users, audit
```

One resource, one route, one controller, one policy:

```
GET    /api/v1/appointments          # scoped by policy to caller's role
POST   /api/v1/appointments
GET    /api/v1/appointments/{id}
PATCH  /api/v1/appointments/{id}/status
POST   /api/v1/appointments/{id}/cancel
```

`AppointmentPolicy` decides whether a customer sees their own, a barber sees their chair's, or an admin sees the branch's. **Role-scoped querying moves out of routes and into policies + query scopes.**

Legacy paths get a 6-month deprecation window: `routes/api/legacy.php` returns `410 Gone` with a `Deprecation` header naming the replacement, so mobile clients in the wild fail loudly rather than silently hitting a stale controller.

### 2.3 Response envelope

Every response, without exception:

```
{ "success": true,  "message": "...", "data": { }, "meta": { } }
{ "success": false, "message": "...", "error": { "code": "BOOKING_SLOT_UNAVAILABLE", "details": {} } }
```

`ApiResponse` already exists and is correct. The failure is that raw Eloquent models are returned in several places, bypassing the envelope and leaking columns. **Every `data` payload is an API Resource. No controller returns a model or a collection of models directly.**

---

## 3. Backend layering

Delete the `Core/` vs `Modules/` vs `Services/` three-way split. It produced `Modules\\Landing\\Services\\PublicService` (255 lines) and `Modules\\Public\\Services\\PublicService` (238 lines) — two classes with the same name doing nearly the same job.

### 3.1 Target tree

```
app/
  Domain/                          # framework-agnostic business rules
    Booking/
      Actions/                     # CreateBooking, CancelBooking, CreateWalkIn, RescheduleBooking
      Services/                    # AvailabilityEngine, SlotCalculator
      DataObjects/                 # BookingData, SlotRange
      Exceptions/                  # BookingSlotUnavailableException
      Policies/                    # AppointmentPolicy
    Payment/
      Actions/                     # InitiateCheckout, ConfirmPayment, VerifyReceipt
      Contracts/                   # PaymentGateway (interface)
      Gateways/                    # PaystackGateway, StripeGateway, ManualTransferGateway
    Catalogue/                     # services, categories, barbers, zones
    Identity/                      # registration, username, roles, tokens
    Content/                       # CMS, blog, gallery, testimonials, theme
    Notification/
      Contracts/                   # NotificationChannel
      Channels/                    # BrevoEmailChannel, ExpoPushChannel, SmsChannel

  Http/
    Controllers/Api/V1/            # thin. resolve → authorize → delegate → respond
    Requests/Api/V1/               # FormRequest per write endpoint
    Resources/Api/V1/              # one Resource per model, explicit fields
    Middleware/
    Responses/ApiResponse.php

  Models/                          # Eloquent only: relations, casts, scopes, fillable, hidden
  Jobs/  Mail/  Notifications/  Providers/  Support/
```

**Controller contract — a method may not exceed ~15 lines:**

```
public function store(StoreAppointmentRequest $request, CreateBooking $action): JsonResponse
{
    $this->authorize('create', Appointment::class);

    $appointment = $action->execute(
        $request->user(),
        BookingData::fromRequest($request)
    );

    return ApiResponse::success(
        new AppointmentResource($appointment->load(['service', 'barber.user'])),
        'Appointment created',
        201
    );
}
```

Note `BookingData::fromRequest($request)` — a typed DTO, not `array_merge($request->all(), $validated)`. That pattern currently appears in `AppointmentApiController` at lines 115 and 212 and lets any unvalidated request key reach the service layer.

### 3.2 Actions vs Services

- **Action** — one business operation with side effects. `CreateBooking::execute()`. Transactional boundary lives here.
- **Service** — stateless computation, no side effects. `AvailabilityEngine::slotsFor()`.
- **Model** — persistence and relations only. No business rules.

`AdminService` (900 lines) decomposes into roughly a dozen Actions across `Domain/Catalogue`, `Domain/Content`, and `Domain/Identity`.

### 3.3 What must be preserved

`app/Services/Booking/BookingService.php` is correct. It wraps slot allocation in `DB::transaction` with `lockForUpdate()` at lines 57, 148 and 243, which is the right defence against double-booking. When it moves to `Domain/Booking/Actions/`, **the locking semantics move with it unchanged.** The same applies to `PaymentService::confirmPaymentFromWebhook()` — its event-ID idempotency check is correct.

---

## 4. Authorization

### 4.1 Current state

Three overlapping mechanisms: a `UserRole` enum, a hand-rolled `CheckRole` middleware, one registered policy (`AppointmentPolicy`, used in exactly three places in `CustomerController`), and `spatie/laravel-permission` installed with **zero usages anywhere in `app/`**.

`CheckRole` also contains a blanket `super_admin` bypass before any permission check runs — a role that can never be denied anything cannot be audited.

### 4.2 Target

Two layers, both mandatory.

**Layer 1 — coarse gate (middleware).** Does this token belong to a role permitted anywhere near this route group? `check.role:barber,admin`.

**Layer 2 — fine gate (policy).** Does *this* user have rights over *this* record? Every controller method touching a model calls `$this->authorize()`. No exceptions, including admin endpoints.

Remove `spatie/laravel-permission` or adopt it fully. Given that CandyCutz has four fixed roles and no runtime permission management, **remove it** and keep the enum + policies. An unused dependency in a security-adjacent area is a liability.

Remove the unconditional `super_admin` bypass. Super admin gets a policy `before()` hook that grants *and writes an audit log entry*, so privileged access leaves a trail.

### 4.3 IDOR checklist

Every route with `{id}` needs a policy check. Currently `/api/v1/appointments/{id}`, `/api/v1/appointments/{id}/status`, and `/api/v1/gallery/{id}` resolve by ID with no ownership verification in the Core controllers — only the `Modules` duplicates check. When the duplicates are deleted, the checks must land in the survivors.

---

## 5. Security

Control
Current
Target

Password in responses
`User` has **no `$hidden`**
`$hidden = ['password', 'remember_token', 'provider_id']` on `User`; every other model gets an explicit Resource

Token lifetime
`sanctum.expiration = null` (never expires)
30 days customer, 12 hours admin/super_admin; refresh on activity; `/auth/sessions` endpoint to revoke a device

Input sanitisation
global `strip_tags()` on all input — **destroys TipTap CMS content**
Delete `SanitizeInput`. Validate per-field in FormRequests. Sanitise rich text with an HTML purifier **only** on CMS body fields. Escape on output.

CORS
hardcoded `10.252.94.238`, private-IP regex, wildcard subdomains, `supports_credentials: true`
`env('CORS_ALLOWED_ORIGINS')` explicit list only. No regex. No private IPs outside `local`.

Mass assignment
`role`, `status`, `is_active` in `User::$fillable`; `array_merge($request->all(), ...)`
Remove privilege fields from `$fillable`; set them only through explicit Actions. DTOs replace `$request->all()`.

File uploads
any image saved as `.webp` regardless of type, no re-encode, no EXIF strip
Validate MIME + extension + magic bytes; re-encode through Intervention Image; strip EXIF; correct extension; private disk for receipts

Receipts
payment receipts on public disk
private disk, served through a signed, policy-checked route

Webhooks
signature verified (correct) but behind `throttle:120,1`
keep verification; exempt from throttle; queue processing; keep event-ID idempotency

Dependency advisories
`block-insecure: false`, 5 advisories ignored
remove suppression, resolve advisories, `composer audit` in CI

Stray scripts
~40 `test_*.php` / `fix_*.php` / `check_*.php` at API root
deleted; anything useful becomes an Artisan command

**On `SanitizeInput`:** this middleware is currently silently corrupting production data. Every blog post written through the TipTap editor in `admin/BlogEditorPage.vue` has its markup stripped before it reaches the database. This is not a hypothetical — it fires on every request through the `Admin` module middleware stack.

---

## 6. Payments

### 6.1 The business conflict

The codebase ships `stripe/stripe-php` as the sole gateway. CandyCutz operates in Keffi, Nasarawa State. **Stripe does not onboard Nigerian merchants.** The Stripe integration cannot process a single naira of real revenue.

Meanwhile the app already implements a parallel, undocumented manual flow: `POST /api/customer/checkout/{id}/receipt` uploads a bank-transfer receipt, and `barber/PaymentVerificationPage.vue` + `admin/VerificationPage.vue` approve it by hand. That flow is the one that actually works in-market, and it has no spec, no state machine, and no fraud controls.

### 6.2 Target

Gateway abstraction with three implementations:

```
interface PaymentGateway
{
    public function initiate(Payment $payment): GatewayCheckout;
    public function verify(string $reference): GatewayResult;
    public function handleWebhook(string $rawPayload, array $headers): WebhookEvent;
}
```

- **`PaystackGateway`** — primary. Cards, bank transfer, USSD. Naira-native. Webhook `x-paystack-signature` HMAC-SHA512 verification.
- **`ManualTransferGateway`** — formalises the existing receipt flow with an explicit state machine: `awaiting_transfer → receipt_uploaded → under_review → verified | rejected`, an SLA timer, and an audit entry per transition.
- **`StripeGateway`** — retained, disabled by default, for any future diaspora/card-abroad case.

Selection by `config('payments.default')`, not by hardcoded class reference.

**Money is `bigInteger` kobo, never `decimal`.** `Appointment` currently casts six money columns as `decimal:2`. Convert to integer minor units with a display accessor.

---

## 7. Database

### 7.1 Current state

`database/migrations/` contains 30 migration classes **and 9 raw `.sql` files** — `create_tables.sql`, `add_customer_barber_features.sql`, `fix_gallery_schema.sql`, `optimize_categories.sql`, `seed_data.sql`, and others. Plus `optimize_db.sql` at the API root and a `05_schema_sync.sql`.

The consequence: **`php artisan migrate:fresh` does not produce the production schema.** Nobody can stand up a working environment from the repository. This alone blocks CI, blocks onboarding, and blocks every form of automated testing.

### 7.2 Target

1. Dump the current production schema as the source of truth.
2. Squash all 30 migrations into one `0001_01_01_000000_create_base_schema.php` reflecting that dump exactly.
3. Convert each `.sql` file's *intent* into either a migration (schema) or a seeder (data). Delete the `.sql` files.
4. Verify: `migrate:fresh` output must diff clean against the production dump.
5. Every subsequent change is a dated migration. No exceptions, no manual SQL.

### 7.3 Integrity to add

- Composite unique on `(barber_id, appointment_date, appointment_time)` where `status NOT IN ('cancelled','no_show')` — a database-level backstop behind the application lock.
- Composite index on `appointments(barber_id, appointment_date, status)`, `appointments(customer_id, status)`, `payments(appointment_id, status)`.
- Unique on `payment_transactions(gateway_event_id)` — idempotency enforced by the schema, not only by the application.
- Foreign keys with deliberate cascade: appointments `restrictOnDelete` from services (never orphan financial history); `cascadeOnDelete` for `appointment_items`, `device_tokens`, `blog_reactions`.
- Soft deletes on anything financially or legally relevant; hard delete only for `device_tokens` and cache.

### 7.4 Seeders and factories

There are currently **zero factories**. Without factories there can be no meaningful feature tests. A factory per model is a prerequisite for §9, not an optional extra.

---

## 8. Frontend

### 8.1 Web — `barbing-saloon-web`

Vue 3 + Vite + Pinia + Tailwind, plain JavaScript, no linting, no type checking, no tests. Four per-role API clients (`customer.api.js`, `barber.api.js`, `admin.api.js`, `superadmin.api.js`) duplicate the same CRUD shapes. `admin/AppointmentsPage.vue` is 816 lines; `admin/DashboardPage.vue` is 774.

Target:

```
src/
  app/            # bootstrap, router, global plugins
  shared/
    api/          # ONE axios client, ONE interceptor chain, resource modules
    ui/           # Base* components (already good — keep)
    composables/  # useToast, useConfirm, useAuth, usePagination
    lib/          # formatters, validators, date helpers
  features/
    booking/  appointments/  services/  gallery/  blog/  payments/  cms/
      api.js  components/  composables/  pages/  routes.js
  portals/
    public/  customer/  barber/  admin/   # layout + route composition ONLY
```

Rules:

- Add ESLint + Prettier + `vue-tsc` in `checkJs` mode. Full TS migration is optional; type-checking JSDoc is not.
- Any `.vue` over 300 lines is decomposed. Data fetching moves to composables; the SFC renders.
- One `shared/api/client.js`. `core/api/interceptors.js` is currently a dead duplicate — delete it.
- Delete `dist/` from version control and add to `.gitignore`.
- Vitest for composables and API modules; Playwright (already a root dependency) for the four critical journeys: book, pay, verify receipt, cancel.

### 8.2 Mobile — `candycutz-mobile-app`

Expo Router + TanStack Query + Zustand + TypeScript. **This is the healthiest part of the repository.** It already has the layering the other two lack.

- Keep the structure. Add `src/features/<domain>/` for anything beyond the current screen set.
- Tokens in `expo-secure-store` (already correct). Never `AsyncStorage`.
- Generate `src/api/types.ts` from the API's OpenAPI spec so client and server cannot drift.
- Add `tsc --noEmit` and Jest + React Native Testing Library to CI.

---

## 9. Testing

Currently: 13 procedural scripts in `tests/` that hit a live server, no `phpunit.xml`, no `--dev` dependencies, and a `PROJECT_STATE.md` claiming 277 passing assertions.

Target — nothing merges without these:

Layer
Tool
Minimum bar

Backend unit
Pest
`AvailabilityEngine`, slot maths, money maths, state machines

Backend feature
Pest + `RefreshDatabase`
every `/api/v1/*` endpoint: happy path, validation failure, unauthenticated, wrong-role, IDOR attempt

Concurrency
Pest
parallel booking of one slot → exactly one success, one `409`

Static
Larastan level 6, Pint
zero errors

Web unit
Vitest
composables, API modules

E2E
Playwright
book, pay, verify receipt, cancel

Mobile
`tsc --noEmit`, Jest
zero type errors

`composer.json` must gain `--dev`: `pestphp/pest`, `pestphp/pest-plugin-laravel`, `larastan/larastan`, `laravel/pint`, `mockery/mockery`, `fakerphp/faker`.

The 13 existing scripts are deleted. Their intent is reimplemented as Pest tests. An integration script that needs a running server and a seeded database is not a test — it cannot gate a merge.

---

## 10. Repository hygiene

Delete:

```
/DELETE/                                    # 16 files
/composer.phar                              # 3.5 MB binary
/barbing-saloon-api/composer.phar
/barbing-saloon-api/composer-setup.php
/barbing-saloon-api/test_*.php              # 14 files
/barbing-saloon-api/fix_*.php               # 4 files
/barbing-saloon-api/check_*.php             # 2 files
/barbing-saloon-api/run_migration*.php      # 2 files
/barbing-saloon-api/seed_*.php              # 3 files
/barbing-saloon-api/migration_otp.php
/barbing-saloon-api/dump_images.php
/barbing-saloon-api/update_*.php            # 2 files
/barbing-saloon-api/optimize_db.sql
/barbing-saloon-api/scratch/                # 4 files
/barbing-saloon-web/dist/                   # committed build output
/artisan                                    # root-level stub pointing into the API
/*.bat, /*.ps1                              # Windows-only launchers with hardcoded C:\\xampp paths
/icon-*.png                                 # 71-byte placeholder files at repo root
```

Anything genuinely needed becomes an Artisan command under `app/Console/Commands/`. Local dev runs on `docker-compose up`, not `start.bat` hardcoding `C:\\xampp\\php\\php.exe`.

### Documentation

28 files under `docs/` with four competing architecture documents and two competing audits. Collapse to seven, each with a named owner and a last-verified date:

```
PROJECT_CONTEXT.md    # what CandyCutz is, who uses it, business grounding
ARCHITECTURE.md       # this file
DATABASE.md           # schema, relationships, index rationale
API.md                # generated from OpenAPI — never hand-written
SECURITY.md           # threat model, controls, incident response
DEVELOPMENT.md        # setup, conventions, definition of done
CHANGELOG.md          # what actually shipped, dated
```

`PROJECT_STATE.md`, `BUILD_PLAN.md`, `TASKS.md`, `DECISIONS.md`, `shiply.md` and `docs/**` are archived to `docs/archive/` with a header stating they are historical and unverified. **They are not deleted** — they contain real business grounding (Keffi coordinates, service zones, operating hours) that must be extracted into `PROJECT_CONTEXT.md` first.

---

## 11. Sequencing

Each phase ends in a working, deployable system. No phase begins before the previous one's exit criteria pass.

Phase
Work
Exit criteria

**0 — Ground truth**
Delete stray scripts. Archive false docs. Write honest `PROJECT_CONTEXT.md`. Dump production schema.
`git status` clean; no `test_*.php` in repo; schema dump committed

**1 — Test harness**
Pest, Pint, Larastan, `phpunit.xml`, factories for all 26 models. Characterisation tests capturing **current** behaviour of every endpoint.
`php artisan test` exits 0; every route has at least one test asserting today's behaviour

**2 — Database truth**
Squash migrations. Convert `.sql` files. Add indexes and constraints.
`migrate:fresh` diffs clean against production dump; tests still green

**3 — Security**
`$hidden`; delete `SanitizeInput`; lock CORS; token expiry; `$fillable` audit; upload hardening; private receipts; remove advisory suppression.
Security test suite green; `composer audit` clean

**4 — One API surface**
Delete `ModuleServiceProvider` and the `/api/*` alias. Consolidate duplicate controllers. Policy on every `{id}` route. Resources everywhere. Legacy → `410 Gone`.
`route:list` shows only `/api/v1/*`; characterisation tests green against new routes

**5 — Domain layer**
`Core/`+`Modules/`+`Services/` → `Domain/`. Decompose `AdminService`. DTOs replace `$request->all()`. Merge duplicate `PublicService`.
No class over 300 lines; no controller method over 15 lines; Larastan level 6

**6 — Payments**
`PaymentGateway` interface; `PaystackGateway`; formalise `ManualTransferGateway` state machine; money → kobo integers.
Paystack test-mode transaction end to end; receipt flow has a tested state machine

**7 — Web**
Restructure to `features/` + `portals/`. One API client. Decompose 300+ line SFCs. ESLint + Vitest + Playwright.
Zero lint errors; no SFC over 300 lines; 4 E2E journeys green

**8 — Mobile & contract**
OpenAPI spec generation; typed mobile client; push notifications; EAS build.
Generated types compile; `tsc --noEmit` clean

**9 — Production**
TLS, secrets, backup drill (restore, not just backup), monitoring, CI gating merges.
Restore drill completed and documented; CI blocks a failing PR

**Phase 1 is not optional and cannot be reordered.** Without characterisation tests, phases 4 and 5 are a rewrite with no safety net, and the last time this repository attempted that it produced the duplication being removed.

---

## 12. Definition of done

A change is done when all of the following hold:

- `php artisan test` exits 0
- `./vendor/bin/pint --test` exits 0
- `./vendor/bin/phpstan analyse` exits 0 at level 6
- `npm run lint` exits 0 in `barbing-saloon-web`
- `npx tsc --noEmit` exits 0 in `candycutz-mobile-app`
- The endpoint appears in the OpenAPI spec
- Any claim added to a `.md` file is verifiable by a command written next to it
