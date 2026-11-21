<template>
  <article
    class="group relative overflow-hidden rounded-3xl border bg-theme-surface p-6 shadow-xl transition-all duration-300 hover:-translate-y-0.5"
    :class="review.is_approved
      ? 'border-theme-border hover:border-[var(--color-accent)]/40'
      : 'border-amber-500/40 hover:border-amber-400/60 bg-theme-surface'"
  >
    <!-- Glowing Top Accent Line -->
    <div
      class="absolute top-0 left-0 right-0 h-1 z-30 transition-opacity"
      :class="!review.is_approved
        ? 'bg-amber-400 opacity-100'
        : (review.is_featured
          ? 'bg-gradient-to-r from-[var(--color-accent)] via-amber-400 to-amber-200 opacity-100'
          : 'bg-gradient-to-r from-[var(--color-accent)] to-amber-400 opacity-0 group-hover:opacity-100')"
    ></div>

    <!-- Decorative quotation watermark -->
    <div class="pointer-events-none absolute right-4 top-2 select-none font-display text-7xl text-theme-text-muted/5 group-hover:text-[var(--color-accent)]/10 transition-colors">
      "
    </div>

    <div class="relative z-10 flex flex-col md:flex-row md:items-start justify-between gap-4">
      <div class="flex-1 min-w-0">
        <!-- Client Info & Service Badge -->
        <div class="flex items-center gap-3.5 mb-3">
          <img
            v-if="review.avatar_url || review.avatar"
            :src="getStorageUrl(review.avatar_url || review.avatar)"
            :alt="`${customerName} avatar`"
            class="h-11 w-11 shrink-0 rounded-full border border-[var(--color-accent)]/30 object-cover shadow-sm"
            loading="lazy"
          />
          <div
            v-else
            class="h-11 w-11 rounded-full bg-[var(--color-accent)]/15 border border-[var(--color-accent)]/30 flex items-center justify-center text-[var(--color-accent)] font-display text-lg shrink-0 shadow-sm"
          >
            {{ customerName.charAt(0).toUpperCase() }}
          </div>

          <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2 flex-wrap">
              <h3 class="font-display text-base sm:text-lg text-theme-text group-hover:text-[var(--color-accent)] transition-colors truncate">
                {{ customerName }}
              </h3>

              <!-- Service Reviewed Badge -->
              <span
                v-if="serviceName"
                class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-[var(--color-accent)]/10 border border-[var(--color-accent)]/20 text-[var(--color-accent)] text-xs font-semibold shrink-0"
              >
                <ScissorsIcon class="h-3 w-3" />
                {{ serviceName }}
              </span>
            </div>

            <!-- Stars and Date Row -->
            <div class="flex items-center gap-3 mt-1 flex-wrap">
              <div class="flex items-center gap-0.5 text-amber-400">
                <span v-for="i in 5" :key="i">
                  <svg
                    v-if="i <= (review.rating || 5)"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="currentColor"
                    class="h-3.5 w-3.5 drop-shadow-[0_0_3px_rgba(245,158,11,0.5)]"
                  >
                    <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z" clip-rule="evenodd" />
                  </svg>
                  <svg
                    v-else
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                    class="h-3.5 w-3.5 opacity-20"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385c.148.621-.531 1.114-1.005.777l-4.783-2.738a.562.562 0 00-.554 0l-4.783 2.738c-.474.337-1.153-.156-1.005-.777l1.285-5.385a.563.563 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                  </svg>
                </span>
                <span class="ml-1 text-xs font-bold text-amber-400">{{ review.rating }}.0</span>
              </div>

              <span class="text-xs text-theme-text-muted flex items-center gap-1 font-mono">
                <ClockIcon class="h-3.5 w-3.5 text-theme-text-muted" />
                {{ formatDate(review.created_at) }}
              </span>
            </div>
          </div>
        </div>

        <!-- Review Body -->
        <div class="mt-3 relative pl-4 border-l-2 border-[var(--color-accent)]/30 py-1">
          <p class="text-theme-text/90 leading-relaxed text-sm italic">
            "{{ review.review || review.comment || 'No written comment provided.' }}"
          </p>
        </div>
      </div>

      <!-- Status Pill -->
      <div class="shrink-0 flex items-center md:flex-col md:items-end gap-2">
        <span
          class="text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full border flex items-center gap-1.5"
          :class="review.is_approved
            ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30'
            : 'bg-amber-500/10 text-amber-400 border-amber-500/30'"
        >
          <span class="h-1.5 w-1.5 rounded-full" :class="review.is_approved ? 'bg-emerald-400' : 'bg-amber-400 animate-pulse'"></span>
          {{ review.is_approved ? 'Published on Site' : 'Pending Review' }}
        </span>

        <span
          v-if="review.is_featured"
          class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md bg-amber-500/10 text-amber-300 border border-amber-500/20 flex items-center gap-1"
        >
          <SparklesIcon class="h-3 w-3 text-amber-400" />
          Featured
        </span>
      </div>
    </div>
  </article>
</template>

<script setup>
import { computed } from 'vue';
import { getStorageUrl } from '@/core/utils/url';
import { ClockIcon, ScissorsIcon, SparklesIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  review: { type: Object, required: true },
  formatDate: { type: Function, required: true },
});

const customerName = computed(() => {
  return props.review.customer_name || props.review.client_name || 'Anonymous Client';
});

const serviceName = computed(() => {
  return props.review.service?.name || props.review.service_name || '';
});
</script>

