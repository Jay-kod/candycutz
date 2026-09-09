# Candycutz — Architectural Decision Records (ADR)

## ADR-001: Backend Convergence into Laravel 11 HTTP Kernel
- **Context**: The legacy project contained procedural scripts in `public/index.php` that bypassed Laravel framework features.
- **Decision**: Route all requests through Laravel 11's HTTP Kernel (`bootstrap/app.php`), enforcing middleware, Sanctum authentication, and modular controllers.
- **Consequences**: Unifies validation, rate limiting, and transactions. Preserves `{ success: true, data: [...] }` envelopes for client compatibility.

---

## ADR-002: Native Expo/React Native Mobile Architecture vs WebView
- **Context**: Candycutz requires dedicated mobile experiences for Customers and Barbers.
- **Decision**: Build a native Expo React Native application using TypeScript, Expo Router, and NativeWind tokens. Reject wrapping the website inside a mobile WebView.
- **Consequences**: Delivers 60fps animations, native gestures, hardware-backed token storage (`expo-secure-store`), and offline push notifications.

---

## ADR-003: Username-First Public Identity
- **Context**: Customer and barber engagement requires a distinct public identity without exposing private real names or emails.
- **Decision**: Enforce unique, case-insensitive usernames (`@username`) across all users. Validate with regex `/^[a-zA-Z0-9_-]{3,30}$/`. Restrict changes to once every 90 days, with Super Admin bypass in God Mode.
- **Consequences**: Protects user privacy, supports social community features, and establishes a consistent display handle across web and mobile apps.

---

## ADR-004: Concurrency Protection via Pessimistic Row Locking
- **Context**: High-demand slots (e.g. Friday/Saturday prime grooming slots) are susceptible to simultaneous double-booking race conditions.
- **Decision**: Enforce atomic slot locking using database transactions with `SELECT ... FOR UPDATE` over the barber schedule window. Reject trusting client-side timestamps.
- **Consequences**: Completely eliminates double-booking race conditions at the database level with minimal lock contention duration (<50ms).

---

## ADR-005: Multi-Branch & Service Zone Relational Modeling
- **Context**: The business initially launches in Keffi, Nasarawa State, but plans expansion across multiple Keffi zones, additional branches, and other cities.
- **Decision**: Architect schema with `businesses` -> `branches` -> `service_zones` / `barbers` / `services` hierarchy. All core entities carry `branch_id`.
- **Consequences**: Zero architectural rewrites required when opening branch #2 or expanding home services into new territories.

---

## ADR-006: Stripe Payment Abstraction & Authoritative Webhooks
- **Context**: Stripe handles online payments, deposits, and refunds. Client network connections in Nigeria may drop before client receives confirmation.
- **Decision**: Treat Stripe webhooks as the single source of truth for payment confirmation. Use idempotency keys stored in `payment_transactions` to handle retries. The mobile client is never trusted to report payment success.
- **Consequences**: Eliminates fraudulent status spoofing and guarantees financial reconciliation even if customer network drops immediately after authorization.

---

## ADR-007: Event-Driven Transactional Email via Brevo Adapter
- **Context**: Domain events (registration, booking, reminders, cancellations) must trigger branded emails through Brevo without blocking HTTP request execution.
- **Decision**: Dispatch Laravel Domain Events (`BookingCreated`, `AppointmentCancelled`) listened to by queued jobs that format Blade templates and dispatch via a dedicated `BrevoNotificationAdapter`.
- **Consequences**: Keeps API endpoints responsive (<100ms) while guaranteeing email delivery through asynchronous queue workers with automatic retries and failure logging.

---

## ADR-008: CMS "God Mode" with Versioned Theme Studio
- **Context**: Super Admins require total operational authority to override schedules, adjust pricing, and modify theme colors without requiring code deployments.
- **Decision**: Implement a versioned Theme Studio supporting Draft -> Preview -> Publish lifecycle with rollback capability. All privileged administrative overrides are strictly recorded in an immutable `audit_logs` table.
- **Consequences**: Business operators have complete visual and operational control, while maintaining a tamper-evident audit trail for accountability and compliance.

---

## ADR-009: Unified Single Expo Mobile App with Role-Based Navigation
- **Context**: The repository was previously split into two fragmented Expo applications (`candycutz-customer-app` and `candycutz-barber-app`), duplicating dependencies, theme tokens, and network configurations.
- **Decision**: Consolidate into **ONE** Expo mobile application (`apps/mobile` or `candycutz-mobile-app`). Implement root navigation guards in `_layout.tsx` that inspect the server-verified role:
  - `role === 'customer'` -> Mounts Customer Navigation (Home, Explore, Book, Appointments, Profile).
  - `role === 'barber'` -> Mounts Barber Navigation (Dashboard, Queue, Schedule, Walk-In, Profile).
  - Unauthenticated -> Mounts Auth Stack (Login, Register, Forgot Password).
- **Consequences**: Halves maintenance overhead, eliminates code duplication, unifies app store presence, and allows staff to test customer experiences without switching applications.

---

## ADR-010: Multi-Device Sanctum Token Scoping vs Session Purging
- **Context**: Previous backend login logic invoked `$user->tokens()->delete()`, instantly destroying web sessions when logging into mobile, and vice versa.
- **Decision**: Issue device-scoped Sanctum tokens (`web-client`, `mobile-client`) without clearing existing tokens during normal login. Provide explicit "Sign out of all devices" endpoint for security overrides.
- **Consequences**: Users can remain concurrently logged in across their personal laptop and mobile phone without session collision.

---

## ADR-011: Canonical RESTful API v1 with Dual-Client Compatibility
- **Context**: Web client called actor-prefixed routes (`/api/public/*`, `/api/customer/*`), while the mobile client expected standard RESTful domain routes (`/api/v1/services`, `/api/v1/appointments`), leading to widespread 404 errors.
- **Decision**: Establish canonical `/api/v1/*` RESTful domain routes as the authoritative API contract for all mobile and external consumers. Maintain backward-compatible route aliases for existing Vue 3 endpoints.
- **Consequences**: Resolves all mobile 404 errors without causing breaking regressions on the functioning Vue 3 web client.

---

## ADR-012: Single-VPS Docker Topology with Nginx Reverse Proxy
- **Context**: The platform requires a reliable, predictable deployment on a single VPS without Kubernetes or complex cloud infrastructure.
- **Decision**: Orchestrate the production VPS using Docker Compose:
  - `nginx`: Reverse proxy handling SSL termination, proxying `/api` to PHP-FPM, and serving pre-built Vue 3 static assets.
  - `app`: PHP 8.2-FPM container with OPcache.
  - `db`: MySQL 8.0 with persistent volume storage.
  - `redis`: In-memory cache and queue driver.
  - `worker`: Dedicated `php artisan queue:work` container for Brevo emails and notifications.
- **Consequences**: Highly predictable, isolated, easily backed up, and straightforward to debug for non-DevOps engineers.
