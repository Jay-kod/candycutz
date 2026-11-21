<template>
  <section class="barber-cta" :class="{ 'barber-cta--preview': compact, 'barber-cta--has-image': Boolean(backgroundImage) }">
    <div
      v-if="backgroundImage"
      class="barber-cta__photo"
      :style="{ backgroundImage: `url('${backgroundImage}')`, ...(resolvedPhotoOpacity !== undefined ? { opacity: resolvedPhotoOpacity } : {}) }"
      aria-hidden="true"
    >
      <img :src="backgroundImage" alt="" fetchpriority="high" decoding="async" class="barber-cta__lcp-img" />
    </div>
    <div class="barber-cta__shade" :style="{ opacity: resolvedOverlayOpacity, background: overlayBackground }" aria-hidden="true"></div>
    <div class="barber-cta__gradient" aria-hidden="true"></div>
    <div v-if="!compact" class="barber-cta__dot-field" aria-hidden="true"></div>

    <div class="barber-cta__content">
      <h1 class="barber-cta__title whitespace-nowrap">
        <span :class="firstTitlePartClass">{{ titleParts[0] }}</span>
        <span v-if="titleParts[1]" :class="secondTitlePartClass">&nbsp;{{ titleParts[1] }}</span>
      </h1>

      <p class="barber-cta__subtitle text-center">{{ subtitle }}</p>
      <p v-if="caption" class="barber-cta__caption">{{ caption }}</p>
      <div class="barber-cta__actions">
        <RouterLink :to="secondaryLink" class="barber-cta__button barber-cta__button--secondary">
          <PhoneIcon class="h-4 w-4" aria-hidden="true" />
          {{ secondaryText }}
        </RouterLink>
        <RouterLink to="/customer/login" class="barber-cta__button barber-cta__button--primary">
          {{ primaryText }}
          <ArrowRightIcon class="h-4 w-4" aria-hidden="true" />
        </RouterLink>
      </div>
    </div>

    <!-- Seamless Luxury Wave Divider -->
    <div v-if="!compact" class="barber-cta__bottom-wave" aria-hidden="true">
      <svg class="barber-cta__wave-svg" viewBox="0 0 1440 100" fill="none" preserveAspectRatio="none">
        <!-- Subtle luxury gold razor-line along the wave edge -->
        <path
          d="M0,50 C360,95 720,15 1080,65 C1260,85 1380,55 1440,50"
          fill="none"
          stroke="var(--color-accent)"
          stroke-width="1.5"
          stroke-opacity="0.35"
        />
        <!-- Solid smooth wave body filled seamlessly with the next section's background -->
        <path
          d="M0,50 C360,95 720,15 1080,65 C1260,85 1380,55 1440,50 L1440,100 L0,100 Z"
          fill="var(--color-bg-primary)"
        />
      </svg>
    </div>
  </section>
</template>

<script setup>
import { computed } from 'vue';
import { buildOverlayGradient, DEFAULT_OVERLAY_COLOR } from '@/core/utils/heroOverlay';
import { RouterLink } from 'vue-router';
import { ArrowRightIcon, PhoneIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  title: { type: String, default: 'Precision Cuts.\nMade for You.' },
  titlePartOne: { type: String, default: '' },
  titlePartTwo: { type: String, default: '' },
  titleColorOrder: { type: String, default: 'first-white' },
  subtitle: { type: String, default: 'Expert barbers. Fresh fades. A better kind of barbershop experience.' },
  caption: { type: String, default: '' },
  primaryText: { type: String, default: 'Book your cut' },
  secondaryText: { type: String, default: 'Contact the shop' },
  secondaryLink: { type: String, default: '/contact' },
  backgroundImage: { type: String, default: '' },
  imageOpacity: { type: [Number, String], default: null },
  overlayOpacity: { type: [Number, String], default: null },
  overlayColor: { type: String, default: DEFAULT_OVERLAY_COLOR },
  compact: { type: Boolean, default: false }
});

