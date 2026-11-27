# Consent & Legal Acceptance — Implementation Plan

**Status:** Proposal. No application code has been changed.
**Verified against:** working tree at commit `e753c2d` (branch `feat/resend-mail`), 2026-11-26.
**Scope:** Terms of Service acceptance, Privacy Policy acceptance, cookie consent recording and enforcement.

---

## 1. What exists today (verified)

### 1.1 Cookie consent — UI only

| Piece | Path |
|---|---|
| Banner | `barbing-saloon-web/src/shared/components/CookieConsentBanner.vue` |
| Preferences modal | `barbing-saloon-web/src/shared/components/CookiePreferencesModal.vue` |
| Public preferences page | `barbing-saloon-web/src/modules/public/pages/CookiePreferencesPage.vue` |
| Profile card | `barbing-saloon-web/src/shared/components/UserProfileCookiePreferencesCard.vue` |
| State | `barbing-saloon-web/src/shared/composables/useCookieConsent.js` |
| Admin CMS editor | `barbing-saloon-web/src/modules/admin/pages/cms/CookiePreferencesEditor.vue` |

Storage is **browser-localStorage only**:

```js
// useCookieConsent.js
export const STORAGE_KEY = 'candycutz_cookie_preferences';

{
  necessary: true,
  preferences: false,
  analytics: false,
  marketing: false,
  updatedAt: "2026-11-26T...",
  version: 1
}
```

- No API call.
- No database table.
- No link to `users.id`.
- No IP address, user agent, or audit entry.
- Clearing browser storage loses the record.
- `hasConsented` is referenced only by consent UI components. A search for `gtag`, `fbq`, `posthog`, `plausible`, `mixpanel`, `google-analytics` returns **zero** matches. Consent currently gates nothing.

### 1.2 Terms & Conditions — not collected

| Check | Result |
|---|---|
| T&C checkbox on registration | **None** (`RegisterPage.vue` lines 59–200) |
| API validation for terms | **None** (`RegisterRequest.php` rules, lines 33–43) |
| Consent written on register | **None** (`RegisterCustomer.php`, lines 29–72) |
| `users` columns for terms | **None** — no `terms_accepted_at`, `terms_version`, `consent_*` |
| Migration for consent | **None** |
| Social registration consent | **None** (`SocialLogin.php` creates/links users without consent) |
| Login-time enforcement | **None** — no middleware, no gate, no redirect |
| Audit entries | `audit_logs` has 197 rows. `auth.register` = 2, `auth.social_register` = 2, and none contain consent data. |

### 1.3 Terms content

- Rendered by `barbing-saloon-web/src/modules/public/pages/TermsPage.vue`.
- Content comes from public settings `terms_sections` / `terms_title` (group `legal`), with hardcoded defaults in the SPA.
- **No version field** exists.
- "Last updated" is `new Date()` on every visit, so it always shows today's date — misleading.
- The default IP clause references "the United States"; this needs business/legal review for a Nigerian business.

### 1.4 Infrastructure available to reuse

- `settings` table + `SettingsService` + public `legal` group.
- `audit_logs` table with `ip_address`, `user_agent`, `old_value`, `new_value`.
- `HasAuditLog` trait.
- `ApiResponse` envelope.
- FormRequests, Actions, Pest tests.
- `EnsureUserActive` middleware — the correct place to add a consent gate alongside it.
- Admin customer dossier: `features/customers/components/CustomerProfilePanel.vue`.
- Superadmin audit page: `modules/superadmin/pages/AuditLogsPage.vue`.

---

## 2. Direct answers

**Can the system tell you which users consented to cookies?**
No. Consent lives in each browser's localStorage. It is not attached to an account and cannot be listed or audited.

**Is it important to know?**
Legally, yes. Nigeria's Data Protection Act 2023 (and GDPR for EU visitors) requires consent to be freely given, specific, informed, unambiguous, and **demonstrable**. Today the interaction pattern is good (equal Accept / Reject, no pre-ticked optional categories), but there is no proof. Operationally it currently gates nothing, so it is decorative rather than functional.

