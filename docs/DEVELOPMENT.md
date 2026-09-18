# CandyCutz Development & Contributor Guide

Welcome to the CandyCutz development guide. This document provides setup instructions, local workflow patterns, and the mandatory Definition of Done quality gates enforced on this repository.

---

## 1. Architectural Topology

Per `ARCHITECTURE.md`, the platform comprises three discrete projects in a single repository:

```
candycutz/
├── barbing-saloon-api/      # Canonical Laravel 11 Monolith API (/api/v1/*)
├── barbing-saloon-web/      # Vue 3 + Vite Single Page Application
├── candycutz-mobile-app/    # Expo / React Native Customer Mobile App
└── docs/                    # Authoritative platform documentation & OpenAPI specs
```

---

## 2. Prerequisites

Ensure your workstation has the following runtimes installed:

* **PHP:** >= 8.2 (with `pdo_mysql`, `pdo_sqlite`, `mbstring`, `xml`, `bcmath`, `intl`)
* **Composer:** >= 2.6
* **Node.js:** >= 20.0.0 (LTS)
* **npm:** >= 10.0.0
* **MySQL:** 8.0 (optional for local testing; SQLite is supported out-of-the-box)

---

## 3. Quickstart Setup

### 3.1 Backend API (`barbing-saloon-api`)

```bash
cd barbing-saloon-api

# 1. Install PHP dependencies
composer install

# 2. Configure environment
cp .env.example .env
php artisan key:generate

# 3. Configure database (SQLite for local testing)
touch database/database.sqlite
# Ensure .env has: DB_CONNECTION=sqlite and DB_DATABASE=database/database.sqlite

# 4. Run migrations and baseline seeders
php artisan migrate --seed

# 5. Start development server
php artisan serve --port=8000
```
The API is served at `http://localhost:8000`. Health endpoint: `http://localhost:8000/api/v1/health`.

### 3.2 Frontend Web (`barbing-saloon-web`)

```bash
cd barbing-saloon-web

# 1. Install Node dependencies
npm install

# 2. Configure environment
cp .env.example .env
# Set VITE_API_BASE_URL=http://localhost:8000/api/v1

# 3. Start Vite dev server
npm run dev
```
The web app is served at `http://localhost:5173`.

### 3.3 Mobile App (`candycutz-mobile-app`)

```bash
cd candycutz-mobile-app

# 1. Install Node dependencies
npm install

# 2. Configure environment
cp .env.example .env
# Set EXPO_PUBLIC_API_URL=http://<YOUR_LOCAL_IP>:8000/api/v1

# 3. Start Expo development server
npx expo start
```

---

## 4. Quality Gates & Definition of Done

Before submitting or merging any pull request, **all** of the following checks must exit `0`. These gates are enforced automatically by `.github/workflows/ci.yml`.

### 4.1 Backend Verification
Run inside `barbing-saloon-api/`:

```bash
# 1. Automated Test Suite (Pest)
php artisan test

# 2. Code Style & Linting (Pint)
./vendor/bin/pint --test

# 3. Static Type Analysis (Larastan Level 6)
./vendor/bin/phpstan analyse --memory-limit=2G
```

### 4.2 Web Frontend Verification
Run inside `barbing-saloon-web/`:

```bash
# 1. ESLint & Vue TypeScript Type Check
npm run lint

# 2. Unit & Component Tests (Vitest)
npm run test:unit -- --run

# 3. Production Bundle Build
npm run build
```

### 4.3 Mobile App Verification
Run inside `candycutz-mobile-app/`:

```bash
# 1. TypeScript Static Type Check
npx tsc --noEmit

# 2. Contract & Unit Tests
npm test
```

---

## 5. Contract Synchronization & API Generation

CandyCutz treats the OpenAPI specification as the contract between backend and clients.

### Regenerating the API Specification
When modifying routes, controllers, or request schemas in `barbing-saloon-api`:

```bash
cd barbing-saloon-api
# Regenerate OpenAPI spec (writes to public/openapi.json and ../docs/openapi.json)
php artisan openapi:generate
```

### Updating Mobile TypeScript Interfaces
To sync client types with the latest OpenAPI spec:

```bash
cd candycutz-mobile-app
# Generates src/api/types.ts from docs/openapi.json
npm run generate-types
```

---

## 6. Disaster Recovery & Database Operations

```bash
cd barbing-saloon-api

# Create a full snapshot
php artisan db:backup

# Restore from latest snapshot
php artisan db:restore

# Force restore specific snapshot
php artisan db:restore storage/app/backups/<filename>.sql --force
```
