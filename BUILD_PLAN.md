# Candycutz — Master Build Plan & Dependency Matrix

## 1. Overview
This build plan defines the dependency chain and acceptance gates for recovering and stabilizing the Candycutz platform. No milestone may commence until its upstream dependencies have met all Definition of Done (DoD) criteria.

---

## 2. Milestone Dependency Chain

```text
[Phase 0: Comprehensive System Audit]
                   │
                   ▼
[Phase 1: Architecture Lock & Contract Specs]
                   │
                   ▼
[Phase 2: Authentication Recovery & Scoped Sessions]
                   │
                   ▼
[Phase 3: Routing & API v1 Harmonization]
                   │
         ┌─────────┴─────────┐
         ▼                   ▼
[Phase 4: Mobile App]    [Phase 5: Concurrency, Payments & Brevo]
(Single Unified Expo)         │
         │                   │
         └─────────┬─────────┘
                   ▼
[Phase 6: Website Integration & Zero-Regression Check]
                   │
                   ▼
[Phase 7: Single-VPS Docker & Nginx Infrastructure]
                   │
                   ▼
[Phase 8: End-to-End QA & Penetration Testing]
                   │
                   ▼
[Phase 9: Production Store Release & Launch Handover]
```

---

## 3. Phase Breakdown & Acceptance Gates

### Phase 0: Comprehensive System Audit & Forensic Inspection
- **Objective**: Complete forensic audit of existing codebase, identification of root causes, and baseline documentation.
- **Deliverables**:
  - `docs/recovery/SYSTEM_AUDIT.md` authored with answers to all 30 architectural questions.
  - Brain files synchronized (`AGENTS.md`, `PROJECT_STATE.md`, `BUILD_PLAN.md`, `DECISIONS.md`).
- **Exit Gate**: All disconnects, missing endpoints, CORS conflicts, and mobile fragmentation documented. Codebase frozen without unplanned modifications.

### Phase 1: Architecture Lock & Contract Specifications
- **Objective**: Author living specifications for API v1, authentication, routing, and deployment before modifying application code.
- **Deliverables**:
  - `docs/api/API_SPEC.md` & `docs/api/API_ERROR_CODES.md`
  - `docs/authentication/AUTHENTICATION_SPEC.md`
  - `docs/routing/ROUTING_SPEC.md`
  - `docs/deployment/DEPLOYMENT_SPEC.md`
- **Exit Gate**: Zero architectural ambiguities or conflicting route expectations remain across web, mobile, and backend.

### Phase 2: Authentication Recovery & Scoped Multi-Device Sessions
- **Objective**: Eliminate token wipeout collisions, implement social auth, and handle password resets.
- **Tasks**:
  - Update `AuthService::login` to issue device-scoped tokens (`web_token`, `mobile_token`) without wiping all user sessions.
  - Implement `/api/v1/auth/social-login` verifying Google and Apple ID tokens.
  - Implement `/api/v1/auth/forgot-password` and `/api/v1/auth/reset-password` using transactional email tokens.
  - Enforce `@username` validation and uniqueness in `RegisterRequest` and `AuthService::register`.
  - Fix CORS configuration in `config/cors.php`, removing raw wildcard headers from `public/index.php`.
- **Exit Gate**: Multi-device login test verifies customer logged in on website remains logged in when logging in on mobile. All auth endpoints return HTTP 200/201.

### Phase 3: Routing & API v1 Harmonization
- **Objective**: Deliver a unified, canonical RESTful API v1 consumed by mobile and web clients.
- **Tasks**:
  - Expose canonical routes: `/api/v1/services`, `/api/v1/barbers`, `/api/v1/availability`, `/api/v1/appointments`, `/api/v1/service-zones`.
  - Maintain backward compatibility for existing Vue 3 routes (`/api/public/*`, `/api/customer/*`, etc.).
  - Fix `axios.js` 401 interceptor to redirect contextually based on the user's current portal (`/admin/login`, `/barber/login`, or `/customer/login`).
  - Standardize JSON error envelopes across all controllers using `ApiResponse::error($code, $message, $details, $status)`.
