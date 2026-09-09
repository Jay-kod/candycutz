# Candycutz — Architecture Essentials & Quick Reference

## 1. System Tenets (The 8 Non-Negotiables)
```text
1. ONE PLATFORM          5. ONE CMS (Vue 3 Admin View)
2. ONE REPOSITORY        6. ONE VPS (Dockerized Ubuntu 22.04)
3. ONE LARAVEL BACKEND   7. ONE EXPO MOBILE APP (Role-based nav)
4. ONE DATABASE (MySQL)  8. ONE AUTH AUTHORITY (Laravel Sanctum)
```

---

## 2. Directory Structure Conventions
```text
candycutz/
├── apps/
│   ├── web/                     # Vue 3 Web Application (barbing-saloon-web)
│   └── mobile/                  # Single Unified Expo Mobile App (Customer + Barber roles)
├── backend/
│   └── laravel/                 # Authoritative Laravel 11 Backend (barbing-saloon-api)
├── infrastructure/
│   ├── docker/                  # Nginx, PHP-FPM, MySQL, Redis definitions
│   └── scripts/                 # Backup, deploy, seed utilities
├── docs/                        # Complete living documentation
├── AGENTS.md                    # Engineering rules & constraints
├── PROJECT_STATE.md             # Real-time state tracker
├── BUILD_PLAN.md                # Phase matrix
├── DECISIONS.md                 # ADRs
└── docker-compose.yml           # Single-VPS orchestration
```

---

## 3. Core Database Entities & Foreign Keys

| Entity | Table Name | Key Foreign Keys | Purpose |
|---|---|---|---|
| **User** | `users` | — | Unified identity for Customer, Barber, Admin |
| **Barber** | `barbers` | `user_id` (1:1) | Stylist bio, rating, chair status, specialties |
| **Service** | `services` | `category_id` | Haircut, beard grooming, VIP package |
| **Category** | `service_categories` | — | Grouping (Hair, Beard, Combos, VIP) |
| **Branch** | `branches` | `business_id` | Physical salon (Keffi Flagship: Angwan Kare, BCG) |
| **Service Zone**| `service_zones` | `branch_id` | Home service radius & travel fee calculator |
| **Appointment** | `appointments` | `customer_id`, `barber_id`, `branch_id`, `service_zone_id` | Booking header & financial totals |
| **Appt Items** | `appointment_items` | `appointment_id`, `service_id` | Multi-service items & durations |
| **Working Hour**| `working_hours` | `barber_id` | Barber weekly schedule (days 0-6, open/close) |
| **Blocked Time**| `blocked_periods` | `barber_id` | Personal break, holiday, administrative block |
| **Payment** | `payments` | `appointment_id`, `customer_id` | Financial ledger, method, Stripe intent ID |
| **Transaction** | `payment_transactions` | `payment_id` | Idempotent transaction log & webhook events |
| **Audit Log** | `audit_logs` | `actor_id` | Super Admin privileged action tracking |

---

## 4. Canonical Route Contracts (`/api/v1/`)

All clients communicate via canonical `/api/v1/` routes:
```text
POST   /api/v1/auth/login              -> Identity + Password -> Token + User
POST   /api/v1/auth/register           -> Customer Onboarding -> Token + User
POST   /api/v1/auth/social-login       -> Google/Apple ID Token -> Token + User
POST   /api/v1/auth/forgot-password    -> Email -> Reset Link Dispatch
POST   /api/v1/auth/reset-password     -> Token + New Password
GET    /api/v1/auth/me                 -> Authenticated User Profile
POST   /api/v1/auth/logout             -> Revoke Current Client Token

GET    /api/v1/services                -> Active services with pricing & duration
GET    /api/v1/barbers                 -> Active barbers with ratings & chair status
GET    /api/v1/availability           -> Slots for date, barber, duration, mode
POST   /api/v1/appointments           -> Create booking with row lock (HTTP 201)
GET    /api/v1/appointments           -> Customer or Barber appointments list
GET    /api/v1/appointments/{id}       -> Single appointment details
PATCH  /api/v1/appointments/{id}/cancel-> Cancel appointment with reason
POST   /api/v1/payments/webhook        -> Signed Stripe webhook confirmation
```
*Note: Existing Vue 3 routes (`/api/public/*`, `/api/customer/*`, `/api/barber/*`, `/api/admin/*`) remain supported as backward-compatible aliases.*

---

## 5. Mobile Role-Based Navigation Routing
There is **ONE** Expo mobile application:
```text
role === 'customer'  ──►  (customer) Tabs: Home | Explore | Book | Appointments | Profile
role === 'barber'    ──►  (barber) Tabs:   Dashboard | Queue | Schedule | Walk-In | Profile
Unauthenticated      ──►  (auth) Stack:    Login | Register | Forgot Password
```
Never create two separate Expo apps. Use `_layout.tsx` to conditionally mount routes based on the verified role returned by `/api/v1/auth/me`.

---

## 6. Token Management Rules
1. **Never Call `$user->tokens()->delete()` During Login**:
   - Web client creates token named `'web-client'`.
   - Mobile app creates token named `'mobile-client'`.
   - Logging into mobile must not invalidate web sessions, and vice versa.
2. **Revocation on Logout**:
   - Calling `/api/v1/auth/logout` deletes `$request->user()->currentAccessToken()`.
   - Only the specific device calling logout is signed out.

---

## 7. Standard API Envelopes

### Success Envelope (`200 OK` / `201 Created`):
```json
{
  "success": true,
  "message": "Operation completed successfully.",
  "data": { ... }
}
```

### Central Error Envelope (`4xx` / `5xx`):
```json
{
  "success": false,
  "error": {
    "code": "BOOKING_SLOT_UNAVAILABLE",
    "message": "The selected appointment time is no longer available.",
    "details": {}
  }
}
```
Never return unstructured error strings or unhandled stack traces in production.
