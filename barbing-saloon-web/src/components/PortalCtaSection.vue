<template>
  <section class="portal-cta" :class="{ 'portal-cta--compact': compact }">
    <!-- Ambient Top Spotlight (matches reference image glow) -->
    <div class="portal-cta__spotlight" aria-hidden="true"></div>

    <!-- Orbiting Floating Badges (10 feature cards surrounding center) -->
    <div class="portal-cta__constellation" aria-hidden="true">
      <div
        v-for="(card, index) in cards"
        :key="card.name"
        class="portal-cta__card"
        :class="card.className"
        :style="{
          top: card.top,
          left: card.left,
          '--rotate-base': card.rotate,
          '--float-duration': `${7 + (index % 4) * 1.5}s`,
          animationDelay: `${-(index % 5) * 1.1}s`
        }"
      >
        <component :is="card.icon" class="h-6 w-6 sm:h-7 sm:w-7 text-gold-light" aria-hidden="true" />
      </div>
    </div>

    <!-- Centered Content -->
    <div class="portal-cta__content !text-center" data-reveal>
      <p v-if="eyebrow" class="portal-cta__eyebrow !text-center">
        {{ eyebrow }}
      </p>

      <h2 class="portal-cta__title !text-center">
        {{ title }}
      </h2>

      <p class="portal-cta__description !text-center">
        {{ description }}
      </p>

      <!-- Action Buttons (High contrast Primary + Glass Secondary) -->
      <div class="portal-cta__actions">
        <RouterLink :to="primaryLink" class="portal-cta__btn portal-cta__btn--primary">
          <span>{{ primaryText }}</span>
          <ArrowUpRightIcon class="h-4 w-4 stroke-[2.5]" aria-hidden="true" />
        </RouterLink>

        <RouterLink
          v-if="secondaryText && secondaryLink"
          :to="secondaryLink"
          class="portal-cta__btn portal-cta__btn--secondary"
        >
          <span>{{ secondaryText }}</span>
        </RouterLink>
      </div>
    </div>
  </section>
</template>

<script setup>
import { RouterLink } from 'vue-router';
import {
  ArrowUpRightIcon,
  ScissorsIcon
} from '@heroicons/vue/24/outline';
import {
  BrushIcon,
  BrushCleaningIcon,
  DropletsIcon,
  ScissorsLineDashedIcon,
  SparklesIcon,
  SprayCanIcon,
  SquareScissorsIcon,
  WavesIcon,
  WindIcon
} from 'lucide-vue-next';

defineProps({
  eyebrow: {
    type: String,
    default: 'The CandyCutz Portal'
  },
  title: {
    type: String,
    default: 'Unlock the Full Experience'
  },
  description: {
    type: String,
    default:
      'Create an account to browse our extensive list of services, check barber availability in real-time, view our exclusive gallery, and manage your appointments seamlessly from your own personalized dashboard.'
  },
  primaryText: {
    type: String,
    default: 'Log in to Customer Portal'
  },
  primaryLink: {
    type: String,
    default: '/customer/login'
  },
  secondaryText: {
    type: String,
    default: 'Create Account'
  },
  secondaryLink: {
    type: String,
    default: '/customer/register'
  },
  compact: {
    type: Boolean,
    default: false
  }
});

// 10 barbering and grooming tools positioned around the center text
const cards = [
  // --- Left Side Arc ---
  {
    name: 'scissors',
    icon: ScissorsIcon,
    top: '26%',
    left: '10%',
    rotate: '-8deg',
    className: 'hidden sm:flex'
  },
  {
    name: 'hair-brush',
    icon: BrushIcon,
    top: '22%',
    left: '23%',
    rotate: '-16deg',
    className: 'hidden md:flex'
  },
  {
    name: 'grooming-brush',
    icon: BrushCleaningIcon,
    top: '47%',
    left: '12%',
    rotate: '12deg',
    className: 'flex'
  },
  {
    name: 'hair-texture',
    icon: WavesIcon,
    top: '52%',
    left: '23%',
    rotate: '-8deg',
    className: 'hidden lg:flex'
  },
  {
    name: 'scissor-cut',
    icon: ScissorsLineDashedIcon,
    top: '68%',
    left: '26%',
    rotate: '8deg',
    className: 'hidden sm:flex'
  },

  // --- Right Side Arc ---
  {
    name: 'hair-dryer',
    icon: WindIcon,
    top: '22%',
    left: '77%',
    rotate: '8deg',
    className: 'hidden md:flex'
  },
  {
    name: 'styling-spray',
    icon: SprayCanIcon,
    top: '28%',
    left: '88%',
    rotate: '-12deg',
    className: 'hidden sm:flex'
  },
  {
    name: 'hair-care',
    icon: SparklesIcon,
    top: '47%',
    left: '84%',
    rotate: '14deg',
    className: 'flex'
  },
  {
    name: 'shampoo',
    icon: DropletsIcon,
    top: '59%',
    left: '78%',
    rotate: '-6deg',
    className: 'hidden lg:flex'
  },
  {
    name: 'barber-scissors',
    icon: SquareScissorsIcon,
    top: '67%',
    left: '89%',
    rotate: '12deg',
    className: 'hidden sm:flex'
  }
];
</script>

<style scoped>
.portal-cta {
  position: relative;
  isolation: isolate;
  display: flex;
  min-height: 420px;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  padding: 3rem 1.5rem;
  background-color: #070708;
  color: #fafafa;
}

