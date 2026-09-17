# Schema Drift Report

**Generated:** 2026-09-17
**Method:** `mysqldump --no-data` of production `candycutz_db` vs `php artisan migrate:fresh` output, both normalized (comments, timestamps, AUTO_INCREMENT values removed).

## Summary

The drift is **minimal** — 3 column-level differences across 2 tables. The table set matches exactly (31 tables each).

## Drift Details

### Table: `barbers`

| Column | Production | Fresh (migrations) | Action |
|--------|-----------|-------------------|--------|
| `rating` | `decimal(3,2) DEFAULT 5.00` | `decimal(3,1) DEFAULT 5.0` | Align migration to `decimal(3,2)` to match production. The 3,2 precision supports values like 4.95. |
| `total_reviews` | `int(11) NOT NULL DEFAULT 0` | **MISSING** | Add column to migrations. Production has it; fresh does not. |
| `chair_status` | Column position differs | Column position differs | Cosmetic; order difference only. No action needed. |

### Table: `services`

| Column | Production | Fresh (migrations) | Action |
|--------|-----------|-------------------|--------|
| `is_available` | `tinyint(1) NOT NULL DEFAULT 1` | **MISSING** | Add column to migrations. Production has it; fresh does not. |

## Raw Diff

```diff
 CREATE TABLE `barbers` (
   ...
   `is_home_service_ready` tinyint(1) NOT NULL DEFAULT 1,
-  `rating` decimal(3,2) NOT NULL DEFAULT 5.00,
-  `total_reviews` int(11) NOT NULL DEFAULT 0,
   `chair_status` varchar(20) NOT NULL DEFAULT 'free',
+  `rating` decimal(3,1) NOT NULL DEFAULT 5.0,
   `experience_years` int(10) unsigned DEFAULT NULL,

 CREATE TABLE `services` (
   ...
   `is_active` tinyint(1) NOT NULL DEFAULT 1,
-  `is_available` tinyint(1) NOT NULL DEFAULT 1,
   `is_featured` tinyint(1) NOT NULL DEFAULT 0,
```

## Phase 2 Work Order

1. Add `total_reviews` column (`int NOT NULL DEFAULT 0`) to the barbers migration.
2. Change `rating` precision from `decimal(3,1)` to `decimal(3,2)` in the barbers migration.
3. Add `is_available` column (`boolean NOT NULL DEFAULT true`) to the services migration.
4. After fixes: `migrate:fresh` dump must diff clean against `production-schema.sql`.

## Integrity Gaps (from ARCHITECTURE.md §7.3)

These are **not** in either schema but are required by the target architecture:

- [ ] Partial unique index on `appointments(barber_id, appointment_date, appointment_time)` for non-cancelled rows
- [ ] Composite index on `appointments(barber_id, appointment_date, status)`
- [ ] Composite index on `appointments(customer_id, status)`
- [ ] Composite index on `payments(appointment_id, status)`
- [ ] Unique on `payment_transactions(gateway_event_id)`
- [ ] FK cascade rules per §7.3
