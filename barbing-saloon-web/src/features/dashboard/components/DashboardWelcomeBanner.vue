<template>
  <div class="relative overflow-hidden rounded-xl border-b border-theme-border bg-theme-surface p-8 lg:p-10">
    <div class="relative z-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
      <div>
        <p class="text-[11px] uppercase tracking-[0.35em] text-admin/70 font-bold">Admin Control Center</p>
        <h1 class="mt-2 font-display text-4xl lg:text-5xl text-theme-text leading-tight">
          Operational <span class="text-admin">Overview</span>
        </h1>
        <p class="mt-3 max-w-xl text-sm text-ivory/50 leading-relaxed">
          Monitor real-time metrics, track revenue, manage appointments and keep the saloon running at peak performance.
        </p>
      </div>

      <div class="flex items-center gap-5 shrink-0">
        <!-- Current Time -->
        <div class="hidden sm:flex flex-col items-end">
          <span class="text-2xl font-display text-theme-text tabular-nums">{{ currentTime }}</span>
          <span class="text-[10px] uppercase tracking-widest text-ivory/40 font-bold mt-0.5">{{ currentDate }}</span>
        </div>

        <div class="h-10 w-px bg-white/10 hidden sm:block"></div>

        <!-- System Status -->
        <div class="flex items-center gap-3 bg-emerald-500/5 px-4 py-2.5 rounded-lg border border-emerald-500/15">
          <span class="relative flex h-2.5 w-2.5">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
          </span>
          <span class="text-xs font-semibold text-emerald-400 whitespace-nowrap">All Systems Operational</span>
        </div>
        
        <!-- Refresh Button -->
        <button @click="$emit('refresh')" class="flex items-center justify-center gap-2 rounded-lg bg-white/[0.05] border border-white/10 px-4 py-2 text-xs font-bold text-white transition-all hover:bg-white/10 shrink-0 group">
          <ArrowPathIcon class="h-4 w-4 group-hover:rotate-180 transition-transform duration-500" :class="{ 'animate-spin': isRefreshing }" />
          <span class="hidden sm:inline">Refresh</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ArrowPathIcon } from '@heroicons/vue/24/outline';

defineProps({
  currentTime: { type: String, default: '' },
  currentDate: { type: String, default: '' },
  isRefreshing: { type: Boolean, default: false }
});

defineEmits(['refresh']);
</script>
