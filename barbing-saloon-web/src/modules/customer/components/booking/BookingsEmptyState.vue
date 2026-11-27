<template>
  <div
    class="col-span-full rounded-xl md:rounded-3xl border border-dashed border-[var(--color-border)] md:border-slate-200 md:dark:border-white/10 py-12 sm:py-16 md:py-20 px-4 sm:px-6 text-center bg-[var(--color-bg-secondary)] md:bg-slate-50/50 md:dark:bg-white/[0.02]"
  >
    <div
      class="mx-auto flex h-14 w-14 sm:h-16 sm:w-16 items-center justify-center rounded-xl md:rounded-2xl bg-[var(--color-bg-tertiary)] md:bg-slate-100 md:dark:bg-white/[0.04] border border-[var(--color-border)] md:border-slate-200 md:dark:border-white/10 mb-3.5 sm:mb-4"
    >
      <CalendarIcon class="h-7 w-7 sm:h-8 sm:w-8 text-[var(--color-accent)]/70" />
    </div>
    <p class="text-[var(--color-text-primary)] md:text-slate-900 md:dark:text-white font-display text-lg sm:text-xl mb-1.5 text-center">
      No Bookings Found
    </p>
    <p class="text-xs sm:text-sm text-[var(--color-text-secondary)] max-w-sm mx-auto mb-5 sm:mb-6 text-center leading-relaxed">
      No appointments matching this filter are currently registered.
    </p>
    <div class="flex flex-col xs:flex-row items-center justify-center gap-2.5">
      <button
        v-if="hasBookings && activeFilter !== 'all'"
        type="button"
        @click="$emit('reset-filter')"
        class="w-full xs:w-auto min-h-[44px] inline-flex items-center justify-center gap-2 rounded-lg md:rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-tertiary)] px-5 py-2.5 text-sm md:text-xs font-bold text-[var(--color-text-primary)] hover:border-[var(--color-accent)]/40 hover:text-[var(--color-accent)] transition-colors cursor-pointer"
      >
        Show All Bookings
      </button>
      <RouterLink
        to="/customer/dashboard/services"
        class="w-full xs:w-auto min-h-[44px] inline-flex items-center justify-center gap-2 rounded-lg md:rounded-xl bg-[var(--color-accent)] px-6 py-2.5 text-sm md:text-xs font-bold text-[var(--color-accent-foreground)] md:text-white transition-all hover:brightness-110 shadow-none md:shadow-lg md:shadow-[var(--color-accent)]/20"
      >
        Browse Services
      </RouterLink>
    </div>
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router';
import { CalendarIcon } from '@heroicons/vue/24/outline';

defineProps({
  hasBookings: { type: Boolean, default: false },
  activeFilter: { type: String, default: 'all' },
});

defineEmits(['reset-filter']);
</script>
