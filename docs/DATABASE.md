# CandyCutz Database Architecture & Runbook

This document is the authoritative guide to the CandyCutz database schema, relational structure, indexing strategy, concurrency controls, and disaster recovery procedures.

---

## 1. Architectural Overview

CandyCutz enforces a single canonical database tier supporting the monolithic Laravel backend (`barbing-saloon-api`). All clients (Web SPA, Customer Mobile App, Admin Dashboard) interface exclusively with this database via the versioned API (`/api/v1/*`).

* **Production Environment:** MySQL 8.0 (InnoDB engine, utf8mb4 collation).
* **Testing & Local CI:** SQLite 3 (in-memory and file-based isolated snapshots).
* **ORM:** Laravel Eloquent with strict type declarations, eager loading, and foreign key integrity.

---

## 2. Relational Entity Graph

```
                                +-------------------+
                                |       users       |
                                +---------+---------+
                                          |
         +--------------------------------+--------------------------------+
         |                                |                                |
+--------v----------+            +--------v----------+            +--------v----------+
|      barbers      |            |   appointments    |            |   device_tokens   |
+--------+----------+            +--------+----------+            +-------------------+
         |                                |
         |                       +--------+--------+
         |                       |                 |
+--------v----------+   +--------v-------+  +------v-------+
| barber_schedules  |   |  appointment_  |  |   payments   |
+-------------------+   |    services    |  +------+-------+
|  blocked_periods  |   +--------+-------+         |
+-------------------+            |          +------v-------+
                                 |          |payment_audits|
                        +--------v-------+  +--------------+
                        |    services    |
                        +--------+-------+
                                 |
                        +--------v-------+
                        |   categories   |
                        +----------------+
```

### 2.1 Core Tables

1. **`users`**
   * Primary authentication and identity table.
   * Roles: `customer`, `barber`, `admin`.
   * Enforces unique `email`, optional `phone`, hashed passwords, and email verification stamps.

2. **`barbers`**
   * Extends user profiles for barbers.
   * Tracks `chair_status` (`available`, `busy`, `on_break`, `offline`), `bio`, `specialties`, and `rating`.

3. **`barber_schedules` & `blocked_periods`**
   * Defines operating hours per day-of-week and explicit calendar blocked intervals (vacations, maintenance).
   * Used by `BookingService` to calculate dynamic appointment slot availability.

4. **`services` & `service_categories`**
   * Catalog of grooming services, durations (in minutes), prices, and category classifications.

5. **`appointments`**
   * Central reservation record connecting a customer, barber, date, start/end time, and total price.
   * State machine states: `pending`, `confirmed`, `in_progress`, `completed`, `cancelled`, `no_show`.
   * Governed by concurrency locks during booking creation.

6. **`payments` & `payment_audits`**
   * Tracks financial transactions for appointments.
   * Methods: `card` (Paystack), `manual_transfer`, `cash`.
   * Statuses: `pending`, `awaiting_transfer`, `under_review`, `completed`, `failed`, `rejected`.
   * Audit log captures state changes, verification timestamps, and rejection rationales.

7. **`device_tokens` & `notifications`**
   * Multi-device push notification tokens (Expo / FCM / APNS) tied to user IDs.
   * Unique composite constraint on `(user_id, token)`.

8. **`galleries`, `blog_posts`, `testimonials`**
   * CMS content tables managed by administrators and displayed across web and mobile experiences.

---

## 3. Indexing Strategy & Performance Rationale

Every index in the schema is designed for specific query access patterns:

| Table | Index Columns | Purpose |
|---|---|---|
| `appointments` | `(barber_id, appointment_date, status)` | Fast slot computation and double-booking conflict checks in `BookingService`. |
| `appointments` | `(user_id, status)` | Optimized customer history and active booking lookups. |
| `appointments` | `(appointment_date, status)` | Daily salon operational reports and admin dashboard metrics. |
| `payments` | `reference` (UNIQUE) | Guaranteed idempotency on payment gateway webhooks and verification lookups. |
| `payments` | `(appointment_id, status)` | Rapid resolution of booking payment state and receipt downloads. |
| `device_tokens` | `(user_id, token)` (UNIQUE) | Prevents duplicate device registrations while enabling multi-device broadcast. |
| `barber_schedules` | `(barber_id, day_of_week)` | Instant calendar template resolution. |
| `blocked_periods` | `(barber_id, starts_at, ends_at)` | Interval overlap querying during availability filtering. |

---

## 4. Concurrency & Data Integrity Guarantees

### 4.1 Double-Booking Prevention (`lockForUpdate`)
`BookingService::createAppointment()` employs pessimistic row locking:
```php
DB::transaction(function () use ($data) {
    // Acquire lock on barber's overlapping appointments
    $conflicts = Appointment::where('barber_id', $data['barber_id'])
        ->where('appointment_date', $data['date'])
        ->whereIn('status', ['confirmed', 'in_progress'])
        ->where(function ($q) use ($start, $end) {
            $q->whereBetween('start_time', [$start, $end])
              ->orWhereBetween('end_time', [$start, $end]);
        })
        ->lockForUpdate()
        ->exists();

    if ($conflicts) {
        throw new BookingConflictException("The selected slot is no longer available.");
    }

    return Appointment::create($data);
});
```

### 4.2 Webhook Idempotency
`PaymentService` verifies webhook signatures and queries existing payment records by immutable `reference` before processing state changes, ensuring duplicate delivery produces zero unintended side effects.

---

## 5. Disaster Recovery Runbook

CandyCutz provides two production-grade Artisan commands for automated database lifecycle operations.

### 5.1 Backup Snapshot (`db:backup`)
Creates a consistent snapshot of the active database.

```bash
# Default backup to storage/app/backups/candycutz_backup_{timestamp}.sql
php artisan db:backup

# Custom filename and directory
php artisan db:backup --filename=pre_migration_backup.sql --directory=/var/backups/candycutz
```

* **SQLite Implementation:** Executes SQLite transaction-safe export of schema, tables, and indexes.
* **MySQL Implementation:** Executes `mysqldump` with transactional flags, falling back to buffered PDO table export if the system binary is absent.

### 5.2 Disaster Recovery Restore (`db:restore`)
Restores the database state from an existing snapshot with post-restore integrity checks.

```bash
# Restore from the latest available snapshot
php artisan db:restore

# Restore from a specified snapshot file (with bypass confirmation)
php artisan db:restore storage/app/backups/candycutz_backup_2026-09-18_200000.sql --force
```

**Post-Restore Automated Verification:**
* Verifies table enumeration in `sqlite_master` or `SHOW TABLES`.
* Verifies core record counts (`users`, `appointments`).
* Restores foreign key enforcement post-import.

### 5.3 Automated Verification Evidence
The disaster recovery drill is continuously tested in the test suite via `tests/Feature/DatabaseBackupRestoreTest.php`:
1. Creates canary data in database.
2. Executes `php artisan db:backup`.
3. Mutates state (deletes canary record).
4. Executes `php artisan db:restore --force`.
5. Confirms the canary record and database schema are restored cleanly.
