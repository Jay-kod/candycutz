# Candycutz — Authentication Specification & Flow Contracts

## 1. Authentication Authority & Principles

Candycutz operates under a strict **Single Authentication Authority**:
- **Sole Authority**: Laravel Sanctum is the single source of truth for identity, password verification, roles, permissions, and token issuance.
- **Unified Identity**: All clients (Web, Mobile, CMS) authenticate against the single `users` table.
- **Client Transports**:
  - **Web Client**: Authenticates via Sanctum Bearer token stored in browser `localStorage`, with CORS origin resolution.
  - **Mobile Client**: Authenticates via Sanctum Bearer token stored in hardware-backed `expo-secure-store`.
- **Zero Token Purging**: Logging into mobile does not kill a web session, and vice versa.

---

## 2. Multi-Identifier Login Contract

Users can log into the platform using any one of three unique credentials alongside their password:
1. **Email address**: e.g., `james@candycutz.com`
2. **Public @username**: e.g., `@james_cutz` (or `james_cutz`)
3. **Nigerian phone number**: e.g., `08012345678` or `+2348012345678`

### Login Request Specification
`POST /api/v1/auth/login`

**Headers**:
```http
Content-Type: application/json
Accept: application/json
```

**Payload**:
```json
{
  "identity": "james@candycutz.com",
  "password": "Password123!",
  "device_name": "mobile-client"
}
```

**Field Rules**:
- `identity`: Required string. Resolved against `users.email`, `users.username`, and `users.phone`.
- `password`: Required string.
- `device_name`: Optional string. Defaults to `'web-client'` if omitted.

**Backend Resolution Logic**:
```php
$user = User::where('email', $identifier)
    ->orWhere('username', $identifier)
    ->orWhere('phone', $identifier)
    ->first();

if (! $user || ! Hash::check($password, $user->password)) {
    throw new AuthenticationException('Invalid credentials');
}

if (! $user->is_active || in_array($user->status, ['deactivated', 'suspended'], true)) {
    throw new AccountSuspendedException('Your account has been deactivated. Please contact support.');
}

// DO NOT call $user->tokens()->delete()
$tokenName = $data['device_name'] ?? 'web-client';
$token = $user->createToken($tokenName)->plainTextToken;
```

**Success Response (`200 OK`)**:
```json
{
  "success": true,
  "message": "Login successful.",
  "data": {
    "token": "1|qXyZ...plainTextSanctumToken...",
    "user": {
      "id": 12,
      "name": "James Oche",
      "real_name": "James Oche",
      "username": "james_cutz",
      "email": "james@candycutz.com",
      "phone": "08012345678",
      "avatar_url": "https://api.candycutz.com/storage/avatars/james.jpg",
      "role": "customer",
      "is_active": true,
      "status": "active",
      "created_at": "2026-01-15T10:00:00Z"
    }
  }
}
```

---

## 3. Customer Registration Contract

`POST /api/v1/auth/register`

**Payload**:
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

**Validation Rules**:
- `name`: `required|string|min:2|max:100`
- `username`: `required|string|min:3|max:30|regex:/^[a-zA-Z0-9_-]+$/|unique:users,username`
- `email`: `required|email|max:150|unique:users,email`
- `phone`: `required|string|regex:/^(?:\+?234|0)(?:7[0-9]|8[0-9]|9[0-1])[0-9]{8}$/|unique:users,phone`
- `password`: `required|string|min:8|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/`

**Backend Behavior**:
- Creates `User` with `role = 'customer'`, `is_active = true`, `status = 'active'`.
- Automatically generates initial Sanctum token for immediate authentication without forcing a second login request.
- Dispatches `UserRegistered` event for welcome email delivery.

---

## 4. Social Authentication Contract (Google & Apple)

All social logins resolve to a single unified Candycutz user record. If an account already exists with the same email, the OAuth provider is safely linked to that account.

`POST /api/v1/auth/social-login`

**Payload**:
```json
{
  "provider": "google",
  "id_token": "eyJhbGciOiJSUzI1NiIsImtpZCI6...",
  "device_name": "mobile-client",
  "user_data": {
    "name": "Chioma Adeleke"
  }
}
```

**Verification Protocol**:
1. **Google**:
   - Fetches Google Public Keys from `https://www.googleapis.com/oauth2/v3/certs`.
   - Verifies JWT signature, issuer (`https://accounts.google.com` or `accounts.google.com`), and audience (`GOOGLE_CLIENT_ID`).
   - Extracts `sub` (Google User ID), `email`, and `email_verified`.
2. **Apple**:
   - Fetches Apple Public Keys from `https://appleid.apple.com/auth/keys`.
   - Verifies JWT signature (ES256), issuer (`https://appleid.apple.com`), and audience (`APPLE_CLIENT_ID`).
   - Extracts `sub` (Apple ID) and optional private relay email.
