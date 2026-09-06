# Candycutz — Existing System Audit

**Audit Date**: September 2026  
**Auditor**: Principal Systems Architect  
**Repository**: `c:\xampp\htdocs\1\candycutz`  
**Status**: Comprehensive Baseline Established  

---

## 1. Executive Summary

Candycutz is an established grooming and barbing business platform with a physical operational hub in **Angwan Kare, BCG, Keffi, Nasarawa State, Nigeria** (Google Maps reference: [Candycutz Location](https://maps.app.goo.gl/RtpPCeBRobajKmwS7)).

The existing repository contains a fully built, responsive Vue 3 single-page web application (`barbing-saloon-web`), an installed Laravel 11.54 backend environment (`barbing-saloon-api`), and a legacy procedural PHP routing layer embedded in `barbing-saloon-api/public/index.php`.

The strategic directive is **preservation and convergence**:
1. Preserve the existing Vue 3 website design, interactive features, animations, and user workflows.
2. Unify the backend into an authoritative Laravel 11 API Gateway.
3. Architect and scaffold dedicated native iOS and Android applications for Customers and Barbers using Expo / React Native.
4. Establish a CMS-controlled "God Mode" and Theme Studio for business operations.

---

## 2. Technology Stack & Framework Inventory

### 2.1 Backend (`barbing-saloon-api`)
| Component | Detected Specification | Evaluation |
|---|---|---|
| **PHP Runtime** | PHP 8.2+ (XAMPP environment at `C:\xampp\php\php.exe`) | Modern, strictly typed, performant |
| **Framework** | Laravel Framework 11.54.0 (`laravel/framework: ^11.0`) | Modern LTS architecture with slim bootstrap |
| **Authentication** | Laravel Sanctum 4.0 (`laravel/sanctum: ^4.0`) + `firebase/php-jwt: ^7.1` | Sanctum handles session & API token auth |
| **Authorization** | Spatie Permissions (`spatie/laravel-permission: ^6.0`) | Granular RBAC capabilities |
| **Database** | MySQL 8.0 / InnoDB (`candycutz_db`), UTF8mb4 Unicode | Relational, ACID-compliant, foreign-key enforced |
| **Serving Mechanism** | PHP Built-in Server on `http://localhost:8000` (`start.bat`) | Development setup |

### 2.2 Frontend Web (`barbing-saloon-web`)
| Component | Detected Specification | Evaluation |
|---|---|---|
| **Framework** | Vue 3.5.0 (`Composition API`, `<script setup>`) | State of the art, reactive, performant |
| **Build Tool** | Vite 8.0.16 with Terser minification & chunk splitting | Fast HMR, modern ES2020 target |
| **Styling** | TailwindCSS 3.4.0 + CSS Custom Properties Design Tokens | Consistent color palette, dark/light theme |
| **State Management** | Pinia 2.1.0 | Lightweight, modular stores |
| **Routing** | Vue Router 4.4.0 | Role-guarded client-side routes |
| **HTTP Client** | Axios 1.7.0 with request/response interceptors | Centralized auth injection and error toast handling |
| **PWA & Offline** | `vite-plugin-pwa` 1.3.0 with service worker and offline fallback | Installable web app |
| **Motion & UX** | Lenis smooth scroll, CSS `@supports (animation-timeline: view())` scroll reveals | Fluid, luxury feel |
| **Rich Text** | Tiptap 2.10.0 editor for blogs | Extensible content authoring |

---

## 3. Architecture & The Dual-Backend Discrepancy

### 3.1 The Finding
The repository exhibits a bifurcated backend structure:
1. **The Modular Laravel 11 Implementation**:
   - Located in `barbing-saloon-api/app/Modules/` with modules for `Auth`, `Landing`, `Customer`, `Barber`, `Admin`, and `SuperAdmin`.
   - Registered through `App\Providers\ModuleServiceProvider` with 97 routes mapped to modular controllers, form requests, and response envelopes (`App\Core\Http\Response\ApiResponse`).
2. **The Procedural Public Fallback**:
   - Located in `barbing-saloon-api/public/index.php` (45 KB), `api_admin.php` (81 KB), `api_barber.php` (56 KB), and `api_customer.php` (35 KB).
   - Driven by raw PDO queries, file-based token decoding, and manual array manipulation.
   - Currently executed whenever `php -S localhost:8000 -t public` runs, because `public/index.php` was never replaced with the standard Laravel 11 application kernel dispatcher.

### 3.2 Impact Analysis
- **Security & Integrity**: The procedural scripts bypass Laravel's middleware stack, CSRF protection, rate limiters, policy gates, and model events.
- **Maintainability**: New features added to Laravel controllers do not take effect if incoming requests are intercepted by legacy PHP scripts.
- **Client Reliance**: The Vue 3 website currently expects specific JSON envelope structures from these endpoints.

### 3.3 Convergence Strategy
- Transition `barbing-saloon-api/public/index.php` to boot Laravel 11's standard HTTP Kernel (`(require_once __DIR__.'/../bootstrap/app.php')->handleRequest(Request::capture())`).
- Ensure all Laravel API responses match the expected shape (`{ success: true, message: "...", data: [...] }`) via `ApiResponse` wrapper.
- Guarantee that both the Vue 3 website and the forthcoming mobile applications interact exclusively with the authoritative Laravel API.

---

## 4. Database Schema & Migration Audit

### 4.1 Existing Tables
- `users`: Basic accounts (email, password hash, role enum, avatar, phone, is_active).
- `barbers`: Profiles linked to users (bio, JSON specialties, rating, years of experience, availability status).
- `service_categories` & `services`: Catalog items with price, duration, and barber foreign key.
- `working_hours` & `holidays`: Availability definitions.
- `appointments`: Booking records with customer, barber, service, date, time, status enum.
- `gallery`: Barber and shop showcase media.
- `testimonials`: Customer reviews with rating and approval flags.
- `blog_posts`: Content marketing posts with slug and author display.
- `settings`: Key-value store for shop configurations.
- `audit_logs`: Primitive system event tracking.
- `personal_access_tokens`: Sanctum API tokens.
- `wishlists`: Customer favorites.
- `payments`: Prototype payment records with mock card references and receipt images.
- `notifications`: User notifications table.

### 4.2 Architectural Deficits to Resolve
1. **Username-First Identity**:
   - `users` table lacks a `username` column, `last_username_change_at`, `deactivated_at`, and an explicit account status lifecycle (`active`, `deactivated`, `suspended`).
2. **Home Services & Multi-Location**:
   - `appointments` table assumes all appointments occur inside a single shop.
   - Missing: `appointment_type` (`in_shop`, `home_service`), `service_zone_id`, `customer_address_id`, `travel_fee`, `coordinates`.
   - Missing geographic and organizational models: `businesses`, `branches`, `service_zones`, `addresses`.
3. **Multi-Service Appointments**:
   - `appointments` table only links to a single `service_id`. Needs `appointment_items` junction table.
4. **Stripe & Financial Integrity**:
   - Existing `payments` table contains loose mock strings. Needs dedicated `payment_transactions` ledger with Stripe PaymentIntent and webhook event references.
5. **Theme Studio & Dynamic Brand Control**:
   - Brand colors are currently hardcoded in CSS and Tailwind config. Needs `theme_settings` and `theme_versions` tables to power CMS draft/publish/rollback workflows.
6. **Push Notifications**:
   - Lacks `device_tokens` table for Expo push notification delivery to mobile clients.

---

## 5. Existing Assets, Media & Branding Audit

### 5.1 Visual Identity & Color Palette
The existing web application utilizes a luxury, grooming-focused color palette:
- **Obsidian Dark Background**: `#050505` to `#0D0D0D`
- **Surface Elevation**: `#121212` to `#1A1A1A`
- **Gold Accent (Primary Brand)**: `#FF9900` / `#C9A84C` (Champagne Gold)
- **Gold Hover / Light**: `#FFB84D` / `#D2AE68`
- **Ivory Light Background**: `#FAFAF8` / `#F7F6F2`
- **Typography**: `Inter` for body copy, `Playfair Display` for serif luxury headings.

### 5.2 Business Grounding
- **Seed Data State**: Initial seeds populated dummy US addresses (`123 Main Street, Barberville, CA 90001`).
- **Verified Business Grounding**: Google Maps location verifies Candycutz operates at:
  - **Street/Area**: Angwan Kare, BCG, Beside Angwan Kare BCG Gas Station, beside Wealths Khort Apartments
  - **City/LGA**: Keffi
  - **State**: Nasarawa State
  - **Postal Code**: 961101
  - **Country**: Nigeria
  - **Coordinates**: Latitude ~8.8471° N, Longitude ~7.8736° E
  - **Timezone**: `Africa/Lagos` (WAT, UTC+1)

---

## 6. Functional Retention Matrix

| Domain | What to Preserve | What to Refactor | What to Implement New |
|---|---|---|---|
| **Public Web** | Hero section, smooth animations, layout, typography, blog reader, gallery viewer. | Settings hydration, available slots query to use Laravel controller. | Home-service zone selector, multi-branch selector. |
| **Auth** | Login & register UI forms, JWT/Sanctum store in Pinia. | Social auth verification to use unified backend service. | Username onboarding flow, 90-day change enforcement, soft deactivation. |
| **Customer Web** | Dashboard layout, appointment listing, reviews modal. | Multi-service checkout flow, address book. | Home-service booking stepper, Stripe Checkout integration. |
| **Barber Web** | Schedule viewer, appointment status updater. | Availability calculation to account for home-service travel. | Travel directions link, location privacy reveal window. |
| **Admin / CMS** | CRUD tables for services, barbers, gallery, testimonials. | Switch from legacy PHP files to Laravel 11 modular controllers. | Theme Studio (Draft/Preview/Publish), Service Zone Manager, Full Audit Trail. |
| **Mobile** | *None (new build)* | *None* | Complete Expo Customer App & Expo Barber App. |

---

## 7. Conclusion & Next Action

The existing system provides a rock-solid, visually stunning web foundation that will be preserved without regression. The immediate path forward is executing the **Project Brain** creation, deploying normalized database migrations, transitioning the backend to native Laravel 11 HTTP dispatch, and scaffolding the Expo mobile clients.
