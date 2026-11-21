# CandyCutz Security Policy & Architecture

This document defines the authoritative security controls, threat mitigations, authentication mechanisms, and compliance policies enforced across the CandyCutz platform.

---

## 1. Threat Model & Security Principles

CandyCutz adheres to defense-in-depth and the principle of least privilege across all system layers.

| Threat Category | Primary Risk | Mitigation Control |
|---|---|---|
| **Broken Authentication** | Credential stuffing, token leakage | Laravel Sanctum personal access tokens with expiration; rate-limited auth routes; timing-attack-safe password verification. |
| **Broken Access Control** | Privilege escalation across user tiers | Role-based middleware (`role:admin`, `role:barber`) and resource policies (`AppointmentPolicy`). |
| **Injection Attacks** | SQL injection, shell command execution | 100% parameter-bound queries via Eloquent PDO; zero raw shell concatenation. |
| **Financial Replay Attacks** | Duplicate payment confirmation | HMAC-SHA512 signature validation with `hash_equals()`; immutable unique payment reference constraints. |
| **Arbitrary File Upload** | Remote code execution via media uploads | Strict MIME validation, maximum size limits, storage isolation, and non-executable hashed filenames. |
| **Denial of Service (DoS)** | Endpoint flooding | Tiered rate limiting (`throttle:api`, `throttle:auth`) backed by Redis/Cache. |

---

## 2. Authentication & Token Lifecycle

Authentication is managed through **Laravel Sanctum**.

### 2.1 Token Issuance & Revocation
* When a user logs in via `POST /api/v1/auth/login` or `POST /api/v1/auth/register`, a cryptographically secure token is generated.
* Tokens are transmitted in the standard HTTP header:
  ```http
  Authorization: Bearer <sanctum_token>
  ```
* **Selective Token Revocation:** On logout (`POST /api/v1/auth/logout`), only the *current* token used for the request is deleted (`$request->user()->currentAccessToken()->delete()`). Other active devices and sessions remain undisturbed unless an explicit global logout is invoked.

### 2.2 Password Security
* Passwords are encrypted using Bcrypt (or Argon2id) with strict work factor configuration (`BCRYPT_ROUNDS=12` in production).
* Plaintext passwords are never stored, logged, or serialized in JSON responses (`$hidden = ['password', 'remember_token']`).

---

## 3. Role-Based Access Control (RBAC)

CandyCutz establishes three discrete user personas:

1. **`customer`**:
   * Can browse public services, barbers, and availability.
   * Can book appointments, manage own appointments, submit payments, and receive notifications.
   * Access forbidden to barber schedules, admin dashboards, or financial verification endpoints.

2. **`barber`**:
   * Can manage own schedule, update own chair status, and review assigned bookings.
   * Restricted from mutating other barbers' records or accessing salon-wide revenue reports.

3. **`admin`**:
   * Complete administrative oversight: manages services, approves manual bank transfer payments, configures salon working hours, and accesses financial analytics.

### Policy Enforcement Example:
```php
// AppointmentPolicy.php
public function update(User $user, Appointment $appointment): bool
{
    return $user->id === $appointment->user_id 
        || $user->id === $appointment->barber_id 
        || $user->role === 'admin';
}
```

---

## 4. Payment Gateway & Webhook Security

### 4.1 HMAC Signature Verification
All external payment webhooks (e.g. Paystack) undergo cryptographic signature validation before payload parsing:

```php
// PaymentWebhookApiController.php
$signature = $request->header('x-paystack-signature');
$computedSignature = hash_hmac('sha512', $request->getContent(), config('services.paystack.secret'));

if (! hash_equals((string) $signature, (string) $computedSignature)) {
    abort(Response::HTTP_UNAUTHORIZED, 'Invalid webhook signature.');
}
```
* Uses `hash_equals()` to eliminate timing-attack vulnerabilities.
* Webhook handling is strictly idempotent; multiple calls with the same reference produce no duplicate payment records or state anomalies.

---

## 5. File Upload Hardening

Media uploads (such as bank transfer receipts and gallery images) are guarded by strict upload constraints:

1. **MIME-Type & Extension Whitelisting:**
   * Transfer receipts: `image/jpeg`, `image/png`, `application/pdf`.
   * Gallery/Portfolio: `image/jpeg`, `image/png`, `image/webp`.
2. **File Size Enforcement:** Capped at 5MB maximum per upload.
3. **Storage Isolation:** Uploads are stored with generated UUIDs in `storage/app/public/*`. Web server configurations disable script execution within the upload storage directory.

---

## 6. Network, CORS & API Hardening

### 6.1 CORS Policy (`config/cors.php`)
* Wildcard origins (`*`) are prohibited in production.
* Origins are strictly whitelist-configured via `CORS_ALLOWED_ORIGINS` (e.g. `https://candycutz.com`, `https://app.candycutz.com`).
* Standard allowed methods: `GET`, `POST`, `PUT`, `PATCH`, `DELETE`, `OPTIONS`.

### 6.2 Rate Limiting
* General API: 60 requests per minute per IP.
* Sensitive Authentication routes (`/auth/login`, `/auth/register`, `/auth/forgot-password`): 5 attempts per minute per IP.

---

## 7. Secret Management & Zero-Secrets Rule

* **No Hardcoded Secrets:** API keys, database credentials, encryption keys, and webhook secrets are exclusively loaded via environment variables.
* **Secret Leak Prevention:** `.env` and sensitive credential files are strictly excluded from version control via `.gitignore`.
* **Continuous Auditing:** Dependencies are scanned during CI using `composer audit` and `npm audit`.