- **Exit Gate**: Automated HTTP tests verify all canonical endpoints return structured JSON with zero 404s or 500s.

### Phase 4: Single Unified Expo Mobile App Convergence
- **Objective**: Consolidate fragmented customer and barber apps into ONE high-performance Expo application.
- **Tasks**:
  - Merge features into `apps/mobile/` (or converged root `candycutz-mobile-app`).
  - Implement role-based navigation guards in `_layout.tsx`:
    - Customer role mounts `(customer)` tabs (Home, Explore, Book, Appointments, Profile).
    - Barber role mounts `(barber)` tabs (Dashboard, Queue, Schedule, Walk-In, Profile).
    - Unauthenticated state mounts `(auth)` stack (Login, Register, Forgot Password).
  - Connect client to canonical `/api/v1/*` endpoints using `expo-secure-store` tokens.
- **Exit Gate**: Single Expo application runs cleanly, allowing customer login and barber login with immediate, correct role switching.

### Phase 5: Database & Business Logic Hardening
- **Objective**: Guarantee double-booking prevention, reliable payments, and async notifications.
- **Tasks**:
  - Require `stripe/stripe-php` in `barbing-saloon-api`.
  - Migrate all appointment creation logic to `App\Services\Booking\BookingService` with pessimistic row locking (`SELECT FOR UPDATE`).
  - Register Stripe webhook listener (`/api/v1/payments/webhook`) with idempotent event processing in `payment_transactions`.
  - Wire Brevo transactional email delivery through queued jobs with automatic retry policies.
- **Exit Gate**: Concurrency test verifies zero double-bookings under simultaneous slot reservation requests. Stripe test charge updates appointment to `confirmed`.

### Phase 6: Website Integration & Zero-Regression Verification
- **Objective**: Confirm full visual and operational health of the existing Vue 3 client.
- **Tasks**:
  - Verify landing page, booking wizard, customer dashboard, barber dashboard, and admin CMS.
  - Compile production bundle (`npm run build`) and synchronize static assets.
- **Exit Gate**: Zero console errors, zero 404s, and verified persistence across all three web portals.

### Phase 7: Single-VPS Docker & Nginx Infrastructure
- **Objective**: Provide a turnkey, production-ready single-VPS topology.
- **Tasks**:
  - Author production `Dockerfile` for Laravel using PHP-FPM 8.2 and OPcache.
  - Author production `nginx.conf` reverse proxying `/api` to PHP-FPM and serving built web assets statically with client-side SPA routing.
  - Author production `docker-compose.yml` orchestrating `nginx`, `app`, `db`, `redis`, and `worker`.
  - Author automated database backup and restore scripts in `infrastructure/scripts/`.
- **Exit Gate**: `docker compose up -d` boots entire platform cleanly on VPS with functional health check at `/api/health`.

### Phase 8: End-to-End QA & Penetration Testing
- **Objective**: Validate complete business scenarios across all clients and roles.
- **Tasks**:
  - Run full authentication test matrix (Web, Mobile, Google, Apple, Password Reset, Deactivation).
  - Run booking and payment test matrix (In-Shop, Home Service, Cancellation, Refund).
  - Audit OWASP compliance, rate limiting, SQL injection defense, and secret masking.
- **Exit Gate**: 100% test matrix pass rate.

### Phase 9: Production Store Release & Launch Handover
- **Objective**: Prepare production application store bundles and operator handovers.
- **Tasks**:
  - Configure `eas.json` for Android (AAB) and iOS production builds.
  - Generate app store icons, splash screens, and privacy policy pages.
  - Author troubleshooting guides in `docs/debugging/` for non-DevOps maintainers.
- **Exit Gate**: Production mobile builds compiled; complete runbooks delivered.