const titleParts = computed(() => {
  const first = props.titlePartOne.trim();
  const second = props.titlePartTwo.trim();
  if (first || second) return [first, second].filter(Boolean);

  const normalizedTitle = props.title.replace(/\s*\n\s*/g, ' ').trim();
  const sentenceBreak = normalizedTitle.match(/^(.+?[.!?])\s+(.+)$/);
  if (sentenceBreak) return [sentenceBreak[1], sentenceBreak[2]];

  const words = normalizedTitle.split(/\s+/);
  if (words.length < 2) return [normalizedTitle];
  const midpoint = Math.ceil(words.length / 2);
  return [words.slice(0, midpoint).join(' '), words.slice(midpoint).join(' ')];
});

const firstTitlePartClass = computed(() => (
  props.titleColorOrder === 'first-gold' ? 'barber-cta__title-gold' : 'barber-cta__title-white'
));
const secondTitlePartClass = computed(() => (
  props.titleColorOrder === 'first-gold' ? 'barber-cta__title-white' : 'barber-cta__title-gold'
));

const resolvedPhotoOpacity = computed(() => {
  if (props.imageOpacity === null || props.imageOpacity === undefined || props.imageOpacity === '') {
    return undefined;
  }
  const val = Number(props.imageOpacity);
  if (isNaN(val)) return undefined;
  const clamped = Math.max(0, Math.min(100, val));
  return clamped > 1 ? clamped / 100 : clamped;
});

const resolvedOverlayOpacity = computed(() => {
  if (props.overlayOpacity === null || props.overlayOpacity === undefined || props.overlayOpacity === '') {
    return 0.45;
  }
  const val = Number(props.overlayOpacity);
  if (!Number.isFinite(val)) return 0.45;
  const clamped = Math.max(0, Math.min(100, val));
  return clamped > 1 ? clamped / 100 : clamped;
});

const overlayBackground = computed(() => buildOverlayGradient(props.overlayColor));
</script>

<style scoped>
.barber-cta {
  position: relative;
  isolation: isolate;
  display: flex;
  flex-shrink: 0;
  box-sizing: border-box;
  height: 100vh;
  min-height: 100vh;
  max-height: 100vh;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  padding: clamp(6.5rem, 14vh, 9.5rem) 1.25rem clamp(5.5rem, 12vh, 8.5rem);
  background:
    radial-gradient(ellipse at 50% 48%, rgba(255, 153, 0, 0.08), transparent 58%),
    var(--color-bg-primary);
  color: var(--color-text-primary);
  transition: background-color 300ms ease, color 300ms ease;
}

.barber-cta--has-image {
  background:
    radial-gradient(ellipse at 50% 40%, rgba(255, 153, 0, 0.05) 0%, transparent 70%),
    #0E0B08;
}

.barber-cta--has-image .barber-cta__title {
  text-shadow: 0 2px 14px rgba(0, 0, 0, 0.6);
}

.barber-cta--has-image .barber-cta__title-white {
  color: #FFFFFF !important;
}

.barber-cta--has-image .barber-cta__title-gold {
  color: var(--color-accent) !important;
  text-shadow: 0 2px 16px rgba(255, 153, 0, 0.35);
}

.barber-cta--has-image .barber-cta__subtitle {
  color: rgba(255, 255, 255, 0.90) !important;
  text-shadow: 0 1px 8px rgba(0, 0, 0, 0.6);
}

.barber-cta--has-image .barber-cta__caption {
  color: rgba(255, 255, 255, 0.72) !important;
  text-shadow: 0 1px 6px rgba(0, 0, 0, 0.5);
}

.barber-cta__photo,
.barber-cta__shade,
.barber-cta__gradient,
.barber-cta__dot-field {
  position: absolute;
  inset: 0;
  pointer-events: none;
}

