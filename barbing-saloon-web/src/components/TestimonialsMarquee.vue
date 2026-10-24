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

    <div class="relative z-10 mx-auto max-w-7xl px-4 md:px-6 mb-10 md:mb-14">
      <header class="max-w-2xl" data-reveal>
        <p class="text-xs font-bold uppercase tracking-[0.25em] text-gold">{{ badge }}</p>
        <h2 id="home-testimonials-title" class="mt-3 font-display text-3xl font-bold tracking-tight text-ivory sm:text-4xl md:text-5xl">
          {{ title }}
        </h2>
        <p class="mt-3 text-sm leading-relaxed text-theme-muted sm:text-base">
          {{ subtitle }}
        </p>
      </header>
    </div>

    <!-- Infinite Testimonials Line (Marquee) -->
    <div class="relative w-full overflow-hidden border-y border-white/10 bg-[#0C0C0E]/95 py-0 shadow-2xl">
      <!-- Gradient Edge Fade Masks -->
      <div
        class="pointer-events-none absolute left-0 top-0 bottom-0 z-20 w-16 sm:w-28 md:w-36 bg-gradient-to-r from-obsidian via-obsidian/80 to-transparent"
        aria-hidden="true"
      ></div>
      <div
        class="pointer-events-none absolute right-0 top-0 bottom-0 z-20 w-16 sm:w-28 md:w-36 bg-gradient-to-l from-obsidian via-obsidian/80 to-transparent"
        aria-hidden="true"
      ></div>

      <!-- Marquee Moving Track -->
      <div class="marquee-container flex select-none overflow-hidden group">
        <!-- First Loop -->
        <div class="marquee-track flex shrink-0 items-stretch">
          <article
            v-for="(testimonial, idx) in trackItems"
            :key="`track1-${testimonial.id || idx}`"
            class="group/card flex w-[320px] sm:w-[380px] md:w-[420px] shrink-0 flex-col justify-between border-r border-white/10 bg-[#0C0C0E] p-6 sm:p-7 md:p-8 transition-colors duration-200 hover:bg-white/[0.035]"
          >
            <!-- Card Header: Quote Icon + Source Pill -->
            <div class="flex items-center justify-between gap-4">
              <div class="text-zinc-500 transition-colors group-hover/card:text-gold/80">
                <svg class="size-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                  <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z" />
                </svg>
              </div>
              <span class="inline-flex items-center rounded-full border border-zinc-700/60 bg-zinc-800/50 px-3 py-1 text-xs font-medium text-zinc-300">
                {{ testimonial.source || 'x.com' }}
              </span>
            </div>

            <!-- Review Text -->
            <p class="my-6 text-sm sm:text-base leading-relaxed text-zinc-100 font-normal">
              {{ testimonial.review || testimonial.comment }}
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

        <!-- Second Duplicate Loop (Ensures Continuous Infinite Ribbon) -->
        <div class="marquee-track flex shrink-0 items-stretch" aria-hidden="true">
          <article
            v-for="(testimonial, idx) in trackItems"
            :key="`track2-${testimonial.id || idx}`"
            class="group/card flex w-[320px] sm:w-[380px] md:w-[420px] shrink-0 flex-col justify-between border-r border-white/10 bg-[#0C0C0E] p-6 sm:p-7 md:p-8 transition-colors duration-200 hover:bg-white/[0.035]"
          >
            <!-- Card Header: Quote Icon + Source Pill -->
            <div class="flex items-center justify-between gap-4">
              <div class="text-zinc-500 transition-colors group-hover/card:text-gold/80">
                <svg class="size-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                  <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z" />
                </svg>
              </div>
              <span class="inline-flex items-center rounded-full border border-zinc-700/60 bg-zinc-800/50 px-3 py-1 text-xs font-medium text-zinc-300">
                {{ testimonial.source || 'x.com' }}
              </span>
            </div>

            <!-- Review Text -->
            <p class="my-6 text-sm sm:text-base leading-relaxed text-zinc-100 font-normal">
              {{ testimonial.review || testimonial.comment }}
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

