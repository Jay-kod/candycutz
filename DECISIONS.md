# Candycutz — Architectural Decision Records (ADR)

## ADR-001: Backend Convergence into Laravel 11 HTTP Kernel
- **Context**: The existing project contained dual backend entry points: modular Laravel 11 controllers in `app/Modules/` and monolithic procedural scripts in `public/index.php`.
- **Decision**: Transition `public/index.php` to boot Laravel 11's standard HTTP Kernel (`(require_once __DIR__.'/../bootstrap/app.php')->handleRequest(Request::capture())`), routing all requests through Laravel middleware, Sanctum authentication, and modular controllers.
- **Consequences**: Unifies security, rate limiting, request validation, and database transactions into a single authoritative pipeline. Requires ensuring API response shapes match `{ success: true, message: "...", data: [...] }` to preserve full compatibility with the existing Vue 3 web client.

---

## ADR-002: Native Expo/React Native Mobile Architecture vs WebView
- **Context**: Candycutz requires dedicated Android and iOS applications for Customers and Barbers.
- **Decision**: Build two separate, native Expo React Native applications (`candycutz-customer-app` and `candycutz-barber-app`) using TypeScript, Expo Router, and NativeWind tokens. Reject WebView wrapping of the existing web app.
- **Consequences**: Delivers high-performance 60fps animations, native gestures, secure hardware-backed token storage (`expo-secure-store`), offline caching, and native push notifications (`expo-notifications`).

---

## ADR-003: Username-First Public Identity
- **Context**: Customer and barber engagement requires a personal, distinct identity without exposing private real names or emails.
- **Decision**: Enforce unique, case-insensitive usernames (`@username`) across customers and barbers. Store real names privately. Validate with regex `/^[a-zA-Z0-9_-]{3,30}$/`. Restrict changes to once every 90 days, with Super Admin bypass in God Mode.
- **Consequences**: Protects user privacy, supports social community features, and establishes a consistent display handle across web and mobile apps.

---

## ADR-004: Concurrency Protection via Pessimistic Row Locking
- **Context**: High-demand slots (e.g., Friday/Saturday prime grooming slots) are susceptible to simultaneous double-booking race conditions.
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
