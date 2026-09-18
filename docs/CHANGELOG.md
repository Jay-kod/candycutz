# CandyCutz Platform Changelog

All notable changes, architectural consolidations, and production hardening milestones across the CandyCutz platform are documented herein.

---

## [Phase 9] - Production Readiness, Disaster Recovery & Governance
* **Date:** September 2026
* **Scope:** Disaster recovery, health checks, CI hardening, and authoritative documentation.
* **Key Deliverables:**
  * **Dynamic Health Check:** Connected `GET /api/v1/health` dynamically verifying live database queries and storage disk write availability with versioned JSON envelopes (`status: "healthy"`).
  * **Disaster Recovery Runbook & Commands:** Built `php artisan db:backup` and `php artisan db:restore` commands supporting transactional SQLite and MySQL backups with post-restore data integrity validation.
  * **Disaster Recovery Automated Drill:** Implemented automated test `tests/Feature/DatabaseBackupRestoreTest.php` proving canary state recovery after database mutation.
  * **Hardened CI Workflow:** Updated `.github/workflows/ci.yml` removing all `continue-on-error: true` flags, enabling strict branch coverage (`remediation/**`), adding web unit tests, production build verification, and mobile test suites.
  * **Authoritative Documentation Suite:** Created `docs/DATABASE.md`, `docs/SECURITY.md`, `docs/DEVELOPMENT.md`, and `docs/CHANGELOG.md`.

---

## [Phase 8] - Mobile Architecture, Contract Enforcement & Push Notifications
* **Date:** September 2026
* **Scope:** React Native / Expo mobile application, OpenAPI 3.0 specification, contract testing, and device token management.
* **Key Deliverables:**
  * **OpenAPI 3.0 Specification:** Generated comprehensive `docs/openapi.json` and `public/openapi.json` encompassing 113 routes across the platform.
  * **Full API Documentation:** Published exhaustive `docs/API.md` documenting request/response payloads, authentication, and error codes.
  * **Device Token Management:** Built `POST` and `DELETE /api/v1/notifications/device-token` endpoints backed by 11 unit/feature tests.
  * **Mobile Client Type Generation:** Synchronized `src/api/types.ts` with the OpenAPI spec, eliminating ad-hoc types and achieving 0 TypeScript errors.
  * **Automated Mobile Verification:** Authored `__tests__/contract.test.js` validating schema adherence, client exports, and notification services (5/5 tests passing).

---

## [Phase 7] - Web Frontend Architecture & Unified Design System
* **Date:** September 2026
* **Scope:** Vue 3 + Vite web application refactoring, component modularity, and automated E2E tests.
* **Key Deliverables:**
  * **Unified Design System:** Implemented atomic Base components (`BaseButton`, `BaseInput`, `BaseModal`, `BaseBadge`, `BaseCard`, `BaseAlert`) in `src/core/components`.
  * **Vue Router & RBAC Navigation Guards:** Structured routing with dynamic title tags, meta descriptions, and role-based redirects (`customer`, `barber`, `admin`).
  * **State Management:** Standardized Pinia stores for authentication, appointments, and notification management.
  * **Automated Testing Suite:** Implemented Vitest component unit tests and Playwright end-to-end user flows for service booking and payment checkout.
  * **Lint & Type Hygiene:** Clean `npm run lint` and `vue-tsc --noEmit` across all pages.

---

## [Phase 6] - Admin Governance, CMS Control Plane & Static Analysis
* **Date:** September 2026
* **Scope:** Salon governance, financial reporting, CMS management, and Larastan Level 6 certification.
* **Key Deliverables:**
  * **Administrative Control Plane:** Added endpoints for salon working hours, service catalog CRUD, user management, and revenue analytics.
  * **CMS Endpoints:** Structured gallery management, blog publishing, and customer testimonial moderation.
  * **Strict Static Analysis:** Eliminated all PHPStan warnings across 210 backend classes, achieving 0 errors at Level 6.

---

## [Phase 5] - Barber Workspace & Chair Management
* **Date:** September 2026
* **Scope:** Barber scheduling, chair status, and appointment management.
* **Key Deliverables:**
  * **Chair Status Engine:** Real-time chair state transitions (`available`, `busy`, `on_break`, `offline`).
  * **Barber Operating Schedules:** Day-of-week working hours and blocked period calendar management.
  * **Barber Appointment Operations:** Assigned appointment status transitions and walk-in customer booking.

---

## [Phase 4] - Customer Lifecycle & Notifications
* **Date:** September 2026
* **Scope:** Customer profiles, booking history, reviews, and notification preferences.
* **Key Deliverables:**
  * **Customer History & Reviews:** Rating and review submission for completed bookings.
  * **In-App Notification Center:** Endpoints for retrieving, marking as read, and deleting notifications.
  * **Notification Settings:** Configurable user delivery preferences.

---

## [Phase 3] - Payment Orchestration & Manual Transfers
* **Date:** September 2026
* **Scope:** Paystack gateway integration, manual bank transfers, receipt verification state machine, and audit trails.
* **Key Deliverables:**
  * **Paystack Webhook Processing:** Cryptographic HMAC-SHA512 verification with idempotent appointment confirmation.
  * **Manual Bank Transfers:** Multi-state receipt verification workflow (`awaiting_transfer` -> `under_review` -> `completed` / `rejected`).
  * **Payment Audit Logging:** Immutable record of receipt verification, rejection reasons, and reviewer tracking.

---

## [Phase 2] - Booking Engine & Concurrency Locking
* **Date:** September 2026
* **Scope:** Appointment scheduling, availability algorithm, and race condition prevention.
* **Key Deliverables:**
  * **Pessimistic Row Locking:** Implemented `lockForUpdate()` in `BookingService` to eliminate double-booking hazards.
  * **Dynamic Slot Computation:** Algorithm factoring in barber operating hours, blocked intervals, existing bookings, and service durations.
  * **Appointment State Machine:** Enforced legal appointment status transitions.

---

## [Phase 1] - Authentication & RBAC Hardening
* **Date:** September 2026
* **Scope:** Personal access token lifecycle, secure password hashing, and role policies.
* **Key Deliverables:**
  * **Sanctum Authentication:** Selective token revocation on logout (`delete()` on active token rather than wiping all user sessions).
  * **RBAC Infrastructure:** Middleware and policies securing customer, barber, and admin resources.

---

## [Phase 0] - Ground Truth Audit & Architectural Remediation
* **Date:** September 2026
* **Scope:** Repository inventory, consolidation to target architecture, and characterisation test baseline.
* **Key Deliverables:**
  * **Architecture Charter:** Authoritative `ARCHITECTURE.md` establishing single-backend, single-database, single-mobile principles.
  * **Consolidation:** Deprecated and archived duplicate projects (`frontend/`, `server/`, duplicate roots).
  * **Test Baseline:** Established characterisation tests across all core domain boundaries.
