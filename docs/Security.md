# Candycutz — Security & Threat Modeling Architecture

## 1. Threat Modeling & Core Defense Objectives
Candycutz processes customer payments, manages staff schedules, and stores personal contact and address details. The security posture enforces defense-in-depth across the API gateway, application domain, database, and mobile runtime.

---

## 2. Authentication & Session Security

### 2.1 Sanctum Token Lifecycles
- Direct logins issue stateful Sanctum personal access tokens.
- Tokens expire after 30 days of inactivity (`config/sanctum.php` expiration setting).
- Tokens are hashed in `personal_access_tokens` table using SHA-256; raw tokens are only visible to the user at the immediate instant of issuance.
- Token revocation is mandatory on password changes or account deactivation.

### 2.2 Brute-Force & Credential Stuffing Defenses
- Login endpoints enforce strict rate limiting: maximum **5 attempts per minute per IP**.
- Subsequent failures trigger exponential backoff headers: `Retry-After: <seconds>` and HTTP 429 Too Many Requests.
- Generic error messages: *"Invalid credentials"* rather than indicating whether the email or password was incorrect.

---

## 3. Authorization & Role-Based Access Control (RBAC)

Access is strictly enforced using Laravel Policies and Spatie Permissions:
- `super_admin`: Total system authority, God-Mode overrides, Theme Studio publishing, audit log inspection.
- `admin`: Branch operations, barber scheduling, service catalog, reports.
- `barber`: View own assigned appointments, update own chair status, manage own portfolio and working hours. Barbers CANNOT view appointments or revenue of other barbers.
- `customer`: View own appointments, manage own addresses, submit reviews. Customers CANNOT access or mutate another customer's bookings.

### 3.1 Policy Enforcement Example
```php
class AppointmentPolicy
{
    public function view(User $user, Appointment $appointment): bool
    {
        if ($user->hasRole('super_admin') || $user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('barber') && $appointment->barber->user_id === $user->id) {
            return true;
        }

        return $appointment->customer_id === $user->id;
    }

    public function cancel(User $user, Appointment $appointment): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Customer can only cancel their own appointment if >= 4 hours remaining
        if ($appointment->customer_id === $user->id) {
            $appointmentTime = Carbon::parse("{$appointment->appointment_date} {$appointment->start_time}");
            return now()->diffInHours($appointmentTime, false) >= 4;
        }

        return false;
    }
}
```

---

## 4. Secure File Uploads (Media & Avatars)

To prevent arbitrary script execution via avatar or portfolio image uploads:
1. **MIME Verification**: Files are validated using server-side binary inspections (`image/jpeg`, `image/png`, `image/webp`), not client file extensions.
2. **File Size Capping**: Avatars capped at 2MB; portfolio photos capped at 5MB.
3. **Randomized Renaming**: Uploaded files are stored with random UUID v4 filenames:
   `uploads/avatars/{uuid}.webp`
4. **Execution Prohibition**: The uploads directory web server configuration explicitly disables script execution (`php_flag engine off` in `.htaccess` / Nginx location block).
5. **Storage Isolation**: Production deployments store media on secure S3-compatible object storage with signed URLs.

---

## 5. Webhook Security & Tamper Proofing
- Stripe webhooks verify incoming cryptographic signatures via `\Stripe\Webhook::constructEvent($payload, $sigHeader, $secret)`.
- Timestamps older than 300 seconds are rejected to defend against replay attacks.
- Duplicate event IDs are dropped immediately using atomic unique index lookups on `payment_transactions.gateway_event_id`.

---

## 6. Audit Trail Immutability
All mutations performed by administrators are written to `audit_logs`:
- The table permits `INSERT` and `SELECT` operations only.
- No `UPDATE` or `DELETE` permissions are granted to the application user on `audit_logs`.
- Records store the initiating user ID, target entity, pre-change JSON, post-change JSON, IP address, and browser User-Agent.
