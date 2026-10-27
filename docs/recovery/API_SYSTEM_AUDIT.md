# CANDYCUTZ — API SYSTEM & INTEGRATION AUDIT

**Date:** 2026-10-23  
**Auditor:** Senior Architect & API Integration Specialist  
**Reference:** `ARCHITECTURE.md` §2, §4, §5  

---

## 1. ENDPOINT MATRIX & AUDIT SUMMARY

An audit of all 297 registered application routes was executed using `php artisan routes:audit` and `php artisan route:list`.

### 1.1 Mount Point Verification
- **Target:** `/api/v1/*` is the only active mount point.
- **Observed:** All active API resources are mounted exclusively under `Route::prefix('v1')` in `routes/api.php`.
- **Legacy Deprecation:** Legacy prefixes (`/api/customer/*`, `/api/barber/*`, `/api/admin/*`, `/api/super_admin/*`) are routed to explicit `410 Gone` handlers returning:
  ```json
  { "error": "This legacy endpoint has been permanently removed. Please upgrade to the v1 API." }
  ```
- **Status:** **Working / Verified**.

---

## 2. CLIENT INTEGRATION & ROUTE HARMONIZATION

### 2.1 Web Client Integration (`barbing-saloon-web`)
- **Central API Client:** `src/core/api/client.js` is the single authoritative Axios client.
- **URL Normalization:** Request interceptor normalizes all paths to ensure `/v1/` prefix is present without duplication:
  ```javascript
  if (config.url.startsWith('/api/v1/')) {
    config.url = config.url.replace('/api/v1/', '/v1/');
  } else if (!config.url.startsWith('/v1/') && !config.url.startsWith('http')) {
    config.url = `/v1/${config.url.replace(/^\/+/, '')}`;
  }
  ```
- **Error Standardization:** Axios response interceptor converts raw HTTP errors into `ApiError` instances containing structured `code`, `action` (`logout`, `wait`, `fix_input`), and `retryable` flags.
- **Authentication Handshake:** Uses Bearer token attached automatically to the `Authorization` header from `localStorage`.
- **Status:** **Working / Verified**.

### 2.2 Standalone Mobile Client (`MOBILE/`)
- **Central API Client:** `src/api/client.ts` uses Axios with `CONFIG.API_BASE_URL` (`${API_ROOT}/api/v1`).
- **Token Security:** Tokens are persisted in `expo-secure-store` via `tokenStorage` with hardware encryption.
- **Request Headers:** Automatically attaches `X-Client-Type: mobile`, `X-App-Version: 1.0.0`, `X-App-Platform: android|ios`.
- **Version Gating:** Catches HTTP `426 Upgrade Required` and dispatches `useSystemStatusStore` force-upgrade modal.
- **Maintenance Handling:** Catches HTTP `503 Service Unavailable` and displays app maintenance barrier.
- **Status:** **Working / Verified**.

---

## 3. SECURITY GATEWAY & RATE LIMITING

### 3.1 Rate Limit Controls (`ApiGateMiddleware` & Laravel Throttles)
| Route Category | Rate Limit | Identifier | Penalty Response |
|---|---|---|---|
| Public Read (Catalogue, Services) | `throttle:api` (60 req/min) | Client IP / Token | HTTP 429 (`RATE_LIMITED`) + `Retry-After` header |
| Auth Sensitive (Login, Register, OTP) | `throttle:auth-sensitive` (10 req/min) | Client IP + Normalized Identity | HTTP 429 + Audit Log Entry |
| Webhook Ingestion (`payments/webhook`) | Exempt from rate limit | Verified via HMAC signature | HTTP 400 on invalid signature |
| Client Error Ingestion (`system/client-errors`)| `throttle:30,1` (30 req/min) | Client IP | HTTP 429 |

### 3.2 Idempotency Engine (`IdempotencyMiddleware`)
- **Header:** `Idempotency-Key` (UUID / token).
- **Scope:** State-mutating POST/PATCH operations (Checkout, Walk-in, Booking).
- **Behavior:**
  - If key is new: request proceeds, response payload and headers are cached in `idempotency_keys` table.
  - If duplicate key with identical payload: cached response is replayed immediately with `X-Cache-Replay: true` without hitting the database.
  - If duplicate key with altered payload: rejected with `409 Conflict` (`IDEMPOTENCY_KEY_REUSED`).
- **Verification:** Tested in `Tests\Feature\IdempotencyTest` (100% Pass).

---

## 4. AUTHORIZATION & ACCESS CONTROL MATRIX

| Role | Permitted Route Groups | Database Ownership Enforcement |
|---|---|---|
| **Guest (Unauthenticated)** | `/health`, `/settings`, `/services`, `/barbers`, `/gallery`, `/blog`, `/auth/login`, `/auth/register` | None (Public data only) |
| **Customer** | Above + `/appointments` (own), `/payments/*` (own), `/wishlist`, `/reviews/me` | Policy check on every `{id}` route (`customer_id === user.id`) |
| **Barber** | Above + `/barber/*`, `/appointments/walk-in`, chair status, daily queue | Policy check on every `{id}` route (`barber.user_id === user.id`) |
| **Admin** | Above + `/admin/*`, CMS, Services CRUD, Verifications, Reports | Role check + policy checks |
| **Super Admin** | Above + `/superadmin/*`, `/super-admin/*`, user management, audit logs | Audit logged before-hook in policies |

All route accesses verified by `Tests\Feature\AuthorizationMatrixTest` and `Tests\Feature\RouteGateAuditTest`.
