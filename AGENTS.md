# Candycutz — Autonomous Agents & Engineering Guidelines

## 1. System Identity & Mission
Candycutz is a production-grade commercial grooming platform operating in Keffi, Nasarawa State, Nigeria.
All software engineers and autonomous agents working on this codebase must adhere strictly to these principles:
- **Never behave like a code generator**: Behave as a seasoned principal software architect and multi-disciplinary engineering team.
- **Inspect → Understand → Plan → Validate → Architect → Scaffold → Implement → Test → Audit → Launch**: Never write code blindly.
- **Never destroy or blindly rewrite the existing website**: The Vue 3 web client is fully functional and must be preserved and enhanced.
- **Unified Platform Architecture**: The website, customer mobile application, barber mobile application, and CMS are clients of one single authoritative Laravel 11 backend.

---

## 2. Agent Operational Rules

### 2.1 Code Quality Standards
- **Meaningful Names**: Descriptive variables and functions without abbreviations (e.g., `calculateHomeServiceTravelFee` instead of `calcFee`).
- **Small, Focused Functions**: Adhere to Single Responsibility Principle (SRP).
- **Strong Typing**: Strict types in PHP (`declare(strict_types=1);`), complete TypeScript interfaces in mobile applications.
- **No Pseudo Code or Placeholders**: Every file committed must be complete, executable, and syntactically valid.
- **Comments Explain 'Why', Not 'What'**: Never state obvious behavior. Document edge cases, race conditions, business constraints, and non-obvious design decisions.
- **Clean Architecture**:
  - Request validation in Form Requests.
  - Complex domain logic in Service classes.
  - Data transfer envelopes via API Resources or standard `ApiResponse`.
  - Concurrency safety with database transactions and row-level locks (`SELECT FOR UPDATE`).

### 2.2 Security & Data Integrity
- **Zero Raw Secrets**: Never commit Stripe secret keys, Brevo API tokens, or database credentials. All credentials must be read from environment variables.
- **Authoritative Server Verification**: Never trust client assertions for pricing, availability, or payment status. All state changes require server validation and Stripe webhook confirmation.
- **Soft Deactivation**: Customers are never permanently purged through standard flows; retain records with `is_active = 0`, `status = 'deactivated'`, and `deactivated_at = NOW()` to protect financial audits.
- **Strict Audit Logging**: Privileged Super Admin actions must generate detailed audit log entries (`user_id`, `action`, `target_type`, `target_id`, `old_values`, `new_values`, `reason`, `ip_address`, `user_agent`).

### 2.3 Mobile App Protocol (Customer & Barber Apps)
- Built exclusively with **Expo**, **React Native**, **TypeScript**, **Expo Router**, **TanStack Query**, **Zustand**, and **NativeWind / Design Tokens**.
- Never wrap the website inside a WebView as the primary application.
- Use `expo-secure-store` for authentication tokens.
- Handle poor network connectivity, offline resilience, and retries with idempotency tokens.

---

## 3. Directory Map & Repository Conventions

```
candycutz/
├── AGENTS.md                          # Engineering rules & agent constraints
├── PROJECT_STATE.md                   # Real-time progress, metrics, and active phase
├── BUILD_PLAN.md                      # Milestone checklist and dependency matrix
├── DECISIONS.md                       # Architectural Decision Records (ADR)
├── docs/                              # Living technical specifications
│   ├── PRD.md                         # Product Requirements Document
│   ├── Architecture.md                # Unified System Architecture
│   ├── Architecture-Essentials.md     # Quick reference core patterns
│   ├── Existing-System-Audit.md       # Audit baseline
│   ├── Database.md                    # ERD, entities, and constraints
│   ├── API.md                         # API v1 contract specifications
│   ├── Authentication.md              # Auth flows, social JWTs & username rules
│   ├── Booking-Engine.md              # Availability & transactional slot logic
│   ├── Home-Services.md               # Geolocation, travel fees & zone models
│   ├── CMS.md                         # Operational control & God Mode
│   ├── Payments.md                    # Stripe integration & idempotency
│   ├── Notifications.md               # Brevo transactional email & push queues
│   ├── Mobile.md                      # Expo customer & barber applications
│   ├── Design-System.md               # Theme tokens (Light/Dark/Studio)
│   ├── Security.md                    # Threat model, RBAC & auditing
│   ├── Testing.md                     # Test strategy & test cases
│   ├── Deployment.md                  # Staging/Production & Docker runbooks
│   └── Launch.md                      # App stores, checklist & go-live plan
├── barbing-saloon-api/                # Authoritative Laravel 11 Backend
│   ├── app/                           # Domain services, models, controllers
│   ├── database/                      # Migrations, factories, seeders
│   └── routes/                        # Versioned API routes
├── barbing-saloon-web/                # Preserved Vue 3 Responsive Web Client
├── candycutz-customer-app/            # Expo React Native Customer Mobile App
└── candycutz-barber-app/              # Expo React Native Barber Mobile App
```
