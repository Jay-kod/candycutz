# CandyCutz Project Context

**Status:** Phase 0 working document. Facts below are verified from the current codebase unless explicitly marked as a business input requiring confirmation.

## Business Identity

- Product name: CandyCutz.
- Business type: grooming and barbing salon.
- Current seeded business: `CandyCutz Enterprise`.
- Current seeded legal name: `CandyCutz Grooming Ltd.`.
- Branch: `Keffi Main Hub` with slug `keffi-central`.
- Current seeded address: Angwan Kare, BCG, beside Angwan Kare BCG Gas Station and Wealths Khort Apartments, Keffi 961101, Nasarawa, Nigeria.
- Current seeded phone: `+234 810 000 0000`.
- Current seeded email: `concierge@candycutz.com`.

Sources: `barbing-saloon-api/database/seeders/KeffiOperationsSeeder.php`, `barbing-saloon-api/database/seeders/SettingSeeder.php`.

## Location

The current code stores the branch coordinates as latitude `8.84710000` and longitude `7.87360000`.

The remediation requirements name latitude `8.8486` and longitude `7.8736`. This is an unresolved business-data discrepancy. It must be confirmed before changing the database or seeders.

## Operating Hours

`WorkingHourSeeder` creates the following default schedule for each barber:

- Monday through Saturday: 08:00–19:00.
- Sunday: closed.

The former `seed_demo_data.php` uses a different Marcus-specific schedule, including Sunday 10:00–18:00. That script is not an authoritative current seeder and must not overwrite production schedules without an explicit business decision.

Source: `barbing-saloon-api/database/seeders/WorkingHourSeeder.php`.

## Home-Service Zones

The current `KeffiOperationsSeeder` creates three grouped zones:

1. **Keffi Central Zone**: BCG, Angwan Kare, Main Market, and Total Filling Station corridor.
2. **University Axis Zone**: Nasarawa State University Main Campus, High Court, Pyanku, and GRA.
3. **Outskirts Zone**: Akwanga Road Axis, Gidan Zakara, and Keffi Bypass corridor.

The remediation requirements name Angwan Kare, High Court, Total, Gidan Zakara, NSUK Main Campus, and Pyanku as individual business areas. The current schema and seeder group them into three zones; splitting them requires a business/data decision.

Source: `barbing-saloon-api/database/seeders/KeffiOperationsSeeder.php`.

## Deployables

- `barbing-saloon-api`: Laravel 11 API and database application.
- `barbing-saloon-web`: Vue 3 and Vite web client.
- `candycutz-mobile-app`: Expo/React Native mobile client.

The root package scripts start the API and web development processes on Windows. The mobile package has separate Expo commands. Docker Compose defines Nginx, PHP-FPM, queue worker, scheduler, MySQL, and Redis services.

Sources: root `package.json`, each deployable's package/configuration files, and `docker-compose.yml`.

## Current Roles

The role enum defines four roles:

- `customer`: authenticated customer operations and appointment access.
- `barber`: barber schedule, blocked periods, chair status, appointments, walk-ins, and barber profile operations.
- `admin`: operational and management functions exposed by the current application.
- `super_admin`: elevated administrative role in the current identity model.

The current route file confirms public catalogue routes, authentication routes, authenticated appointment and notification routes, and barber-specific operations. Complete role enforcement is not yet proven across every route.

Sources: `barbing-saloon-api/app/Core/Enums/UserRole.php`, `barbing-saloon-api/routes/api_v1.php`, and the related controllers.

## Current API Mounts

`routes/api.php` mounts the same `routes/api_v1.php` route set twice:

- `/api/v1/*`.
- `/api/*`.

This is a known duplication targeted for Phase 4, not a desired final state.

## Known Broken

The following defects are documented as current findings and require characterization tests before remediation:

- Duplicate API mount points: `barbing-saloon-api/routes/api.php`.
- Raw SQL files coexist with PHP migrations: `barbing-saloon-api/database/migrations/`.
- Unsafe migration runner can mark migrations without executing them: `barbing-saloon-api/run_migrations.php`.
- Global input sanitization is attached to the API middleware stack: `barbing-saloon-api/routes/api.php` and the middleware alias configuration. Its effect on TipTap content must be verified before removal.
- Ad hoc maintenance scripts use hard-coded local database credentials and inconsistent database names: `barbing-saloon-api/fix_all_passwords.php`, `barbing-saloon-api/fix_password.php`, `barbing-saloon-api/seed_customer.php`, and `barbing-saloon-api/seed_manual.php`.
- `fix_all_passwords.php` resets every user's password to the same value. It must never be run against production.
- `seed_demo_data.php` deletes appointments before recreating demo records. It is not safe as a production seeder.
- The repository has procedural scripts under `barbing-saloon-api/tests/` rather than a verified PHPUnit/Pest harness.
- The working tree includes a pre-existing unrelated modification in `candycutz-mobile-app/src/components/common/AppPreloader.tsx`.

This document does not claim that password exposure, IDOR, CORS, upload, payment, or webhook defects are fixed or present until each is verified against the current implementation and characterized by a test.
