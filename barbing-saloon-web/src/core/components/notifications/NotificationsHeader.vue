<template>
  <header class="rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] p-4 sm:p-6 space-y-4">
    <!-- Top Identity & Utility Controls -->
    <div class="flex items-start justify-between gap-3">
      <div class="min-w-0 flex-1">
        <div class="flex items-center gap-2 mb-1">
          <span
            class="inline-flex h-2 w-2 rounded-full bg-[var(--color-accent)] animate-pulse shrink-0"
            aria-hidden="true"
          ></span>
          <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-[var(--color-accent)]">
            Activity Stream
          </p>
          <span
            class="hidden xs:inline-flex items-center rounded-md border border-[var(--color-border)] bg-[var(--color-bg-tertiary)] px-1.5 py-0.5 text-[10px] font-semibold text-[var(--color-text-muted)]"
          >
            Real-time
          </span>
        </div>

        <h1 class="font-display text-2xl sm:text-3xl font-bold tracking-tight text-[var(--color-text-primary)] leading-tight">
          Your <span class="text-[var(--color-accent)]">Notifications</span>
        </h1>

        <p class="mt-1 text-xs sm:text-sm text-[var(--color-text-secondary)] text-left-forced">
          Stay updated on bookings, payments, and live alerts.
        </p>
      </div>

      <!-- Compact Utility Actions -->
      <div class="flex items-center gap-2 shrink-0">
        <button
          type="button"
          @click="$emit('refresh')"
          :disabled="isLoading || isRefreshing"
          class="min-h-[40px] min-w-[40px] sm:min-h-[42px] px-2.5 sm:px-3.5 rounded-lg border border-[var(--color-border)] bg-[var(--color-bg-tertiary)] text-xs font-semibold text-[var(--color-text-primary)] hover:border-[var(--color-accent)]/40 hover:text-[var(--color-accent)] transition-colors inline-flex items-center justify-center gap-1.5 disabled:opacity-50"
          title="Refresh notifications"
          aria-label="Refresh notifications"
        >
          <ArrowPathIcon
            class="w-4 h-4 shrink-0"
            :class="{ 'animate-spin text-[var(--color-accent)]': isLoading || isRefreshing }"
          />
          <span class="hidden sm:inline">{{ isRefreshing ? 'Refreshing...' : 'Refresh' }}</span>
        </button>

        <button
          v-if="showSettings"
          type="button"
          @click="$emit('open-settings')"
          class="min-h-[40px] min-w-[40px] sm:min-h-[42px] px-2.5 sm:px-3.5 rounded-lg border border-[var(--color-border)] bg-[var(--color-bg-tertiary)] text-xs font-semibold text-[var(--color-text-primary)] hover:border-[var(--color-accent)]/40 hover:text-[var(--color-accent)] transition-colors inline-flex items-center justify-center gap-1.5"
          title="Notification preferences"
          aria-label="Notification preferences"
        >
          <Cog6ToothIcon class="w-4 h-4 shrink-0" />
          <span class="hidden sm:inline">Settings</span>
        </button>
      </div>
    </div>

    <!-- Bottom View Switch & Balanced Action Row -->
    <div class="pt-3.5 border-t border-[var(--color-border)] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
      <!-- All / Unread Segmented Switch -->
      <div
        role="tablist"
        aria-label="Notification read status"
        class="inline-flex w-full sm:w-auto items-center p-1 rounded-lg bg-[var(--color-bg-tertiary)] border border-[var(--color-border)]"
      >
        <button
          type="button"
          role="tab"
          :aria-selected="readFilter === 'all'"
          @click="$emit('update:readFilter', 'all')"
          class="flex-1 sm:flex-initial min-h-[34px] px-3 py-1 rounded-md text-xs font-semibold inline-flex items-center justify-center gap-1.5 transition-colors"
          :class="
            readFilter === 'all'
              ? 'bg-[var(--color-bg-secondary)] text-[var(--color-text-primary)] shadow-xs border border-[var(--color-border)]'
              : 'text-[var(--color-text-secondary)] hover:text-[var(--color-text-primary)]'
          "
        >
          <span>All</span>
          <span
            class="rounded-md px-1.5 py-0.5 text-[10px] font-bold tabular-nums leading-none"
            :class="
              readFilter === 'all'
                ? 'bg-[var(--color-accent)]/15 text-[var(--color-accent)]'
                : 'bg-[var(--color-bg-secondary)] text-[var(--color-text-muted)]'
            "
          >
            {{ totalCount }}
          </span>
        </button>

        <button
          type="button"
          role="tab"
          :aria-selected="readFilter === 'unread'"
          @click="$emit('update:readFilter', 'unread')"
          class="flex-1 sm:flex-initial min-h-[34px] px-3 py-1 rounded-md text-xs font-semibold inline-flex items-center justify-center gap-1.5 transition-colors"
          :class="
            readFilter === 'unread'
              ? 'bg-[var(--color-bg-secondary)] text-[var(--color-text-primary)] shadow-xs border border-[var(--color-border)]'
              : 'text-[var(--color-text-secondary)] hover:text-[var(--color-text-primary)]'
          "
        >
          <span>Unread</span>
          <span
            class="rounded-md px-1.5 py-0.5 text-[10px] font-bold tabular-nums leading-none"
            :class="
              unreadCount > 0
                ? 'bg-[var(--color-accent)] text-[var(--color-accent-foreground)]'
                : 'bg-[var(--color-bg-secondary)] text-[var(--color-text-muted)]'
            "
          >
            {{ unreadCount }}
          </span>
        </button>
      </div>

      <!-- Balanced Action Buttons -->
      <div class="flex flex-wrap items-center gap-2">
        <button
          type="button"
          @click="$emit('toggle-filters')"
          :aria-expanded="showFilters"
          class="min-h-[38px] px-3 py-1.5 rounded-lg border text-xs font-semibold inline-flex items-center justify-center gap-1.5 transition-colors"
          :class="
            showFilters || hasActiveFilters
              ? 'border-[var(--color-accent)]/40 bg-[var(--color-accent)]/10 text-[var(--color-accent)]'
              : 'border-[var(--color-border)] bg-[var(--color-bg-primary)] text-[var(--color-text-primary)] hover:border-[var(--color-accent)]/40 hover:text-[var(--color-accent)]'
          "
        >
          <FunnelIcon class="w-4 h-4 shrink-0" />
          <span>Filters</span>
          <span
            v-if="activeFilterCount > 0"
            class="inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-md bg-[var(--color-accent)] text-[var(--color-accent-foreground)] text-[10px] font-bold tabular-nums"
          >
            {{ activeFilterCount }}
          </span>
        </button>

        <button
          v-if="unreadCount > 0"
          type="button"
          @click="$emit('mark-all-read')"
          class="min-h-[38px] px-3.5 py-1.5 rounded-lg bg-[var(--color-accent)] text-[var(--color-accent-foreground)] text-xs font-bold inline-flex items-center justify-center gap-1.5 hover:brightness-110 transition-all"
        >
          <CheckCircleIcon class="w-4 h-4 shrink-0" />
          <span>Mark all read</span>
        </button>

        <button
          v-if="totalCount > 0"
          type="button"
          @click="$emit('clear-all')"
          :disabled="isClearing"
          class="min-h-[38px] px-3 py-1.5 rounded-lg border border-rose-500/25 bg-rose-500/10 text-rose-600 dark:text-rose-400 hover:bg-rose-500 hover:text-white hover:border-rose-500 text-xs font-semibold inline-flex items-center justify-center gap-1.5 transition-colors disabled:opacity-50"
          title="Clear all notifications"
        >
          <TrashIcon class="w-4 h-4 shrink-0" />
          <span>{{ isClearing ? 'Clearing...' : 'Clear all' }}</span>
        </button>
      </div>
    </div>
  </header>
</template>

<script setup>
import {
  ArrowPathIcon,
  CheckCircleIcon,
  Cog6ToothIcon,
  FunnelIcon,
  TrashIcon,
} from '@heroicons/vue/24/outline';

defineProps({
  totalCount: { type: Number, default: 0 },
  unreadCount: { type: Number, default: 0 },
  isLoading: { type: Boolean, default: false },
  isRefreshing: { type: Boolean, default: false },
  isClearing: { type: Boolean, default: false },
  showFilters: { type: Boolean, default: false },
  showSettings: { type: Boolean, default: false },
  hasActiveFilters: { type: Boolean, default: false },
  activeFilterCount: { type: Number, default: 0 },
  readFilter: { type: String, default: 'all' },
});

defineEmits([
  'refresh',
  'open-settings',
  'toggle-filters',
  'mark-all-read',
  'clear-all',
  'update:readFilter',
]);
</script>
