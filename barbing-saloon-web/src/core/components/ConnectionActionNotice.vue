<template>
  <section
    v-if="hasActionFailure"
    role="status"
    aria-live="polite"
    class="mb-5 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 text-amber-950 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-100"
  >
    <div class="min-w-0">
      <p class="text-sm font-semibold">We couldn't confirm that request.</p>
      <p class="mt-1 text-xs opacity-80">Review this page before trying again. Requests are never repeated automatically.</p>
    </div>
    <div class="flex shrink-0 items-center gap-2">
      <button
        type="button"
        :disabled="isProbing"
        class="rounded-lg border border-current/20 px-3 py-1.5 text-xs font-semibold transition hover:bg-black/5 disabled:cursor-wait disabled:opacity-60 dark:hover:bg-white/10"
        @click="retry"
      >
        {{ isProbing ? 'Checking...' : 'Check connection' }}
      </button>
      <button
        type="button"
        class="rounded-lg px-2 py-1.5 text-xs font-medium opacity-70 transition hover:bg-black/5 hover:opacity-100 dark:hover:bg-white/10"
        aria-label="Dismiss connection notice"
        @click="clearActionFailure"
      >
        Dismiss
      </button>
    </div>
  </section>
</template>

<script setup>
import { useConnectionStatus } from '@/core/composables/useConnectionStatus';

const { hasActionFailure, isProbing, retry, clearActionFailure } = useConnectionStatus();
</script>
