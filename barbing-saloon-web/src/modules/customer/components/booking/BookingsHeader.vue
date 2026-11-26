<template>
  <div
    class="relative overflow-hidden rounded-xl md:rounded-3xl border border-[var(--color-border)] md:border-slate-200 md:dark:border-white/[0.08] bg-[var(--color-bg-secondary)] md:bg-gradient-to-br md:from-amber-500/10 md:via-amber-400/5 md:to-white md:dark:from-[var(--color-bg-secondary)] md:dark:to-[var(--color-bg-secondary)] p-4 sm:p-6 md:p-8 lg:p-12 shadow-none md:shadow-2xl"
  >
    <div
      class="hidden md:block absolute -right-16 -top-16 h-64 w-64 rounded-full bg-[var(--color-accent)]/10 blur-3xl pointer-events-none"
    ></div>
    <div
      class="hidden md:block absolute -left-16 -bottom-16 h-48 w-48 rounded-full bg-[var(--color-accent)]/5 blur-3xl pointer-events-none"
    ></div>

    <div class="relative z-10 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4 md:gap-6">
      <div class="min-w-0 flex-1">
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <div class="flex items-center gap-2 mb-1.5 sm:mb-2">
              <span class="inline-flex h-2 w-2 rounded-full bg-[var(--color-accent)] animate-pulse shrink-0"></span>
              <p class="text-[11px] sm:text-[10px] uppercase tracking-[0.2em] md:tracking-[0.25em] font-bold text-[var(--color-accent)]">
                Personal Schedule
              </p>
            </div>
            <h1 class="font-display text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight text-[var(--color-text-primary)] md:text-slate-900 md:dark:text-white leading-tight">
              My <span class="text-[var(--color-accent)] md:text-transparent md:bg-clip-text md:bg-gradient-to-r md:from-[var(--color-accent)] md:to-[var(--color-accent)]/80">Bookings</span>
            </h1>
          </div>

          <button
            type="button"
            @click="$emit('refresh')"
            :disabled="loading"
            class="md:hidden min-h-[44px] min-w-[44px] rounded-lg border border-[var(--color-border)] bg-[var(--color-bg-tertiary)] text-[var(--color-text-primary)] hover:border-[var(--color-accent)]/40 hover:text-[var(--color-accent)] transition-colors inline-flex items-center justify-center shrink-0 disabled:opacity-50"
            title="Refresh bookings"
            aria-label="Refresh bookings"
          >
            <ArrowPathIcon class="h-4 w-4" :class="{ 'animate-spin text-[var(--color-accent)]': loading }" />
          </button>
        </div>

        <p class="mt-1.5 sm:mt-2 text-xs sm:text-sm text-[var(--color-text-secondary)] max-w-xl leading-relaxed text-left-forced">
          Track your upcoming haircuts, grooming sessions, review receipt confirmations, and access verification codes.
        </p>
      </div>

      <div class="flex items-center gap-2.5 shrink-0">
        <button
          type="button"
          @click="$emit('refresh')"
          :disabled="loading"
          class="hidden md:inline-flex min-h-[44px] min-w-[44px] rounded-xl border border-slate-200 dark:border-white/[0.1] bg-white/80 dark:bg-white/[0.04] text-slate-700 dark:text-white/80 hover:border-[var(--color-accent)]/40 hover:text-[var(--color-accent)] transition-colors items-center justify-center shrink-0 disabled:opacity-50"
          title="Refresh bookings"
          aria-label="Refresh bookings"
        >
          <ArrowPathIcon class="h-4 w-4" :class="{ 'animate-spin text-[var(--color-accent)]': loading }" />
        </button>

        <RouterLink
          to="/customer/dashboard/services"
          class="flex w-full md:w-auto min-h-[44px] items-center justify-center gap-2 rounded-lg md:rounded-xl bg-[var(--color-accent)] px-5 sm:px-6 py-2.5 sm:py-3 text-sm md:text-xs font-bold text-[var(--color-accent-foreground)] md:text-white transition-all hover:brightness-110 shadow-none md:shadow-lg md:shadow-[var(--color-accent)]/20 active:scale-[0.98]"
        >
          <CalendarIcon class="h-4 w-4 shrink-0" />
          <span>Book New Cut</span>
        </RouterLink>
      </div>
    </div>
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router';
import { ArrowPathIcon, CalendarIcon } from '@heroicons/vue/24/outline';

defineProps({
  loading: { type: Boolean, default: false },
});

defineEmits(['refresh']);
</script>
