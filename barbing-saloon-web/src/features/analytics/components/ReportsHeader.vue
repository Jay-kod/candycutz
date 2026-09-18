<template>
  <div class="relative overflow-hidden rounded-3xl border border-admin/20 bg-gradient-to-br from-obsidian via-charcoal to-[#2A1100] p-8 shadow-2xl flex flex-col md:flex-row md:items-center justify-between gap-6">
    <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-admin/5 blur-3xl pointer-events-none"></div>
    <div class="absolute -left-16 -bottom-16 h-48 w-48 rounded-full bg-admin/5 blur-3xl pointer-events-none"></div>
    
    <div class="relative z-10">
      <p class="text-xs uppercase tracking-[0.3em] text-admin/70 font-bold">Reports</p>
      <h1 class="mt-2 font-display text-4xl text-theme-text drop-shadow-lg">
        Operational <span class="text-transparent bg-clip-text bg-gradient-to-r from-admin to-admin-light">Analytics</span>
      </h1>
      <p class="mt-2 text-sm text-ivory/60">Comprehensive overview of revenue, performance, and trends.</p>
    </div>

    <div class="relative z-10 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
      <div class="flex items-center gap-2 bg-black/40 p-2 rounded-xl border border-white/10 backdrop-blur-md">
        <button 
          v-for="option in rangeOptions" 
          :key="option.value"
          @click="$emit('update:selectedRange', option.value)"
          class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-300"
          :class="selectedRange === option.value 
            ? 'bg-admin text-obsidian shadow-[0_0_15px_rgba(255,103,0,0.3)]' 
            : 'text-ivory/60 hover:text-admin hover:bg-white/5'"
        >
          {{ option.label }}
        </button>
      </div>

      <!-- Generate Report Button -->
      <button 
        @click="$emit('generate')"
        :disabled="loading"
        class="flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-admin to-amber-500 text-obsidian font-bold text-sm shadow-[0_0_20px_rgba(255,103,0,0.3)] hover:shadow-[0_0_30px_rgba(255,103,0,0.5)] transition-all duration-300 hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        <DocumentArrowDownIcon class="h-5 w-5" />
        Generate Report
      </button>
    </div>
  </div>
</template>

<script setup>
import { DocumentArrowDownIcon } from '@heroicons/vue/24/outline';

defineProps({
  loading: { type: Boolean, default: false },
  selectedRange: { type: String, required: true },
  rangeOptions: { type: Array, required: true }
});

defineEmits(['update:selectedRange', 'generate']);
</script>
