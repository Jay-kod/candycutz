# Candycutz — Platform Architecture Specification

## 1. System Identity & Core Architectural Principles

Candycutz is a commercial grooming platform operating in Keffi, Nasarawa State, Nigeria. It provides on-demand and scheduled barbershop grooming, VIP in-shop appointments, and luxury home services across Keffi and surrounding educational campuses (Nasarawa State University Keffi Main and Pyanku campuses).

The platform strictly adheres to the following eight non-negotiable architectural mandates:

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

### Core Tenets
1. **Single Backend & Database Authority**: Laravel 11 is the sole authoritative engine for identity, pricing, availability, booking transactions, and audit records. Frontends never compute pricing or declare payment/booking success.
2. **Single Unified Mobile Client**: There is only **one** Expo mobile app (`apps/mobile` / `candycutz-mobile-app`). It implements server-verified role-based navigation:
   - `role === 'customer'` -> Customer Navigation (Home, Explore, Book, Appointments, Profile).
   - `role === 'barber'` -> Barber Navigation (Dashboard, Queue, Schedule, Walk-In, Profile).
3. **No WebViews**: The mobile app is natively compiled with Expo / React Native, using hardware-backed keychains (`expo-secure-store`) and native gesture handlers.
4. **Preserved Vue 3 Web Platform**: The existing Vue 3 client (`apps/web` / `barbing-saloon-web`) is fully preserved and communicates via canonical RESTful `/api/v1/*` endpoints with backward-compatible aliases.
5. **Single-VPS Production Topology**: Production runs under Docker Compose on a single VPS hosting Nginx (Reverse Proxy & Static Asset server), PHP 8.2-FPM, MySQL 8.0, Redis (Cache & Queues), and a Supervisor Queue Worker.

---

## 2. High-Level Production System Topology

```text
                                  INTERNET
                                      │
                                      ▼
                        ┌───────────────────────────┐
                        │      NGINX (Port 443)     │
                        │    Reverse Proxy & SSL    │
                        └─────────────┬─────────────┘
                                      │
                 ┌────────────────────┼────────────────────┐
                 │                    │                    │
                 ▼                    ▼                    ▼
          candycutz.com        api.candycutz.com     admin.candycutz.com
                 │                    │                    │
                 ▼                    ▼                    ▼
          Vue 3 Web SPA        Laravel 11 API       CMS Admin View
         (Static Assets)        (PHP-FPM:9000)      (Vue 3 Web SPA)
                                      │
                 ┌────────────────────┼────────────────────┐
                 ▼                    ▼                    ▼
            MySQL 8.0               Redis             Local Storage
           (InnoDB ACID)        (Cache & Queue)     (/storage/app/public)
                                      │
                              ┌───────┴───────┐
                              ▼               ▼
                        Queue Worker      Scheduler
                       (Brevo/Events)   (Reminders/Cron)

           ─────────────────────────────────────────────────────

                              MOBILE CLIENT
                       Expo / React Native (Device)
                                      │
                                      │ HTTPS (/api/v1/*)
                                      ▼
                                 Laravel API
```

---

## 3. Client Architecture & Boundaries

### 3.1 The Web Client (`apps/web` / `barbing-saloon-web`)
- **Technology**: Vue 3 (Composition API), Vite, Pinia, Vue Router (HTML5 History Mode), TailwindCSS.
- **Role**: Serves desktop, tablet, and mobile browsers for public visitors, customers, staff barbers, and administrators.
- **State Management**: Pinia stores (`auth.store.js`, `theme.store.js`) maintaining volatile client session state.
- **Authentication Transport**: Bearer token attached via Axios request interceptor (`Authorization: Bearer <token>`). Tokens are stored in `localStorage` under `candycutz_auth_token`.
- **Context-Aware Error Handling**: When receiving a 401 Unauthorized response, the Axios interceptor inspects the current route:
  - If current URL starts with `/admin` -> Redirects to `/admin/login`.
  - If current URL starts with `/barber` -> Redirects to `/barber/login`.
  - Otherwise -> Redirects to `/customer/login`.

