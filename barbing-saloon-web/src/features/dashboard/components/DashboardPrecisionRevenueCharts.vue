<template>
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
    <!-- Left Card: Stacked Segmented Equalizer Chart (8 cols) -->
    <div class="lg:col-span-8 rounded-xl border border-white/[0.08] bg-[#0B0F13]/90 p-5 sm:p-6 relative overflow-hidden group hover:border-admin/40 transition-all flex flex-col justify-between">
      <!-- Technical Corner Ticks with CandyCutz Accent -->
      <span class="absolute top-1.5 left-1.5 w-1.5 h-1.5 border-t border-l border-admin/30 group-hover:border-admin transition-colors"></span>
      <span class="absolute top-1.5 right-1.5 w-1.5 h-1.5 border-t border-r border-admin/30 group-hover:border-admin transition-colors"></span>
      <span class="absolute bottom-1.5 left-1.5 w-1.5 h-1.5 border-b border-l border-admin/30 group-hover:border-admin transition-colors"></span>
      <span class="absolute bottom-1.5 right-1.5 w-1.5 h-1.5 border-b border-r border-admin/30 group-hover:border-admin transition-colors"></span>

      <!-- Header with Real Revenue Metric -->
      <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 pb-6 border-b border-white/[0.04]">
        <div>
          <div class="text-2xl sm:text-[28px] font-bold text-white tracking-tight font-sans tabular-nums">
            ₦{{ formatCurrency(periodRevenueTotal) }}
          </div>
          <p class="text-xs text-ivory/50 mt-0.5">Total Revenue in selected performance window</p>

          <!-- Legend with System Brand Colors -->
          <div class="flex items-center gap-4 mt-2.5 text-xs text-ivory/60">
            <span class="flex items-center gap-1.5">
              <span class="h-2 w-2 rounded-full bg-admin"></span>
              <span>Revenue (₦)</span>
            </span>
            <span class="flex items-center gap-1.5">
              <span class="h-2 w-2 rounded-full bg-admin-dark/60"></span>
              <span>Bookings volume</span>
            </span>
          </div>
        </div>

        <!-- Timeframe Selector & Trend -->
        <div class="flex flex-col items-start sm:items-end gap-2">
          <div class="inline-flex rounded-lg bg-[#141820] p-0.5 border border-white/[0.08]">
            <button
              v-for="tf in timeframes"
              :key="tf"
              type="button"
              @click="activeTimeframe = tf"
              :class="[
                'rounded-md px-2.5 py-1 text-xs font-semibold transition-all cursor-pointer',
                activeTimeframe === tf
                  ? 'bg-admin text-white shadow-sm'
                  : 'text-ivory/40 hover:text-white'
              ]"
            >
              {{ tf }}
            </button>
          </div>

          <div class="text-[11px] font-medium text-emerald-400 flex items-center gap-1">
            <span>↑</span>
            <span>Real-time data flow</span>
          </div>
        </div>
      </div>

      <!-- Segmented Equalizer Chart Canvas -->
      <div class="relative pt-6 pb-2">
        <div class="flex items-end gap-2 sm:gap-4 h-56">
          <!-- Y-Axis Scale -->
          <div class="flex flex-col justify-between h-full text-[10px] text-ivory/30 font-mono pr-2 shrink-0 select-none pb-5">
            <span>₦{{ formatCompact(maxScale) }}</span>
            <span>₦{{ formatCompact(maxScale * 0.75) }}</span>
            <span>₦{{ formatCompact(maxScale * 0.5) }}</span>
            <span>₦{{ formatCompact(maxScale * 0.25) }}</span>
            <span>₦0</span>
          </div>

          <!-- Stacked Segmented Equalizer Columns -->
          <div class="flex-1 h-full flex flex-col justify-between relative">
            <!-- Horizontal Dashed Guide Lines -->
            <div class="absolute inset-x-0 inset-y-0 flex flex-col justify-between pointer-events-none opacity-10 pb-5">
              <div class="border-b border-dashed border-white w-full"></div>
              <div class="border-b border-dashed border-white w-full"></div>
              <div class="border-b border-dashed border-white w-full"></div>
              <div class="border-b border-dashed border-white w-full"></div>
              <div class="border-b border-white w-full"></div>
            </div>

            <!-- Columns Area populated with real trend data -->
            <div class="flex-1 flex items-end justify-between gap-1 sm:gap-2 z-10 pb-5">
              <div
                v-for="(col, cIdx) in displayColumns"
                :key="'eq-col-' + cIdx"
                class="group/bar flex flex-col-reverse justify-start items-center flex-1 h-full relative cursor-pointer"
              >
                <!-- Segmented Bricks Stack -->
                <div class="flex flex-col-reverse gap-[2px] w-full max-w-[12px]">
                  <span
                    v-for="b in col.totalBricks"
                    :key="'brick-' + b"
                    class="w-full h-[5px] rounded-[1px] transition-all"
                    :class="b <= col.revenueBricks ? 'bg-admin shadow-[0_0_8px_rgba(255,103,0,0.3)]' : 'bg-admin-dark/40 group-hover/bar:bg-admin-dark/60'"
                  ></span>
                </div>

                <!-- Hover Tooltip -->
                <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 opacity-0 group-hover/bar:opacity-100 transition-all pointer-events-none bg-[#1A1E26] border border-admin/30 text-[10px] text-white px-2.5 py-1 rounded shadow-xl whitespace-nowrap z-30 tabular-nums">
                  <span class="text-admin font-bold">{{ col.day }}:</span> ₦{{ formatCurrency(col.revenue) }} ({{ col.bookings }} appts)
                </div>
              </div>
            </div>

            <!-- X-Axis Labels from Real Trend -->
            <div class="flex justify-between text-[10px] text-ivory/40 font-mono pt-1">
              <span v-for="col in displayColumns" :key="col.day" class="truncate max-w-[40px] text-center">
                {{ col.day }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Card: Period Revenue Bars (4 cols) -->
    <div class="lg:col-span-4 rounded-xl border border-white/[0.08] bg-[#0B0F13]/90 p-5 sm:p-6 relative overflow-hidden group hover:border-admin/40 transition-all flex flex-col justify-between">
      <!-- Technical Corner Ticks with CandyCutz Accent -->
      <span class="absolute top-1.5 left-1.5 w-1.5 h-1.5 border-t border-l border-admin/30 group-hover:border-admin transition-colors"></span>
      <span class="absolute top-1.5 right-1.5 w-1.5 h-1.5 border-t border-r border-admin/30 group-hover:border-admin transition-colors"></span>
      <span class="absolute bottom-1.5 left-1.5 w-1.5 h-1.5 border-b border-l border-admin/30 group-hover:border-admin transition-colors"></span>
      <span class="absolute bottom-1.5 right-1.5 w-1.5 h-1.5 border-b border-r border-admin/30 group-hover:border-admin transition-colors"></span>

      <!-- Header with Real Peak/Current Revenue -->
      <div class="flex items-start justify-between pb-6 border-b border-white/[0.04]">
        <div>
          <div class="text-2xl sm:text-[28px] font-bold text-white tracking-tight font-sans tabular-nums">
            ₦{{ formatCurrency(topPeriodVal) }}
          </div>
          <p class="text-xs text-ivory/50 mt-0.5">Top Period Revenue ({{ topPeriodLabel }})</p>
        </div>

        <div class="text-[11px] font-medium text-emerald-400 flex items-center gap-1">
          <span>↑</span>
          <span>Verified</span>
        </div>
      </div>

      <!-- Segmented Dashed Period Revenue Bars -->
      <div class="relative pt-6 pb-2 h-56 flex flex-col justify-between">
        <!-- Bars Container -->
        <div class="flex-1 flex items-end justify-between gap-3 px-2 pb-5 relative">
          <div
            v-for="p in periodBars"
            :key="p.label"
            class="group/pbar flex flex-col items-center flex-1 h-full justify-end relative cursor-pointer"
          >
            <!-- Callout Pill for Highlighted Period -->
            <div
              v-if="p.highlight"
              class="absolute -top-7 left-1/2 -translate-x-1/2 rounded bg-admin text-white border border-admin-light/30 px-1.5 py-0.5 text-[10px] font-bold shadow-lg whitespace-nowrap tabular-nums"
            >
              ₦{{ formatCompact(p.val) }}
            </div>

            <!-- Dashed Segmented Bar -->
            <div class="flex flex-col-reverse gap-[2px] w-3.5 sm:w-4">
              <span
                v-for="seg in p.segments"
                :key="'pseg-' + seg"
                class="w-full h-[5px] rounded-[1px] transition-colors"
                :class="p.highlight ? 'bg-admin' : 'bg-white/20 group-hover/pbar:bg-white/40'"
              ></span>
            </div>
          </div>
        </div>

        <!-- Period Labels -->
        <div class="flex justify-between text-[10px] text-ivory/40 font-mono px-2">
          <span v-for="p in periodBars" :key="'lbl-' + p.label">{{ p.label }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  revenueTrend: { type: Array, default: () => [] },
  bookingTrend: { type: Array, default: () => [] },
  stats: { type: Object, default: () => ({}) },
  revenue: { type: Object, default: () => ({}) }
});

const timeframes = ['7D', '14D', '1M', 'All'];
const activeTimeframe = ref('7D');

const formatCurrency = (val) => {
  return new Intl.NumberFormat('en-NG', { maximumFractionDigits: 0 }).format(Number(val || 0));
};

const formatCompact = (val) => {
  const n = Number(val || 0);
  if (n >= 1000000) return (n / 1000000).toFixed(1) + 'M';
  if (n >= 1000) return (n / 1000).toFixed(1) + 'K';
  return n.toString();
};

const periodRevenueTotal = computed(() => {
  const r = props.revenue || {};
  const s = props.stats || {};
  return Number(r.total_revenue || s.month_revenue || s.week_revenue || s.today_revenue || 0);
});

// Real data columns mapped from props.revenueTrend
const displayColumns = computed(() => {
  const revData = props.revenueTrend || [];
  const bookData = props.bookingTrend || [];

  if (!revData.length) {
    // Generate default week if empty
    const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    return days.map(d => ({
      day: d,
      revenue: 0,
      bookings: 0,
      totalBricks: 6,
      revenueBricks: 2
    }));
  }

  const maxVal = Math.max(...revData.map(d => Number(d.revenue || 0)), 1);

  return revData.map((d, i) => {
    const rev = Number(d.revenue || 0);
    const book = Number(bookData[i]?.count || 0);
    // Relative bricks out of 20
    const revBricks = Math.max(Math.round((rev / maxVal) * 14), rev > 0 ? 3 : 1);
    const bookBricks = Math.min(book * 2, 6);
    const totalBricks = revBricks + bookBricks;

    return {
      day: d.day || `Day ${i + 1}`,
      date: d.date,
      revenue: rev,
      bookings: book,
      totalBricks: Math.max(totalBricks, 4),
      revenueBricks: revBricks
    };
  });
});

const maxScale = computed(() => {
  const revData = props.revenueTrend || [];
  const max = Math.max(...revData.map(d => Number(d.revenue || 0)), 1000);
  return Math.ceil(max / 1000) * 1000;
});

// Real Period Bars for the 4-col card (Today, Yesterday, Week, Month, Total)
const periodBars = computed(() => {
  const s = props.stats || {};
  const r = props.revenue || {};

  const today = Number(s.today_revenue || 0);
  const week = Number(s.week_revenue || 0);
  const month = Number(s.month_revenue || 0);
  const total = Number(r.total_revenue || 0);

  const values = [
    { label: 'Today', val: today },
    { label: 'Week', val: week },
    { label: 'Month', val: month },
    { label: 'Total', val: total }
  ];

  const max = Math.max(...values.map(v => v.val), 1);

  return values.map(v => {
    const segs = Math.max(Math.round((v.val / max) * 18), v.val > 0 ? 4 : 2);
    return {
      label: v.label,
      val: v.val,
      segments: segs,
      highlight: v.val === max && v.val > 0
    };
  });
});

const topPeriodVal = computed(() => {
  const bars = periodBars.value;
  const max = Math.max(...bars.map(b => b.val), 0);
  return max;
});

const topPeriodLabel = computed(() => {
  const bars = periodBars.value;
  const top = bars.find(b => b.highlight);
  return top ? top.label : 'This Month';
});
</script>
