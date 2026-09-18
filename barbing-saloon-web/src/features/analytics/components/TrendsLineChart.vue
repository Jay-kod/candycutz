<template>
  <div class="relative h-64 mt-4">
    <!-- Grid & Y-axis (Optional, used by Customer) -->
    <div v-if="showYAxis" class="absolute inset-x-0 top-0 bottom-8 flex">
      <div class="w-8 flex flex-col justify-between text-[10px] text-theme-muted/70 font-bold pb-0.5">
        <span>{{ maxValue }}</span>
        <span>{{ Math.round(maxValue / 2) }}</span>
        <span>0</span>
      </div>
      <div class="flex-1 flex flex-col justify-between relative pt-1.5 pb-1.5">
        <div class="border-t border-dashed border-theme-border/30 w-full"></div>
        <div class="border-t border-dashed border-theme-border/30 w-full"></div>
        <div class="border-t border-theme-border/50 w-full"></div>
      </div>
    </div>

    <!-- Line Graph Container -->
    <div class="absolute z-10" :class="showYAxis ? 'left-8 right-6 top-1.5 bottom-9' : 'left-8 right-6 top-6 bottom-12'">
      <svg class="w-full h-full overflow-visible" viewBox="0 0 100 100" preserveAspectRatio="none">
        <!-- Area fill -->
        <path :d="areaPath" fill="url(#lineGradient)" class="transition-all duration-500 ease-out opacity-40" />
        <!-- Line -->
        <path :d="linePath" fill="none" stroke="#D4AF37" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="transition-all duration-500 ease-out" style="filter: drop-shadow(0 0 8px rgba(212,175,55,0.5))" />
        <defs>
          <linearGradient id="lineGradient" x1="0%" y1="0%" x2="0%" y2="100%">
            <stop offset="0%" stop-color="#D4AF37" stop-opacity="0.8" />
            <stop offset="100%" stop-color="#D4AF37" stop-opacity="0" />
          </linearGradient>
        </defs>
      </svg>
      
      <!-- Interactive Data Points -->
      <div v-for="(p, idx) in graphPoints" :key="'point-'+idx" 
           class="absolute w-3.5 h-3.5 -ml-[7px] -mt-[7px] rounded-full bg-charcoal border-2 border-gold shadow-[0_0_10px_rgba(212,175,55,0.6)] group/point cursor-pointer transition-all duration-300 hover:scale-[1.8] hover:bg-gold z-20"
           :style="{ left: `${p.x}%`, top: `${p.y}%` }">
        <!-- Tooltip -->
        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-3 opacity-0 group-hover/point:opacity-100 transition-all duration-200 bg-obsidian border border-gold/30 text-xs px-3 py-1.5 rounded-lg shadow-xl text-gold font-bold whitespace-nowrap z-30 pointer-events-none scale-50 group-hover/point:scale-100 origin-bottom">
          <span v-if="tooltipPrefix">{{ tooltipPrefix }}</span>{{ Number(p.value).toLocaleString() }}<span v-if="tooltipSuffix"> {{ tooltipSuffix }}{{ p.value === 1 ? '' : 's' }}</span>
        </div>
      </div>

      <!-- X-axis labels -->
      <div v-for="(p, idx) in graphPoints" :key="'label-'+idx"
           class="absolute -bottom-8 w-16 -ml-8 flex justify-center"
           :style="{ left: `${p.x}%` }">
        <span class="text-[10px] text-theme-muted uppercase tracking-wider font-bold">{{ p.label }}</span>
      </div>
    </div>
    
    <div v-if="trends.length === 0" class="w-full h-full flex items-center justify-center text-ivory/30 text-sm absolute inset-0 z-0">
      {{ emptyMessage }}
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
  valueKey: {
    type: String,
    required: true
  },
  labelKey: {
    type: String,
    default: 'month'
  },
  emptyMessage: {
    type: String,
    default: 'No data available for this range.'
  },
  showYAxis: {
    type: Boolean,
    default: false
  },
  tooltipPrefix: {
    type: String,
    default: ''
  },
  tooltipSuffix: {
    type: String,
    default: ''
  }
});

const maxValue = computed(() => {
  if (props.trends.length === 0) return 0;
  return Math.max(...props.trends.map(t => Number(t[props.valueKey])), 1);
});

const normalizedTrends = computed(() => {
  if (props.trends.length === 0) return [];
  const max = maxValue.value;
  return props.trends.map(t => ({
    label: t[props.labelKey],
    value: t[props.valueKey],
    percentage: Math.max((Number(t[props.valueKey]) / max) * 100, 4) // minimum height
  }));
});

const graphPoints = computed(() => {
  const trends = normalizedTrends.value;
  if (trends.length === 0) return [];
  if (trends.length === 1) {
    return [{ x: 50, y: 100 - trends[0].percentage, value: trends[0].value, label: trends[0].label }];
  }
  
  return trends.map((t, idx) => {
    const x = (idx / (trends.length - 1)) * 100;
    const y = 100 - t.percentage;
    return { x, y, value: t.value, label: t.label };
  });
});

const linePath = computed(() => {
  const points = graphPoints.value;
  if (points.length === 0) return '';
  if (points.length === 1) return `M 0,${points[0].y} L 100,${points[0].y}`;
  return points.map((p, i) => `${i === 0 ? 'M' : 'L'} ${p.x},${p.y}`).join(' ');
});

const areaPath = computed(() => {
  const points = graphPoints.value;
  if (points.length === 0) return '';
  const path = linePath.value;
  return `${path} L 100,100 L 0,100 Z`;
});
</script>
