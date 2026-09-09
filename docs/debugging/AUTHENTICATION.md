# Candycutz — Authentication Debugging Runbook

## 1. Problem: Logging In on Mobile Logs Out the Website (Session Collision)

### What it looks like:
You log in on your phone, then click anything on the website on your computer, and the website immediately throws:
> `401 Unauthorized: Unauthenticated.`

### Root Cause:
In `AuthService::login`, all tokens for the user were deleted:
```php
$user->tokens()->delete(); // Destructive purge!
```

### How to Fix:
Do NOT delete all tokens upon login. Issue a new personal access token named after the client device:
```php
$tokenName = $data['device_name'] ?? 'web-client';
$token = $user->createToken($tokenName)->plainTextToken;
```
Both devices will have separate records in `personal_access_tokens` with independent lifecycles.

---

## 2. Problem: 429 Too Many Requests During Development

### What it looks like:
API requests to `/api/v1/auth/*` fail with:
> `HTTP 429 Too Many Requests`

### Root Cause:
`ModuleServiceProvider` had a restrictive throttle rule:
```php
'Auth' => ['api', 'throttle:10,1', ...] // Only 10 requests per minute!
```
Normal page reloads and failed login attempts immediately exhaust this limit.

### How to Fix:
Adjust the throttle limit to 60 requests per minute for local development in `app/Providers/ModuleServiceProvider.php`:
```php
'Auth' => ['api', 'throttle:60,1', 'sanitize.input', 'security.headers'],
```

---

## 3. Problem: Google Sign-In Blocked in Browser

### What it looks like:
Clicking "Sign in with Google" fails with:
> `Google sign-in was blocked by this browser (private/incognito mode or third-party cookies disabled).`

### Root Cause:
The Google Identity Services SDK (`gsi/client`) requires third-party cookie access in certain popup configurations.

### How to Fix:
1. Ensure the user is testing in a standard (non-incognito) browser window.
2. In `useSocialAuth.js`, ensure `ux_mode: 'popup'` is initialized with the correct `client_id` retrieved from `/api/public/settings`.
3. In Google Cloud Console, ensure the authorized JavaScript origins include:
   - `http://localhost:5173`
   - `http://localhost:5174`
   - `https://candycutz.com`
