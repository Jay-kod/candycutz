<template>
  <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C]/90 p-5 sm:p-6 backdrop-blur-xl shadow-xl flex flex-col justify-between">
    <!-- Header with Trend Summary -->
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between mb-4">
      <div>
        <h2 class="font-display text-lg sm:text-xl font-bold text-white flex items-center gap-2">
          Revenue <span class="bg-gradient-to-r from-[#FF6700] to-amber-400 bg-clip-text text-transparent">Trajectory</span>
        </h2>
        <p class="text-xs text-white/50">Timeline progression of booked and realized turnover.</p>
      </div>

      <div class="flex items-center gap-2">
        <span class="inline-flex items-center gap-1.5 rounded-lg border border-white/[0.08] bg-white/[0.03] px-3 py-1 text-xs font-semibold text-white/80">
          <span class="h-2 w-2 rounded-full bg-[#FF6700]"></span>
          Peak: ₦{{ formatCurrency(maxRevenue) }}
        </span>
      </div>
    </div>

    <!-- Chart Container -->
    <div class="relative h-64 w-full">
      <!-- Grid reference lines -->
      <div class="absolute inset-x-0 top-2 bottom-8 flex flex-col justify-between pointer-events-none">
        <div class="border-t border-dashed border-white/[0.07] w-full flex justify-end">
          <span class="text-[9px] text-white/30 -mt-2">₦{{ formatCurrency(maxRevenue) }}</span>
        </div>
        <div class="border-t border-dashed border-white/[0.07] w-full flex justify-end">
          <span class="text-[9px] text-white/30 -mt-2">₦{{ formatCurrency(maxRevenue / 2) }}</span>
        </div>
        <div class="border-t border-white/[0.1] w-full flex justify-end">
          <span class="text-[9px] text-white/30 -mt-2">₦0</span>
        </div>
      </div>

      <!-- SVG Path Plot -->
      <div v-if="graphPoints.length > 0" class="absolute left-2 right-10 top-2 bottom-8 z-10">
        <svg class="h-full w-full overflow-visible" viewBox="0 0 100 100" preserveAspectRatio="none">
          <defs>
            <linearGradient id="analyticsRevenueGrad" x1="0%" y1="0%" x2="0%" y2="100%">
              <stop offset="0%" stop-color="#FF6700" stop-opacity="0.35" />
              <stop offset="100%" stop-color="#FF6700" stop-opacity="0.0" />
            </linearGradient>
          </defs>

          <!-- Area fill -->
          <path :d="areaPath" fill="url(#analyticsRevenueGrad)" class="transition-all duration-700 ease-out" />

          <!-- Line stroke -->
          <path
            :d="linePath"
            fill="none"
            stroke="#FF6700"
            stroke-width="2.5"
            stroke-linecap="round"
            stroke-linejoin="round"
            class="transition-all duration-700 ease-out"
            style="filter: drop-shadow(0 0 8px rgba(255,103,0,0.6));"
          />
        </svg>

        <!-- Interactive Data Points -->
        <div
          v-for="(p, idx) in graphPoints"
          :key="'pt-' + idx"
          class="group/pt absolute -ml-2 -mt-2 h-4 w-4 cursor-pointer rounded-full border-2 border-[#FF6700] bg-[#0B0F13] shadow-[0_0_10px_rgba(255,103,0,0.6)] transition-all duration-200 hover:scale-150 hover:bg-[#FF6700] z-20"
          :style="{ left: `${p.x}%`, top: `${p.y}%` }"
        >
          <!-- Tooltip -->
          <div class="pointer-events-none absolute bottom-full left-1/2 -translate-x-1/2 mb-2 scale-90 opacity-0 transition-all duration-200 group-hover/pt:scale-100 group-hover/pt:opacity-100 z-30">
            <div class="rounded-xl border border-white/20 bg-[#0B0F13]/95 px-3 py-1.5 shadow-2xl backdrop-blur-md whitespace-nowrap text-center">
              <p class="text-[10px] uppercase tracking-wider text-white/50">{{ p.label }}</p>
              <p class="font-display text-xs font-bold text-[#FF6700]">₦{{ formatCurrency(p.value) }}</p>
            </div>
          </div>
        </div>

        <!-- X-axis Labels -->
        <div
          v-for="(p, idx) in graphPoints"
          :key="'lbl-' + idx"
          class="absolute -bottom-7 -ml-8 w-16 text-center"
          :style="{ left: `${p.x}%` }"
        >
          <span class="text-[10px] font-semibold text-white/40 tracking-wider uppercase">{{ p.shortLabel }}</span>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="flex h-full items-center justify-center text-xs text-white/40">
        No revenue trends reported for this range.
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  trends: {
    type: Array,
    default: () => []
  },
  formatCurrency: {
    type: Function,
    default: (val) => Number(val || 0).toLocaleString()
  }
});

const maxRevenue = computed(() => {
  if (!props.trends || props.trends.length === 0) return 10000;
  const values = props.trends.map(t => Number(t.revenue || 0));
  const max = Math.max(...values, 0);
  return max > 0 ? max : 10000;
});

const graphPoints = computed(() => {
  if (!props.trends || props.trends.length === 0) return [];
  const max = maxRevenue.value;
  const total = props.trends.length;

  return props.trends.map((item, idx) => {
    const val = Number(item.revenue || 0);
    const x = total === 1 ? 50 : (idx / (total - 1)) * 100;
    const y = 100 - (val / max) * 85 - 5; // keep inside bounds
    const rawLabel = item.month || item.date || `Day ${idx + 1}`;
    const shortLabel = String(rawLabel).length > 6 ? String(rawLabel).slice(-5) : rawLabel;

    return {
      x,
      y,
      value: val,
      label: rawLabel,
      shortLabel
    };
  });
});

const linePath = computed(() => {
  const pts = graphPoints.value;
  if (pts.length === 0) return '';
  return pts.reduce((acc, p, idx) => {
    return idx === 0 ? `M ${p.x} ${p.y}` : `${acc} L ${p.x} ${p.y}`;
  }, '');
});

const areaPath = computed(() => {
  const pts = graphPoints.value;
  if (pts.length === 0) return '';
  const first = pts[0];
  const last = pts[pts.length - 1];
  return `${linePath.value} L ${last.x} 100 L ${first.x} 100 Z`;
});
</script>

