# Candycutz — Design System & Visual Language

## 1. Visual Philosophy & Brand Character
Candycutz embodies **Modern Luxury Grooming**:
- **Tone**: Bold, minimal, confident, architectural, and clean.
- **Atmosphere**: A five-star private member's barbershop club.
- **Themes**: Deliberately designed dual-mode experiences (Warm Ivory in daylight; Deep Obsidian at night), not mere programmatic color inversions.
- **Animations**: Purposeful, GPU-accelerated micro-interactions (staggered card reveals, soft button presses, shimmer loaders). Avoid generic flashy animations or excessive gradients.

---

## 2. Master Design Tokens

### 2.1 Light Theme (Warm Ivory & Champagne Gold)
Designed for bright outdoor sunlight and airy salon aesthetics:
- **`background`**: `#F7F6F2` (Warm Ivory Canvas)
- **`surface`**: `#FFFFFF` (Pure White Card Surface)
- **`surfaceElevated`**: `#F0EDE6` (Elevated Cream Container)
- **`textPrimary`**: `#111111` (Deep Obsidian Ink)
- **`textSecondary`**: `#666666` (Neutral Graphite)
- **`border`**: `#E5E2DB` (Subtle Muted Linen)
- **`brandPrimary`**: `#C6A15B` (Refined Champagne Gold)
- **`brandDark`**: `#9B7735` (Deep Antique Bronze)

### 2.2 Dark Theme (Obsidian & Metallic Champagne)
Designed for evening elegance and battery-efficient mobile viewing:
- **`background`**: `#0B0B0B` (True Obsidian Dark)
- **`surface`**: `#151515` (Deep Charcoal Card Surface)
- **`surfaceElevated`**: `#1D1D1D` (Elevated Card Element)
- **`textPrimary`**: `#F5F3EE` (Soft Ivory White)
- **`textSecondary`**: `#A9A7A1` (Muted Warm Silver)
- **`border`**: `#2A2A2A` (Subtle Smoked Border)
- **`brandPrimary`**: `#D2AE68` (Luminous Champagne Gold)
- **`brandDark`**: `#A9823F` (Rich Warm Gold)

### 2.3 Semantic Feedback Tokens
- **`success`**: `#16A34A` (Emerald Green)
- **`warning`**: `#D97706` (Warm Amber)
- **`danger`**: `#DC2626` (Ruby Crimson)
- **`neutral`**: `#6B7280` (Cool Slate)

---

## 3. Typography Scale & Font Pairings

| Scale Role | Font Family | Size | Weight | Line Height | Usage |
|---|---|---|---|---|---|
| **Display Hero** | `Playfair Display` | 48px – 72px | Bold (700) | 1.1 | Main splash hero, landing tagline |
| **Heading 1** | `Playfair Display` | 36px – 44px | SemiBold (600) | 1.2 | Page title, modal headers |
| **Heading 2** | `Playfair Display` | 28px – 32px | SemiBold (600) | 1.25 | Section titles |
| **Heading 3** | `Inter` | 20px – 24px | Medium (500) | 1.3 | Card titles, service names |
| **Body Lead** | `Inter` | 16px – 18px | Regular (400) | 1.5 | Article excerpts, intro paragraphs |
| **Body Regular** | `Inter` | 14px – 15px | Regular (400) | 1.45 | Standard text, descriptions |
| **Caption / Meta**| `Inter` | 12px – 13px | Medium (500) | 1.4 | Badges, timestamps, duration |
| **Button CTA** | `Inter` | 15px – 16px | SemiBold (600) | 1.0 | Action buttons, tabs |

### 3.1 Approved Google Fonts Whitelist (Theme Studio)
- Primary Sans: `Inter`, `Manrope`, `Plus Jakarta Sans`, `DM Sans`, `Poppins`
- Luxury Display: `Playfair Display`, `Cinzel`, `Cormorant Garamond`

---

## 4. Component Design Patterns

### 4.1 Base Button States
- **Primary Gold**: Solid `#C6A15B` background, deep obsidian text `#111111`, subtle hover lift (`-1px`), 150ms cubic-bezier transition.
- **Outline Gold**: 1px `#C6A15B` border, transparent background, gold text, 10% opacity fill on hover.
- **Ghost Action**: Transparent background, text secondary, borderless.
- **Loading State**: Subtle spinning gold ring (`border-t-transparent animate-spin`); button text dimmed to 0 opacity.
- **Disabled State**: Opacity 40%, cursor not-allowed, zero hover transform.

### 4.2 Skeleton Shimmer Loader
- Uses CSS `@keyframes shimmer`:
```css
@keyframes shimmer {
  0% { transform: translateX(-100%); }
  100% { transform: translateX(100%); }
}
```
- Replaces harsh spinner wheels with anatomical content placeholders mimicking the layout of cards, text, and avatars.
