# Candycutz — API v1 Contract Specification

## 1. Global API Standards

- **Base URL**: `https://api.candycutz.com/api/v1` (Local: `http://localhost:8000/api/v1`)
- **Protocol**: HTTPS exclusively in production
- **Format**: JSON (`Content-Type: application/json`, `Accept: application/json`)
- **Authentication**: Bearer Token via Sanctum (`Authorization: Bearer <token>`)
- **Idempotency**: All booking and payment mutation endpoints accept an optional header:
  `X-Idempotency-Key: <UUIDv4>`

---

## 2. Standard Envelope Schemas

### 2.1 Success Envelope (`200 OK`, `201 Created`)
```json
{
  "success": true,
  "message": "Operation completed successfully.",
  "data": { ... }
}
```

### 2.2 Error Envelope (`400`, `401`, `403`, `404`, `422`, `500`)
```json
{
  "success": false,
  "error": {
    "code": "ERROR_CODE_CONSTANT",
    "message": "Human-readable explanation of the failure.",
    "details": {
      "field_name": ["Specific validation or constraint detail."]
    }
  }
}
```

---

## 3. Endpoints Specification

### 3.1 Authentication

#### `POST /auth/login`
- **Description**: Authenticate user via email, `@username`, or phone number.
- **Request Body**:
  ```json
  {
    "identity": "james_cutz",
    "password": "Password123!",
    "device_name": "mobile-client"
  }
  ```
- **Response `200 OK`**:
  ```json
  {
    "success": true,
    "message": "Login successful.",
    "data": {
      "token": "1|abcdef123456...",
      "user": {
        "id": 5,
        "name": "James Oche",
        "username": "james_cutz",
        "email": "james@candycutz.com",
        "phone": "08012345678",
        "role": "customer",
        "avatar_url": null,
        "is_active": true
      }
    }
  }
  ```

#### `POST /auth/register`
- **Description**: Customer registration.
- **Request Body**:
  ```json
  {
    "name": "Chioma Adeleke",
    "username": "chioma_glam",
    "email": "chioma@example.com",
    "phone": "08123456789",
    "password": "SecurePassword1!",
    "password_confirmation": "SecurePassword1!"
  }
  ```
- **Response `201 Created`**: Returns standard token and user payload.

#### `POST /auth/social-login`
- **Description**: Exchange Google / Apple ID token for Candycutz session token.
- **Request Body**:
  ```json
  {
    "provider": "google",
    "id_token": "eyJhbGciOiJSUzI1NiIs...",
    "device_name": "mobile-client",
    "user_data": {
      "name": "Chioma Adeleke"
    }
  }
  ```
- **Response `200 OK`**: Returns standard token and user payload.

#### `GET /auth/me`
- **Description**: Retrieve authenticated user profile, active role, and permissions.
- **Headers**: `Authorization: Bearer <token>`
- **Response `200 OK`**: Returns user profile.

#### `POST /auth/logout`
- **Description**: Revoke current client token.
- **Headers**: `Authorization: Bearer <token>`
- **Response `200 OK`**: `{ "success": true, "message": "Logged out successfully." }`

---

### 3.2 Services & Catalog

#### `GET /services`
- **Description**: List all active grooming services.
- **Query Parameters**:
  - `category_id` (optional integer)
  - `type` (optional: `in_shop`, `home_service`, `all`)
- **Response `200 OK`**:
  ```json
  {
    "success": true,
    "data": [
      {
        "id": 1,
        "name": "Executive Fade & Beard Sculpt",
        "slug": "executive-fade-beard-sculpt",
        "category_id": 1,
        "category_name": "Signature Combos",
        "price": 7500.00,
        "duration_minutes": 45,
        "description": "Precision fade, hot towel beard treatment, razor edge finish, and luxury cologne.",
        "image_url": "https://api.candycutz.com/storage/services/fade.jpg",
        "is_home_service_eligible": true
      }
    ]
  }
  ```

#### `GET /service-categories`
- **Description**: List service categories.
- **Response `200 OK`**:
  ```json
  {
    "success": true,
    "data": [
      { "id": 1, "name": "Haircuts", "slug": "haircuts" },
      { "id": 2, "name": "Beard Grooming", "slug": "beard-grooming" },
      { "id": 3, "name": "VIP Combos", "slug": "vip-combos" }
    ]
  }
  ```

---

### 3.3 Barbers & Working Hours

