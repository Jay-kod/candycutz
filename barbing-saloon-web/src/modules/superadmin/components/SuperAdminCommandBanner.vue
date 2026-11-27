<template>
  <div
    class="rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] p-4 transition-colors xs:p-6 sm:p-8"
  >
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <p class="text-xs uppercase tracking-[0.25em] font-semibold text-rose-500">
          Super Admin · Platform Governance
        </p>
        <h1 class="mt-1 font-display text-3xl sm:text-4xl font-bold text-[var(--color-text-primary)]">
          Platform Overview
        </h1>
        <p class="mt-1 text-xs text-[var(--color-text-muted)]">
          Accounts, system health, and governance activity
        </p>
      </div>

      <div class="flex flex-wrap items-center gap-3">
        <div
          class="flex items-center gap-2 rounded-lg border px-3 py-2 text-xs font-semibold"
          :class="statusPresentation.classes"
          role="status"
          aria-live="polite"
        >
          <span class="h-2 w-2 rounded-full" :class="statusPresentation.dot"></span>
          <span>{{ statusPresentation.label }}</span>
        </div>

        <button
          type="button"
          @click="$emit('refresh')"
          :disabled="isRefreshing"
          class="flex items-center gap-2 rounded-lg border border-rose-500/30 px-3 py-2 text-xs sm:text-sm font-semibold text-rose-600 transition-colors hover:bg-rose-500/10 dark:text-rose-400 cursor-pointer disabled:opacity-50"
        >
          <ArrowPathIcon :class="['h-4 w-4', isRefreshing ? 'animate-spin' : '']" />
          <span>{{ isRefreshing ? 'Refreshing…' : 'Refresh data' }}</span>
        </button>
      </div>
    </div>
    <p class="mt-4 border-t border-[var(--color-border)] pt-3 text-xs text-[var(--color-text-muted)]">
      Last updated:
      <span class="font-medium tabular-nums text-[var(--color-text-secondary)]">{{ updatedAtLabel }}</span>
    </p>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { ArrowPathIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  isRefreshing: {
    type: Boolean,
    default: false,
  },
  platformStatus: {
    type: String,
    default: 'unknown',
  },
  lastUpdated: {
    type: Date,
    default: null,
  },
});

defineEmits(['refresh']);

const statusPresentation = computed(() => {
  const presentations = {
    operational: {
      label: 'All systems operational',
      classes: 'border-emerald-500/30 bg-emerald-500/5 text-emerald-600 dark:text-emerald-400',
      dot: 'bg-emerald-500',
    },
    attention: {
      label: 'Review required',
      classes: 'border-amber-500/30 bg-amber-500/5 text-amber-600 dark:text-amber-400',
      dot: 'bg-amber-500',
    },
    degraded: {
      label: 'Service degraded',
      classes: 'border-rose-500/30 bg-rose-500/5 text-rose-600 dark:text-rose-400',
      dot: 'bg-rose-500',
    },
  };

  return presentations[props.platformStatus] || {
    label: 'Status unavailable',
    classes: 'border-[var(--color-border)] text-[var(--color-text-muted)]',
    dot: 'bg-[var(--color-text-muted)]',
  };
});

const updatedAtLabel = computed(() => {
  if (!props.lastUpdated) return 'Not yet refreshed';
  return new Intl.DateTimeFormat('en-NG', {
    dateStyle: 'medium',
    timeStyle: 'short',
  }).format(props.lastUpdated);
});
</script>