/* Ambient Top-Center Spotlight Glow */
.portal-cta__spotlight {
  position: absolute;
  inset: 0;
  pointer-events: none;
  z-index: 1;
  background:
    radial-gradient(ellipse 70% 45% at 50% 0%, rgba(255, 255, 255, 0.08), transparent 70%),
    radial-gradient(ellipse 50% 30% at 50% 12%, rgba(255, 153, 0, 0.08), transparent 60%);
}

/* Constellation Container */
.portal-cta__constellation {
  position: absolute;
  inset: 0;
  pointer-events: none;
  z-index: 2;
}

/* Floating Squircle Feature Cards */
.portal-cta__card {
  position: absolute;
  display: flex;
  width: 68px;
  height: 68px;
  align-items: center;
  justify-content: center;
  border-radius: 20px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(22, 22, 24, 0.78);
  box-shadow:
    0 12px 35px rgba(0, 0, 0, 0.7),
    inset 0 1px 0 rgba(255, 255, 255, 0.08);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  color: rgba(255, 255, 255, 0.88);
  pointer-events: auto;
  cursor: default;
  transform: translate(0, 0) rotate(var(--rotate-base, 0deg));
  animation: portal-card-float var(--float-duration, 8s) ease-in-out infinite;
  transition:
    transform 0.3s cubic-bezier(0.16, 1, 0.3, 1),
    border-color 0.3s ease,
    box-shadow 0.3s ease;
}

.portal-cta__card:hover {
  transform: translate(0, -6px) rotate(var(--rotate-base, 0deg)) scale(1.1);
  border-color: rgba(255, 153, 0, 0.5);
  box-shadow:
    0 16px 40px rgba(0, 0, 0, 0.85),
    0 0 24px rgba(255, 153, 0, 0.2),
    inset 0 1px 0 rgba(255, 255, 255, 0.15);
  color: #ffffff;
  z-index: 10;
}

/* Center Content */
.portal-cta__content {
  position: relative;
  z-index: 3;
  width: 100%;
  max-width: 640px;
  margin: 0 auto;
  text-align: center;
}

.portal-cta__eyebrow {
  display: block;
  width: 100%;
  text-align: center !important;
  margin-bottom: 1rem;
  color: #ff9900;
  font-size: 0.75rem;
  font-weight: 600;
  letter-spacing: 0.25em;
  text-transform: uppercase;
}

.portal-cta__title {
  display: block;
  width: 100%;
  text-align: center !important;
  margin: 0;
  font-family: inherit;
  font-size: clamp(2.2rem, 4.5vw, 3.25rem);
  font-weight: 600;
  letter-spacing: -0.04em;
  line-height: 1.1;
  color: #ffffff;
  text-wrap: balance;
}

.portal-cta__description {
  display: block;
  width: 100%;
  text-align: center !important;
  max-width: 34rem;
  margin: 0.9rem auto 0;
  color: #a1a1aa;
  font-size: clamp(0.9rem, 1.5vw, 1rem);
  line-height: 1.5;
  text-wrap: balance;
}

/* Buttons */
.portal-cta__actions {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: center;
  gap: 0.85rem;
  margin-top: 1.5rem;
}

.portal-cta__btn {
  display: inline-flex;
  min-height: 48px;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.75rem 1.4rem;
  border-radius: 12px;
  font-size: 0.9375rem;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

.portal-cta__btn:hover {
  transform: translateY(-2px);
}

.portal-cta__btn:active {
  transform: translateY(0) scale(0.98);
}

/* Primary button uses the CandyCutz gold accent. */
.portal-cta__btn--primary {
  background-color: #ff9900;
  color: #0d0d0d;
  border: 1px solid #ff9900;
  box-shadow: 0 4px 20px rgba(255, 153, 0, 0.16);
}

.portal-cta__btn--primary:hover {
  background-color: #ffb84d;
  border-color: #ffb84d;
  box-shadow: 0 6px 28px rgba(255, 153, 0, 0.28);
}

/* Secondary button keeps the dark surface with a gold hairline accent. */
.portal-cta__btn--secondary {
  background-color: rgba(24, 24, 27, 0.7);
  color: #ffb84d;
  border: 1px solid rgba(255, 153, 0, 0.3);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
}

.portal-cta__btn--secondary:hover {
  background-color: rgba(39, 29, 16, 0.88);
  border-color: rgba(255, 153, 0, 0.55);
}

/* Keyframe Float Animation with Subtle Tilt Jitter */
@keyframes portal-card-float {
  0%, 100% {
    transform: translate(0, 0) rotate(var(--rotate-base, 0deg));
  }
  50% {
    transform: translate(0, -10px) rotate(calc(var(--rotate-base, 0deg) + 2deg));
  }
}

/* Responsive adjustments */
@media (max-width: 1024px) {
  .portal-cta__card {
    width: 58px;
    height: 58px;
    border-radius: 17px;
  }
}

@media (max-width: 640px) {
  .portal-cta {
    min-height: 360px;
    padding: 2.25rem 1.25rem;
  }

  .portal-cta__card {
    width: 48px;
    height: 48px;
    border-radius: 14px;
  }

  .portal-cta__card :deep(svg) {
    width: 20px;
    height: 20px;
  }

  .portal-cta__title {
    font-size: clamp(2rem, 8vw, 2.5rem);
  }

  .portal-cta__description {
    margin-top: 0.75rem;
    font-size: 0.9rem;
  }

  .portal-cta__actions {
    flex-direction: column;
    width: 100%;
    max-width: 280px;
    margin: 1.25rem auto 0;
  }

  .portal-cta__btn {
    width: 100%;
  }
}

/* Accessibility / Reduced Motion */
@media (prefers-reduced-motion: reduce) {
  .portal-cta__card {
    animation: none;
  }

  .portal-cta__btn,
  .portal-cta__card {
    transition: none;
  }
}
</style>
