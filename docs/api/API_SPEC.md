# CANDYCUTZ — CANONICAL API SPECIFICATION (`/api/v1/*`)

**Version:** 1.0.0-canonical  
**Date:** 2026-10-23  
**Status:** Authoritative API Contract  
**Authority:** `ARCHITECTURE.md` §2  

---

## 1. ARCHITECTURAL & TRANSPORT STANDARDS

### 1.1 Base URL & Mounting
The API exposes a **single mount point**: `/api/v1/*`.
All legacy paths (`/api/customer/*`, `/api/barber/*`, `/api/admin/*`, `/api/super_admin/*`) return `410 Gone` with a guidance payload.

### 1.2 Unified JSON Response Envelopes
Every response adheres to the standardized envelope structure:

#### Success Response (`200 OK`, `201 Created`):
```json
{
  "success": true,
  "message": "Operation completed successfully.",
  "data": {},
  "meta": {}
}
```

#### Error Response (`400`, `401`, `403`, `404`, `422`, `429`, `500`):
```json
{
  "success": false,
  "message": "Human-readable error description.",
  "error": {
    "code": "ERROR_MACHINE_CODE",
    "details": {},
    "action": "none|retry|logout|fix_input|wait",
    "retryable": false,
    "retry_after": null
  }
}
```

### 1.3 Transport Headers
- `Authorization: Bearer <token>`
- `Content-Type: application/json`
- `Accept: application/json`
- `X-Client-Type: web | mobile`
- `X-App-Version: 1.0.0`
- `X-Request-Id: req_<timestamp><random>`
- `Idempotency-Key: <uuid>` (for state-mutating checkout / payment operations)

### 1.4 Money Representation
All monetary fields (`price`, `total_amount`, `grand_total`, `travel_fee`, `deposit_amount`) are handled server-side as **integer minor units (kobo)**:
- `100 kobo = ₦1.00 NGN`.
- Frontend displays formatted Naira (`₦`) via shared formatters.

---

## 2. CORE DOMAIN API ENDPOINTS

### 2.1 Authentication & Identity Module (`Domain\Identity`)

#### `POST /api/v1/auth/register`
- **Purpose:** Customer self-registration.
- **Auth:** Public. Rate limited: `throttle:auth-sensitive` (10 per minute per IP).
- **Request Payload:**
  - `name`: string, required, max 255
  - `username`: string, required, unique, regex: `^[a-zA-Z0-9_.]+$`
  - `email`: string, required, email, unique
  - `phone`: string, required, Nigerian format (`+234...` or `0...`)
  - `password`: string, required, min 8
  - `password_confirmation`: string, required, same:password
- **Response:** `201 Created` with `AuthUserResource` and Sanctum plain-text token (30-day expiry).
- **Consumers:** Web Customer Signup, Mobile Customer Registration.
- **Automated Tests:** `AuthTest::test_user_can_register`, `AuthHardeningTest`.

#### `POST /api/v1/auth/login`
- **Purpose:** Universal multi-role credential authentication (Email, Username, or Phone).
- **Auth:** Public. Rate limited: `throttle:auth-sensitive`.
- **Request Payload:**
  - `identity`: string, required (matches email, `@username`, or phone)
  - `password`: string, required
  - `device_name`: string, optional (e.g. `Pixel 7`, `Chrome Windows`)
- **Response:** `200 OK` with user profile, role, token, and optional barber details if actor is a barber.
- **Consumers:** Web Portal Login, Mobile App Login, Biometric Fallback.
- **Automated Tests:** `AuthTest`, `AuthHardeningTest`, `AuthorizationMatrixTest`.

#### `GET /api/v1/auth/me`
- **Purpose:** Hydrates current user identity, active role, permissions, and barber chair linkage.
- **Auth:** `auth:sanctum`.
- **Response:** `200 OK` with `AuthUserResource`.
- **Consumers:** Web bootstrap (`auth.store.js`), Mobile app startup (`authStore.ts`).

#### `POST /api/v1/auth/logout`
- **Purpose:** Revokes current device's personal access token without affecting active sessions on other devices.
- **Auth:** `auth:sanctum`.
- **Response:** `200 OK`.

---

### 2.2 Booking & Appointment Engine (`Domain\Booking`)

#### `GET /api/v1/availability`
- **Purpose:** Calculates real-time available time slots for a given barber, service, and date.
- **Auth:** Public.
- **Query Parameters:**
  - `barber_id`: integer, required, exists:barbers,id
  - `service_id`: integer, optional
  - `date`: date (YYYY-MM-DD), required, today or future
- **Logic:** Queries working hours, subtracts blocked periods, subtracts confirmed appointments, and projects valid slot intervals.
- **Consumers:** Web Booking Stepper, Mobile Appointment Selector.
- **Automated Tests:** `AppointmentTest`, `ConcurrencyTest`.

#### `POST /api/v1/appointments`
- **Purpose:** Atomically creates a pending customer booking with pessimistic row collision locking.
- **Auth:** `auth:sanctum` (`customer`).
- **Request Payload:**
  - `barber_id`: integer, required
  - `service_id`: integer, required
  - `appointment_date`: date (YYYY-MM-DD), required
  - `appointment_time`: time (HH:MM), required
  - `appointment_type`: `in_shop` | `home_service`
  - `notes`: string, optional
  - `destination_address`: object, required if `home_service`
- **Concurrency Safeguard:**
  1. Barber row `lockForUpdate()`
  2. Overlapping appointment collision check with `lockForUpdate()`
  3. Database-level `active_slot_key` unique constraint backstop
- **Response:** `201 Created` with `AppointmentResource`.
- **Consumers:** Web Customer Booking Flow, Mobile Customer Booking Flow.
- **Automated Tests:** `AppointmentStateMachineTest`, `BookingServiceTest`, `ConcurrencyTest`.

