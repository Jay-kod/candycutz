# Phase 0 Cleanup Manifest

**Status:** Classified from the current working tree. This manifest is the review record for Phase 0 cleanup.

## Preserve or Convert

| Candidate | Decision | Reason or replacement |
| --- | --- | --- |
| `barbing-saloon-api/fix_all_passwords.php` | Convert, then delete | Destructive password reset for every user. Replace with a guarded Artisan command only if a legitimate recovery workflow is confirmed. |
| `barbing-saloon-api/fix_gallery.php` | Investigate, then delete | Deletes gallery rows matching a dummy image path. Preserve the intent only as a reviewed data migration or cleanup command. |
| `barbing-saloon-api/fix_password.php` | Convert, then delete | Resets one barber password using hard-coded credentials. Replace with a secure password-reset/admin workflow. |
| `barbing-saloon-api/seed_customer.php` | Convert, then delete | Creates or mutates a demo customer. Fold into a non-production seeder with environment gating. |
| `barbing-saloon-api/seed_demo_data.php` | Convert, then delete | Creates demo data but deletes existing appointments first. Replace with an idempotent non-production `DemoSeeder`; never run unchanged. |
| `barbing-saloon-api/seed_manual.php` | Convert, then delete | Creates a legacy users table and demo accounts in a different database name. Reconcile with Laravel seeders before replacement. |
| `barbing-saloon-api/run_migration.php` | Convert, then delete | Applies one raw SQL migration directly. Convert its intent to a Laravel migration. |
| `barbing-saloon-api/run_migrations.php` | Delete after verification | Does not execute Laravel migration `up()` methods; it can falsely mark migrations as complete. |
| `barbing-saloon-api/migration_otp.php` | Convert, then delete | Adds `verification_code` directly. Find whether the column exists in current migrations/schema, then retain only as a Laravel migration if needed. |
| `barbing-saloon-api/dump_images.php` | Delete after review | Read-only profile image inspection utility; no production application behavior. |
| `barbing-saloon-api/update_author_display.php` | Convert, then delete | Adds a blog column directly. Verify current migration/schema state and retain the schema intent as a Laravel migration if missing. |
| `barbing-saloon-api/update_passwords.php` | Delete after review | Hard-coded demo password reset. No safe production behavior. |
| `barbing-saloon-api/scratch/*` | Delete after reading | Ad hoc settings/image/optimization utilities; useful intent must become a seeder or Artisan command. |
| `barbing-saloon-api/optimize_db.sql` | Convert or archive, then delete | Manual index/schema changes require comparison with migrations and production schema. |
| `DELETE/*` | Delete after review | Historical artifacts outside the active application tree. |
| `barbing-saloon-web/dist/*` | Delete from version control | Generated build output; rebuild during deployment. |
| Root and API `composer.phar` | Delete | Committed binary; Composer must be installed externally. |
| Root `artisan` stub | Delete after command verification | The Laravel entry point is `barbing-saloon-api/artisan`. |
| Root `*.bat` and `*.ps1` launchers | Replace or delete | Hard-coded Windows/XAMPP launchers conflict with portable Docker development. Preserve useful commands in documented scripts only. |
| Root placeholder icon PNGs | Delete after asset verification | Verify no active client references them before removal. |

## Preserve Until Characterization

The following files must not be deleted before Phase 1 captures their useful behavior:

- `barbing-saloon-api/test_*.php`.
- `barbing-saloon-api/test.php`.
- `barbing-saloon-api/tests/*.php`.
- Any diagnostic script that is the only evidence for a current production behavior.

The repository rule is stronger than the cleanup shortcut: behavior is captured first, then the scripts are removed.

## Schema Capture Blocker

XAMPP's `mysql.exe` and `mysqldump.exe` exist, but MySQL is not currently reachable at `127.0.0.1:3306`. Production schema capture and fresh-schema comparison are therefore blocked until a database source is made available.

No `production-schema.sql` or `DRIFT.md` is created with guessed content.
