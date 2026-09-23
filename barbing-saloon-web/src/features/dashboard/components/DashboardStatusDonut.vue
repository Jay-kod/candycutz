<template>
  <div class="rounded-xl border border-theme-border bg-theme-surface p-6">
    <h3 class="text-sm font-bold text-theme-text mb-5 flex items-center gap-2">
      <ChartPieIcon class="h-4 w-4 text-admin" />
      Appointment Status
    </h3>

    <div class="flex items-center gap-6">
      <!-- Donut -->
      <div class="relative w-32 h-32 shrink-0">
        <svg viewBox="0 0 100 100" class="w-full h-full transform -rotate-90">
          <circle cx="50" cy="50" r="38" fill="transparent" stroke="rgba(255,255,255,0.04)" stroke-width="14" />
          <circle v-for="(seg, idx) in donutSegments" :key="idx"
                  cx="50" cy="50" r="38" fill="transparent"
                  :stroke="seg.color"
                  stroke-width="14"
                  :stroke-dasharray="`${seg.length} ${seg.gap}`"
                  :stroke-dashoffset="seg.offset"
                  stroke-linecap="round"
                  class="transition-all duration-1000 ease-out" />
        </svg>
        <div class="absolute inset-0 flex flex-col items-center justify-center">
          <span class="text-2xl font-display text-theme-text">{{ totalAppointments || 0 }}</span>
          <span class="text-[9px] uppercase tracking-widest text-ivory/30">Total</span>
        </div>
      </div>

      <!-- Legend -->
      <div class="flex-1 space-y-2.5">
        <div v-for="item in donutLegend" :key="item.label" class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <div class="h-2.5 w-2.5 rounded-sm" :style="{ backgroundColor: item.color }"></div>
            <span class="text-xs text-ivory/50 capitalize">{{ item.label.replace('_', ' ') }}</span>
          </div>
          <span class="text-xs font-bold text-theme-text tabular-nums">{{ item.count }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { ChartPieIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  statusBreakdown: { type: Object, default: () => ({}) },
  totalAppointments: { type: Number, default: 0 }
});

const donutColors = {
  completed: '#34d399',
  confirmed: '#60a5fa',
  pending: '#fbbf24',
  cancelled: '#f87171',
  no_show: '#c084fc'
};

const donutLegend = computed(() => {
  const bd = props.statusBreakdown || {};
  return Object.entries(bd)
    .map(([label, count]) => ({ label, count, color: donutColors[label] || '#666' }))
    .filter(item => item.count > 0);
});

const donutSegments = computed(() => {
  const total = donutLegend.value.reduce((s, i) => s + i.count, 0);
  if (total === 0) return [];
  const circumference = 2 * Math.PI * 38;
  let offset = 0;
  return donutLegend.value.map(item => {
    const pct = item.count / total;
    const length = pct * circumference;
    const gap = circumference - length;
    const seg = { color: item.color, length, gap, offset: -offset };
    offset += length;
    return seg;
  });
});
</script>