### 3.2 The Single Unified Mobile Application (`apps/mobile` / `candycutz-mobile-app`)
- **Technology**: React Native, Expo SDK 51, TypeScript, Expo Router (file-based navigation), TanStack React Query v5, Zustand, NativeWind tokens.
- **Role**: Installed on user devices (iOS App Store & Google Play). Provides dedicated experiences for both Customers and Barbers within a single application bundle.
- **Root Navigation Switch**:
  ```text
  app/
  ├── _layout.tsx              # Root layout & auth state listener
  ├── (auth)/                  # Public authentication flows
  │   ├── login.tsx            # Multi-identifier login (email, username, phone)
  │   ├── register.tsx         # Customer registration with @username
  │   └── forgot-password.tsx  # Password reset request
  ├── (customer)/              # Customer role experience
  │   ├── (tabs)/
  │   │   ├── index.tsx        # Home, flagship highlights, quick book
  │   │   ├── services.tsx     # Service catalog & category filter
  │   │   ├── appointments.tsx # Active bookings & QR verification codes
  │   │   └── profile.tsx      # Profile, address book, preferences
  │   ├── book/[serviceId].tsx # Multi-step booking wizard (In-Shop & Home)
  │   └── confirmation.tsx     # Booking confirmation & calendar add
  └── (barber)/                # Staff barber role experience
      ├── (tabs)/
      │   ├── index.tsx        # Live chair status, queue & timer
      │   ├── schedule.tsx     # Weekly hours & block-out dates
      │   ├── appointments.tsx # Client history & appointment actions
      │   └── profile.tsx      # Barber bio, specialties, portfolio
      └── walkin.tsx           # 30-second rapid walk-in registration
  ```
- **Token Storage**: Hardware-encrypted key storage via `expo-secure-store` (`TOKEN_KEY = 'candycutz_auth_token'`). Never uses plain `AsyncStorage`.
- **Offline Resilience**: TanStack Query caches server state; mutations for bookings require an idempotency key (`X-Idempotency-Key`) to prevent double-submitting across patchy cellular networks.

---

## 4. Backend Domain Modularization (`barbing-saloon-api`)

The backend is refactored from actor-based groupings into **business domain modules** under `app/Modules/`:

```text
app/Modules/
├── Authentication/            # Multi-identifier login, Sanctum tokens, Social Auth, Passwords
├── Users/                     # User profile, @username uniqueness, Address book, Deactivation
├── Barbers/                   # Barber profiles, chairs, working hours, commission metrics
├── Services/                  # Service catalog, categories, pricing, durations
├── Bookings/                  # Availability engine, pessimistic row locking, appointment lifecycle
├── HomeServices/              # Service zones, Keffi distance/radius calculation, travel fees
├── Payments/                  # Stripe integration, payment transactions, webhook listener
├── Notifications/             # Brevo transactional email, Expo push notifications, audit alerts
├── Reviews/                   # Customer ratings, testimonials, review moderation
├── CMS/                       # Static pages, banner announcements, gallery portfolio
├── Themes/                    # Theme Studio, color tokens, Draft/Preview/Publish versioning
└── Administration/            # Super Admin God Mode, user overrides, audit logging
```

Each module encapsulates its respective:
- `Controllers/`: HTTP input/output handling.
- `Requests/`: Form request validation with custom rules.
- `Resources/`: Strict JSON serialization envelopes.
- `Services/`: Isolated domain logic and database transactions.
- `Policies/`: Granular authorization policies.
- `routes.php`: Route declarations mounted under `/api/v1/`.

---

## 5. Concurrency & Double-Booking Protection

High-demand barbershop slots (e.g. Friday afternoons and Saturday mornings in Keffi) are vulnerable to simultaneous reservation attempts. Candycutz eliminates race conditions through **pessimistic row locking** inside atomic database transactions:

