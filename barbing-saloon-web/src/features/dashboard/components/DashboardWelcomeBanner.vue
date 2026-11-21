<template>
  <div class="relative overflow-hidden rounded-2xl border border-white/[0.07] bg-[#0A0F12]/80 backdrop-blur-xl p-6 lg:p-8 shadow-2xl">
    <!-- Ambient Warm Glow Background -->
    <div class="pointer-events-none absolute -left-20 -top-20 h-64 w-64 rounded-full bg-admin/10 blur-[80px]"></div>
    <div class="pointer-events-none absolute right-0 top-0 h-48 w-48 rounded-full bg-white/[0.02] blur-[60px]"></div>

    <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
      <div>
        <div class="inline-flex items-center gap-2 rounded-full bg-admin/10 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.25em] text-admin border border-admin/20">
          <span class="h-1.5 w-1.5 rounded-full bg-admin animate-pulse"></span>
          Admin Control Center
        </div>
        <h1 class="mt-3 font-display text-3xl sm:text-4xl lg:text-[40px] text-white tracking-tight leading-tight">
          Operational <span class="text-admin font-normal italic">Overview</span>
        </h1>
        <p class="mt-2 max-w-xl text-xs sm:text-sm text-ivory/50 leading-relaxed font-sans">
          Real-time metrics, appointment volume, revenue tracking, and salon health at a glance.
        </p>
      </div>

      <div class="flex items-center flex-wrap gap-4 shrink-0">
        <!-- Live Clock -->
        <div class="flex flex-col items-start md:items-end rounded-xl bg-white/[0.03] border border-white/[0.05] px-4 py-2.5">
          <span class="text-xl font-bold font-sans text-white tabular-nums tracking-tight">{{ currentTime || '--:--' }}</span>
          <span class="text-[10px] uppercase tracking-wider text-ivory/40 font-semibold mt-0.5">{{ currentDate }}</span>
        </div>

        <!-- System Status Pill -->
        <div class="flex items-center gap-2.5 bg-emerald-500/10 px-3.5 py-2.5 rounded-xl border border-emerald-500/20">
          <span class="relative flex h-2 w-2">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
          </span>
          <span class="text-xs font-semibold text-emerald-400">All Systems Operational</span>
        </div>
        
        <!-- Refresh Button -->
        <button 
          @click="$emit('refresh')" 
          :disabled="isRefreshing"
          class="flex items-center justify-center gap-2 rounded-xl bg-white/[0.04] border border-white/[0.08] px-4 py-2.5 text-xs font-semibold text-white/90 transition-all hover:bg-white/[0.08] hover:border-white/20 active:scale-95 disabled:opacity-50 group shadow-sm cursor-pointer"
        >
          <ArrowPathIcon class="h-4 w-4 text-admin transition-transform duration-500 group-hover:rotate-180" :class="{ 'animate-spin': isRefreshing }" />
          <span>Refresh</span>
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