**Which users accepted T&C before registering?**
None can be identified. Acceptance was never requested, validated, stored, or audited. Recorded acceptances: **0**.

**Does the system force acceptance before the next login?**
No. There is no gate, no endpoint, and no client redirect. A user can register, log in, and use every feature without ever seeing the T&C.

---

## 3. Target design

### 3.1 One consent ledger, not columns on `users`

A dedicated table preserves history and supports re-acceptance when policies change.

```sql
CREATE TABLE user_consents (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id       BIGINT UNSIGNED NULL,          -- NULL for anonymous cookie consent
    consent_id    CHAR(36) NULL,                 -- anonymous browser id, links guest -> user
    type          VARCHAR(20) NOT NULL,          -- terms | privacy | cookies
    version       VARCHAR(40) NOT NULL,          -- e.g. 2026-11-26 or 1.0
    accepted_at   TIMESTAMP NOT NULL,
    ip_address    VARCHAR(45) NULL,
    user_agent    TEXT NULL,
    source        VARCHAR(40) NOT NULL,          -- register | login_gate | social | settings | admin
    metadata      JSON NULL,                     -- cookie categories, policy hash, etc.
    created_at    TIMESTAMP NULL,
    updated_at    TIMESTAMP NULL,
    CONSTRAINT fk_user_consents_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uq_user_consent (user_id, type, version),
    KEY idx_consent_type_version (type, version),
    KEY idx_consent_consent_id (consent_id)
);
```

Why this shape:

- `user_id` nullable so guest cookie choices can be recorded before login.
- `consent_id` lets the frontend link an anonymous record to the account on login.
- `metadata` holds `{ necessary: true, preferences: false, analytics: true, marketing: false }`.
- Unique key makes recording idempotent (same user + type + version).
- No destructive updates: a new version inserts a new row.

### 3.2 Policy versions

Add settings (group `legal`, already public):

| Key | Example | Purpose |
|---|---|---|
| `terms_version` | `2026-11-26` | Current T&C version |
| `terms_updated_at` | `2026-11-26` | Real "last updated" date |
| `privacy_version` | `2026-11-26` | Current privacy version |
| `cookie_policy_version` | `2026-11-26` | Current cookie policy version |

When an admin saves new legal content, the version must be bumped explicitly (a "Publish new version" action). Do not auto-bump on every keystroke.

### 3.3 Enforcement model

- **Registration:** `terms_accepted` and `privacy_accepted` are required booleans, plus `terms_version` / `privacy_version` from public settings. Reject with `422` if missing or stale.
- **Login:** response includes `consent_required: true` and `required_versions` when the user has no row for the current version.
- **Authenticated routes:** `EnsureConsentAccepted` middleware returns `403` with code `CONSENT_REQUIRED` and `data: { terms_version, privacy_version, accept_url }`. Exempt: `/auth/*`, `/health*`, `/payments/webhook`, `/system/client-errors`.
- **Frontend:** axios interceptor sees `CONSENT_REQUIRED`, opens a blocking acceptance screen, posts acceptance, then retries the original request.
- **Social login:** same gate. The user accepts on first authenticated request; `source = 'social'`.
- **Admins/superadmins/barbers:** decide explicitly (see §8). Recommendation: apply to all roles, because employment does not remove personal-data consent.

### 3.4 API contract

```
POST /api/v1/auth/register
{
  "name": "...", "phone": "...", "email": "...",
  "password": "...", "password_confirmation": "...",
  "terms_accepted": true,
  "privacy_accepted": true,
  "terms_version": "2026-11-26",
  "privacy_version": "2026-11-26"
}
```

```
POST /api/v1/account/consents/accept        (auth)
{
  "type": "terms",
  "version": "2026-11-26"
}
-> 200 { success: true, data: { accepted_at, type, version } }
```

