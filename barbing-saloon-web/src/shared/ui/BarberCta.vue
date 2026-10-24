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
      <h1 class="barber-cta__title" :style="{ color: resolvedTitleColor }">
        {{ title.replace(/\s*\n\s*/g, ' ').trim() }}
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
  subtitle: { type: String, default: 'Expert barbers. Fresh fades. A better kind of barbershop experience.' },
  caption: { type: String, default: '' },
  titleColor: { type: String, default: '#ffb84d' },
  primaryText: { type: String, default: 'Book your cut' },
  secondaryText: { type: String, default: 'Contact the shop' },
  secondaryLink: { type: String, default: '/contact' },
  backgroundImage: { type: String, default: '' },
  imageOpacity: { type: [Number, String], default: null },
  overlayOpacity: { type: [Number, String], default: null },
  compact: { type: Boolean, default: false }
});

const resolvedTitleColor = computed(() => (
  /^#[\da-f]{6}$/i.test(props.titleColor) ? props.titleColor : '#ffb84d'
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
    radial-gradient(ellipse at 50% 48%, rgba(255, 153, 0, 0.075), transparent 58%),
    #090909;
  color: #f5f5f5;
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
  background: linear-gradient(180deg, rgba(9, 9, 9, 0.4), rgba(9, 9, 9, 0.55));
  opacity: 0.78;
}

.barber-cta__dot-field {
  z-index: -1;
  background-image: radial-gradient(rgba(255, 184, 77, 0.24) 0.7px, transparent 0.7px);
  background-position: center 12px;
  background-size: 12px 12px;
  mask-image: radial-gradient(ellipse at center, black 8%, transparent 76%);
  opacity: 0.28;
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
  border: 1px solid rgba(255, 153, 0, 0.25);
  border-radius: 999px;
  padding: 0.25rem 0.7rem 0.25rem 0.35rem;
  background: rgba(13, 13, 13, 0.68);
  color: #e7e7e7;
  font-size: 0.75rem;
  text-decoration: none;
  transition: border-color 160ms ease, background-color 160ms ease;
}

.barber-cta__announcement:hover {
  border-color: rgba(255, 153, 0, 0.5);
  background: rgba(35, 28, 17, 0.85);
}

.barber-cta__announcement-badge {
  border: 1px solid rgba(255, 153, 0, 0.35);
  border-radius: 999px;
  padding: 0.14rem 0.4rem;
  color: #ffb84d;
  font-size: 0.63rem;
  font-weight: 700;
  letter-spacing: 0.04em;
}

.barber-cta__title {
  margin: 0;
  font-size: clamp(2.5rem, 6vw, 4.15rem);
  font-weight: 700;
  letter-spacing: -0.055em;
  line-height: 1.06;
  text-wrap: balance;
}

.barber-cta__subtitle {
  max-width: 38rem;
  margin: 1rem auto 0;
  color: #e5e5e5;
  font-size: clamp(0.9rem, 1.7vw, 1rem);
  letter-spacing: 0.025em;
  line-height: 1.6;
  text-align: center;
  text-wrap: balance;
}

.barber-cta__caption {
  max-width: 34rem;
  margin: 0.5rem auto 0;
  color: #a5a5a5;
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
  border: 1px solid rgba(255, 255, 255, 0.16);
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
  border-color: #ff9900;
  background: #ff9900;
  color: #0d0d0d;
}

.barber-cta__button--primary:hover {
  border-color: #ffb84d;
  background: #ffb84d;
}

.barber-cta__button--secondary {
  border-color: rgba(255, 153, 0, 0.35);
  background: rgba(15, 15, 15, 0.72);
  color: #ffb84d;
}

.barber-cta__button--secondary:hover {
  border-color: rgba(255, 153, 0, 0.65);
  background: rgba(38, 29, 16, 0.88);
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
    font-size: clamp(2.35rem, 10.5vw, 3.5rem);
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
