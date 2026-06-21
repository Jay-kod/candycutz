# CandyCutz Production Roadmap

This file breaks the work into small phases so you can follow it step by step.
Do not move to the next phase until the current one is complete.

## Working Rules

- Keep backend and frontend work in sync with the phase order below.
- Finish the foundation before building features.
- Keep writes in services, validation in form requests, and API output in resources or response wrappers.
- Keep uploads secure and server-controlled.
- Keep API errors generic and avoid leaking stack traces.
- Keep the CandyCutz name, palette, and tone consistent everywhere.

## Phase 0 - Project Setup

### Step 0.1 - Backend scaffold

- Create the Laravel 11 project.
- Create the app/Modules folders for Auth, Public, Customer, Barber, Admin, and SuperAdmin.
- Create the app/Core folders for middleware, response, traits, helpers, and enums.
- Install Sanctum and spatie/laravel-permission.
- Configure .env for CandyCutz, database, Sanctum, filesystem, session, and mail.
- Run Sanctum install and storage link.

### Step 0.2 - Frontend scaffold

- Create the Vue 3 + Vite project.
- Create the src/modules folders for auth, public, customer, barber, admin, and superadmin.
- Create the src/core folders for components, layouts, composables, guards, api, and utils.
- Install router, pinia, axios, validation, tiptap, drag-and-drop, toast, icons, and PWA support.
- Configure the frontend .env file for the API base URL.

### Step 0.3 - Design system

- Configure Tailwind colors and fonts.
- Add the CSS variable theme in src/assets/css/main.css.
- Import Inter and Playfair Display in index.html.

### Phase 0 done when

- Both projects exist and install dependencies cleanly.
- The CandyCutz design tokens are in place.

## Phase 1 - Database Foundation

### Step 1.1 - Create enums

- Create UserRole.
- Create AppointmentStatus.
- Create BlogStatus.
- Create GalleryCategory.

### Step 1.2 - Create migrations

- Create the tables in the exact order from the original spec.
- Add foreign keys, soft deletes, timestamps, and required indexes.

### Step 1.3 - Create models

- Create all models in app/Models.
- Add fillable arrays, casts, soft deletes, and relationships.

### Step 1.4 - Create seeders

- Add realistic seeders for all tables.
- Make sure DatabaseSeeder runs them in dependency order.
- ServiceSeeder is complete with 10 realistic services across all required categories.
- BarberSeeder and WorkingHourSeeder are complete with realistic barber profiles and Mon-Sat hours.
- GallerySeeder, TestimonialSeeder, BlogSeeder, SettingSeeder, and AppointmentSeeder are complete with linked domain data.

### Phase 1 done when

- The database migrates in order.
- The seed data is realistic and linked correctly.
- The models reflect the schema and relationships.

## Phase 2 - Core Security

### Step 2.1 - Middleware

- Create CheckRole.
- Create SanitizeInput.
- Create ForceHttps.
- Create SecurityHeaders.
- Create LogApiRequest.
- Register all middleware aliases and groups.
- Security middleware and request logging are now implemented.

### Step 2.2 - Traits and response wrapper

- Create HasSecureUploads.
- Create HasAuditLog.
- Create HasApiResponse.
- Create ApiResponse.
- Shared response and secure upload helpers are now implemented.

### Step 2.3 - App service providers

- Register the module service provider.
- Force HTTPS in production.
- Keep mass assignment protected.
- AppServiceProvider and ModuleServiceProvider are now in place.
- API exception handling is now in place through app/Exceptions/Handler.php.

### Phase 2 done when

- Security is centralized.
- API responses follow one standard shape.
- Audit logs can be written for mutating requests.

## Phase 3 - Auth First

### Step 3.1 - Backend auth

- Build register, login, logout, and me endpoints.
- Force customer role on registration.
- Return one auth response shape.
- Backend auth routes, request validation, service, controller, and resource are now implemented.

### Step 3.2 - Frontend auth

- Build auth API methods.
- Build the auth store and composable.
- Build login and register pages with inline validation.
- Add route guards and redirect logic.
- Frontend auth API, store, route guards, and pages are now implemented.

### Phase 3 done when

- A new customer can register and log in.
- Auth pages redirect correctly.
- Invalid credentials remain generic.

## Phase 4 - Public Site

### Step 4.1 - Backend public routes

- Build the public API endpoints.
- Implement available slots logic.
- Add contact form handling.
- Public API endpoints, slot generation, and contact handling are now implemented.

### Step 4.2 - Frontend public pages

- Build the public layout.
- Build the home page sections.
- Build services, barbers, gallery, blog, testimonials, and contact pages.
- Public layout exists and the home page now hydrates from the public API.
- Public routes and the full public page set are now implemented.

### Phase 4 done when

- Public content renders from the API.
- Slot generation works with business rules.
- The site looks like CandyCutz.
- Public browsing pages are routed and reachable.

## Phase 5 - Customer Area

### Step 5.1 - Backend customer routes

- Build dashboard, booking, history, profile, avatar, and reviews routes.
- Add booking ownership policy.
- Add booking and profile services.
- Customer dashboard, booking, profile, and reviews routes are now implemented.
- Booking ownership is now enforced with an appointment policy.

### Step 5.2 - Frontend customer pages

- Build the customer layout.
- Build the dashboard.
- Build the booking stepper.
- Build booking history, review modal, and profile pages.
- Customer layout, dashboard, bookings, profile, and reviews pages are now implemented.

