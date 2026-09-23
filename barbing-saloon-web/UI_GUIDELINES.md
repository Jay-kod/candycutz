# CandyCutz UI & Dashboard Design Guidelines

This document outlines the design rules for all overview, dashboard, analytics, and card components across the CandyCutz web platform and mobile app.

---

## The 5 Core Rules

### 1. Flat Accent Color (No Gradients on Surfaces or Text)
- **Single Brand Tint**: 
  - Admin portal: Admin orange (`#FF6700`, Tailwind `admin`).
  - Barber & Customer portal / Mobile: Gold (`#FF9900`, Tailwind `gold`).
- **No Surface Gradients**: Avoid `bg-gradient-to-*` on page headers, KPI cards, banners, or progress bars. Use flat `bg-theme-surface` or `bg-theme-bg`.
- **No Gradient Text**: Do not use `bg-clip-text text-transparent bg-gradient-to-r`. Use flat `text-admin` or `text-gold`.
- **Section Headers**: Replace gradient card headers with a clean 1px hairline border (`border-b border-theme-border`).

### 2. No Icon Tiles in Stat Cards
- **No Background Tiles**: Do NOT wrap icons in `h-10 w-10 rounded-xl bg-*/10 border` or circular pill containers.
- **Inline Placement**: Icons must sit inline next to the label, sized to 20px (`w-5 h-5`), using the same muted color as the label text.
- **No Background Watermarks**: Never place oversized, low-opacity watermark icons behind card numbers.

### 3. Metric Hierarchy
- **Primary Metric**: Revenue is always the primary metric — larger font (`text-3xl` or `text-4xl`), prominent placement, full-width or top-level.
- **Secondary Metrics**: Other stat cards (e.g. Total Bookings, Active Customers) are secondary. Limit secondary cards to 3–4 per row.

### 4. Hairline Borders, Tight Radii, No Surface Shadows
- **Card Radii**: Use `rounded-xl` (12px) for cards and modals.
- **Element Radii**: Use `rounded-lg` (8px) for inputs, buttons, and badges; `rounded-md` (6px) for small pills or chips.
- **Borders**: Standardize on a single 1px hairline border: `border border-theme-border` (web) or `borderWidth: 1, borderColor: colors.border` (mobile).
- **No Skeuomorphic Glow or Blur**: Eliminate `shadow-2xl`, arbitrary `shadow-[0_...]`, `backdrop-blur-xl`, `backdrop-blur-sm`, and floating blurred orbs.

### 5. Strict Number Formatting
- **Currency & Quantities**: Always format using `Intl.NumberFormat('en-NG')` (e.g. `₦1,250,000`). Never rely on arbitrary unlocalized `toLocaleString()` calls.
- **Tabular Numerals**: Apply `tabular-nums` (`font-variant-numeric: tabular-nums`) to all KPI values, table counts, timers, and prices to ensure stable alignment.

---

## Code Examples

### Stat / KPI Card (Vue 3 / Tailwind)

```vue
<!-- CORRECT -->
<article class="group rounded-xl border border-theme-border bg-theme-surface p-6 transition-colors duration-300 hover:border-gold/30">
  <div class="flex items-center gap-2 text-theme-muted mb-2">
    <CalendarDaysIcon class="w-5 h-5 shrink-0 text-gold" />
    <p class="text-xs font-bold uppercase tracking-wider">Today's Bookings</p>
  </div>
  <p class="text-3xl font-bold tracking-tight text-theme-text tabular-nums">
    {{ formatNumber(stats.today_bookings) }}
  </p>
</article>

<!-- FORBIDDEN -->
<article class="group relative overflow-hidden rounded-3xl border border-white/10 bg-gradient-to-br from-[#121212] to-[#1E1E1E] p-6 shadow-2xl backdrop-blur-xl hover:-translate-y-1">
  <div class="absolute -right-4 -top-4 opacity-[0.06] pointer-events-none">
    <CalendarDaysIcon class="w-28 h-28" />
  </div>
  <div class="h-12 w-12 rounded-2xl bg-gold/10 border border-gold/20 flex items-center justify-center">
    <CalendarDaysIcon class="w-6 h-6 text-gold" />
  </div>
  <p class="text-5xl font-black drop-shadow-md text-white">
    {{ stats.today_bookings }}
  </p>
</article>
```

### Mobile Card (React Native)

```tsx
// CORRECT
<Card style={{ borderRadius: RADIUS.md, borderWidth: 1, borderColor: colors.border }}>
  <View style={{ backgroundColor: colors.surfaceElevated, padding: SPACING.md }}>
    <Text style={{ color: colors.textPrimary, fontVariant: ['tabular-nums'] }}>
      ₦{formatNumber(revenue)}
    </Text>
  </View>
</Card>

// FORBIDDEN
<Card style={{ borderRadius: RADIUS.lg, elevation: 6 }}>
  <LinearGradient colors={['#242016', '#141419', '#0E0E12']}>
    <View style={styles.cardRimGlow} />
    ...
  </LinearGradient>
</Card>
```
