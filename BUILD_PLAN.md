# Candycutz — Master Build Plan & Dependency Matrix

## 1. Overview
This build plan defines the precise dependency chain and acceptance gates for engineering the Candycutz platform. No milestone may commence until its upstream dependencies have met all Definition of Done (DoD) criteria.

---

## 2. Milestone Breakdown & Dependencies

```
[Phase 0: Audit] ─────────► [Phase 1: Project Brain Docs]
                                   │
                                   ▼
[Phase 2: Database Schema & Migrations]
                                   │
                                   ▼
[Phase 3: Laravel Backend Convergence & Security]
                                   │
         ┌─────────────────────────┴─────────────────────────┐
         ▼                                                   ▼
[Phase 4: Identity & Auth]                         [Phase 7: CMS & Theme Studio]
         │                                                   │
         ▼                                                   ▼
[Phase 5: Booking & Home Services]                 [Phase 6: Stripe & Brevo]
         │                                                   │
         └─────────────────────────┬─────────────────────────┘
                                   │
         ┌─────────────────────────┴─────────────────────────┐
         ▼                                                   ▼
[Phase 8: Customer Mobile App]                     [Phase 9: Barber Mobile App]
         │                                                   │
         └─────────────────────────┬─────────────────────────┘
                                   │
                                   ▼
[Phase 10: Web Client Harmonization & Integration]
                                   │
                                   ▼
[Phase 11: End-to-End QA & Security Audit]
                                   │
                                   ▼
[Phase 12: Production Store Release & Launch]
```

---

## 3. Detailed Phase Scope & Gates

### Phase 1: Project Brain & Living Documentation
- **Inputs**: Audit findings from Phase 0.
- **Tasks**:
  - Author `AGENTS.md`, `PROJECT_STATE.md`, `BUILD_PLAN.md`, `DECISIONS.md`.
  - Author all 17 technical documents in `docs/` (`PRD.md`, `Architecture.md`, `Database.md`, `API.md`, `Booking-Engine.md`, `Home-Services.md`, `Payments.md`, `Notifications.md`, `CMS.md`, `Mobile.md`, `Design-System.md`, `Security.md`, etc.).
- **Exit Gate**: 100% of required documentation files exist with complete architectural specifications and no missing sections.

### Phase 2: Database Schema & Normalized Migrations
- **Inputs**: `docs/Database.md`.
- **Tasks**:
  - Implement 24 normalized entities.
  - Implement foreign keys, cascading constraints, unique indexes, and soft deletes.
  - Seed baseline business records for Keffi operational center.
- **Exit Gate**: Migrations run cleanly (`php artisan migrate:fresh --seed`) without SQL syntax or constraint errors.

### Phase 3: Laravel Backend Convergence & Core Security
- **Inputs**: Laravel 11.54 core, Phase 2 database.
- **Tasks**:
  - Route `public/index.php` through Laravel HTTP Kernel.
  - Register security middleware: `CheckRole`, `SanitizeInput`, `ForceHttps`, `SecurityHeaders`, `LogApiRequest`.
  - Standardize error envelopes via `ApiResponse`.
- **Exit Gate**: All public endpoints (`/api/public/*`) respond with valid JSON via Laravel kernel.

### Phase 4: Identity & Username-First Auth Domain
- **Inputs**: User model, Sanctum, Phase 3 gateway.
- **Tasks**:
  - Username validation (`/^[a-zA-Z0-9_-]{3,30}$/`), 90-day change limits.
  - Social authentication handlers (Google & Apple ID token verification).
  - Soft deactivation flow and Super Admin restoration.
- **Exit Gate**: Unit tests verify username collision prevention, social login onboarding, and deactivation states.

### Phase 5: Booking Engine & Home Services Core
- **Inputs**: Models for appointments, branches, service zones, barbers, working hours.
- **Tasks**:
  - Server-side slot availability calculation with duration and buffer rules.
  - Concurrency collision lock via `SELECT FOR UPDATE` in transactions.
  - Home-service distance calculation, zone fee lookup, and location privacy masking.
- **Exit Gate**: Concurrency integration test verifies zero double-bookings under simultaneous requests.

### Phase 6: Stripe Payments & Brevo Transactional Email
- **Inputs**: Stripe API keys, Brevo SMTP/API credentials.
- **Tasks**:
  - `PaymentService` abstraction with `StripePaymentProvider`.
  - Webhook listener with idempotency key deduplication.
  - Brevo domain event listener with queue worker retries.
- **Exit Gate**: Mock webhook triggers successful booking confirmation and dispatches Brevo notification.

### Phase 7: CMS "God Mode" & Theme Studio
- **Inputs**: `theme_settings`, `theme_versions`, `cms_pages`, `cms_sections`.
- **Tasks**:
  - Theme Studio with Draft -> Preview -> Publish lifecycle and rollback capability.
  - Super Admin overrides for appointments, availability, pricing, and users with full audit logging.
- **Exit Gate**: Publishing a theme version updates theme tokens across web and mobile clients.

### Phase 8: Customer Mobile Application (Expo / React Native)
- **Inputs**: `docs/Mobile.md`, API v1 endpoints.
- **Tasks**:
  - Scaffold `candycutz-customer-app` with Expo Router, TypeScript, NativeWind.
  - Branded splash, session restoration, onboarding.
  - Discovery, catalog, booking stepper (In-Shop & Home Service), appointments, profile.
- **Exit Gate**: Application runs without errors on iOS Simulator and Android Emulator.

### Phase 9: Barber / Staff Mobile Application (Expo / React Native)
- **Inputs**: `docs/Mobile.md`, API v1 barber endpoints.
- **Tasks**:
  - Scaffold `candycutz-barber-app` with Expo Router, TypeScript, NativeWind.
  - Daily queue, appointment status transitions (Check-In, In-Progress, Complete, No-Show).
  - Working hours and schedule block manager.
- **Exit Gate**: Barber can transition appointment states and block personal time from the mobile app.

### Phase 10: Web Client Harmonization & Integration
- **Inputs**: Vue 3 `barbing-saloon-web`, converged Laravel backend.
- **Tasks**:
  - Align frontend Axios calls with Laravel API v1 response envelope.
  - Verify theme styling, booking stepper, customer dashboard, barber dashboard, admin panel.
- **Exit Gate**: Zero visual or functional regression on the existing web platform.

### Phase 11: End-to-End QA, Security & Concurrency Audit
- **Tasks**:
  - Automated PHPUnit / Pest test execution.
  - TypeScript compilation checks on mobile apps.
  - Security audit: OWASP compliance, rate limiting, SQL injection defense, secret masking.
- **Exit Gate**: 100% test suite pass rate and clean audit report.

### Phase 12: Production Store Release & Launch
- **Tasks**:
  - Configure EAS Build and EAS Submit (`eas.json`).
  - Generate app store icons, splash screens, and privacy disclosures.
  - Prepare Docker production deployment scripts.
- **Exit Gate**: Production-ready builds generated for Android (AAB) and iOS.
