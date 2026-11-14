<template>
  <div class="lg:col-span-4 rounded-xl border border-slate-200 dark:border-theme-border bg-white dark:bg-theme-surface/90 p-5 sm:p-6 relative overflow-hidden group hover:border-admin/40 transition-all flex flex-col justify-between shadow-sm">
    <!-- Technical Corner Ticks with CandyCutz Accent -->
    <span class="absolute top-1.5 left-1.5 w-1.5 h-1.5 border-t border-l border-admin/30 group-hover:border-admin transition-colors"></span>
    <span class="absolute top-1.5 right-1.5 w-1.5 h-1.5 border-t border-r border-admin/30 group-hover:border-admin transition-colors"></span>
    <span class="absolute bottom-1.5 left-1.5 w-1.5 h-1.5 border-b border-l border-admin/30 group-hover:border-admin transition-colors"></span>
    <span class="absolute bottom-1.5 right-1.5 w-1.5 h-1.5 border-b border-r border-admin/30 group-hover:border-admin transition-colors"></span>

    <!-- Header with Real Peak/Current Revenue -->
    <div class="flex items-start justify-between pb-6 border-b border-slate-100 dark:border-theme-border">
      <div>
        <div class="text-2xl sm:text-[28px] font-bold text-slate-900 dark:text-theme-text tracking-tight font-sans tabular-nums">
          ₦{{ formatCurrency(topPeriodVal) }}
        </div>
        <p class="text-xs text-slate-500 dark:text-theme-text-muted mt-0.5">Monthly Revenue ({{ currentMonthName }})</p>
      </div>

      <div class="text-[11px] font-medium text-emerald-600 dark:text-emerald-400 flex items-center gap-1 font-mono">
        <span>●</span>
        <span>Verified Ledger</span>
      </div>
    </div>

    <!-- Segmented Dashed Monthly Bars -->
    <div class="relative pt-6 pb-2 h-56 flex flex-col justify-between">
      <div class="flex-1 flex items-end justify-between gap-3 sm:gap-4 md:gap-5 px-3 pb-5 relative">
        <div
          v-for="m in monthlyBars"
          :key="m.month"
          class="group/mbar flex flex-col items-center flex-1 h-full justify-end relative cursor-pointer"
        >
          <!-- Callout Pill for Highlighted Month -->
          <div
            v-if="m.highlight"
            class="absolute -top-7 left-1/2 -translate-x-1/2 rounded bg-admin text-white border border-admin-light/30 px-2 py-0.5 text-[10px] font-bold shadow-lg whitespace-nowrap tabular-nums z-20"
          >
            ₦{{ formatCompact(m.val) }}
          </div>

          <!-- Wide Dashed Segmented Bar -->
          <div class="flex flex-col-reverse gap-[2px] w-7 sm:w-8 md:w-9">
            <span
              v-for="seg in m.segments"
              :key="'mseg-' + seg"
              class="w-full h-[5px] sm:h-[6px] rounded-[1px] transition-colors"
              :class="m.highlight ? 'bg-admin shadow-[0_0_8px_rgba(255,103,0,0.3)]' : 'bg-slate-200 dark:bg-[#353945] group-hover/mbar:bg-slate-300 dark:group-hover/mbar:bg-[#4A4F5E]'"
            ></span>
          </div>
        </div>
      </div>

      <!-- Month Labels -->
      <div class="flex justify-between text-[10px] text-slate-500 dark:text-theme-text-muted font-mono px-3">
        <span v-for="m in monthlyBars" :key="'lbl-' + m.month">{{ m.month }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  monthlyRevenueTrend: { type: Array, default: () => [] },
  periodRevenueTotal: { type: Number, default: 0 },
});

const formatCurrency = (val) => {
  return new Intl.NumberFormat('en-NG', { maximumFractionDigits: 0 }).format(Number(val || 0));
};

const formatCompact = (val) => {
  const n = Number(val || 0);
  if (n >= 1000000000) return (n / 1000000000).toFixed(1) + 'B';
  if (n >= 1000000) return (n / 1000000).toFixed(1) + 'M';
  if (n >= 1000) return (n / 1000).toFixed(1) + 'K';
  return n.toString();
};

const currentMonthName = computed(() => {
  return new Date().toLocaleDateString('en-US', { month: 'short' });
});

const monthlyBars = computed(() => {
  const mTrend = props.monthlyRevenueTrend || [];
  if (mTrend.length) {
    const maxVal = Math.max(...mTrend.map((m) => Number(m.revenue || 0)), 1);
    return mTrend.map((m) => {
      const rev = Number(m.revenue || 0);
      const segs = rev > 0 ? Math.max(Math.round((rev / maxVal) * 18), 2) : 1;
      return {
        month: m.month,
        val: rev,
        highlight: Boolean(m.is_current),
        segments: segs,
      };
    });
  }
  const currentVal = props.periodRevenueTotal;
  return [
    { month: 'May', segments: 1, highlight: false, val: 0 },
    { month: 'Jun', segments: 1, highlight: false, val: 0 },
    { month: 'Jul', segments: 1, highlight: false, val: 0 },
    { month: 'Aug', segments: 1, highlight: false, val: 0 },
    { month: 'Sep', segments: 1, highlight: false, val: 0 },
    { month: currentMonthName.value, segments: currentVal > 0 ? 18 : 1, highlight: true, val: currentVal },
  ];
});

const topPeriodVal = computed(() => {
  const current = monthlyBars.value.find((m) => m.highlight);
  return current ? current.val : props.periodRevenueTotal;
});
</script>