### Phase 5 done when

- Customers can manage only their own bookings.
- Booking flow works end to end.

## Phase 6 - Barber Area

### Step 6.1 - Backend barber routes

- Build dashboard, schedule, complete, no-show, and profile routes.
- Add barber ownership policy.
- Add schedule service.
- Barber dashboard, schedule, appointment status, and profile routes are now implemented.

### Step 6.2 - Frontend barber pages

- Build the barber layout.
- Build dashboard, schedule, action card, and profile pages.
- Barber layout, dashboard, schedule, appointments, and profile pages are now implemented.

### Phase 6 done when

- Barbers can only see and update their own schedule.
- Status changes are logged.

## Phase 7 - Admin Area

### Step 7.1 - Backend admin routes

- Build appointments, services, categories, gallery, testimonials, blog, working hours, holidays, and reports routes.
- Use secure uploads for all media.
- Admin management routes, secure uploads, and reports are now implemented.

### Step 7.2 - Frontend admin pages

- Build the admin layout.
- Build dashboard, appointments, services, gallery, testimonials, blog, working hours, and reports pages.
- Admin layout and all admin management pages are now implemented.

### Phase 7 done when

- Admin can manage all operational content.
- Reports and filters work correctly.

## Phase 8 - Super Admin Area

### Step 8.1 - Backend super admin routes

- Build users, settings, and audit log routes.
- Add user creation, activation toggling, and settings group update services.
- Super admin user management, settings, and audit log endpoints are now implemented.

### Step 8.2 - Frontend super admin pages

- Extend the admin layout.
- Build users, settings, and audit log pages.
- Super admin layout and all management pages are now implemented.

### Phase 8 done when

- Super admin can manage staff and settings.
- Audit logs stay read-only.

## Phase 9 - PWA

### Step 9.1 - PWA setup

- Install the Vite PWA plugin.
- Configure the manifest.
- Add the offline fallback page.
- Add backup manifest and icons.
- Vite PWA plugin is installed and configured with auto-update service worker registration.
- Manifest is configured with CandyCutz branding, icons, and offline fallback.
- Offline fallback page shows branded message when user is offline.
- Four SVG icons (192x192, 512x512, and maskable variants) are in place for app installation.

### Phase 9 done when

- The app installs as a PWA.
- Offline fallback works.

## Phase 10 - Email And Jobs

### Step 10.1 - Mailables and templates

- Create booking, reminder, cancellation, thank-you, and admin notification mails.
- Create matching Blade templates.
- Five mail classes created: BookingConfirmation, BookingReminder, BookingCancellation, BookingThankYou, AdminNotification.
- Five Blade templates created in resources/views/mail/ with CandyCutz branding.

### Step 10.2 - Jobs

- Create the jobs for booking confirmation, reminders, thank-you emails, and admin notifications.
- Five job classes created: SendBookingConfirmation, SendBookingReminder, SendBookingCancellation, SendBookingThankYou, SendAdminNotification.
- Jobs are dispatched at key booking lifecycle events: creation, cancellation, completion, and no-show.
- Email jobs dispatched from CustomerService (confirmation, cancellation) and BarberService (thank you, completion notifications).

### Phase 10 done when

- Email flows are branded and connected to the booking lifecycle.

## Phase 11 - Final Polish

### Step 11.1 - Base UI components

- Build base button, input, modal, table, badge, card, and skeleton components.
- Seven base components created in src/core/components/: BaseButton, BaseInput, BaseModal, BaseTable, BaseBadge, BaseCard, BaseSkeleton.
- All components use CandyCutz color scheme (#0D0D0D dark, #C9A84C gold) and Tailwind CSS.

### Step 11.2 - Error handling and cleanup

- Centralize axios interceptors.
- Centralize backend exception handling.
- Remove console logs, dd, and dump calls.
- Axios interceptors enhanced in src/core/api/axios.js with comprehensive error handling for 401, 403, 422, 404, 500, timeouts, and network errors.
- Backend exception handler enhanced in app/Exceptions/Handler.php with security (no stack traces in production), QueryException handling, and proper HTTP status codes.
- All debug statements (console.log, dd, dump) verified removed from application code.

### Step 11.3 - Final verification

- Confirm all endpoints return the standard response format.
- Confirm forms show inline validation.
- Confirm loading states and empty states exist.
- Confirm role redirects work.
- Confirm no stack traces or SQL errors reach the client.
- All endpoints use ApiResponse helper for consistent JSON response format.
- Form validation handled by vee-validate with inline error display in BaseInput component.
- Base components include loading states (BaseSkeleton) and empty states (BaseTable empty message).
- Role-based route guards implemented in src/core/guards/routeGuards.js.
- Production environment uses generalized error messages; no sensitive error details exposed.

### Phase 11 done when

- The app is ready for handoff.
- Core flows work end to end.

## Build Order

1. Finish Phase 0.
2. Finish Phase 1.
3. Finish Phase 2.
4. Finish Phase 3.
5. Finish Phase 4.
6. Finish Phase 5.
7. Finish Phase 6.
8. Finish Phase 7.
9. Finish Phase 8.
10. Finish Phase 9.
11. Finish Phase 10.
12. Finish Phase 11.

## Definition Of Done

- CandyCutz is installable and secure.
- The backend and frontend both run from their project roots.
- Auth, public browsing, booking, management, and reporting all work.
- The code structure matches the module, service, request, and response approach from the original plan.