#### `GET /barbers`
- **Description**: List active barbers with current chair availability.
- **Response `200 OK`**:
  ```json
  {
    "success": true,
    "data": [
      {
        "id": 2,
        "user_id": 3,
        "name": "Marcus Vance",
        "username": "marcus_cutz",
        "avatar_url": "https://api.candycutz.com/storage/avatars/marcus.jpg",
        "rating": 4.95,
        "total_reviews": 128,
        "chair_status": "free",
        "specialties": ["Skin Fades", "Beard Sculpting", "Hot Towel Shave"],
        "bio": "Over 10 years master barber experience specializing in precision scissor work and sharp hairline enhancements."
      }
    ]
  }
  ```

#### `GET /availability`
- **Description**: Calculate available starting time slots for a given barber, date, and duration.
- **Query Parameters**:
  - `barber_id`: Required integer
  - `date`: Required date string (`YYYY-MM-DD`)
  - `duration_minutes`: Required integer (e.g. `45`)
  - `mode`: Optional string (`in_shop` or `home_service`)
- **Response `200 OK`**:
  ```json
  {
    "success": true,
    "data": [
      { "time": "09:00", "available": true },
      { "time": "09:45", "available": true },
      { "time": "10:30", "available": false, "reason": "booked" },
      { "time": "11:15", "available": true }
    ]
  }
  ```

---

### 3.4 Bookings & Appointments

#### `POST /appointments`
- **Description**: Atomically reserve a grooming slot using database row locks (`SELECT FOR UPDATE`).
- **Headers**:
  - `Authorization: Bearer <token>`
  - `X-Idempotency-Key: <UUID>`
- **Request Body**:
  ```json
  {
    "barber_id": 2,
    "appointment_date": "2026-09-12",
    "start_time": "10:00",
    "appointment_type": "in_shop",
    "services": [
      { "id": 1 },
      { "id": 4 }
    ],
    "payment_method": "stripe",
    "notes": "Wedding grooming prep"
  }
  ```
- **Response `201 Created`**:
  ```json
  {
    "success": true,
    "message": "Appointment reserved successfully.",
    "data": {
      "id": 42,
      "booking_reference": "CC-00042",
      "status": "pending_payment",
      "appointment_date": "2026-09-12",
      "start_time": "10:00",
      "end_time": "11:15",
      "total_duration_minutes": 75,
      "total_amount": 12000.00,
      "travel_fee": 0.00,
      "grand_total": 12000.00,
      "barber": {
        "id": 2,
        "name": "Marcus Vance"
      },
      "payment": {
        "payment_intent_id": "pi_3MtwLwLkdIwHu7ix28a3tqPa",
        "client_secret": "pi_3MtwLwLkdIwHu7ix28a3tqPa_secret_YrZ...",
        "amount": 12000.00,
        "currency": "NGN"
      }
    }
  }
  ```

#### `GET /appointments`
- **Description**: List user's appointments (Customer gets their bookings; Barber gets their chair queue).
- **Query Parameters**:
  - `status`: Optional string (`all`, `upcoming`, `completed`, `cancelled`)
  - `page`, `per_page`
- **Response `200 OK`**: Paginated list of appointments with services, barber, and payment status.

#### `PATCH /appointments/{id}/status`
- **Description**: Update appointment operational state (Barbers and Admins only).
- **Request Body**:
  ```json
  {
    "status": "in_progress",
    "reason": "Client seated in chair #2"
  }
  ```
- **Allowed Transitions**:
  - `pending` -> `confirmed` | `cancelled`
  - `confirmed` -> `checked_in` | `cancelled` | `no_show`
  - `checked_in` -> `in_progress`
  - `in_progress` -> `completed`

---

### 3.5 Payments & Webhooks

#### `POST /payments/webhook`
- **Description**: Receive and verify cryptographically signed Stripe webhook events.
- **Headers**:
  - `Stripe-Signature: t=1614555845,v1=5257a869e7ecebeda32affa62cd4ff0...`
- **Behavior**:
  - Verifies event payload against `STRIPE_WEBHOOK_SECRET`.
  - On `payment_intent.succeeded`:
    - Checks `payment_transactions` for duplicate processing.
    - Transitions `payments.status` to `successful`.
    - Transitions `appointments.status` to `confirmed`.
    - Dispatches async Brevo email job.
- **Response `200 OK`**: `{ "received": true }`

---

### 3.6 Infrastructure Health Check

#### `GET /health`
- **Description**: Real-time probe of critical server subsystems.
- **Response `200 OK`**:
  ```json
  {
    "status": "healthy",
    "timestamp": "2026-09-09T23:45:00Z",
    "services": {
      "database": "connected",
      "redis": "connected",
      "storage": "writable",
      "queue": "active"
    }
  }
  ```
