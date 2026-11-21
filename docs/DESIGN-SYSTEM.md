# CandyCutz — Design System & UI Token Specification

**Version:** 2.0 (Modern SaaS Rebuild)  
**Authoritative Framework:** Vue 3.5 + Tailwind CSS 4.3  
**Design Philosophy:** Luxury Grooming Craftsmanship meets High-Efficiency SaaS Operations.

---

## 1. Brand Identity & Color System

The CandyCutz design system preserves the authentic luxury identity of the brand while delivering the crisp legibility, compact information density, and refined micro-interactions expected of a modern SaaS management platform.

### 1.1 Color Tokens

```css
:root {
  /* Surface & Background (Light Mode) */
  --cc-bg-canvas: #FAF9F6;
  --cc-bg-surface: #FFFFFF;
  --cc-bg-surface-elevated: #F4F1EA;
  --cc-bg-subtle: #EDE8DE;
  --cc-border-subtle: rgba(0, 0, 0, 0.06);
  --cc-border-default: rgba(0, 0, 0, 0.12);
  --cc-border-strong: rgba(0, 0, 0, 0.20);

  /* Typography */
  --cc-text-primary: #111827;
  --cc-text-secondary: #4B5563;
  --cc-text-muted: #9CA3AF;

  /* Primary Brand Accent (Champagne Gold) */
  --cc-accent-gold: #D4AF37;
  --cc-accent-gold-hover: #B89628;
  --cc-accent-gold-subtle: rgba(212, 175, 55, 0.12);
  --cc-accent-gold-border: rgba(212, 175, 55, 0.25);

  /* Semantic Feedback */
  --cc-success: #16A34A;
  --cc-success-subtle: rgba(22, 163, 74, 0.10);
  --cc-warning: #D97706;
  --cc-warning-subtle: rgba(217, 119, 6, 0.10);
  --cc-danger: #DC2626;
  --cc-danger-subtle: rgba(220, 38, 38, 0.10);
  --cc-info: #2563EB;
  --cc-info-subtle: rgba(37, 99, 235, 0.10);
}

[data-theme="dark"], .dark {
  /* Surface & Background (Dark Mode - Default) */
  --cc-bg-canvas: #0B0F17;
  --cc-bg-surface: #121824;
  --cc-bg-surface-elevated: #1A2232;
  --cc-bg-subtle: #242E42;
  --cc-border-subtle: rgba(255, 255, 255, 0.06);
  --cc-border-default: rgba(255, 255, 255, 0.10);
  --cc-border-strong: rgba(255, 255, 255, 0.18);

  /* Typography */
  --cc-text-primary: #F8FAFC;
  --cc-text-secondary: #94A3B8;
  --cc-text-muted: #64748B;

  /* Primary Brand Accent (Champagne Gold Glow) */
  --cc-accent-gold: #FF9900;
  --cc-accent-gold-hover: #FFB84D;
  --cc-accent-gold-subtle: rgba(255, 153, 0, 0.12);
  --cc-accent-gold-border: rgba(255, 153, 0, 0.25);

  /* Semantic Feedback */
  --cc-success: #10B981;
  --cc-success-subtle: rgba(16, 185, 129, 0.12);
  --cc-warning: #F59E0B;
  --cc-warning-subtle: rgba(245, 158, 11, 0.12);
  --cc-danger: #EF4444;
  --cc-danger-subtle: rgba(239, 68, 68, 0.12);
  --cc-info: #38BDF8;
  --cc-info-subtle: rgba(56, 189, 248, 0.12);
}
```

### 1.2 Role Badge Accents
Instead of recoloring entire portal interfaces, portals maintain unified dark/light surfaces while using elegant role indicators:
* **Super Admin:** Crimson Ruby Badge (`text-rose-400 bg-rose-500/10 border-rose-500/20`)
* **Admin:** Tangerine Amber Badge (`text-amber-400 bg-amber-500/10 border-amber-500/20`)
* **Barber:** Emerald Mint Badge (`text-emerald-400 bg-emerald-500/10 border-emerald-500/20`)
* **Customer:** Champagne Gold Badge (`text-yellow-400 bg-yellow-500/10 border-yellow-500/20`)

---

## 2. Typography Hierarchy

| Style Role | Font Family | Size / Line-height | Tracking / Weight | Usage |
| :--- | :--- | :--- | :--- | :--- |
| **Brand Display** | `Playfair Display`, serif | 24px - 32px | -0.02em / Bold | Dashboard section hero, Brand Wordmark |
| **Section Heading** | `Inter`, sans-serif | 18px - 20px | -0.01em / Semibold | Card titles, drawer headers |
| **Stat Metric** | `Inter` / Mono tabular | 28px - 32px | -0.02em / Bold | KPI numbers, revenue figures |
| **Body Primary** | `Inter`, sans-serif | 14px / 20px | Normal / Medium | Table rows, input fields, descriptions |
| **Label / Caption** | `Inter`, sans-serif | 11px - 12px | +0.02em / Semibold | Table column headers, badge text |
| **Micro Subtitle** | `Inter`, sans-serif | 10px | +0.06em / Uppercase | Group category headers, metadata |

---

## 3. Core UI Component Specifications

### 3.1 StatCard (`src/shared/ui/StatCard.vue`)
* Compact layout with top icon wrap, value, label, and trend pill (+14.2% vs last week).
* Built-in subtle glass reflection and hover translation (`hover:-translate-y-0.5`).
* Zero cumulative layout shift (CLS) with matching skeleton loader.

### 3.2 StatusBadge (`src/shared/ui/StatusBadge.vue`)
* Standardized statuses: `pending`, `confirmed`, `completed`, `cancelled`, `no_show`, `active`, `suspended`, `verified`, `unverified`.
* Soft translucent background pill with a pulsing dot indicator.

### 3.3 DataTable (`src/shared/ui/DataTable.vue`)
* Sticky table header with subtle border.
* Interactive row hover state with smooth transitions.
* Column sorting indicators, responsive horizontal scroll container, and integrated empty/loading states.
* Automatic mobile fallback to card layout.

### 3.4 Modal & Drawer Primitives
* Backdrop blur with clean keyboard dismiss (`Escape`).
* Proper ARIA focus trap and unmount cleanup.
* Standardized header, scrollable body, and fixed action footer.

