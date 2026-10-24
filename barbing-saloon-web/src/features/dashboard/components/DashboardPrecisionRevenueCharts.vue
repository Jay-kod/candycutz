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
          <p class="text-xs text-ivory/50 mt-0.5">Total Salon Revenue ({{ timeframeLabel }})</p>

          <!-- Legend with System Brand Colors -->
          <div class="flex items-center gap-4 mt-2.5 text-xs text-ivory/60">
            <span class="flex items-center gap-1.5">
              <span class="h-2 w-2 rounded-full bg-admin"></span>
              <span>Revenue Volume (₦)</span>
            </span>
            <span class="flex items-center gap-1.5">
              <span class="h-2 w-2 rounded-full bg-[#353945]"></span>
              <span>Appointments Volume</span>
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

          <div class="text-[11px] font-medium text-emerald-400 flex items-center gap-1 font-mono">
            <span>●</span>
            <span>Live Salon Stream</span>
          </div>
        </div>
      </div>

      <!-- Segmented Equalizer Chart Canvas: Dense, Wide & Closely Spaced Bars -->
      <div class="relative pt-6 pb-2">
        <div class="flex items-end gap-2 sm:gap-3 h-56">
          <!-- Y-Axis Scale -->
          <div class="flex flex-col justify-between h-full text-[10px] text-ivory/30 font-mono pr-1 shrink-0 select-none pb-5">
            <span>{{ yAxisLabels[4] }}</span>
            <span>{{ yAxisLabels[3] }}</span>
            <span>{{ yAxisLabels[2] }}</span>
            <span>{{ yAxisLabels[1] }}</span>
            <span>{{ yAxisLabels[0] }}</span>
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

            <!-- 28 Columns: Wider and Closer to Each Other -->
            <div class="flex-1 flex items-end justify-between gap-[2px] sm:gap-[3px] z-10 pb-5 w-full">
              <div
                v-for="(col, cIdx) in activeColumns"
                :key="'eq-col-' + cIdx"
                class="group/bar flex flex-col-reverse justify-start items-center flex-1 h-full relative cursor-pointer"
              >
                <!-- Segmented Bricks Stack: Wide and closely packed across each column -->
                <div class="flex flex-col-reverse gap-[2px] w-full">
                  <span
                    v-for="b in col.totalBricks"
                    :key="'brick-' + b"
                    class="w-full h-[5px] sm:h-[6px] rounded-[1px] transition-all"
                    :class="b <= col.whiteBricks ? 'bg-admin shadow-[0_0_8px_rgba(255,103,0,0.3)]' : 'bg-[#353945] group-hover/bar:bg-[#4A4F5E]'"
                  ></span>
                </div>

                <!-- Hover Tooltip -->
                <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 opacity-0 group-hover/bar:opacity-100 transition-all pointer-events-none bg-[#1A1E26] border border-admin/30 text-[10px] text-white px-2.5 py-1 rounded shadow-xl whitespace-nowrap z-30 tabular-nums">
                  <span class="text-admin font-bold">{{ col.dateLabel }}:</span> ₦{{ formatCompact(col.revenue) }} ({{ col.bookings }} appts)
                </div>
              </div>
            </div>

            <!-- X-Axis Labels: Evenly spaced dates along the 28 columns -->
            <div class="flex justify-between text-[10px] text-ivory/40 font-mono pt-1">
              <span v-for="dl in spacedDateLabels" :key="dl" class="truncate">
                {{ dl }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Card: Monthly Segmented Bar Chart with Wide Bars (4 cols) -->
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
          <p class="text-xs text-ivory/50 mt-0.5">Monthly Revenue ({{ currentMonthName }})</p>
        </div>

        <div class="text-[11px] font-medium text-emerald-400 flex items-center gap-1 font-mono">
          <span>●</span>
          <span>Verified Ledger</span>
        </div>
      </div>

      <!-- Segmented Dashed Monthly Bars: Wide Bars -->
      <div class="relative pt-6 pb-2 h-56 flex flex-col justify-between">
        <!-- Bars Container with Wide Bars -->
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
                :class="m.highlight ? 'bg-admin shadow-[0_0_8px_rgba(255,103,0,0.3)]' : 'bg-[#353945] group-hover/mbar:bg-[#4A4F5E]'"
              ></span>
            </div>
          </div>
        </div>

        <!-- Month Labels -->
        <div class="flex justify-between text-[10px] text-ivory/40 font-mono px-3">
          <span v-for="m in monthlyBars" :key="'lbl-' + m.month">{{ m.month }}</span>
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
  thirtyDayTrend: { type: Array, default: () => [] },
  monthlyRevenueTrend: { type: Array, default: () => [] },
  stats: { type: Object, default: () => ({}) },
  revenue: { type: Object, default: () => ({}) }
});