#### `POST /api/v1/appointments/walk-in`
- **Purpose:** Registers an immediate walk-in client from staff desk or admin portal.
- **Auth:** `auth:sanctum` (`barber`, `admin`, `super_admin`).
- **Request Payload:**
  - `barber_id`: integer, required if actor is admin
  - `service_id`: integer, required
  - `customer_name`: string, required
  - `customer_phone`: string, required
  - `customer_email`: string, optional
- **Response:** `201 Created` with immediate `confirmed` status.
- **Consumers:** Web Barber Walk-in Desk, Web Admin Walk-in Portal, Mobile Barber Queue Desk.
- **Automated Tests:** `AppointmentTest`, `AppointmentStateMachineTest`.

#### `PATCH /api/v1/appointments/{id}/cancel`
- **Purpose:** Transitions appointment to `cancelled` status.
- **Auth:** `auth:sanctum`. Policy checked (`AppointmentPolicy::cancel`).
- **Policy Enforcement:** Customers may only cancel their own bookings; Barbers may only cancel bookings assigned to their chair; Admins may cancel any booking.
- **Response:** `200 OK`.
- **Consumers:** Customer Appointments, Barber Desk, Admin Console.

---

### 2.3 Payments & Financial Domain (`Domain\Payment`)

#### `POST /api/v1/payments/checkout`
- **Purpose:** Initiates checkout for an appointment.
- **Auth:** `auth:sanctum`.
- **Headers:** Optional `Idempotency-Key`.
- **Request Payload:**
  - `appointment_id`: integer, required
  - `payment_method`: `paystack` | `manual_transfer` | `pay_at_venue`
- **Business Rule:** Server calculates amount due from canonical service pricing. Client-supplied amount is completely ignored.
- **Response:**
  - Paystack: `{ "authorization_url": "https://checkout.paystack.com/...", "reference": "CC-..." }`
  - Manual Transfer: `{ "bank_details": { ... }, "status": "awaiting_transfer" }`
- **Consumers:** Web Payment Step, Mobile Checkout.
- **Automated Tests:** `ManualTransferPaymentTest`, `Phase2BackendTest`.

#### `POST /api/v1/payments/appointments/{appointmentId}/receipt`
- **Purpose:** Uploads bank-transfer payment proof for manual verification.
- **Auth:** `auth:sanctum` (`customer`).
- **Storage:** Stored in private disk (`receipts/private/`), served exclusively via signed authorized routes.
- **State Transition:** Progresses manual transfer state machine from `awaiting_transfer` to `under_review`.
- **Consumers:** Web Receipt Uploader, Mobile Camera Receipt Upload.
- **Automated Tests:** `ManualTransferPaymentTest`, `PaymentTest`.

#### `POST /api/v1/payments/webhook`
- **Purpose:** Ingestion of Paystack payment event webhooks.
- **Auth:** Public endpoint. Exempt from throttle.
- **Security:** Verified with HMAC-SHA512 header (`x-paystack-signature`) against `PAYSTACK_SECRET_KEY`.
- **Idempotency:** `WebhookEvent::firstOrCreate(['provider_event_key' => ...])`. Duplicate events return immediate `200 OK`.
- **Single-Writer Semantics:** Delegated to `PaymentConfirmer::confirm()` inside row-level lock.
- **Automated Tests:** `Phase2BackendTest`, `PaymentWebhookTest`.

---

### 2.4 Catalogue & Public Marketing (`Domain\Catalogue` & `Domain\Content`)

#### `GET /api/v1/services`
- **Purpose:** Public active service catalog with duration, pricing, and category.
- **Auth:** Public. Cached in Redis/Array (`CatalogueCache::remember`).
- **Response:** `200 OK` array of `ServiceResource`.

#### `GET /api/v1/barbers`
- **Purpose:** Public active barber team profiles, specializations, ratings, and avatar media.
- **Auth:** Public. Cached in Redis/Array.
- **Response:** `200 OK` array of `BarberResource`.

#### `GET /api/v1/gallery`
- **Purpose:** Filterable haircut cuts, beard trims, and shop photos.
- **Auth:** Public.
- **Query Parameters:** `category` (`haircut`, `beard`, `combo`, `before_after`, `shop`), `barber_id`.
- **Response:** `200 OK` array of `GalleryResource`.

---

### 2.5 Admin, Telemetry & System Health (`Domain\Admin` & `Domain\System`)

#### `GET /api/v1/health`
- **Purpose:** Public lightweight liveness probe for uptime monitoring and load balancers.
- **Auth:** Public. Fast execution (< 5ms).
- **Response:** `{"status": "ok", "timestamp": "...", "version": "1.0.0"}`.

#### `GET /api/v1/health/ready`
- **Purpose:** Readiness probe checking database connectivity, cache connection, and disk storage.
- **Auth:** Public (non-leaking diagnostics).
- **Response:** `200 OK` if all operational subsystems are healthy; `503 Service Unavailable` if core subsystem is down.

#### `GET /api/v1/admin/gate/metrics`
- **Purpose:** Real-time API Gate telemetry: total requests, p95 latency, query volume, rate-limit events.
- **Auth:** `auth:sanctum`, `check.role:admin,super_admin`.
- **Response:** Aggregated 24h gate statistics.
- **Automated Tests:** `ApiGateTest`, `ApiGateEfficiencyTest`.

#### `POST /api/v1/admin/system/backups`
- **Purpose:** On-demand database backup creation with gzip compression.
- **Auth:** `auth:sanctum`, `check.role:admin,super_admin`.
- **Response:** `201 Created` with backup metadata.
- **Automated Tests:** `SystemHealthAndBackupTest`.