3. **User Resolution & Account Linking**:
   - Checks for existing user by `(auth_provider, provider_id)`.
   - If not found, checks for existing user by `email`. If found, updates `auth_provider` and `provider_id`.
   - If no user exists, provisions a new user:
     - Generates unique `@username` from email prefix or name (e.g. `chioma_284`).
     - Sets random 32-character bcrypt password.
     - Assigns role `'customer'`.
4. **Token Generation**:
   - Issues client-scoped Sanctum token and returns standard user profile.

---

## 5. Password Lifecycle (Reset & Change)

### 5.1 Forgot Password
`POST /api/v1/auth/forgot-password`
- **Payload**: `{ "email": "user@example.com" }`
- **Behavior**: Generates secure password reset token in `password_reset_tokens` table.
- **Dispatch**: Enqueues Brevo transactional email with password reset link pointing to the web client: `https://candycutz.com/reset-password?token={token}&email={email}`.
- **Response**: Returns `200 OK` generic success to prevent email enumeration.

### 5.2 Reset Password
`POST /api/v1/auth/reset-password`
- **Payload**: `{ "token": "...", "email": "...", "password": "NewPassword1!", "password_confirmation": "NewPassword1!" }`
- **Behavior**: Verifies token validity, updates password hash, revokes all tokens, and dispatches confirmation email.

### 5.3 Change Password (Authenticated)
`POST /api/v1/auth/change-password`
- **Payload**: `{ "current_password": "...", "new_password": "...", "new_password_confirmation": "..." }`
- **Behavior**: Verifies current password before updating to new hash.

---

## 6. Username Modification Policy

- Usernames are public identities (`@username`).
- Users may freely choose their username during registration.
- Subsequent changes are limited to **once every 90 days**:
  ```php
  if ($user->last_username_change_at && $user->last_username_change_at->addDays(90)->isFuture()) {
      $daysRemaining = now()->diffInDays($user->last_username_change_at->addDays(90));
      throw new ValidationException("You can only change your username once every 90 days. Please wait {$daysRemaining} days.");
  }
  ```
- **Super Admin Override**: Super Admins may override username restrictions at any time via CMS with reason logging.

---

## 7. Account Deactivation Protocol

- Customers can request deactivation via `POST /api/v1/customer/account/deactivate`.
- Customers are **never permanently purged (hard-deleted)** from the database.
- Backend sets:
  ```php
  $user->update([
      'is_active' => false,
      'status' => 'deactivated',
      'deactivated_at' => now(),
  ]);
  $user->tokens()->delete(); // Invalidate all active tokens upon deactivation
  ```
- Historical appointments, payment ledgers, and audit logs remain strictly intact.
- Super Admin can restore any deactivated user via `PATCH /api/v1/admin/users/{id}/restore`.

---

## 8. Authentication Test Matrix

Before declaring the authentication domain verified, the following test cases must pass:

| # | Test Scenario | Expected Outcome | HTTP Status |
|---|---|---|---|
| **AUTH-01** | Website registration with valid fields | User created, role=customer, token returned | `201 Created` |
| **AUTH-02** | Mobile registration with `@username` | User created with custom username, token returned | `201 Created` |
| **AUTH-03** | Registration with duplicate email | Validation error | `422 Unprocessable` |
| **AUTH-04** | Registration with duplicate username | Validation error: "Username taken" | `422 Unprocessable` |
| **AUTH-05** | Login with Email | 200 OK, token issued | `200 OK` |
| **AUTH-06** | Login with `@username` | 200 OK, token issued | `200 OK` |
| **AUTH-07** | Login with Nigerian Phone number | 200 OK, token issued | `200 OK` |
| **AUTH-08** | Multi-device login (Web then Mobile) | Both tokens remain valid simultaneously | `200 OK` |
| **AUTH-09** | Logout from Mobile | Mobile token deleted; Web token remains active | `200 OK` |
| **AUTH-10** | Google Social Sign-In (New User) | User created with unique username, token issued | `200 OK` |
| **AUTH-11** | Google Social Sign-In (Existing Email) | Provider linked to existing user, token issued | `200 OK` |
| **AUTH-12** | Apple Social Sign-In | User created / linked, token issued | `200 OK` |
| **AUTH-13** | Forgot password request | Reset email queued via Brevo | `200 OK` |
| **AUTH-14** | Reset password with valid token | Password updated, old tokens revoked | `200 OK` |
| **AUTH-15** | Login with invalid password | Error: "Invalid credentials" | `401 Unauthorized` |
| **AUTH-16** | Login with deactivated account | Error: "Account is currently inactive" | `403 Forbidden` |
| **AUTH-17** | Customer token accessing `/api/v1/admin/*` | Role guard blocks with JSON 403 | `403 Forbidden` |
| **AUTH-18** | Barber token accessing `/api/v1/admin/*` | Role guard blocks with JSON 403 | `403 Forbidden` |
