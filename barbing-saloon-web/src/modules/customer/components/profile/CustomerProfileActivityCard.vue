<template>
  <div
    class="rounded-xl md:rounded-3xl border border-[var(--color-border)] md:border-slate-200 md:dark:border-white/[0.08] bg-[var(--color-bg-secondary)] md:bg-white md:dark:bg-[#12161C] p-4 sm:p-6 lg:p-8 shadow-none md:shadow-xl flex flex-col justify-between"
  >
    <div>
      <!-- Display & System Preference -->
      <div
        class="mb-5 sm:mb-6 rounded-xl md:rounded-2xl border border-[var(--color-border)] md:border-slate-200/80 md:dark:border-white/[0.06] bg-[var(--color-bg-tertiary)]/50 md:bg-slate-50 md:dark:bg-white/[0.03] p-3.5 sm:p-4"
      >
        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Display &amp; System</h3>
        <p class="mt-1 text-[11px] text-[var(--color-text-secondary)] text-left-forced">
          Personal dashboard preferences
        </p>
        <div class="mt-3.5 sm:mt-4 flex items-center justify-between gap-3">
          <div class="min-w-0">
            <p class="text-xs font-semibold text-slate-900 dark:text-white text-left-forced">Theme</p>
            <p class="mt-0.5 text-[11px] sm:text-[10px] text-[var(--color-text-secondary)] text-left-forced">
              {{ isDark ? 'Dark appearance' : 'Light appearance' }}
            </p>
          </div>
          <ThemeToggleSwitch />
        </div>
      </div>

      <!-- Recent Activity Feed -->
      <h2
        class="font-display text-lg sm:text-xl text-slate-900 dark:text-white pb-3.5 sm:pb-4 border-b border-[var(--color-border)] md:border-slate-100 md:dark:border-white/[0.06] flex items-center gap-2"
      >
        <ClockIcon class="w-5 h-5 text-[var(--color-accent)] shrink-0" />
        <span>Recent <span class="text-[var(--color-accent)] md:text-transparent md:bg-clip-text md:bg-gradient-to-r md:from-[var(--color-accent)] md:to-amber-200">Activity</span></span>
      </h2>

      <div class="mt-4 sm:mt-5 space-y-2.5 sm:space-y-3">
        <article
          v-for="booking in latestBookings"
          :key="booking.id"
          class="rounded-xl md:rounded-2xl border border-[var(--color-border)] md:border-slate-200/80 md:dark:border-white/[0.06] bg-[var(--color-bg-tertiary)]/40 md:bg-slate-50 md:dark:bg-[#1E232B]/40 p-3.5 sm:p-4 transition-all hover:border-amber-400/40 dark:hover:border-white/20 hover:bg-slate-100 dark:hover:bg-[#1E232B]"
        >
          <div class="flex flex-col xs:flex-row xs:items-start justify-between gap-2 xs:gap-3">
            <div class="min-w-0 flex-1">
              <p class="font-display text-sm sm:text-base text-slate-900 dark:text-white truncate text-left-forced">
                {{ booking.service?.name || 'Grooming Cut' }}
              </p>
              <p class="mt-1 text-xs text-[var(--color-text-secondary)] tabular-nums text-left-forced">
                {{ booking.appointment_date }} at {{ booking.appointment_time }}
              </p>
            </div>
            <span
              class="self-start shrink-0 rounded-md md:rounded-full bg-[var(--color-bg-secondary)] md:bg-slate-100 md:dark:bg-white/[0.05] border border-[var(--color-border)] md:border-slate-200 md:dark:border-white/10 px-2.5 py-0.5 text-[10px] font-bold text-slate-700 dark:text-white/70 capitalize"
            >
              {{ booking.status }}
            </span>
          </div>
        </article>

        <div
          v-if="latestBookings.length === 0"
          class="rounded-xl md:rounded-2xl border border-dashed border-[var(--color-border)] md:border-slate-200 md:dark:border-white/10 p-6 sm:p-10 text-center bg-[var(--color-bg-tertiary)]/30 md:bg-slate-50/50 md:dark:bg-white/[0.02]"
        >
          <p class="text-xs text-[var(--color-text-secondary)] text-center">
            No recent appointments recorded.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ClockIcon } from '@heroicons/vue/24/outline';
import ThemeToggleSwitch from '@/components/ThemeToggleSwitch.vue';

defineProps({
  isDark: { type: Boolean, default: false },
  latestBookings: { type: Array, default: () => [] },
});
</script>