```
POST /api/v1/consent/cookies                (public, throttle:30,1)
{
  "consent_id": "uuid",
  "version": "2026-11-26",
  "preferences": { "necessary": true, "preferences": false, "analytics": true, "marketing": false }
}
-> 204
```

```
GET /api/v1/admin/consents?type=terms&version=2026-11-26&status=accepted|pending
GET /api/v1/admin/consents/export.csv
GET /api/v1/admin/customers/{id}            (extend existing response)
```

### 3.5 Admin visibility

- New "Consent & Legal" tab on the Customers area (or a card in the customer dossier).
- Columns: user, email, role, terms version, accepted at, source, IP, cookie categories.
- Filters: accepted / pending / stale version; type; date range.
- CSV export for audits.
- Counters: `accepted_current`, `pending`, `stale`.
- Consent actions also appear in the existing superadmin audit log.

---

## 4. Phased implementation

### Phase 0 — Decisions (blocking)

1. Backfill policy for the 8 existing users (see §7).
2. Scope: customers only, or all roles?
3. Legal copy owner and the real effective date.
4. Whether cookie consent must also be recorded for anonymous visitors.

### Phase 1 — Backend consent ledger + registration enforcement

**Files to add**

- `database/migrations/<date>_create_user_consents_table.php`
- `app/Models/UserConsent.php`
- `app/Domain/Identity/Actions/RecordConsent.php`
- `app/Http/Requests/Api/V1/Auth/RegisterRequest.php` (extend)
- `app/Domain/Identity/Actions/RegisterCustomer.php` (extend)
- `app/Http/Resources/Api/V1/AuthUserResource.php` (add `consent` block)
- `tests/Feature/Consent/RegistrationConsentTest.php`

**Behaviour**

- Wrap user creation + consent rows in one `DB::transaction`.
- `source = 'register'`, capture IP + user agent.
- Audit action `auth.terms_accepted`, `auth.privacy_accepted` via `HasAuditLog`.
- Add `terms_version` / `privacy_version` to public settings output.

**Exit criteria**

- Register without acceptance -> `422` with field errors.
- Register with acceptance -> `201`, two `user_consents` rows, audit rows present.
- Replaying the same request does not duplicate rows.

### Phase 2 — Login gate + blocking acceptance screen

**Files to add/extend**

- `app/Http/Middleware/EnsureConsentAccepted.php`
- `bootstrap/app.php` (register alias, append to authenticated stack)
- `app/Http/Controllers/Api/V1/AccountApiController.php` (`acceptConsent`)
- `routes/api/v1/account.php`
- `barbing-saloon-web/src/core/api/client.js` (handle `CONSENT_REQUIRED`)
- `barbing-saloon-web/src/modules/auth/pages/ConsentAcceptPage.vue`
- `barbing-saloon-web/src/portals/auth/routes.js`
- `barbing-saloon-web/src/modules/auth/store/auth.store.js` (store consent flags)

**Behaviour**

- Login/register response includes `consent_required` and versions.
- Gate returns `403` (not `401`) so the token is not cleared.
- Frontend shows a non-dismissible screen with links to `/terms` and `/privacy`; acceptance posts and retries.
- Logout remains reachable.

**Exit criteria**

- Existing user with no consent is blocked on first authenticated request.
- After acceptance, the same request succeeds without re-login.
- `logout` and `/auth/me` remain available while blocked.

### Phase 3 — Cookie consent recorded server-side

**Files to add/extend**

- `app/Http/Controllers/Api/V1/ConsentApiController.php`
- `routes/api/v1/catalogue.php` or a new `consent.php` (public route)
- `barbing-saloon-web/src/shared/composables/useCookieConsent.js` (POST after persist; queue on failure)
- `barbing-saloon-web/src/core/api/client.js` (send `consent_id`)
- `tests/Feature/Consent/CookieConsentTest.php`

**Behaviour**