const defaultTestimonials = [
  {
    id: 'sample-1',
    client_name: 'Guillermo Rauch',
    role: 'CEO, Vercel',
    source: 'x.com',
    client_avatar: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80',
    review: 'CandyCutz is why I still have sharp hair. No more pulling it out over bad fades or uneven contours. The craft is in safe hands.',
  },
  {
    id: 'sample-2',
    client_name: 'Ibrahim Suleiman',
    role: 'Verified Client • Fade & Beard',
    source: 'x.com',
    client_avatar: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&auto=format&fit=crop&q=80',
    review: 'The fade was clean, precise, and exactly the style I asked for. The shop atmosphere is top-tier and the barbers take real pride in their work.',
  },
  {
    id: 'sample-3',
    client_name: 'Elon Musk',
    role: 'CEO, X.com',
    source: 'x.com',
    client_avatar: 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=150&auto=format&fit=crop&q=80',
    review: 'I tried to book a fresh cut elsewhere, but nobody matches CandyCutz precision. Fast scheduling, zero waiting around, razor-sharp styling.',
  },
  {
    id: 'sample-4',
    client_name: 'David Adeleke',
    role: 'VIP Member • Full Grooming',
    source: 'x.com',
    client_avatar: 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?w=150&auto=format&fit=crop&q=80',
    review: 'A welcoming lounge, professional service, and a crisp lineup. The team takes time to understand what you want and nails it every visit.',
  },
  {
    id: 'sample-5',
    client_name: 'Tim Cook',
    role: 'CEO, Apple',
    source: 'x.com',
    client_avatar: 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150&auto=format&fit=crop&q=80',
    review: 'Clean lines, immaculate craft, and consistent excellence from start to finish. It is the gold standard of modern grooming.',
  },
  {
    id: 'sample-6',
    client_name: 'Chinedu Okafor',
    role: 'Verified Client • Sharp Lineup',
    source: 'x.com',
    client_avatar: 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?w=150&auto=format&fit=crop&q=80',
    review: 'Booking on the web app was seamless, the chair was ready on arrival, and the fade was a masterclass. Definitely my regular spot now.',
  },
];

const trackItems = computed(() => {
  const source = props.testimonials && props.testimonials.length > 0
    ? props.testimonials
    : defaultTestimonials;

  // Duplicate items if needed so each track is sufficiently long for wide screens
  let items = [...source];
  while (items.length < 6) {
    items = items.concat(source);
  }
  return items;
});

const markAvatarFailed = (id) => {
  failedAvatars.value = new Set([...failedAvatars.value, id]);
};

const getInitials = (name) => {
  const words = (name || 'Client').trim().split(/\s+/).filter(Boolean);
  return words.slice(0, 2).map((w) => w[0].toUpperCase()).join('');
};

const getSubtitle = (item) => {
  if (item.role) return item.role;
  if (item.service?.name) return `Client • ${item.service.name}`;
  if (item.subtitle) return item.subtitle;
  if (item.client_name?.startsWith('Sample Customer ')) return 'CandyCutz Regular Client';
  return 'Verified Client';
};
</script>

<style scoped>
.marquee-container {
  mask-image: linear-gradient(
    to right,
    transparent 0%,
    black 60px,
    black calc(100% - 60px),
    transparent 100%
  );
  -webkit-mask-image: linear-gradient(
    to right,
    transparent 0%,
    black 60px,
    black calc(100% - 60px),
    transparent 100%
  );
}

.marquee-track {
  display: flex;
  flex-shrink: 0;
  animation: marquee-scroll 45s linear infinite;
  will-change: transform;
  transition: none !important;
}

.marquee-container:hover .marquee-track,
.marquee-container:active .marquee-track {
  animation-play-state: paused;
}

@keyframes marquee-scroll {
  0% {
    transform: translateX(0%);
  }
  100% {
    transform: translateX(-100%);
  }
}

@media (prefers-reduced-motion: reduce) {
  .marquee-track {
    animation-play-state: paused;
  }
}
</style>
