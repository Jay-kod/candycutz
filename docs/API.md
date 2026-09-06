# Candycutz — REST API Contract Specification (v1)

## 1. Overview & Standards
All communication between client applications (Web, Customer App, Barber App, CMS Admin) and the backend occurs over HTTPS via the versioned prefix `/api/v1/`.

### 1.1 Envelope Structure
Every response from the server conforms strictly to the standard envelope:
```json
{
  "success": true,
  "message": "Operation completed successfully",
  "data": { ... },
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 75
  }
}
```

### 1.2 Error Envelope Structure
```json
{
  "success": false,
  "message": "The requested appointment slot is no longer available.",
  "errors": {
    "slot": ["The barber has already been booked for this timeframe."]
  },
  "error_code": "SLOT_ALREADY_BOOKED"
}
```

### 1.3 Rate Limiting
- Public endpoints: 60 requests / minute / IP
- Auth endpoints (login, register, reset): 5 requests / minute / IP
- Customer / Barber authenticated endpoints: 120 requests / minute / user
- Admin / Super Admin endpoints: 200 requests / minute / user

---

## 2. API Endpoints by Domain

### 2.1 Authentication & Identity (`/api/v1/auth`)
| Method | Endpoint | Description | Auth |
|---|---|---|---|
| `POST` | `/auth/register` | Register new customer account | Public |
| `POST` | `/auth/login` | Login with email/username + password | Public |
| `POST` | `/auth/social-login` | Sign in with Google / Apple ID token | Public |
| `POST` | `/auth/logout` | Revoke active Sanctum bearer token | Sanctum |
| `GET` | `/auth/me` | Fetch authenticated user profile & roles | Sanctum |
| `POST` | `/auth/claim-username` | Initial username selection for social users | Sanctum |
| `PATCH` | `/auth/username` | Change username (enforces 90-day limit) | Sanctum |
| `POST` | `/auth/forgot-password` | Dispatch password reset email via Brevo | Public |
| `POST` | `/auth/reset-password` | Set new password with reset token | Public |
| `DELETE`| `/auth/deactivate` | Soft-deactivate customer account | Sanctum |

### 2.2 Public Discovery Catalog (`/api/v1/public`)
| Method | Endpoint | Description | Auth |
|---|---|---|---|
| `GET` | `/public/settings` | General shop info, location, contact, social | Public |
| `GET` | `/public/theme` | Current published theme design tokens | Public |
| `GET` | `/public/service-categories`| List active service categories | Public |
| `GET` | `/public/services` | List available services (filterable by category) | Public |
| `GET` | `/public/services/{slug}` | Detailed service description and photos | Public |
| `GET` | `/public/barbers` | List active barbers with ratings & specialties | Public |
| `GET` | `/public/barbers/{id}` | Barber detailed bio, portfolio & reviews | Public |
| `GET` | `/public/service-zones` | List home-service delivery zones in Keffi | Public |
| `GET` | `/public/available-slots` | Dynamic slot availability query engine | Public |
| `GET` | `/public/gallery` | Filterable portfolio gallery | Public |
| `GET` | `/public/testimonials` | Approved client reviews | Public |
| `GET` | `/public/blog` | Published articles & grooming tips | Public |
| `POST` | `/public/contact` | Submit contact message | Public |

### 2.3 Customer Operations (`/api/v1/customer`)
| Method | Endpoint | Description | Auth |
|---|---|---|---|
| `GET` | `/customer/dashboard` | Upcoming appointment, recent visits, stats | Customer |
| `POST` | `/customer/bookings` | Create appointment (In-Shop or Home Service) | Customer |
| `GET` | `/customer/bookings` | Paginated appointment history | Customer |
| `GET` | `/customer/bookings/{id}` | Single appointment full breakdown | Customer |
| `PATCH`| `/customer/bookings/{id}/reschedule` | Reschedule booking to a new valid slot | Customer |
| `PATCH`| `/customer/bookings/{id}/cancel` | Cancel booking (enforces policy window) | Customer |
| `POST` | `/customer/bookings/{id}/review` | Submit star rating and feedback | Customer |
| `GET` | `/customer/addresses` | List saved home/work delivery addresses | Customer |
| `POST` | `/customer/addresses` | Add new saved address with coordinates | Customer |
| `DELETE`| `/customer/addresses/{id}`| Remove saved address | Customer |
| `POST` | `/customer/profile` | Update real name, phone, avatar | Customer |
| `POST` | `/customer/device-token` | Register Expo push notification token | Customer |

### 2.4 Barber Operations (`/api/v1/barber`)
| Method | Endpoint | Description | Auth |
|---|---|---|---|
| `GET` | `/barber/dashboard` | Today's queue, chair load, revenue stats | Barber |
| `GET` | `/barber/appointments` | Barber's calendar and upcoming appointments | Barber |
| `GET` | `/barber/appointments/{id}` | Detailed client view (unmasks address if <2h) | Barber |
| `PATCH`| `/barber/appointments/{id}/status` | Transition status (check_in, in_progress, completed, no_show) | Barber |
| `POST` | `/barber/walk-in` | Instant seat walk-in client | Barber |
| `GET` | `/barber/schedule` | Weekly working hours configuration | Barber |
| `POST` | `/barber/blocked-periods` | Block personal break / time off | Barber |
| `DELETE`| `/barber/blocked-periods/{id}`| Remove blocked period | Barber |
| `POST` | `/barber/gallery` | Upload new portfolio haircut photo | Barber |
| `POST` | `/barber/profile` | Update bio, specialties, avatar | Barber |

### 2.5 Payments & Webhooks (`/api/v1/payments`)
| Method | Endpoint | Description | Auth |
|---|---|---|---|
| `POST` | `/payments/initialize` | Create Stripe PaymentIntent for booking | Customer |
| `POST` | `/payments/webhook/stripe`| Authoritative Stripe webhook listener | Signed Webhook |
| `GET` | `/payments/{id}/status` | Check verified payment status | Customer |

### 2.6 Admin & Super Admin God Mode (`/api/v1/admin`)
| Method | Endpoint | Description | Auth |
|---|---|---|---|
| `GET` | `/admin/dashboard` | Global revenue, chair occupancy, KPIs | Admin |
| `GET` | `/admin/appointments` | Master appointment calendar with filters | Admin |
| `POST` | `/admin/appointments/override`| Force reschedule/reassign with audit reason | Super Admin |
| `POST` | `/admin/services` | Create/update services and categories | Admin |
| `POST` | `/admin/barbers` | Manage staff accounts and branch assignment | Admin |
| `POST` | `/admin/service-zones` | Create/update Keffi home-service zones & fees | Admin |
| `GET` | `/admin/theme` | Load Theme Studio drafts & published version | Super Admin |
| `POST` | `/admin/theme/publish` | Publish new theme version | Super Admin |
| `POST` | `/admin/theme/rollback`| Rollback to previous theme version | Super Admin |
| `GET` | `/admin/audit-logs` | Immutable audit trail query | Super Admin |
| `PATCH`| `/admin/users/{id}/status` | Activate, deactivate, or suspend any user | Super Admin |
