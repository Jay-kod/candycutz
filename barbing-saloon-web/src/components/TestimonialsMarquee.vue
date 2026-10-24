<template>
  <section
    class="relative overflow-hidden border-y border-white/5 bg-obsidian py-16 text-theme-text md:py-24"
    aria-labelledby="home-testimonials-title"
  >
    <!-- Ambient Glow -->
    <div
      class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_70%_60%_at_50%_0%,rgba(255,153,0,0.06),transparent_70%)]"
      aria-hidden="true"
    ></div>

    <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 mb-12 md:mb-16">
      <header class="text-center max-w-3xl mx-auto" data-reveal>
        <h2 id="home-testimonials-title" class="font-display text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight text-gold">
          {{ title }}
        </h2>
        <p class="mt-4 text-sm sm:text-base leading-relaxed text-theme-muted max-w-2xl mx-auto">
          {{ subtitle }}
        </p>
      </header>
    </div>

    <p v-if="loading" class="mx-auto max-w-7xl px-4 text-center text-sm text-theme-muted">
      Loading approved testimonials...
    </p>
    <p v-else-if="!visibleTestimonials.length" class="mx-auto max-w-7xl px-4 text-center text-sm text-theme-muted">
      Approved customer testimonials will appear here.
    </p>

    <div v-else class="testimonials-marquee relative w-full overflow-hidden border-y border-white/10 bg-[#0C0C0E]/95 py-0 shadow-2xl">
      <div
        v-for="(loop, loopIndex) in 2"
        :key="loopIndex"
        class="testimonials-track flex shrink-0 items-stretch"
        :class="direction === 'right' ? 'testimonials-track-right' : 'testimonials-track-left'"
        :style="{ '--testimonials-marquee-duration': `${speed}s` }"
        :aria-hidden="loopIndex === 1 ? 'true' : undefined"
      >
        <article
          v-for="(testimonial, idx) in visibleTestimonials"
          :key="`${loopIndex}-${testimonial.id || idx}`"
          class="group/card flex w-[320px] flex-1 shrink-0 flex-col justify-between border-r border-white/10 bg-[#0C0C0E] p-6 transition-colors duration-200 hover:bg-white/[0.035] sm:w-[380px] sm:p-7 md:w-[420px] md:p-8"
        >
          <!-- Card Header: Quote Icon + Source Pill -->
          <div class="flex items-center justify-between gap-4">
            <div class="text-zinc-500 transition-colors group-hover/card:text-gold/80">
              <svg class="size-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z" />
              </svg>
            </div>
            <span class="inline-flex items-center rounded-full border border-zinc-700/60 bg-zinc-800/50 px-3 py-1 text-xs font-medium text-zinc-300">
              {{ getSource(testimonial) }}
            </span>
          </div>

          <!-- Review Text -->
          <p class="my-6 text-sm sm:text-base leading-relaxed text-zinc-100 font-normal">
            "{{ testimonial.review || testimonial.comment }}"
          </p>

          <!-- Bottom: Author Avatar, Name & Role -->
          <div class="flex items-center gap-3 pt-2 border-t border-white/5">
            <img
              v-if="testimonial.client_avatar && !failedAvatars.has(testimonial.id)"
              :src="getStorageUrl(testimonial.client_avatar)"
              :alt="`${testimonial.client_name} avatar`"
              class="size-10 sm:size-11 rounded-full object-cover border border-zinc-700/60 shrink-0"
              loading="lazy"
              @error="markAvatarFailed(testimonial.id)"
            />
            <div
              v-else
              class="flex size-10 sm:size-11 shrink-0 items-center justify-center rounded-full border border-gold/30 bg-gold/10 text-xs sm:text-sm font-bold text-gold"
              aria-hidden="true"
            >
              {{ getInitials(testimonial.client_name) }}
            </div>
            <div class="min-w-0 flex-1">
              <p class="truncate text-sm sm:text-base font-semibold text-white tracking-tight">
                {{ testimonial.client_name || 'CandyCutz client' }}
              </p>
              <p class="truncate text-xs text-zinc-400 mt-0.5">
                {{ getSubtitle(testimonial) }}
              </p>
            </div>
          </div>
        </article>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, ref } from 'vue';
import { getStorageUrl } from '@/core/utils/url';

const props = defineProps({
  testimonials: {
    type: Array,
    default: () => [],
  },
  loading: {
    type: Boolean,
    default: false,
  },
  speed: {
    type: Number,
    default: 45,
  },
  direction: {
    type: String,
    default: 'left',
    validator: (value) => ['left', 'right'].includes(value),
  },
  badge: {
    type: String,
    default: 'Client stories',
  },
  title: {
    type: String,
    default: 'What our clients say',
  },
  subtitle: {
    type: String,
    default: 'A look at the kind of premium experience customers can expect.',
  },
});

const failedAvatars = ref(new Set());

const visibleTestimonials = computed(() => (
  (Array.isArray(props.testimonials) ? props.testimonials : [])
    .filter((item) => item.is_approved === true && item.is_featured === true && (item.review || item.comment))
));

const markAvatarFailed = (id) => {
  failedAvatars.value = new Set([...failedAvatars.value, id]);
};

const getInitials = (name) => {
  const words = (name || 'Client').trim().split(/\s+/).filter(Boolean);
  return words.slice(0, 2).map((w) => w[0].toUpperCase()).join('');
};

const getSubtitle = (item) => {
  if (item.client_name?.startsWith('Sample Customer ')) return 'Demo sample — not a customer review';
  if (item.service?.name) return `Client • ${item.service.name}`;
  if (item.role) return item.role;
  if (item.subtitle) return item.subtitle;
  return 'Customer testimonial';
};

const getSource = (item) => {
  if (item.source) return item.source;
  if (item.client_name?.startsWith('Sample Customer ')) return 'Demo sample';
  return 'x.com';
};
</script>

<style scoped>
.testimonials-marquee {
  display: flex;
}

.testimonials-track {
  min-width: 100%;
  will-change: transform;
  animation-duration: var(--testimonials-marquee-duration, 45s);
  animation-timing-function: linear;
  animation-iteration-count: infinite;
}

.testimonials-track-left {
  animation-name: testimonials-scroll-left;
}

.testimonials-track-right {
  animation-name: testimonials-scroll-right;
}

@keyframes testimonials-scroll-left {
  from { transform: translateX(0); }
  to { transform: translateX(-100%); }
}

@keyframes testimonials-scroll-right {
  from { transform: translateX(-100%); }
  to { transform: translateX(0); }
}

@media (prefers-reduced-motion: reduce) {
  .testimonials-track {
    animation: none;
  }
}
</style>
