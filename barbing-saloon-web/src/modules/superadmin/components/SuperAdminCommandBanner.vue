<template>
  <div
    class="rounded-2xl border p-6 sm:p-8 transition-colors border-rose-500/20 bg-gradient-to-br from-rose-950/20 via-[var(--color-bg-secondary)] to-[var(--color-bg-secondary)] shadow-sm"
  >
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <p class="text-xs uppercase tracking-[0.25em] font-semibold text-rose-500">
          Super Admin Command Center
        </p>
        <h1 class="mt-1 font-display text-3xl sm:text-4xl font-bold text-[var(--color-text-primary)]">
          System Overview
        </h1>
        <p class="mt-1 text-xs text-[var(--color-text-muted)]">
          {{ currentDate }}
        </p>
      </div>

      <div class="flex flex-wrap items-center gap-3">
        <!-- Live Clock -->
        <div class="flex items-center gap-2 rounded-xl border border-rose-500/20 bg-[var(--color-bg-tertiary)] px-3.5 py-2 font-mono text-sm sm:text-base text-rose-500 tabular-nums shadow-sm">
          <ClockIcon class="h-4 w-4 opacity-70" />
          <span>{{ currentTime }}</span>
        </div>

        <!-- Refresh Button -->
        <button
          type="button"
          @click="$emit('refresh')"
          :disabled="isRefreshing"
          class="flex items-center gap-2 rounded-xl border border-rose-500/30 bg-rose-500/10 px-3.5 py-2 text-xs sm:text-sm font-semibold text-rose-600 dark:text-rose-400 hover:bg-rose-500/20 transition-all duration-200 cursor-pointer disabled:opacity-50"
        >
          <ArrowPathIcon :class="['h-4 w-4', isRefreshing ? 'animate-spin' : '']" />
          <span>{{ isRefreshing ? 'Loading…' : 'Refresh' }}</span>
        </button>

        <!-- System Status -->
        <div class="flex items-center gap-2 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1.5 text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">
          <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
          <span>Online</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { ClockIcon, ArrowPathIcon } from '@heroicons/vue/24/outline';

defineProps({
  isRefreshing: {
    type: Boolean,
    default: false,
  },
});

defineEmits(['refresh']);

const currentTime = ref('');
const currentDate = ref('');
let timeInterval = null;

const updateTime = () => {
  const now = new Date();
  currentTime.value = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
  currentDate.value = now.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
};

onMounted(() => {
  updateTime();
  timeInterval = setInterval(updateTime, 1000);
});

onUnmounted(() => {
  if (timeInterval) clearInterval(timeInterval);
});
</script>

