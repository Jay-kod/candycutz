<template>
  <section class="features-section relative py-16 md:py-24 overflow-hidden bg-[var(--color-bg-primary)] border-y border-[var(--color-border-subtle)] transition-colors duration-300">
    <!-- Ambient Glow -->
    <div class="absolute inset-0 pointer-events-none bg-[radial-gradient(ellipse_at_center,rgba(201,168,76,0.06),transparent_70%)]"></div>

    <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <!-- Section Header -->
      <div v-if="displayTitle || displaySubtitle" class="text-center max-w-3xl mx-auto mb-12 md:mb-16" data-reveal>
        <h2 v-if="displayTitle" class="font-display text-3xl sm:text-4xl lg:text-5xl text-[var(--color-accent)] drop-shadow-sm">
          {{ displayTitle }}
        </h2>
        <p v-if="displaySubtitle" class="mt-4 text-sm sm:text-base text-[var(--color-text-secondary)] font-light leading-relaxed text-center mx-auto max-w-2xl">
          {{ displaySubtitle }}
        </p>
      </div>

      <!-- Features Block Grid (efferd/features-1) -->
      <div class="w-full" data-reveal>
        <div class="grid grid-cols-1 gap-5 min-[480px]:grid-cols-2 lg:grid-cols-4 sm:gap-6">
          <div
            v-for="(feature, index) in featureList"
            :key="feature.title || index"
            class="feature-card group relative flex flex-col items-center justify-center rounded-2xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] p-6 text-center shadow-md shadow-[var(--color-shadow)] transition-all duration-300 hover:-translate-y-1 hover:border-[var(--color-accent)] hover:shadow-xl sm:p-7"
          >
            <!-- Feature Icon -->
            <div class="relative flex items-center justify-center size-12 rounded-xl bg-[var(--color-accent)]/10 border border-[var(--color-accent)]/20 text-[var(--color-accent)] transition-all duration-300 group-hover:scale-110 group-hover:bg-[var(--color-accent)]/20 group-hover:shadow-[0_0_20px_rgba(201,168,76,0.3)]">
              <component :is="resolveIcon(feature.icon)" class="size-6 transition-transform group-hover:rotate-6" />
            </div>

            <!-- Title -->
            <h3 class="font-heading mt-4 font-semibold text-sm md:text-base text-[var(--color-text-primary)] group-hover:text-[var(--color-accent)] transition-colors">
              {{ feature.title }}
            </h3>

            <!-- Description -->
            <p class="mt-2 text-xs text-[var(--color-text-secondary)] leading-relaxed max-w-[200px] text-center mx-auto">
              {{ feature.description }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed } from 'vue';
import {
  Zap,
  ShieldCheck,
  Activity,
  Globe,
  Scissors,
  Sparkles,
  Clock,
  Award,
  Star,
  Crown,
  Heart,
  Flame,
  Layers,
  Calendar,
  Smartphone,
  CheckCircle,
} from 'lucide-vue-next';

const iconMap = {
  zap: Zap,
  shield: ShieldCheck,
  'shield-check': ShieldCheck,
  activity: Activity,
  globe: Globe,
  scissors: Scissors,
  sparkles: Sparkles,
  clock: Clock,
  award: Award,
  star: Star,
  crown: Crown,
  heart: Heart,
  flame: Flame,
  layers: Layers,
  calendar: Calendar,
  smartphone: Smartphone,
  check: CheckCircle,
};

const defaultFeatures = [
  {
    title: 'Precision Craft',
    icon: 'scissors',
    description: 'Master barbers dedicated to flawless fades, lines, and custom contours.',
  },
  {
    title: 'Luxury Lounge',
    icon: 'crown',
    description: 'Exclusive ambiance with bespoke amenities and complimentary refreshments.',
  },
  {
    title: 'Instant Booking',
    icon: 'zap',
    description: 'Real-time schedule availability and effortless queue coordination.',
  },
  {
    title: 'Sanitized & Safe',
    icon: 'shield-check',
    description: 'Hospital-grade tool sterilization and highest hygienic standards.',
  },
];

const props = defineProps({
  badge: {
    type: String,
    default: '',
  },
  title: {
    type: String,
    default: '',
  },
  subtitle: {
    type: String,
    default: '',
  },
  features: {
    type: [Array, String],
    default: () => [],
  },
});

const displayBadge = computed(() => props.badge || 'WHY CHOOSE CANDYCUTZ');
const displayTitle = computed(() => props.title || 'Engineered for Perfection');
const displaySubtitle = computed(
  () => props.subtitle || 'Every detail of the CandyCutz experience is calibrated to deliver the pinnacle of men’s grooming.'
);

const featureList = computed(() => {
  let list = props.features;
  if (typeof list === 'string') {
    try {
      list = JSON.parse(list);
    } catch {
      list = [];
    }
  }
  if (!Array.isArray(list) || list.length === 0) {
    return defaultFeatures;
  }
  return list;
});

const resolveIcon = (iconName) => {
  if (!iconName) return Sparkles;
  const key = String(iconName).toLowerCase().trim();
  return iconMap[key] || Sparkles;
};
</script>

<style scoped>
:global([data-theme="light"]) .features-section {
  background-color: var(--color-surface, #FFFFFF);
  border-color: rgba(0, 0, 0, 0.08);
}

:global([data-theme="light"]) .features-section h2 {
  color: #CC7A00;
}

:global([data-theme="light"]) .feature-card {
  background-color: #FFFFFF;
  border-color: rgba(0, 0, 0, 0.08);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
}

:global([data-theme="light"]) .feature-card:hover {
  background-color: #FFFFFF;
  border-color: rgba(255, 153, 0, 0.5);
  box-shadow: 0 12px 30px rgba(255, 153, 0, 0.12);
}
</style>
