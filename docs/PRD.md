# Candycutz — Product Requirements Document (PRD)

## 1. Product Overview & Vision
Candycutz is a modern commercial grooming and barbing platform engineered to deliver an exceptional, luxury client experience both in-shop and via on-demand home service. Founded and initially operational in **Angwan Kare, BCG, Keffi, Nasarawa State, Nigeria** ([Google Maps](https://maps.app.goo.gl/RtpPCeBRobajKmwS7)), the platform bridges physical artistry with modern digital booking, payments, and business management.

---

## 2. Target Personas & User Roles

### 2.1 Customer Persona ("The Modern Gentleman / Professional")
- **Profile**: University students (Nasarawa State University, Keffi), local professionals, business executives, and style-conscious residents.
- **Pain Points**: Long walk-in queues, unpredictable wait times, lack of home-service grooming, inconvenient cash payments, inconsistent barber availability.
- **Goals**: Effortless appointment booking, selecting their favorite barber, requesting home service haircuts, paying seamlessly via card/Apple Pay/Google Pay, tracking booking status.

### 2.2 Barber Persona ("The Craft Artisan / Master Stylist")
- **Profile**: Professional barbers and groomers employed by or affiliated with Candycutz.
- **Pain Points**: Disorganized paper calendars, double bookings, no-shows without compensation, unclear home-service travel directions, manual revenue tracking.
- **Goals**: Real-time daily appointment queue, clear customer display identity, streamlined chair status updates (Check-In, In-Progress, Complete), portfolio showcase, personal time blocking.

### 2.3 Shop Admin Persona ("The Operations Manager")
- **Profile**: Day-to-day salon manager.
- **Goals**: Monitor all chairs and staff in real-time, enter walk-in clients rapidly, reassign appointments when barbers are unavailable, review daily operational revenue reports.

### 2.4 Super Admin Persona ("The Business Owner / God Mode")
- **Profile**: Candycutz executive leadership and system administrators.
- **Goals**: Unrestricted oversight of all branches, staff, pricing, services, and customers; complete control over branding and themes via the CMS Theme Studio; immutable audit logging for every privileged action.

---

## 3. Core Functional Requirements

### 3.1 Identity & Authentication
- **Username-First Public Identity**: Every user has a unique `@username` handle (3-30 chars, alphanumeric with underscore and hyphen). Real name is stored securely for billing and compliance.
- **Multi-Method Authentication**:
  - Email + Password with email verification.
  - Username + Password.
  - Phone number SMS verification (where configured).
  - Social Auth: Sign in with Google (OAuth 2.0 / OpenID Connect) and Sign in with Apple.
  - Post-social onboarding: Mandatory username selection before booking.
- **Username Change Rule**: Max once every 90 days. Super Admin can override.
- **Account Deactivation**: Soft deactivation (`status = 'deactivated'`, `deactivated_at = NOW()`). Customer sees "Account Deleted"; historical financial records, appointments, and audit logs are permanently preserved.

### 3.2 Service Catalog & Management
- Multi-category support: Haircuts, Beard Grooming, Hair Treatment, Styling, Luxury Packages.
- CMS attributes: Name, slug, description, price (NGN / USD), duration in minutes, primary image, secondary gallery images, home-service eligibility flag, active status, featured badge.
- Custom barber pricing and duration overrides where authorized.

### 3.3 Booking Engine & Scheduling
- **Appointment Modes**:
  - `IN_SHOP`: Service performed at the Candycutz physical salon in Keffi.
  - `HOME_SERVICE`: Barber travels to the customer's specified address within defined service zones.
- **Availability Calculations**: Authoritative server-side evaluation factoring in:
  - Branch business hours & holidays.
  - Barber weekly working hours & individual days off.
  - Existing confirmed bookings.
  - Barber personal blocked periods.
  - Multi-service duration summation + cleanup buffers.
  - Home-service travel buffers before and after appointments.
- **Double-Booking Prevention**: Database-level pessimistic locking (`SELECT FOR UPDATE`) within ACID transactions.
- **Status Lifecycle**: `pending` → `confirmed` → `checked_in` → `in_progress` → `completed` | `cancelled` | `no_show`.

### 3.4 Home-Service Geolocation & Logistics
- Address book: Customers save multiple home/office addresses with street, landmark, city, and GPS coordinates.
- Service Zones: Defined geographic polygons or radial zones (e.g., Keffi Central, University Campus, GRA).
- Travel Fee Calculation: Dynamic base fee + per-kilometer surcharge determined by service zone.
- Location Privacy: Customer exact GPS coordinates and address are shielded from the barber until 2 hours before the scheduled appointment window.

### 3.5 Payments & Financial Ledger
- **Payment Provider**: Stripe (Cards, Apple Pay, Google Pay).
- **Payment Options**: Pay at shop, deposit (CMS-configurable percentage), or full advance payment.
- **Server Verification**: Authoritative confirmation via signed Stripe webhooks (`payment_intent.succeeded`). Client claims of payment success are never trusted.
- **Refund Engine**: Automated or admin-initiated refunds processed through Stripe API with audit logging.

### 3.6 Communications & Notifications
- **Transactional Email**: Brevo event-driven notifications (Account Registration, Email Verification, Password Reset, Booking Confirmation, Reschedule, Cancellation, 2-Hour Appointment Reminder, Receipt).
- **Push Notifications**: Expo push notifications delivered to iOS and Android devices for instant operational alerts.

### 3.7 CMS Control Plane & Theme Studio
- **Operational Control**: Full CRUD over services, categories, barbers, working hours, holidays, and policies.
- **Content Management**: Edit hero sections, banners, announcements, testimonials, FAQ, gallery, and blog posts.
- **Theme Studio**: Configure light and dark brand tokens (brand gold, surfaces, backgrounds, typography). Supports Draft → Preview → Publish lifecycle with instant rollback.

---

## 4. Non-Functional Requirements
- **Performance**: API response times < 120ms (p95); mobile app cold launch < 1.5s; 60fps scrolling and transitions.
- **Reliability & Offline Resilience**: Mobile app caches catalog data; offline detection warns users before booking; idempotency tokens prevent duplicate submissions on flaky mobile connections.
- **Security**: Sanctum token auth, bcrypt/argon2 password hashing, rate limiting against brute force, OWASP Top 10 defenses, encrypted secrets.
- **Extensibility**: Multi-branch and multi-zone relational structure allowing seamless expansion across Keffi, Nasarawa, and beyond without schema refactoring.
