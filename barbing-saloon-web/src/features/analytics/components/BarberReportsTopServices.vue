<template>
  <div class="rounded-2xl border border-theme-border bg-theme-surface p-6 backdrop-blur-sm">
    <h2 class="text-lg font-bold text-theme-text mb-6 flex items-center gap-2">
      <SparklesIcon class="h-5 w-5 text-gold" /> Most Popular Services
    </h2>
    
    <div class="space-y-4" v-if="topServices.length > 0">
      <div v-for="(service, index) in topServices" :key="index" 
            class="flex items-center justify-between p-4 rounded-xl bg-theme-bg/50 border border-theme-border hover:bg-theme-surface transition-colors">
        <div class="flex items-center gap-4">
          <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-500/10 font-bold text-emerald-400">
            #{{ index + 1 }}
          </div>
          <div>
            <p class="font-medium text-theme-text">{{ service.name }}</p>
            <p class="text-xs text-theme-muted">{{ service.count }} times booked</p>
          </div>
        </div>
        <div class="text-right">
          <p class="font-display text-lg text-gold-light">₦{{ Number(service.revenue).toLocaleString() }}</p>
          <p class="text-xs text-theme-muted">Generated revenue</p>
        </div>
      </div>
    </div>
    <div v-else class="text-center py-10 text-theme-muted">
      No service data available for this period.
    </div>
  </div>
</template>

<script setup>
import { SparklesIcon } from '@heroicons/vue/24/outline';

defineProps({
  topServices: { type: Array, required: true }
});
</script>