.barber-cta__gradient {
  z-index: -1;
  background-image:
    radial-gradient(rgba(255, 255, 255, 0.16) 1px, transparent 1px),
    url('/images/hero-pattern.png');
  background-size: 16px 16px, 16px 16px;
  background-repeat: repeat;
  background-position: center;
  opacity: 0.28;
}

:root[data-theme="light"] .barber-cta .barber-cta__gradient,
.light .barber-cta .barber-cta__gradient {
  background-image:
    radial-gradient(rgba(0, 0, 0, 0.14) 1px, transparent 1px),
    url('/images/hero-pattern.png');
  filter: invert(1);
  opacity: 0.22;
}

.barber-cta__dot-field {
  position: absolute;
  inset: 0;
  pointer-events: none;
}

.barber-cta__lcp-img { width: 100%; height: 100%; object-fit: cover; object-position: center; }
.barber-cta__photo {
  z-index: -3;
  background-position: center;
  background-size: cover;
  opacity: 0.35;
  mask-image: linear-gradient(to bottom, black 65%, transparent 95%);
  -webkit-mask-image: linear-gradient(to bottom, black 65%, transparent 95%);
}

.barber-cta__shade {
  z-index: -2;
  background:
    radial-gradient(ellipse at 50% 40%, rgba(250, 190, 60, 0.20) 0%, rgba(212, 143, 24, 0.26) 38%, rgba(138, 86, 11, 0.45) 72%, rgba(20, 14, 6, 0.85) 100%),
    linear-gradient(180deg, rgba(230, 168, 23, 0.10) 0%, rgba(14, 10, 4, 0.75) 75%, #0E0B08 100%);
  transition: opacity 300ms ease;
}

.barber-cta__dot-field {
  display: none;
}

.barber-cta__content {
  position: relative;
  z-index: 10;
  width: min(100%, 820px);
  padding: 0 1rem;
  margin: 0 auto;
  text-align: center;
}

/* ============================================================
   Seamless Luxury Wave Divider
   ============================================================ */
.barber-cta__bottom-wave {
  position: absolute;
  bottom: -1px;
  left: 0;
  right: 0;
  width: 100%;
  height: clamp(48px, 8vw, 95px);
  pointer-events: none;
  z-index: 2;
  overflow: hidden;
  line-height: 0;
}

.barber-cta__wave-svg {
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 100%;
  display: block;
  filter: drop-shadow(0 -8px 24px rgba(0, 0, 0, 0.18));
}

.barber-cta__announcement {
  display: inline-flex;
  min-height: 34px;
  align-items: center;
  gap: 0.65rem;
  border: 1px solid var(--color-border);
  border-radius: 999px;
  padding: 0.25rem 0.7rem 0.25rem 0.35rem;
  background: var(--color-bg-secondary);
  color: var(--color-text-primary);
  font-size: 0.75rem;
  text-decoration: none;
  transition: border-color 160ms ease, background-color 160ms ease;
}

.barber-cta__announcement:hover {
  border-color: var(--color-accent);
  background: var(--color-bg-tertiary);
}

.barber-cta__announcement-badge {
  border: 1px solid var(--color-accent);
  border-radius: 999px;
  padding: 0.14rem 0.4rem;
  color: var(--color-accent);
  font-size: 0.63rem;
  font-weight: 700;
  letter-spacing: 0.04em;
}

.barber-cta__title {
  margin: 0;
  max-width: 100%;
  font-size: clamp(1.25rem, 6vw, 4.15rem);
  font-weight: 700;
  letter-spacing: -0.055em;
  line-height: 1.06;
  overflow-wrap: break-word;
  text-wrap: balance;
}

.barber-cta__title span {
  display: inline;
}

.barber-cta__title-white {
  color: var(--color-text-primary);
}

.barber-cta__title-gold {
  color: var(--color-accent);
}

.barber-cta__subtitle {
  max-width: 38rem;
  margin: 1rem auto 0;
  color: var(--color-text-secondary);
  font-size: clamp(0.9rem, 1.7vw, 1rem);
  letter-spacing: 0.025em;
  line-height: 1.6;
  text-align: center;
  text-wrap: balance;
}

.barber-cta__caption {
  max-width: 34rem;
  margin: 0.5rem auto 0;
  color: var(--color-text-muted);
  font-size: clamp(0.75rem, 1.4vw, 0.875rem);
  line-height: 1.6;
  text-align: center;
  text-wrap: balance;
}

.barber-cta__actions {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 0.65rem;
  margin-top: 1.8rem;
}

.barber-cta__button {
  display: inline-flex;
  min-height: 46px;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  border-radius: 10px;
  padding: 0.68rem 1rem;
  font-size: 0.875rem;
  font-weight: 600;
  text-decoration: none;
  transition: background-color 180ms ease, border-color 180ms ease, transform 180ms ease;
}

.barber-cta__button:hover {
  transform: translateY(-2px);
}

.barber-cta__button--primary {
  border: 1px solid var(--color-accent);
  background: var(--color-accent);
  color: var(--color-accent-foreground);
  box-shadow: 0 4px 14px rgba(255, 153, 0, 0.25);
}

.barber-cta__button--primary:hover {
  border-color: var(--color-accent-hover);
  background: var(--color-accent-hover);
  box-shadow: 0 6px 20px rgba(255, 153, 0, 0.35);
}

.barber-cta__button--secondary {
  border: 1px solid var(--color-border);
  background: var(--color-bg-secondary);
  color: var(--color-accent);
  box-shadow: 0 2px 8px var(--color-shadow);
}

.barber-cta__button--secondary:hover {
  border-color: var(--color-accent);
  background: var(--color-bg-tertiary);
  color: var(--color-accent-hover);
}

.barber-cta--preview {
  height: auto !important;
  min-height: 430px !important;
  max-height: none !important;
  border-radius: 12px;
  padding: 2.5rem 1.25rem;
}

@media (max-width: 640px) {
  .barber-cta {
    height: 100vh;
    min-height: 100vh;
    max-height: 100vh;
    padding-top: max(5.75rem, calc(4.75rem + env(safe-area-inset-top, 0px)));
    padding-bottom: max(4.5rem, calc(3.5rem + env(safe-area-inset-bottom, 0px)));
    padding-inline: 1rem;
  }

  .barber-cta__bottom-wave {
    height: clamp(38px, 8vw, 60px);
  }

  .barber-cta__announcement {
    max-width: 100%;
    gap: 0.45rem;
    font-size: 0.68rem;
  }

  .barber-cta__title {
    font-size: clamp(1.45rem, 8vw, 3.5rem);
    line-height: 1.1;
  }

  .barber-cta__subtitle {
    max-width: 25rem;
    margin-top: 0.85rem;
    font-size: 0.95rem;
  }

  .barber-cta__actions {
    gap: 0.55rem;
    margin-top: 1.5rem;
  }

  .barber-cta__button {
    min-height: 44px;
    padding-inline: 0.8rem;
    font-size: 0.8rem;
  }

  @media (max-width: 480px) {
    .barber-cta__actions {
      display: flex;
      flex-wrap: nowrap;
      width: 100%;
      max-width: 380px;
      margin-inline: auto;
    }
    .barber-cta__button {
      flex: 1 1 0;
      min-width: 0;
      width: auto;
      padding-inline: 0.65rem;
      font-size: clamp(0.75rem, 3.2vw, 0.85rem);
      white-space: nowrap;
    }
  }

  @media (max-width: 340px) {
    .barber-cta__actions {
      flex-direction: column;
      max-width: 260px;
    }
    .barber-cta__button {
      width: 100%;
    }
  }

  .barber-cta--preview {
    height: auto !important;
    min-height: 390px !important;
    max-height: none !important;
    padding: 2rem 1rem;
  }
}

@media (prefers-reduced-motion: reduce) {
  .barber-cta__button {
    transition: none;
  }
}
</style>