const timeframes = ['14D', '1M', '3M', '6M'];
const activeTimeframe = ref('1M');

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

const periodRevenueTotal = computed(() => {
  const r = props.revenue || {};
  const s = props.stats || {};
  return Number(r.total_revenue || s.month_revenue || s.week_revenue || s.today_revenue || 0);
});

const currentMonthName = computed(() => {
  return new Date().toLocaleDateString('en-US', { month: 'short' });
});

// Y-Axis scale labels
const yAxisLabels = computed(() => {
  const total = periodRevenueTotal.value;
  if (total >= 10000000) {
    return ['0', '25M', '50M', '75M', '100M'];
  }
  return ['0', '5k', '10k', '15k', '20k'];
});

// Dynamic Columns mapped directly from real API 30-day timeline
const thirtyDayColumns = computed(() => {
  const trend = props.thirtyDayTrend || [];
  if (trend.length) {
    const maxRev = Math.max(...trend.map(d => Number(d.revenue || 0)), 1);
    const maxCount = Math.max(...trend.map(d => Number(d.count || 0)), 1);
    return trend.map(d => {
      const rev = Number(d.revenue || 0);
      const cnt = Number(d.count || 0);
      const orangeBricks = rev > 0 ? Math.max(Math.round((rev / maxRev) * 16), 2) : 0;
      const slateBricks = cnt > 0 ? Math.max(Math.round((cnt / maxCount) * 6), 1) : 0;
      const totalBricks = Math.max(orangeBricks + slateBricks, 1);
      return {
        whiteBricks: orangeBricks,
        totalBricks,
        dateLabel: d.date_label || d.date,
        revenue: rev,
        bookings: cnt
      };
    });
  }

  // Fallback to 7-day trend mapped onto 28-day range if 30-day not yet populated
  const revTrend = props.revenueTrend || [];
  const bookTrend = props.bookingTrend || [];
  const now = new Date();
  return Array.from({ length: 28 }, (_, idx) => {
    const dayOffset = 27 - idx;
    const dateObj = new Date(now.getTime() - dayOffset * 24 * 60 * 60 * 1000);
    const dateLabel = dateObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    const revItem = revTrend[idx - 21];
    const bookItem = bookTrend[idx - 21];
    const rev = Number(revItem?.revenue || 0);
    const cnt = Number(bookItem?.count || 0);
    return {
      whiteBricks: rev > 0 ? 8 : 0,
      totalBricks: rev > 0 ? 14 : (cnt > 0 ? 4 : 1),
      dateLabel,
      revenue: rev,
      bookings: cnt
    };
  });
});

// Timeframe label
const timeframeLabel = computed(() => {
  if (activeTimeframe.value === '14D') return '14 days';
  if (activeTimeframe.value === '3M') return '3 months';
  if (activeTimeframe.value === '6M') return '6 months';
  return '30 days';
});

// Dynamic columns based on active timeframe
const activeColumns = computed(() => {
  if (activeTimeframe.value === '14D') {
    return thirtyDayColumns.value.slice(-14);
  }
  return thirtyDayColumns.value;
});

// Spaced date labels along the active columns
const spacedDateLabels = computed(() => {
  const cols = activeColumns.value;
  if (!cols.length) return [];
  if (cols.length <= 14) {
    return [0, 4, 9, cols.length - 1].map(i => cols[i]?.dateLabel || '');
  }
  const indices = [0, 4, 8, 12, 16, 20, 24, cols.length - 1];
  return indices.map(i => cols[i]?.dateLabel || '');
});

// Right Card: Real Monthly Performance Bars
const monthlyBars = computed(() => {
  const mTrend = props.monthlyRevenueTrend || [];
  if (mTrend.length) {
    const maxVal = Math.max(...mTrend.map(m => Number(m.revenue || 0)), 1);
    return mTrend.map(m => {
      const rev = Number(m.revenue || 0);
      const segs = rev > 0 ? Math.max(Math.round((rev / maxVal) * 18), 2) : 1;
      return {
        month: m.month,
        val: rev,
        highlight: Boolean(m.is_current),
        segments: segs
      };
    });
  }
  const currentVal = periodRevenueTotal.value;
  return [
    { month: 'May', segments: 1, highlight: false, val: 0 },
    { month: 'Jun', segments: 1, highlight: false, val: 0 },
    { month: 'Jul', segments: 1, highlight: false, val: 0 },
    { month: 'Aug', segments: 1, highlight: false, val: 0 },
    { month: 'Sep', segments: 1, highlight: false, val: 0 },
    { month: currentMonthName.value, segments: currentVal > 0 ? 18 : 1, highlight: true, val: currentVal }
  ];
});

const topPeriodVal = computed(() => {
  const current = monthlyBars.value.find(m => m.highlight);
  return current ? current.val : periodRevenueTotal.value;
});
</script>
