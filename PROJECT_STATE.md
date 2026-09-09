# Candycutz — Living Project State

**Current Status**: Phase 1 (Architecture Lock & Living Specifications) Completed  
**Active Phase**: Phase 2 — Authentication Recovery & Multi-Device Session Isolation  
**System Version**: 1.2.0-specs  
**Last Updated**: September 2026  

---

## 1. Phase Tracker & Milestone Status

| Phase | Description | Status | Progress | Key Deliverable |
|---|---|---|---|---|
| **Phase 0** | Comprehensive System Audit & Forensic Inspection | **COMPLETED** | 100% | `docs/recovery/SYSTEM_AUDIT.md` |
| **Phase 1** | Architecture Lock & Contract Specifications | **COMPLETED** | 100% | `API_SPEC.md`, `AUTHENTICATION_SPEC.md`, `ROUTING_SPEC.md`, `DEPLOYMENT_SPEC.md` |
| **Phase 2** | Authentication Recovery & Multi-Device Session Isolation | **ACTIVE PHASE** | 0% | Social Auth, Password Reset, Scoped Tokens |
| **Phase 3** | Routing & API v1 Harmonization | **QUEUED** | 0% | Canonical RESTful endpoints (`/api/v1/*`), Context-Aware 401 |
| **Phase 4** | Single Unified Expo Mobile App Convergence | **QUEUED** | 0% | Merge customer & barber apps into one Expo client |
| **Phase 5** | Database & Business Logic Hardening | **QUEUED** | 0% | Atomic slot booking, Stripe webhooks, Brevo queue |
| **Phase 6** | Website Integration Verification | **QUEUED** | 0% | Zero-regression Vue 3 web verification |
| **Phase 7** | Single-VPS Dockerized Infrastructure | **QUEUED** | 0% | Nginx reverse proxy, PHP-FPM, Redis, MySQL, backups |
| **Phase 8** | End-to-End QA & Penetration Testing | **QUEUED** | 0% | Automated multi-role test matrix |
| **Phase 9** | Production Store & VPS Launch Handover | **QUEUED** | 0% | Production deployment runbooks & handover |

---

## 2. Core Forensic Audit Findings

1. **Dual Mobile App Fracture**: Two separate mobile projects (`candycutz-customer-app` and `candycutz-barber-app`) duplicate 80% of their code. They must be merged into ONE Expo application with role-based navigation guards.
2. **Session Collision Bug**: Backend `AuthService::login` calls `$user->tokens()->delete()`, destroying existing web tokens when logging into mobile, and vice versa.
3. **Missing Critical Endpoints**:
   - `/api/auth/social-login` (404)
   - `/api/auth/forgot-password` (404)
   - `/api/v1/payments/webhook` (404)
   - Canonical RESTful endpoints (`/api/v1/services`, `/api/v1/barbers`, `/api/v1/availability`, `/api/v1/appointments`) are 404 for mobile clients.
4. **CORS Header Collision**: Raw PHP `header('Access-Control-Allow-Origin: *')` in `public/index.php` collides with `config/cors.php` and breaks credentialed browser requests.
5. **Context-Blind Web 401 Redirect**: `axios.js` redirects all 401s to `/customer/login`, abruptly ejecting Admins and Barbers from their respective portals.
6. **Missing Stripe Dependency**: `stripe/stripe-php` is missing from `composer.json`, causing fatal errors if live payment methods are invoked.
7. **Infrastructure Debt**: `docker-compose.yml` mounts raw SQL files and lacks Nginx, Redis, and a background queue worker.

---

## 3. Physical Business Grounding (Keffi Operational Flagship)

- **Brand**: CandyCutz Luxury Grooming & Barbing Saloon
- **Flagship Location**: Angwan Kare, BCG, Beside Angwan Kare BCG Gas Station, beside Wealths Khort Apartments, BCG, Keffi 961101, Nasarawa State, Nigeria
- **Coordinates**: Lat `8.8486`, Long `7.8736`
- **Operating Hours**: 08:00 - 20:00 Daily (Sunday 10:00 - 18:00)
- **Home Service Coverage**: Angwan Kare, High Court, Total, Gidan Zakara, NSUK Main & Pyanku Campuses

---

## 4. Active Runtime Environment

- **MySQL Server**: Running on `127.0.0.1:3306` (Daemon Task)
- **Laravel Backend**: Running on `0.0.0.0:8000` (Daemon Task)
- **Vite Web Client**: Running on `0.0.0.0:5174` (Daemon Task)
- **Expo Customer Packager**: Running on `10.252.94.238:8081` (Daemon Task)
- **Git Branch**: `main` (Up to date with `origin/main` at commit `6978c5e`)
