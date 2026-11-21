# CandyCutz — Dashboard Restructuring & Modernization Plan

**Document:** Implementation Roadmap & Technical Architecture  
**Scope:** `barbing-saloon-web` and relevant backend endpoints  
**Target State:** Modern commercial SaaS dashboard with role-specific experiences.

---

## 1. Executive Summary

This plan outlines the modular restructuring and visual elevation of the CandyCutz dashboard system. We are transforming the existing dashboard into a world-class, responsive, and maintainable SaaS application while preserving 100% of the underlying business logic, API integrations, database integrity, and brand identity.

---

## 2. Architectural Blueprint

### 2.1 Component Decomposition (Sub-300 Line Rule)
The legacy `DashboardSidebar.vue` (574 lines) and `DashboardLayout.vue` (549 lines) will be replaced with modular, high-cohesion components:

```
src/core/layouts/
├── DashboardLayout.vue               # Master Shell (<150 lines)
├── components/
│   ├── DashboardSidebar.vue          # Sidebar Container (<180 lines)
│   │   ├── SidebarBrand.vue          # Logo, Wordmark & Role Badge (<90 lines)
│   │   ├── SidebarNavAccordion.vue   # Accordion Grouping (<120 lines)
│   │   ├── SidebarNavItem.vue        # Navigation Link with Tooltips (<80 lines)
│   │   └── SidebarUserFooter.vue     # User Account & Quick Controls (<100 lines)
│   └── DashboardHeader.vue           # Top Navigation Bar (<120 lines)
│       ├── HeaderBreadcrumbs.vue     # Dynamic Page Trail (<60 lines)
│       ├── HeaderSearchBar.vue       # Global Quick Command Search (<90 lines)
│       └── HeaderQuickActions.vue    # Notifications, Theme & Profile (<90 lines)
```

### 2.2 Navigation Hierarchy & Accordion Grouping
To solve the 35+ flat navigation link clutter in the Admin Portal, navigation is structured into collapsible accordion groups with persistent open/close state:

#### Admin Navigation Architecture:
1. **Operations (Default Open):**
   - Dashboard (`/admin/dashboard`)
   - Appointments Calendar (`/admin/appointments`)
   - Walk-In Queue (`/admin/walk-in`)
   - Customer Management (`/admin/customers`)
   - Barber Management (`/admin/barbers`)
2. **Financials & Verification:**
   - Bank Details & Transfer Gateway (`/admin/bank-details`)
   - Payment Verification Queue (`/admin/payments`)
   - Financial Reports & Dossiers (`/admin/reports`)
   - Analytics Insights (`/admin/analytics`)
3. **Website & Content Management (Collapsible Accordion):**
   - Website Hub (`/admin/websites`)
   - Home Page & Hero Editor (`/admin/websites/home`)
   - Services Catalog (`/admin/services`)
   - Lookbook Gallery (`/admin/gallery`)
   - Testimonials & Reviews (`/admin/testimonials`)
   - Editorial Blog (`/admin/blog`)
   - Feedback Inbox (`/admin/feedback`)
4. **System & Governance (Collapsible Accordion):**
   - Working Hours & Shifts (`/admin/working-hours`)
   - Feature Flags (`/admin/feature-flags`)
   - API Directory & Keys (`/admin/api`)
   - System Diagnostics & Health (`/admin/health`)
   - Error Logs & Backups (`/admin/system-logs`)

---

## 3. Role-Specific Dashboard Rebuild Specifications

### 3.1 Admin Dashboard (`src/modules/admin/pages/DashboardPage.vue`)
* **Urgent Action Ribbon:** Live counter of pending manual bank transfers requiring receipt verification and unassigned appointments. Clicking any item opens immediate review drawer.
* **Compact KPI Matrix:** 4 compact cards displaying:
  1. *Today's Bookings* (with completed/pending split)
  2. *Verified Revenue* (formatted in Naira `₦` from kobo)
  3. *Active Barbers On-Duty* (live chair status)
  4. *Customer Retention Rate*
* **Compact Dual-Chart Section:**
  - 7-Day Revenue & Appointment Flow
  - Status Breakdown (Confirmed, Pending, Completed, No-Show)
* **Real-time Queue & Appointments:** Direct quick-action table showing next 5 upcoming appointments with 1-click status change or barber reassignment.

### 3.2 Super Admin Dashboard (`src/modules/superadmin/pages/DashboardPage.vue`)
* **Command Bar:** Global system health indicator, server memory/load, active Sanctum sessions, and maintenance mode toggle.
* **Platform KPIs:** Total users by role breakdown, system-wide transaction volume, API error rate, and active feature flags.
* **User & Admin Management Quick Strip:** Direct search & filter for managing platform staff, issuing password resets, or auditing recent system activities.
* **Audit Trail Feed:** Recent high-level security events (role changes, settings updates, manual overrides).

### 3.3 Barber Dashboard (`src/features/barber/pages/BarberDashboardPage.vue`)
* **Chair Status Bar:** Prominent toggle for *Available / Busy / On Break* with real-time feedback.
* **Today's Timeline:** Chronological appointment schedule with customer phone, service details, and countdown to next client.
* **Venue Payment Collector:** Quick-action modal to collect cash or transfer payment at the venue and confirm appointment completion.
* **Daily Earnings Summary:** Transparent breakdown of completed services and tips.

### 3.4 Customer Dashboard (`src/modules/customer/pages/DashboardPage.vue`)
* **Hero Grooming Card:**
  - Next appointment countdown (*"In 2 hours, 15 minutes with Marcus"*).
  - Prominent 4-digit verification code with high-contrast display.
  - 1-click reschedule or directions.
* **Quick Re-book Ribbon:** Repeat past favourite cuts with a single click.
* **Payment & Receipt Status:** Instant feedback on uploaded bank transfer receipts (Pending Verification / Approved).
* **Recent Activity & Wishlist:** Saved styles from the lookbook gallery.

---

## 4. Execution Sequence & Safety Milestones

| Stage | Focus Area | Deliverables | Verification Criterion |
| :---: | :--- | :--- | :--- |
| **Stage 1** | **Audit & System Documentation** | `CANDYCUTZ-AUDIT-REPORT.md`, `DESIGN-SYSTEM.md`, `DASHBOARD-IMPLEMENTATION-PLAN.md` | Ground truth aligned with ARCHITECTURE.md |
| **Stage 2** | **Design Tokens & Shared UI** | `StatCard.vue`, `StatusBadge.vue`, `ActionDropdown.vue`, `DataTable.vue` | Standalone Vitest unit tests pass |
| **Stage 3** | **Modular Sidebar & Topbar** | Decomposed `DashboardSidebar.vue`, `SidebarNavAccordion.vue`, `DashboardHeader.vue` | Sub-300 lines; responsive on mobile & desktop |
| **Stage 4** | **Admin Dashboard Rebuild** | Refactored `AdminLayout.vue` and `DashboardPage.vue` | Loads real backend data; clean ESLint & Vitest |
| **Stage 5** | **SuperAdmin Dashboard Rebuild** | Modernized `SuperAdminLayout.vue` and `DashboardPage.vue` | System telemetry & user management fully functional |
| **Stage 6** | **Barber & Customer Portals** | Modernized agenda, chair toggle, and customer grooming countdown | Role authentication & permissions preserved |
| **Stage 7** | **Final Quality Assurance** | Full suite execution: Pest tests, Vitest tests, Pint styling, build check | Zero regressions; build passes |

