<template>
  <button
    type="button"
    @click="handleClick"
    :disabled="isProbing"
    class="inline-flex items-center gap-1.5 px-2.5 py-1 sm:px-3 sm:py-1.5 rounded-full border text-xs font-mono font-medium transition-all duration-200 active:scale-95 cursor-pointer disabled:opacity-60"
    :class="statusClasses"
    :title="tooltipText"
    aria-label="Database connection status"
  >
    <!-- Status Dot -->
    <span class="relative flex h-2 w-2 shrink-0">
      <span
        v-if="isDisconnected"
        class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 bg-rose-400"
      ></span>
      <span
        class="relative inline-flex rounded-full h-2 w-2"
        :class="dotClass"
      ></span>
    </span>

    <!-- Database Icon -->
    <CircleStackIcon class="h-3.5 w-3.5 shrink-0" :class="iconClass" />

    <!-- Status Text -->
    <span class="font-semibold tracking-tight">
      {{ statusLabel }}
    </span>

    <!-- Latency (when connected and available) -->
    <span v-if="isConnected && latencyMs" class="hidden md:inline text-[10px] opacity-70 tabular-nums">
      {{ latencyMs }}ms
    </span>

    <!-- Probe Spinner -->
    <ArrowPathIcon
      v-if="isProbing"
      class="h-3 w-3 animate-spin shrink-0 ml-0.5"
    />
  </button>
</template>

<script setup>
import { computed } from 'vue';
import { useConnectionStatus } from '@/core/composables/useConnectionStatus';
import { CircleStackIcon, ArrowPathIcon } from '@heroicons/vue/24/outline';

const {
  isConnected,
  isDisconnected,
  isRecovered,
  isProbing,
  latencyMs,
  retry,
  checkHealth,
} = useConnectionStatus();

const handleClick = () => {
  if (isDisconnected.value) {
    retry();
  } else {
    checkHealth();
  }
};

const statusLabel = computed(() => {
  if (isDisconnected.value) return 'DB: Offline';
  return 'DB: Active';
});

const statusClasses = computed(() => {
  if (isDisconnected.value) {
    return 'bg-rose-500/15 border-rose-500/40 text-rose-300 hover:bg-rose-500/25 shadow-sm';
  }
  if (isRecovered.value) {
    return 'bg-emerald-500/15 border-emerald-500/40 text-emerald-300 hover:bg-emerald-500/25 shadow-sm';
  }
  return 'bg-emerald-500/10 border-emerald-500/30 text-emerald-400 hover:bg-emerald-500/20';
});

const dotClass = computed(() => {
  if (isDisconnected.value) return 'bg-rose-500';
  return 'bg-emerald-400';
});

const iconClass = computed(() => {
  if (isDisconnected.value) return 'text-rose-400';
  return 'text-emerald-400';
});

const tooltipText = computed(() => {
  if (isDisconnected.value) {
    return 'Database is offline. Click to test MySQL connection.';
  }
  return 'Database is active and operational. Click to re-verify.';
});
</script>

