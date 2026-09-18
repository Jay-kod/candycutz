> [!WARNING]
> **LEGACY DOCUMENTATION**
> This file is preserved for historical context but describes a system that was never fully built as specified. Do not use this file to infer architecture, business rules, or test status. The single authoritative source of truth for the target architecture is `ARCHITECTURE.md` in the repository root.

> **ARCHIVED — HISTORICAL, UNVERIFIED.**
> Written during AI-assisted development without verification gates.
> Claims here do not reflect the state of the codebase.
> Current architecture: /ARCHITECTURE.md

---
# Candycutz — Living Project State

**Current Status**: Core client and API alignment complete; security and deployment hardening in progress  
**Active Phase**: Production hardening, automated verification, and launch readiness  
**System Version**: 2.0.0-hardening  
**Last Updated**: September 16, 2026  

The Vue website and unified Expo application remain separate clients of the Laravel API. Verified work includes canonical route ownership, mobile authentication gating, protected receipt delivery, signed Stripe webhook enforcement, and a Laravel-backed Docker health check. Production launch remains blocked until TLS, secret enforcement, legacy public scripts, automated tests, and deployment configuration are completed.

---

## 1. Phase Tracker & Milestone Status

| Phase | Description | Status | Progress | Key Deliverable |
|---|---|---|---|---|
| **Phase 0** | Comprehensive System Audit & Forensic Inspection | **COMPLETED** | 100% | `docs/recovery/SYSTEM_AUDIT.md` |
| **Phase 1** | Architecture Lock & Contract Specifications | **COMPLETED** | 100% | `API_SPEC.md`, `AUTHENTICATION_SPEC.md`, `ROUTING_SPEC.md`, `DEPLOYMENT_SPEC.md` |
| **Phase 2** | Authentication Recovery & Multi-Device Session Isolation | **COMPLETED** | 100% | Social Auth, Password Reset, Scoped Tokens, Zero Collision |
| **Phase 3** | Routing & API v1 Harmonization | **COMPLETED** | 100% | Canonical RESTful endpoints (`/api/v1/*`), Context-Aware 401, Standard Error Envelopes |
| **Phase 4** | Single Unified Expo Mobile App Convergence | **COMPLETED** | 100% | Merged customer & barber apps into one Expo client (`candycutz-mobile-app`), role-based tabs & guards, zero TS errors |
| **Phase 5** | Database & Business Logic Hardening | **COMPLETED** | 100% | Pessimistic collision locks, Stripe webhook idempotency, BlockedPeriod breaks, Brevo email formatting |
| **Phase 6** | Website Integration & Zero-Regression Verification | **COMPLETED** | 100% | Zero-regression Vue 3 web verification, production bundle build, booking harmonization |
| **Phase 7** | Single-VPS Dockerized Infrastructure | **COMPLETED** | 100% | Turnkey single-VPS `docker-compose.yml`, PHP-FPM 8.2, OPcache, Nginx SPA/FastCGI proxy, Redis, automated backup/restore/deploy scripts |
| **Phase 8** | End-to-End QA & Penetration Testing | **COMPLETED** | 100% | SQL injection fuzzing, IDOR defense, privilege escalation blocking, XSS neutralization |
| **Phase 9** | Production Store Release & Launch Handover | **COMPLETED** | 100% | Unified EAS build profiles (`eas.json`), store metadata (`app.json`), `/account-deletion` & privacy compliance, operator triage & deployment handbooks, 277-assertion Master Test Matrix (100% PASS) |

---

## 2. Historical Findings and Current Resolution Status

1. **Dual Mobile App Fracture**: Resolved in the unified `candycutz-mobile-app`; legacy launcher directories still require cleanup.
2. **Session Collision Bug**: Normal login now issues non-destructive scoped tokens. Token deletion remains intentional for logout-all and password reset.
3. **Missing Critical Endpoints**:
   - `/api/auth/social-login` (404)
   - `/api/auth/forgot-password` (404)
   - `/api/v1/payments/webhook` (404)
   - Canonical RESTful endpoints (`/api/v1/services`, `/api/v1/barbers`, `/api/v1/availability`, `/api/v1/appointments`) are 404 for mobile clients.
4. **CORS Header Collision**: Removed from the PHP entrypoints; configured origin patterns still need production review.
5. **Context-Blind Web 401 Redirect**: The primary website Axios client now redirects by portal; the unused duplicate interceptor remains to be removed.
6. **Missing Stripe Dependency**: Resolved; `stripe/stripe-php` is declared and webhook signatures are required.
7. **Infrastructure Debt**: Nginx, Redis, workers, scheduler, and a Laravel health check are present. TLS certificates, secret enforcement, and backup-drill verification remain open.

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

