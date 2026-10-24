<template>
  <section
    class="relative py-16 md:py-24 overflow-hidden bg-obsidian border-y border-white/5 z-20 shadow-2xl"
    aria-label="CandyCutz Achievements and Key Statistics"
    ref="sectionRef"
  >
    <!-- Background Ambient Lighting & Gradients -->
    <div
      class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_80%_60%_at_50%_0%,rgba(255,153,0,0.08),transparent_70%)]"
      aria-hidden="true"
    ></div>
    <div
      class="pointer-events-none absolute -bottom-32 left-1/2 -translate-x-1/2 w-[600px] h-[300px] bg-gold/5 blur-[120px] rounded-full"
      aria-hidden="true"
    ></div>

    <!-- Top Accent Hairline -->
    <div
      class="pointer-events-none absolute top-0 inset-x-0 h-[1px] bg-gradient-to-r from-transparent via-gold/40 to-transparent"
      aria-hidden="true"
    ></div>

    <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <!-- Standardized Section Header -->
      <div class="text-center max-w-3xl mx-auto mb-12 md:mb-16" data-reveal>
        <h2 class="font-display text-3xl sm:text-4xl lg:text-5xl text-gold drop-shadow-sm text-center">
          Proven Craftsmanship by the Numbers
        </h2>
        <p class="mt-4 text-sm sm:text-base text-theme-muted font-light leading-relaxed text-center mx-auto max-w-2xl">
          Every cut is a statement of precision, passion, and personal style.
        </p>
      </div>

      <!-- Statistics Grid -->
      <div
        class="grid gap-5 sm:gap-6 items-stretch"
        :class="statItems.length >= 4 ? 'grid-cols-1 min-[480px]:grid-cols-2 lg:grid-cols-4' : 'grid-cols-1 sm:grid-cols-3'"
        data-reveal
      >
        <div
          v-for="(stat, index) in statItems"
          :key="stat.id"
          class="group relative flex flex-col items-center gap-3 rounded-2xl p-6 sm:p-7 lg:p-8 text-center transition-all duration-300 hover:-translate-y-1 cursor-default overflow-hidden border border-white/10 hover:border-gold/50 bg-gradient-to-b from-white/[0.05] via-white/[0.02] to-transparent backdrop-blur-xl shadow-[inset_0_1px_1px_rgba(255,255,255,0.08),_0_12px_24px_-10px_rgba(0,0,0,0.6)] hover:shadow-[0_18px_36px_-10px_rgba(255,153,0,0.2),_inset_0_0_18px_rgba(255,153,0,0.04)]"
          :style="{ '--reveal-delay': `${index * 0.15}s` }"
        >
          <!-- Corner Decor Crosshairs -->
          <DecorIcon position="top-right" class="opacity-30 group-hover:opacity-80 group-hover:stroke-gold transition-opacity" />
          <DecorIcon position="bottom-left" class="opacity-30 group-hover:opacity-80 group-hover:stroke-gold transition-opacity" />

          <!-- Top Card Edge Glow Light Rim -->
          <div
            class="pointer-events-none absolute top-0 inset-x-6 h-[1px] bg-gradient-to-r from-transparent via-gold/60 to-transparent opacity-40 group-hover:opacity-100 transition-opacity duration-500"
            aria-hidden="true"
          ></div>

          <!-- Shiny Hover Sweep / Sheen -->
          <div
            class="pointer-events-none absolute -inset-full bg-gradient-to-r from-transparent via-white/[0.04] to-transparent -rotate-45 -translate-x-full group-hover:translate-x-full transition-transform duration-1000 ease-out"
            aria-hidden="true"
          ></div>

          <!-- Card Header: Glowing Icon Vessel & Rating -->
          <div class="flex flex-col items-center">
            <div
              class="relative flex items-center justify-center size-12 sm:size-14 rounded-xl bg-gradient-to-b from-gold/20 via-gold/10 to-transparent border border-gold/30 text-gold shadow-[0_0_15px_rgba(255,153,0,0.15)] transition-all duration-300 group-hover:scale-105 group-hover:shadow-[0_0_24px_rgba(255,153,0,0.3)] group-hover:border-gold/60"
            >
              <component
                :is="stat.icon"
                class="size-6 sm:size-7 transition-transform duration-300 group-hover:rotate-6"
              />
            </div>

            <!-- 5-Star Rating Indicator for Clients -->
            <div
              v-if="stat.ratingStars"
              class="flex items-center gap-1 mt-2"
              aria-label="5 Star Rated"
            >
              <Star
                v-for="s in 5"
                :key="s"
                class="size-2.5 sm:size-3 fill-gold text-gold transition-transform duration-300 group-hover:scale-110"
              />
            </div>
            <div v-else class="h-2.5 sm:h-3 mt-2"></div>
          </div>

          <!-- Card Body: Numeric Counter & Primary Label -->
          <div class="text-center w-full">
            <div
              class="inline-flex items-baseline justify-center"
              :aria-label="stat.ariaLabel"
            >
              <span
                class="font-display text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight tabular-nums bg-gradient-to-b from-[#FFF5DC] via-[#FFB84D] to-[#D97706] bg-clip-text text-transparent drop-shadow-[0_2px_10px_rgba(255,153,0,0.22)] select-none"
              >
                {{ splitValue(displayValues[stat.id] || stat.value).num }}
              </span>
              <span
                v-if="splitValue(displayValues[stat.id] || stat.value).suffix"
                class="font-display text-xl sm:text-2xl lg:text-3xl font-bold tracking-tight bg-gradient-to-b from-[#FFB84D] to-[#D97706] bg-clip-text text-transparent ml-1 drop-shadow-sm select-none"
              >
                {{ splitValue(displayValues[stat.id] || stat.value).suffix }}
              </span>
            </div>

            <h3
              class="mt-2 text-xs sm:text-sm font-bold uppercase tracking-widest text-ivory/90 group-hover:text-gold transition-colors duration-300 text-center"
            >
              {{ stat.label }}
            </h3>
          </div>

        </div>
      </div>

      <!-- Bottom Reassurance Strip -->
      <div
        class="mt-12 sm:mt-16 pt-6 border-t border-white/5 flex flex-wrap items-center justify-center gap-4 sm:gap-8 text-xs text-ivory/60 uppercase tracking-wider"
        data-reveal
      >
        <span class="flex items-center gap-2">
          <Award class="size-4 text-gold shrink-0" />
          Master Barbers Certified
        </span>
        <span class="hidden sm:inline text-gold/30 select-none">◆</span>
        <span class="flex items-center gap-2">
          <Sparkles class="size-4 text-gold shrink-0" />
          Luxury Grooming Lounge
        </span>
        <span class="hidden sm:inline text-gold/30 select-none">◆</span>
        <span class="flex items-center gap-2">
          <Clock class="size-4 text-gold shrink-0" />
          Walk-Ins & Online Bookings
        </span>
      </div>
    </div>

    <!-- Bottom Accent Hairline -->
    <div
      class="pointer-events-none absolute bottom-0 inset-x-0 h-[1px] bg-gradient-to-r from-transparent via-gold/30 to-transparent"
      aria-hidden="true"
    ></div>
  </section>
