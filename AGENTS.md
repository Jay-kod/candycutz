# Candycutz — Autonomous Agents & Engineering Guidelines

## 1. System Identity & Mission
Candycutz is a production-grade commercial grooming platform operating in Keffi, Nasarawa State, Nigeria.
All software engineers and autonomous agents working on this codebase must adhere strictly to these principles:
- **Never behave like a code generator**: Behave as a seasoned principal software architect and multi-disciplinary engineering team.
- **Inspect → Understand → Plan → Validate → Architect → Scaffold → Implement → Test → Audit → Launch**: Never write code blindly.
- **Never destroy or blindly rewrite the existing website**: The Vue 3 web client is fully functional and must be preserved and enhanced.
- **Unified Platform Architecture**: The website, customer mobile views, barber mobile views, and CMS are clients of one single authoritative Laravel 11 backend.

---

## 2. Non-Negotiable Architectural Decisions

```text
ONE PLATFORM
ONE GITHUB REPOSITORY
ONE LARAVEL BACKEND
ONE DATABASE
ONE CMS
ONE VPS
ONE EXPO MOBILE APP
ONE AUTHENTICATION AUTHORITY
```

1. **One Expo Mobile Application**: There is only ONE mobile app (`apps/mobile` / `candycutz-mobile-app`). It provides role-based navigation:
   - `role === 'customer'` -> Customer navigation (Home, Explore, Book, Appointments, Profile).
   - `role === 'barber'` -> Barber navigation (Dashboard, Appointments, Schedule, Walk-in, Profile).
   - Never build or maintain two separate Expo applications for customer and barber.
   - Never wrap the website inside a WebView as the primary application.
2. **One Authentication Authority**: Laravel Sanctum is the sole authority for identity, roles, permissions, passwords, and tokens.
   - Frontends never determine authorization.
   - The user account is unified in the `users` table. The same account can access web, mobile, and CMS where authorized.
3. **One Database**: All clients interact with the same MySQL database through versioned API endpoints (`/api/v1/`). Never introduce a second database.
4. **One VPS Topology**: The production environment runs on a single VPS hosting Nginx (Reverse Proxy & Static Asset server), Laravel (PHP-FPM), MySQL, Redis, and a Queue Worker under Docker Compose.

---

## 3. Agent Operational Rules

### 3.1 Code Quality Standards
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

### 3.2 Security & Data Integrity
- **Zero Raw Secrets**: Never commit Stripe secret keys, Brevo API tokens, or database credentials. All credentials must be read from environment variables.
- **Authoritative Server Verification**: Never trust client assertions for pricing, availability, or payment status. All state changes require server validation and Stripe webhook confirmation.
- **Soft Deactivation**: Customers are never permanently purged through standard flows; retain records with `is_active = 0`, `status = 'deactivated'`, and `deactivated_at = NOW()` to protect financial audits.
- **Strict Audit Logging**: Privileged Super Admin actions must generate detailed audit log entries (`user_id`, `action`, `target_type`, `target_id`, `old_values`, `new_values`, `reason`, `ip_address`, `user_agent`).
- **Non-Destructive Token Management**: Never call `$user->tokens()->delete()` during standard login. Always issue client-scoped personal access tokens (`web_token`, `mobile_token`) so that logging into mobile does not kill a web session, and vice versa.

### 3.3 Routing Rules
- **Canonical API v1**: All mobile clients and external consumers communicate exclusively through `/api/v1/*` routes.
- **Web Compatibility**: Existing Vue 3 routes (`/api/public/*`, `/api/customer/*`, `/api/barber/*`, `/api/admin/*`) must remain supported through clean routing aliases or controllers to guarantee zero regression on the existing web client.
- **Context-Aware 401 Handling**: The web client Axios interceptor must redirect based on current route path (`/admin/login`, `/barber/login`, or `/customer/login`). Never hard-redirect admins or barbers to `/customer/login`.
- **CORS Compliance**: Configure CORS strictly via `config/cors.php` with dynamic origin resolution and credential support. Never emit raw wildcard `Access-Control-Allow-Origin: *` headers from `public/index.php`.

### 3.4 Mobile Application Protocol
- Built exclusively with **Expo**, **React Native**, **TypeScript**, **Expo Router**, **TanStack Query**, **Zustand**, and **NativeWind / Design Tokens**.
- Use `expo-secure-store` for hardware-backed token storage. Never store tokens in plain `AsyncStorage`.
- Handle poor network connectivity, offline resilience, and retries with idempotency tokens.
- Never hard-code host IP addresses in mobile code. Use environment variables resolved via `Constants.expoConfig`.

---

## 4. Forbidden Practices

1. **DO NOT** create a second backend or microservice.
2. **DO NOT** create a second database.
3. **DO NOT** create two separate Expo mobile projects.
4. **DO NOT** wipe all user tokens (`$user->tokens()->delete()`) on login.
5. **DO NOT** execute un-locked booking slot reservations.
6. **DO NOT** emit raw PHP `header('Access-Control-Allow-Origin: *')` alongside session/credential configs.
7. **DO NOT** wrap the web application in a mobile WebView.
8. **DO NOT** leave scratch/ad-hoc scripts in application roots.
9. **DO NOT** commit un-hashed passwords or live API secrets.
10. **DO NOT** put AI-related references, model names, prompts, artificial attribution, or conversational commentary into code, UI, or commit messages.

---

## 5. Repository Directory Map

```text
candycutz/
├── apps/
│   ├── web/                           # Preserved Vue 3 Web Client (barbing-saloon-web)
│   └── mobile/                        # Single Unified Expo Mobile App (Customer + Barber roles)
├── backend/
│   └── laravel/                       # Authoritative Laravel 11 Backend (barbing-saloon-api)
├── infrastructure/
│   ├── docker/                        # Nginx, PHP-FPM, MySQL, Redis compose definitions
│   └── scripts/                       # Backup, deployment, and operational scripts
├── docs/                              # Living technical specifications
│   ├── architecture/                  # System architecture, topology, and data flow
│   ├── authentication/                # Authentication spec, social auth, session rules
│   ├── routing/                       # Web, mobile, and API routing contracts
│   ├── api/                           # API v1 contract & error codes
│   ├── deployment/                    # VPS setup, Docker, backup & restore
│   ├── debugging/                     # Module-by-module troubleshooting runbooks
│   └── recovery/                      # Forensic audits and recovery plans
├── AGENTS.md                          # Engineering rules & agent constraints
├── PROJECT_STATE.md                   # Real-time progress, metrics, and active phase
├── BUILD_PLAN.md                      # Milestone checklist and dependency matrix
├── DECISIONS.md                       # Architectural Decision Records (ADR)
├── docker-compose.yml                 # Unified production orchestration
└── .env.example                       # Documented environment blueprint
```
