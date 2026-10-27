<template>
  <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C]/90 p-5 sm:p-6 backdrop-blur-xl shadow-xl">
    <div class="flex items-center justify-between mb-4">
      <div>
        <h2 class="font-display text-lg sm:text-xl font-bold text-white flex items-center gap-2">
          Booking <span class="bg-gradient-to-r from-[#FF6700] to-rose-400 bg-clip-text text-transparent">Volume</span>
        </h2>
        <p class="text-xs text-white/50">Daily distribution of completed service slots.</p>
      </div>

      <span class="inline-flex items-center gap-1.5 rounded-lg border border-white/[0.08] bg-white/[0.03] px-2.5 py-1 text-xs font-semibold text-white/70">
        Peak: {{ maxCount }} appts
      </span>
    </div>

    <!-- Booking Bars -->
    <div class="space-y-3">
      <div
        v-for="item in normalizedTrends"
        :key="item.label"
        class="group"
      >
        <div class="flex justify-between text-xs mb-1.5">
          <span class="font-medium text-white/70 group-hover:text-white transition-colors">
            {{ item.label }}
          </span>
          <span class="font-display font-bold text-white tabular-nums">
            {{ item.count }} <span class="text-[10px] text-white/40 font-normal">appts</span>
          </span>
        </div>

        <div class="h-2 w-full overflow-hidden rounded-full bg-white/[0.05] border border-white/[0.04]">
          <div
            class="h-full rounded-full bg-gradient-to-r from-[#FF6700] to-rose-500 transition-all duration-700"
            :style="{ width: `${item.percentage}%` }"
          ></div>
        </div>
      </div>

      <div v-if="normalizedTrends.length === 0" class="py-8 text-center text-xs text-white/40">
        No booking trend volume available for this timeframe.
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
  }
});

const maxCount = computed(() => {
  if (!props.trends || props.trends.length === 0) return 0;
  const counts = props.trends.map(t => Number(t.count || 0));
  return Math.max(...counts, 0);
});

const normalizedTrends = computed(() => {
  if (!props.trends || props.trends.length === 0) return [];
  const max = maxCount.value > 0 ? maxCount.value : 1;

  return props.trends.map(item => {
    const count = Number(item.count || 0);
    const label = item.month || item.date || 'Period';
    const percentage = Math.min(Math.round((count / max) * 100), 100);

    return {
      label,
      count,
      percentage
    };
  });
});
</script>