</template>

<script setup>
import { computed, ref, reactive, onMounted, onUnmounted, watch } from 'vue';
import {
  Award,
  Scissors,
  Users,
  Star,
  Sparkles,
  Clock,
} from 'lucide-vue-next';
import DecorIcon from './DecorIcon.vue';

const props = defineProps({
  years: {
    type: String,
    default: '10+',
  },
  services: {
    type: String,
    default: '20+',
  },
  clients: {
    type: String,
    default: '5k+',
  },
  barbers: {
    type: String,
    default: '',
  },
});

const sectionRef = ref(null);
const hasAnimated = ref(false);

const statItems = computed(() => {
  const items = [
    {
      id: 'years',
      value: props.years || '10+',
      label: 'Years Experience',
      icon: Award,
      ratingStars: false,
      ariaLabel: `${props.years || '10+'} Years of Experience`,
    },
    {
      id: 'services',
      value: props.services || '20+',
      label: 'Premium Services',
      icon: Scissors,
      ratingStars: false,
      ariaLabel: `${props.services || '20+'} Premium Services Available`,
    },
    {
      id: 'clients',
      value: props.clients || '5k+',
      label: 'Happy Clients',
      icon: Users,
      ratingStars: true,
      ariaLabel: `${props.clients || '5k+'} Satisfied Clients`,
    },
  ];

  if (props.barbers) {
    items.splice(2, 0, {
      id: 'barbers',
      value: props.barbers,
      label: 'Master Barbers',
      icon: Scissors,
      ratingStars: false,
      ariaLabel: `${props.barbers} Master Barbers on Duty`,
    });
  }

  return items;
});

