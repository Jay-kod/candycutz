<template>
  <!-- Zero-CLS Shimmer Loading Skeletons -->
  <div v-if="mode === 'loading'" class="space-y-3" aria-busy="true">
    <div
      v-for="n in 4"
      :key="n"
      class="rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] p-4 sm:p-5 space-y-3"
    >
      <div class="flex items-start gap-3">
        <div class="h-10 w-10 rounded-lg skeleton-block shrink-0"></div>
        <div class="flex-1 space-y-2">
          <div class="flex items-center justify-between gap-2">
            <div class="h-4 w-20 rounded-md skeleton-block"></div>
            <div class="h-3 w-16 rounded-md skeleton-block"></div>
          </div>
          <div class="h-5 w-48 max-w-full rounded-md skeleton-block"></div>
        </div>
      </div>
      <div class="h-4 w-full rounded-md skeleton-block"></div>
      <div class="h-4 w-4/5 rounded-md skeleton-block"></div>
    </div>
  </div>

  <!-- Empty State: Inbox Completely Empty -->
  <div
    v-else-if="mode === 'empty'"
    class="rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] px-5 py-12 sm:p-16 flex flex-col items-center justify-center text-center"
  >
    <div
      class="h-16 w-16 rounded-xl bg-[var(--color-bg-tertiary)] border border-[var(--color-border)] flex items-center justify-center mb-4"
    >
      <BellIcon class="w-8 h-8 text-[var(--color-text-muted)]" />
    </div>
    <h2 class="font-display text-xl sm:text-2xl font-bold text-[var(--color-text-primary)] mb-1.5">
      You're all caught up
    </h2>
    <p class="text-xs sm:text-sm text-[var(--color-text-secondary)] max-w-sm text-center">
      When new bookings, payment updates, or reminders arrive, they will appear here in real-time.
    </p>
    <div class="mt-5 flex flex-wrap items-center justify-center gap-2.5">
      <button
        v-if="isCustomer"
        type="button"
        @click="$emit('browse-services')"
        class="min-h-[40px] px-4 py-2 rounded-lg bg-[var(--color-accent)] text-[var(--color-accent-foreground)] text-xs font-bold hover:brightness-110 transition-all"
      >
        Browse Services
      </button>
      <button
        type="button"
        @click="$emit('refresh')"
        class="min-h-[40px] px-4 py-2 rounded-lg border border-[var(--color-border)] bg-[var(--color-bg-tertiary)] text-xs font-semibold text-[var(--color-text-primary)] hover:border-[var(--color-accent)]/40 transition-colors"
      >
        Refresh Feed
      </button>
    </div>
  </div>

  <!-- Empty State: No Unread Notifications -->
  <div
    v-else-if="mode === 'empty-unread'"
    class="rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] px-5 py-12 sm:p-14 flex flex-col items-center justify-center text-center"
  >
    <div
      class="h-14 w-14 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center mb-4 text-emerald-600 dark:text-emerald-400"
    >
      <CheckCircleIcon class="w-7 h-7" />
    </div>
    <h2 class="font-display text-xl font-bold text-[var(--color-text-primary)] mb-1.5">
      No unread notifications
    </h2>
    <p class="text-xs sm:text-sm text-[var(--color-text-secondary)] max-w-sm mb-5 text-center">
      Every notification in your stream has been read.
    </p>
    <button
      type="button"
      @click="$emit('show-all')"
      class="min-h-[40px] px-4 py-2 rounded-lg border border-[var(--color-border)] bg-[var(--color-bg-tertiary)] text-xs font-semibold text-[var(--color-text-primary)] hover:border-[var(--color-accent)]/40 transition-colors"
    >
      View all notifications
    </button>
  </div>

  <!-- Empty State: Filters Returned No Matches -->
  <div
    v-else
    class="rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] px-5 py-12 sm:p-14 flex flex-col items-center justify-center text-center"
  >
    <div
      class="h-14 w-14 rounded-xl bg-[var(--color-bg-tertiary)] border border-[var(--color-border)] flex items-center justify-center mb-4"
    >
      <FunnelIcon class="w-7 h-7 text-[var(--color-text-muted)]" />
    </div>
    <h2 class="font-display text-xl font-bold text-[var(--color-text-primary)] mb-1.5">
      No matching notifications
    </h2>
    <p class="text-xs sm:text-sm text-[var(--color-text-secondary)] max-w-sm mb-5 text-center">
      Try clearing your search or adjusting your selected filters.
    </p>
    <button
      type="button"
      @click="$emit('reset-filters')"
      class="min-h-[40px] inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-[var(--color-accent)]/10 border border-[var(--color-accent)]/30 text-[var(--color-accent)] text-xs font-bold hover:bg-[var(--color-accent)]/20 transition-colors"
    >
      <XMarkIcon class="h-4 w-4" />
      <span>Reset Filters</span>
    </button>
  </div>
</template>

<script setup>
import { BellIcon, CheckCircleIcon, FunnelIcon, XMarkIcon } from '@heroicons/vue/24/outline';

defineProps({
  mode: {
    type: String,
    required: true, // 'loading' | 'empty' | 'empty-unread' | 'empty-filtered'
  },
  isCustomer: {
    type: Boolean,
    default: false,
  },
});

defineEmits(['browse-services', 'refresh', 'show-all', 'reset-filters']);
</script>