```php
return DB::transaction(function () use ($barberId, $date, $startTime, $endTime, $customer, $data) {
    // Acquire pessimistic row-level lock on any conflicting appointments for this barber
    $conflict = Appointment::where('barber_id', $barberId)
        ->where('appointment_date', $date)
        ->whereNotIn('status', ['cancelled'])
        ->where(function ($query) use ($startTime, $endTime) {
            $query->where('start_time', '<', $endTime)
                  ->where('end_time', '>', $startTime);
        })
        ->lockForUpdate()
        ->exists();

    if ($conflict) {
        throw new SlotUnavailableException('The selected slot has just been reserved by another client.');
    }

    // Persist appointment header and appointment items atomically
    ...
});
```
- Lock contention is minimized (<50ms) by indexing `(appointment_date, barber_id, start_time, end_time)`.
- Client retries receive a standardized `409 Conflict` or `422 Unprocessable Content` with code `BOOKING_SLOT_UNAVAILABLE`.

---

## 6. Financial Integrity & Stripe Reconciliation

```text
[Customer Device]              [Laravel API]              [Stripe API]
       │                             │                          │
       │ 1. Initialize Checkout      │                          │
       ├────────────────────────────►│                          │
       │                             │ 2. Create PaymentIntent  │
       │                             ├─────────────────────────►│
       │                             │◄─────────────────────────┤
       │                             │ (Returns client_secret)  │
       │ 3. Return client_secret     │                          │
       │◄────────────────────────────┤                          │
       │                             │                          │
       │ 4. Confirm Card Payment     │                          │
       ├─────────────────────────────┼─────────────────────────►│
       │                             │                          │
       │                             │ 5. Webhook: payment_intent.succeeded
       │                             │◄─────────────────────────┤
       │                             │                          │
       │                             │ 6. Verify Signature & Idempotency
       │                             │ 7. Transition status -> 'confirmed'
       │                             │ 8. Dispatch Brevo Confirmation Email
```
- **Zero Client Trust**: The frontend is never trusted to report payment success. Status transitions to `confirmed` occur exclusively via cryptographically signed Stripe webhooks (`stripe-signature`).
- **Idempotent Webhooks**: All webhook events are logged in `payment_transactions` by `stripe_event_id`. Duplicate webhook deliveries are acknowledged with `200 OK` without re-executing business side effects.

---

## 7. Security, Auditing & User Identity

### 7.1 Multi-Identifier Login
Users authenticate using any of their three unique identifiers:
1. **Email address** (`john@example.com`)
2. **Public @username** (`@johndoe`)
3. **Nigerian phone number** (`08012345678` or `+2348012345678`)

### 7.2 Username System Rules
- Length: 3 to 30 characters.
- Character set: `a-z`, `0-9`, `_`, `-`. Case-insensitive.
- Unique index on `users.username`.
- Standard users may change their username once every 90 days (`users.last_username_change_at`). Super Admin can bypass this limit with reason logging.

### 7.3 Soft Account Deactivation
When a customer requests account deletion:
- `is_active` is set to `0`.
- `status` is set to `'deactivated'`.
- `deactivated_at` is set to `now()`.
- Active personal access tokens are revoked.
- Historical bookings, payments, and audit records are **preserved intact** for accounting and legal integrity. Super Admin can reactivate accounts at any time.

### 7.4 Immutable Audit Logging
All privileged Super Admin actions (schedule overrides, price adjustments, role modifications, theme publication) generate an audit log record:
```json
{
  "actor_id": 1,
  "action": "barber.force_approve_appointment",
  "target_type": "Appointment",
  "target_id": 1042,
  "old_values": { "status": "pending" },
  "new_values": { "status": "confirmed" },
  "reason": "Customer paid cash deposit at Keffi counter",
  "ip_address": "197.210.64.12",
  "user_agent": "Mozilla/5.0 ...",
  "created_at": "2026-09-09T23:45:00Z"
}
```
Audit logs cannot be updated or deleted through application endpoints.