// Reactive object holding the displayed animated text for each stat
const displayValues = reactive({
  years: props.years || '10+',
  services: props.services || '20+',
  barbers: props.barbers || '5+',
  clients: props.clients || '5k+',
});

// Helper: split string into numeric part and suffix for refined styling
const splitValue = (val) => {
  if (!val) return { num: '', suffix: '' };
  const str = String(val).trim();
  const match = str.match(/^([0-9.,]+)\s*([a-zA-Z%+]+)$/);
  if (match) {
    return { num: match[1], suffix: match[2] };
  }
  return { num: str, suffix: '' };
};

// Helper: parse string like "10+", "20+", "5k+", "5,000+"
const parseStatValue = (val) => {
  if (!val || typeof val !== 'string') return null;
  const match = val.trim().match(/^([0-9.,]+)\s*([a-zA-Z%+]*)$/);
  if (!match) return null;
  const rawNum = parseFloat(match[1].replace(/,/g, ''));
  if (isNaN(rawNum)) return null;
  return {
    target: rawNum,
    suffix: match[2] || '',
    hasCommas: match[1].includes(','),
  };
};

// Smooth easing counter animation
const animateNumbers = () => {
  if (hasAnimated.value) return;
  hasAnimated.value = true;

  // Respect prefers-reduced-motion
  if (typeof window !== 'undefined' && window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    displayValues.years = props.years;
    displayValues.services = props.services;
    displayValues.barbers = props.barbers;
    displayValues.clients = props.clients;
    return;
  }

  const duration = 1600; // ms
  const startTime = performance.now();

  const parsed = {
    years: parseStatValue(props.years),
    services: parseStatValue(props.services),
    barbers: parseStatValue(props.barbers),
    clients: parseStatValue(props.clients),
  };

  const step = (now) => {
    const elapsed = now - startTime;
    const progress = Math.min(elapsed / duration, 1);
    // Cubic ease-out curve
    const easeProgress = 1 - Math.pow(1 - progress, 3);

    statItems.value.forEach((item) => {
      const p = parsed[item.id];
      if (!p) {
        displayValues[item.id] = item.value;
        return;
      }

      const currentVal = Math.round(p.target * easeProgress);
      const formatted = p.hasCommas ? currentVal.toLocaleString() : currentVal.toString();
      displayValues[item.id] = `${formatted}${p.suffix}`;
    });

    if (progress < 1) {
      requestAnimationFrame(step);
    } else {
      // Final ensure exact value
      displayValues.years = props.years;
      displayValues.services = props.services;
      displayValues.barbers = props.barbers;
      displayValues.clients = props.clients;
    }
  };

  requestAnimationFrame(step);
};

// Keep displayValues in sync if props change after fetch
watch(
  () => [props.years, props.services, props.clients, props.barbers],
  ([y, s, c, b]) => {
    displayValues.years = y;
    displayValues.services = s;
    displayValues.clients = c;
    displayValues.barbers = b;
  }
);

let observer = null;

onMounted(() => {
  if (typeof window !== 'undefined' && 'IntersectionObserver' in window && sectionRef.value) {
    observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            animateNumbers();
            observer?.disconnect();
          }
        });
      },
      { threshold: 0.15 }
    );
    observer.observe(sectionRef.value);
  } else {
    animateNumbers();
  }
});

onUnmounted(() => {
  observer?.disconnect();
});
</script>
