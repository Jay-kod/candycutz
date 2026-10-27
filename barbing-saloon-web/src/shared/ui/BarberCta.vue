<template>
  <section class="barber-cta" :class="{ 'barber-cta--preview': compact }">
    <div
      v-if="backgroundImage"
      class="barber-cta__photo"
      :style="{ backgroundImage: `url('${backgroundImage}')`, ...(resolvedPhotoOpacity !== undefined ? { opacity: resolvedPhotoOpacity } : {}) }"
      aria-hidden="true"
    ></div>
    <div class="barber-cta__shade" :style="resolvedOverlayOpacity !== undefined ? { opacity: resolvedOverlayOpacity } : {}" aria-hidden="true"></div>
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
  </section>
</template>

<script setup>
import { computed } from 'vue';
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
  if (props.overlayOpacity === null || props.overlayOpacity === undefined || props.overlayOpacity === '') return undefined;
  const val = Number(props.overlayOpacity);
  if (!Number.isFinite(val)) return undefined;
  return Math.max(0, Math.min(100, val)) / 100;
});
</script>

<style scoped>
.barber-cta {
  position: relative;
  isolation: isolate;
  display: flex;
  min-height: 100vh;
  min-height: 100dvh;
  min-height: 100svh;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  padding: clamp(6.5rem, 14vh, 9.5rem) 1.25rem clamp(4rem, 8vh, 6rem);
  background:
    radial-gradient(ellipse at 50% 48%, rgba(255, 153, 0, 0.08), transparent 58%),
    var(--color-bg-primary);
  color: var(--color-text-primary);
  transition: background-color 300ms ease, color 300ms ease;
}

.barber-cta__photo,
.barber-cta__shade,
.barber-cta__dot-field {
  position: absolute;
  inset: 0;
  pointer-events: none;
}

.barber-cta__photo {
  z-index: -2;
  background-position: center;
  background-size: cover;
  opacity: 0.22;
}

.barber-cta__shade {
  z-index: -1;
  background: linear-gradient(180deg, var(--color-bg-primary), var(--color-bg-primary));
  opacity: 0.78;
}

.barber-cta__dot-field {
  z-index: -1;
  background-image: radial-gradient(var(--color-accent) 0.7px, transparent 0.7px);
  background-position: center 12px;
  background-size: 12px 12px;
  mask-image: radial-gradient(ellipse at center, black 8%, transparent 76%);
  opacity: 0.22;
}

.barber-cta__content {
  width: min(100%, 820px);
  padding: 0 1rem;
  margin: 0 auto;
  text-align: center;
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
  font-size: clamp(1.25rem, 6vw, 4.15rem);
  font-weight: 700;
  letter-spacing: -0.055em;
  line-height: 1.06;
  white-space: nowrap;
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
  min-height: 430px;
  border-radius: 12px;
  padding: 2.5rem 1.25rem;
}

@media (max-width: 640px) {
  .barber-cta {
    min-height: 100svh;
    padding: 6.5rem 1rem 2.5rem;
  }

  .barber-cta__announcement {
    max-width: 100%;
    gap: 0.45rem;
    font-size: 0.68rem;
  }

  .barber-cta__title {
    font-size: clamp(1.25rem, 6vw, 3.5rem);
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

  .barber-cta--preview {
    min-height: 390px;
    padding: 2rem 1rem;
  }
}

@media (prefers-reduced-motion: reduce) {
  .barber-cta__button {
    transition: none;
  }
}
</style>