- Generate a UUID `consent_id` on first visit and persist it.
- Post categories + policy version after every change.
- If authenticated, attach `user_id`; on login, claim the anonymous row.
- Only initialise analytics/marketing modules when the matching category is true. Today no such scripts exist; add the guard so future scripts cannot bypass consent.

**Exit criteria**

- Accept all / reject all / custom each produce one server row.
- Guest row is claimed on login, not duplicated.
- No non-essential script loads before consent.

### Phase 4 — Admin reporting + export

**Files to add/extend**

- `app/Http/Controllers/Api/V1/Admin/ConsentAdminApiController.php`
- `routes/api/v1/admin.php`
- `app/Domain/Identity/Actions/GetConsents.php`
- `barbing-saloon-web/src/features/customers/components/CustomerConsentCard.vue`
- `barbing-saloon-web/src/features/customers/pages/ConsentReportPage.vue` (or a tab)
- `barbing-saloon-web/src/portals/admin/routes.js`

**Exit criteria**

- Admin can list accepted/pending/stale users and export CSV.
- Customer dossier shows current T&C, privacy, and cookie status.
- Superadmin audit log contains consent actions.

### Phase 5 — Tests, backfill, rollout

- Pest: registration, gate, acceptance, cookie endpoint, admin filters, IDOR (customer cannot read another user's consents).
- Vitest: consent composable posts, retry after `CONSENT_REQUIRED`.
- Playwright: register -> accept -> book; existing user -> blocked -> accept -> continue.
- Run `php artisan test`, `pint --test`, `phpstan`, `npm run lint`.
- Deploy backend first, then web. The gate must be behind a feature flag until the frontend ships.

---

## 5. Data migration / backfill

Existing users: 8. Existing consent records: 0.

| Option | Meaning | Recommendation |
|---|---|---|
| **A. Force on next login** | Insert nothing; gate prompts every existing user; record with `source='login_gate'` | **Recommended.** Honest and auditable. |
| B. Grandfather | Insert rows with `accepted_at = users.created_at` | Rejected: fabricates consent. |
| C. New users only | No gate for existing accounts | Rejected: leaves most users unrecorded. |

For option A, communicate the change before deploy: "On your next sign-in you will be asked to review and accept the updated Terms and Privacy Policy."

---

## 6. Test matrix

| # | Scenario | Expected |
|---|---|---|
| 1 | Register without `terms_accepted` | 422, no user created |
| 2 | Register with stale `terms_version` | 422 |
| 3 | Register with current versions | 201, user + 2 consent rows + audit |
| 4 | Duplicate accept (same version) | 200, no duplicate row |
| 5 | Login as user with no consent | `consent_required: true` |
| 6 | Authenticated request before acceptance | 403 `CONSENT_REQUIRED` |
| 7 | Accept then retry | 200 |
| 8 | Logout while blocked | 200 |
| 9 | Social login first time | Gate applies, `source='social'` |
| 10 | Cookie accept-all | One row, all categories true |
| 11 | Cookie reject-all | One row, only necessary true |
| 12 | Guest cookie row then login | Row claimed, no duplicate |
| 13 | Admin list filter accepted/pending | Correct counts |
| 14 | Customer requests another user's consents | 403 |
| 15 | CSV export | Correct headers and rows |

---

## 7. Risks and guardrails

- **Lockout risk:** a bad gate can block every authenticated request. Exempt auth, health, webhook, and client-error routes; ship behind `FEATURE_CONSENT_GATE`; monitor 403 rates.
- **Token clearing:** return `403`, never `401`, so the SPA does not log the user out.
- **Version drift:** the frontend must fetch versions from `publicSettings()`, not hardcode them.
- **Cache:** `SettingsService` caches public settings for 1 hour; bumping a version must call `clearCache()` (the `Setting` model already forgets `settings.public` on save).
- **Legal copy:** the T&C "Last updated" must be a stored date, not `new Date()`. The "United States" clause needs review.
- **PII in exports:** consent exports contain IP and user agent. Restrict to admin/superadmin and audit the export action.
- **Anonymous consent:** storing IP for guests is personal data; document retention (recommend 24 months) and purpose.

---

## 8. Open business decisions

1. Backfill: option A (force on next login) — confirm.
2. Apply the gate to customers only, or all roles?
3. Who owns the legal copy and the effective date?
4. Record anonymous cookie consent, or only authenticated users?
5. Retention period for consent records and IP addresses.
6. Should the cookie banner block interaction until a choice is made, or remain a non-blocking pill? (Current pill is non-blocking; GDPR guidance generally accepts non-blocking banners if no non-essential cookies load first.)

---

## 9. Definition of done

- `php artisan test` exits 0, including the new consent suite.
- `./vendor/bin/pint --test` and `phpstan` exit 0.
- `npm run lint` exits 0 in `barbing-saloon-web`.
- `GET /api/v1/admin/consents` returns correct accepted/pending/stale counts.
- A new registration produces a consent row and an audit entry.
- An existing user is forced to accept on next login and can continue afterwards.
- No non-essential script runs before cookie consent.

---

## 10. Implementation status (2026-11-26)

### Delivered

| Layer | Item |
|---|---|
| Database | `user_consents` ledger (nullable `user_id`, guest `consent_id`, `type`, `version`, `accepted_at`, `ip_address`, `user_agent`, `source`, `metadata`, unique per user/type/version). Migration applied. |
| Backend | `UserConsent` model, `ConsentService`, `RecordConsent` action, `GetConsentReport` action. |
| Registration | `terms_accepted` required; stale `terms_version` rejected; `terms` + `privacy` evidence rows written inside the registration transaction; audit entry `consent.terms_accepted`. |
| Gate | `EnsureConsentAccepted` appended to the API stack; returns `403 CONSENT_REQUIRED` with context. Enforced for `customer` only; auth, health, consent, client-error and webhook routes exempt. |
| API | `GET /api/v1/account/consents/status`, `POST /api/v1/account/consents/accept`, `POST /api/v1/consents/cookies` (guests via UUID). |
| Admin API | `GET /api/v1/admin/consents`, `GET /api/v1/admin/consents/export` (CSV, formula-injection safe). |
| Customer dossier | Consent status + acceptance history returned by `GetCustomerProfile` and shown in the drawer. |
| SPA | Registration checkbox; blocking `/consent` screen; route guard redirects pending customers (legal pages remain reachable); API `CONSENT_REQUIRED` opens the gate; cookie banner posts decisions to the API. |
| Admin UI | `/admin/consents` report with counts, filters, search, pagination and CSV download; nav entry "Consent Log". |
| Terms page | Displays the real `terms_version` instead of a fabricated "last updated" date. |
| Tests | 15 Pest tests (`tests/Feature/ConsentTest.php`). Full suites: 449 backend, 494 frontend, production build passing. |

### Still requires a business decision

1. **Existing 8 customers** have no consent rows, so they will be forced to accept on their next sign-in. No timestamps are fabricated. To soften the rollout, set `CONSENT_ENFORCE=false` in `.env` until the legal copy is approved.
2. **Scope**: the gate currently applies to `customer` only (`config/consent.php`). Admins, super admins and barbers are exempt.
3. **Legal copy**: the default Terms page still contains the "United States" jurisdiction sentence and has no acceptance clause. Replace with counsel-approved copy and bump `terms_version`; the gate will then force re-acceptance automatically.
4. **Retention**: no pruning job exists for `user_consents`. Decide a retention period (e.g. 7 years) and add a scheduled prune if required.
5. **Cookie banner**: still non-blocking. No analytics/marketing scripts are present, so nothing loads before consent today.
6. **Social sign-up**: Google sign-ups are created without a consent row and are stopped by the gate on their first protected request, then record acceptance via `/consent`.
