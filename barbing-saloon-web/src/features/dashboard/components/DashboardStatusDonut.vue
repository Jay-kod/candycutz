<template>
  <div class="rounded-2xl border border-white/[0.07] bg-[#0A0F12]/80 backdrop-blur-xl p-5 lg:p-6 shadow-xl flex flex-col justify-between">
    <!-- Header -->
    <div class="flex items-center justify-between pb-4 border-b border-white/[0.05]">
      <div class="flex items-center gap-3">
        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-admin/10 border border-admin/20 text-admin shrink-0">
          <ChartPieIcon class="h-5 w-5" />
        </div>
        <div>
          <h2 class="text-sm font-bold text-white tracking-wide">Appointment Status</h2>
          <p class="text-[11px] text-white/40">Status breakdown ratio</p>
        </div>
      </div>

      <!-- Completion Ratio Pill -->
      <div class="rounded-full bg-emerald-500/10 px-2.5 py-0.5 border border-emerald-500/20 text-[10px] font-bold text-emerald-400">
        {{ completionPercent }}% Completed
      </div>
    </div>

    <!-- Donut Canvas & Legend -->
    <div class="pt-6 pb-2 flex flex-col sm:flex-row items-center justify-around gap-6 h-52 sm:h-56">
      <!-- SVG Donut -->
      <div class="relative w-36 h-36 shrink-0">
        <svg viewBox="0 0 100 100" class="w-full h-full transform -rotate-90">
          <!-- Background track -->
          <circle 
            cx="50" 
            cy="50" 
            r="38" 
            fill="transparent" 
            stroke="rgba(255,255,255,0.05)" 
            stroke-width="12" 
          />
          <!-- Status Segments -->
          <circle 
            v-for="(seg, idx) in donutSegments" 
            :key="idx"
            cx="50" 
            cy="50" 
            r="38" 
            fill="transparent"
            :stroke="seg.color"
            stroke-width="12"
            :stroke-dasharray="`${seg.length} ${seg.gap}`"
            :stroke-dashoffset="seg.offset"
            stroke-linecap="round"
            class="transition-all duration-1000 ease-out" 
          />
        </svg>

        <!-- Center Label -->
        <div class="absolute inset-0 flex flex-col items-center justify-center">
          <span class="text-2xl font-bold text-white tabular-nums tracking-tight font-sans">
            {{ computedTotal }}
          </span>
          <span class="text-[9px] uppercase tracking-wider font-semibold text-white/40">
            Total
          </span>
        </div>
      </div>

      <!-- Status Legend List -->
      <div class="flex-1 w-full max-w-[200px] space-y-2">
        <div 
          v-for="item in donutLegend" 
          :key="item.label" 
          class="flex items-center justify-between group"
        >
          <div class="flex items-center gap-2 min-w-0">
            <span class="h-2 w-2 rounded-full shrink-0" :style="{ backgroundColor: item.color }"></span>
            <span class="text-xs text-white/60 capitalize truncate group-hover:text-white transition-colors">
              {{ item.label.replace('_', ' ') }}
            </span>
          </div>
          <div class="flex items-center gap-2 shrink-0">
            <span class="text-xs font-bold text-white tabular-nums">{{ item.count }}</span>
            <span class="text-[10px] text-white/30 tabular-nums">({{ item.pct }}%)</span>
          </div>
        </div>

        <div v-if="!donutLegend.length" class="text-center py-4 text-xs text-white/30">
          No appointment records
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
  completed: '#10B981', // Emerald
  confirmed: '#38BDF8', // Sky
  pending: '#F59E0B',   // Amber
  cancelled: '#EF4444', // Red
  no_show: '#A855F7'    // Purple
};

const donutLegend = computed(() => {
  const bd = props.statusBreakdown || {};
  const entries = Object.entries(bd)
    .map(([label, count]) => ({
      label,
      count: Number(count || 0),
      color: donutColors[label] || '#64748B'
    }))
    .filter(item => item.count > 0);

  const total = entries.reduce((s, i) => s + i.count, 0);

  return entries.map(item => ({
    ...item,
    pct: total > 0 ? Math.round((item.count / total) * 100) : 0
  }));
});

const computedTotal = computed(() => {
  const sumFromBreakdown = donutLegend.value.reduce((s, i) => s + i.count, 0);
  return sumFromBreakdown > 0 ? sumFromBreakdown : (props.totalAppointments || 0);
});

const completionPercent = computed(() => {
  const completedItem = donutLegend.value.find(i => i.label === 'completed');
  if (!completedItem || computedTotal.value === 0) return 0;
  return Math.round((completedItem.count / computedTotal.value) * 100);
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
