# CandyCutz — Responsive Design System & Implementation Reference

**Status:** Authoritative  
**Scope:** Design tokens, fluid typography, layout patterns, and cross-browser conventions across `barbing-saloon-web`.

---

## 1. Responsive Design Philosophy

CandyCutz enforces a **mobile-first, fluid, and component-contained** responsive architecture:
1. **One Unified Codebase:** No separate codebases or device-sniffed templates for Android vs. iOS.
2. **Fluid Scaling Over Discontinuous Jumps:** Use CSS `clamp()`, `min()`, `max()`, and percentage-based flex containers rather than rigid fixed-pixel switches.
3. **Safe-Area Inset Integration:** Fixed headers, mobile navigation drawers, and bottom action sheets must integrate `env(safe-area-inset-*)` values to accommodate modern device physical ergonomics (notches, Dynamic Islands, home indicator bars).
4. **Touch Target Compliance:** All interactive elements must maintain $\ge 44\text{px} \times 44\text{px}$ effective hit areas on touch viewports ($<1024\text{px}$).

---

## 2. Breakpoint Architecture

Breakpoints are defined in [`tailwind.config.js`](file:///c:/xampp/htdocs/l/m/candycutz-main/barbing-saloon-web/tailwind.config.js) based on actual content and layout requirements:

| Token | Min-Width | Target Device Archetypes | Layout Transition |
| :--- | :---: | :--- | :--- |
| `xxs` | `320px` | Ultra-compact phones (iPhone SE 1st gen, small Android) | Single-column actions, compact title font size |
| `xs` | `375px` | Standard compact mobile (iPhone 6/7/8/SE 2/3) | Dual button row allowed with fluid inline padding |
| `sm` | `640px` | Large phones & small tablets in portrait | Features grid switches to 2 columns |
| `md` | `768px` | Tablets portrait (iPad, Galaxy Tab) | Main navbar switches to expanded pill; table layouts reveal |
| `lg` | `1024px` | Laptops, tablets landscape | Features grid expands to 4 columns; sidebar switches to static desktop |
| `xl` | `1280px` | Desktop displays | Max-width content constraint (`max-w-7xl`) |
| `2xl` | `1536px` | High-res monitors | Centered page containers with fluid margins |
| `3xl` | `1920px` | Full HD / Large monitors | Preserves max content boundary, prevents stretching |

---

## 3. Fluid Typography Tokens

CandyCutz utilizes CSS `clamp()` to scale typography smoothly across screen widths:

```css
/* Display Headlines (Hero) - Fluid across Mobile to 4K */
font-size: clamp(1.45rem, 8vw, 3.25rem); /* Mobile <= 640px */
font-size: clamp(1.85rem, 5.5vw, 3.5rem); /* Tablet 641px - 1023px */
font-size: clamp(2rem, 5.2vw, 4.65rem); /* Desktop & Large >= 1024px */
line-height: 1.08;

/* Section Headings (H2) */
font-size: clamp(1.5rem, 4vw, 2.75rem);
line-height: 1.2;

/* Subtitles / Lead Paragraphs */
font-size: clamp(1.1rem, 1.6vw, 1.35rem); /* Desktop */
font-size: 0.95rem; /* Mobile */
line-height: 1.6;

/* Body Text */
font-size: clamp(0.875rem, 1.2vw, 1rem);
line-height: 1.55;

/* Action Buttons */
font-size: clamp(0.75rem, 3.2vw, 0.85rem); /* Mobile <= 480px */
font-size: 0.925rem; /* Tablet */
font-size: 1rem; /* Desktop min-height: 52px */

/* Captions / Meta Info */
font-size: clamp(0.8rem, 1.2vw, 1.05rem);
```

---

## 4. Homepage Hero Architecture (`BarberCta.vue`)

The homepage hero delivers a full-screen, high-impact brand entrance while maintaining visual stability:

```css
.barber-cta {
  position: relative;
  isolation: isolate;
  display: flex;
  flex-shrink: 0;
  box-sizing: border-box;
  height: 99.5vh;
  min-height: 99.5vh;
  max-height: 99.5vh;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  padding: clamp(6.5rem, 14vh, 9.5rem) 1.25rem clamp(5.5rem, 12vh, 8.5rem);
}

@media (max-width: 640px) {
  .barber-cta {
    height: 99.5vh;
    min-height: 99.5vh;
    max-height: 99.5vh;
    padding-top: max(5.75rem, calc(4.75rem + env(safe-area-inset-top, 0px)));
    padding-bottom: max(4.5rem, calc(3.5rem + env(safe-area-inset-bottom, 0px)));
    padding-inline: 1rem;
  }
}
```

### Hero Design Invariants:
1. **No dvh/svh Ambiguity:** `99.5vh` calculates reliably to 99.5% of `window.innerHeight` across all devices, preventing dynamic address bar jumpiness or iframe cutoff.
2. **Integrated Wave Divider:** Pinned at `bottom: -1px;`, the wave SVG seamlessly matches `--color-bg-primary`, submerging the subsequent `#features` section below the fold.
3. **Typography Contrast Guarantee:** In `.barber-cta--has-image`, title text is hard-coded to `#FFFFFF !important` with drop-shadows, ensuring $\ge 12:1$ contrast against the dark photograph.
4. **CMS Preview Card Decoupling:** In preview mode (`.barber-cta--preview`), viewport height constraints are released (`height: auto !important; max-height: none !important; min-height: 430px !important;`).

---

## 5. Navigation & Layout Adaptations

### Fixed Public Header (`PublicLayout.vue`)
```css
.public-header {
  position: fixed;
  z-index: 50;
  top: 0;
  right: 0;
  left: 0;
  padding-top: max(0.55rem, env(safe-area-inset-top, 0px));
  padding-left: max(0.65rem, env(safe-area-inset-left, 0px));
  padding-right: max(0.65rem, env(safe-area-inset-right, 0px));
  padding-bottom: 0.55rem;
  pointer-events: none;
}
```

### Mobile Navigation Drawer (`PublicLayout.vue`)
* **Width:** `width: min(82vw, 320px);` (replaces narrow 55vw).
* **Safe Insets:** `padding-top: max(1rem, env(safe-area-inset-top, 0px));` and `padding-bottom: max(1.25rem, env(safe-area-inset-bottom, 0px));`.
* **Touch Targets:** Minimum 44px height on all navigation and action items.

---

## 6. Build Synchronization Pipeline

For local development under XAMPP / Apache, `npm run build` runs [`scripts/sync-to-public.mjs`](file:///c:/xampp/htdocs/l/m/candycutz-main/barbing-saloon-web/scripts/sync-to-public.mjs):
```bash
npm run build
# 1. Runs prebuild responsiveness audit (audit-responsiveness.mjs)
# 2. Compiles Vite production bundle into barbing-saloon-web/dist
# 3. Synchronizes dist contents into barbing-saloon-api/public/
# 4. Preserves index.php and .htaccess in api/public/
```
