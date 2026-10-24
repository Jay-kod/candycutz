<template>
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
    <!-- Left Card: Stacked Segmented Equalizer Chart (8 cols) -->
    <div class="lg:col-span-8 rounded-xl border border-[#1E2025] bg-[#0E0F12] p-5 sm:p-6 relative overflow-hidden group hover:border-[#2D3038] transition-all flex flex-col justify-between">
      <!-- Technical Corner Ticks -->
      <span class="absolute top-1.5 left-1.5 w-1.5 h-1.5 border-t border-l border-white/10 group-hover:border-white/25 transition-colors"></span>
      <span class="absolute top-1.5 right-1.5 w-1.5 h-1.5 border-t border-r border-white/10 group-hover:border-white/25 transition-colors"></span>
      <span class="absolute bottom-1.5 left-1.5 w-1.5 h-1.5 border-b border-l border-white/10 group-hover:border-white/25 transition-colors"></span>
      <span class="absolute bottom-1.5 right-1.5 w-1.5 h-1.5 border-b border-r border-white/10 group-hover:border-white/25 transition-colors"></span>

      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 pb-6 border-b border-white/[0.04]">
        <div>
          <div class="text-2xl sm:text-[28px] font-bold text-white tracking-tight font-sans tabular-nums">
            ₦{{ formattedTotalSales }}
          </div>
          <p class="text-xs text-white/50 mt-0.5">Total Sales in last 30 days</p>

          <!-- Legend -->
          <div class="flex items-center gap-4 mt-2.5 text-xs text-white/50">
            <span class="flex items-center gap-1.5">
              <span class="h-2 w-2 rounded-full bg-white"></span>
              <span>New user</span>
            </span>
            <span class="flex items-center gap-1.5">
              <span class="h-2 w-2 rounded-full bg-[#40434C]"></span>
              <span>Existing user</span>
            </span>
          </div>
        </div>

        <!-- Timeframe Selector & Trend -->
        <div class="flex flex-col items-start sm:items-end gap-2">
          <div class="inline-flex rounded-lg bg-[#14161B] p-0.5 border border-white/[0.06]">
            <button
              v-for="tf in timeframes"
              :key="tf"
              type="button"
              @click="activeTimeframe = tf"
              :class="[
                'rounded-md px-2.5 py-1 text-xs font-semibold transition-all cursor-pointer',
                activeTimeframe === tf
                  ? 'bg-[#22252C] text-white shadow-sm'
                  : 'text-white/40 hover:text-white/70'
              ]"
            >
              {{ tf }}
            </button>
          </div>

          <div class="text-[11px] font-medium text-emerald-400 flex items-center gap-1">
            <span>↑</span>
            <span>1.5% vs last 30 days</span>
          </div>
        </div>
      </div>

      <!-- Segmented Equalizer Chart Canvas -->
      <div class="relative pt-6 pb-2">
        <div class="flex items-end gap-2 sm:gap-4 h-56">
          <!-- Y-Axis Labels -->
          <div class="flex flex-col justify-between h-full text-[10px] text-white/30 font-mono pr-2 shrink-0 select-none pb-5">
            <span>20k</span>
            <span>15k</span>
            <span>10k</span>
            <span>5k</span>
            <span>0k</span>
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

            <!-- Columns Area -->
            <div class="flex-1 flex items-end justify-between gap-1 sm:gap-1.5 z-10 pb-5">
              <div
                v-for="(col, cIdx) in equalizerColumns"
                :key="'eq-col-' + cIdx"
                class="group/bar flex flex-col-reverse justify-start items-center flex-1 h-full relative cursor-pointer"
              >
                <!-- Segmented Bricks Stack -->
                <div class="flex flex-col-reverse gap-[2px] w-full max-w-[10px]">
                  <span
                    v-for="b in col.totalBricks"
                    :key="'brick-' + b"
                    class="w-full h-[5px] rounded-[1px] transition-colors"
                    :class="b <= col.whiteBricks ? 'bg-white' : 'bg-[#40434C]'"
                  ></span>
                </div>

                <!-- Hover Tooltip -->
                <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 opacity-0 group-hover/bar:opacity-100 transition-all pointer-events-none bg-[#1A1C22] border border-white/10 text-[10px] text-white px-2 py-1 rounded shadow-lg whitespace-nowrap z-30">
                  <span class="text-white/40">{{ col.label }}:</span> ₦{{ col.totalVal }}K
                </div>
              </div>
            </div>

            <!-- X-Axis Labels -->
            <div class="flex justify-between text-[10px] text-white/35 font-mono pt-1">
              <span v-for="date in xDates" :key="date">{{ date }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Card: Monthly Segmented Bar Chart (4 cols) -->
    <div class="lg:col-span-4 rounded-xl border border-[#1E2025] bg-[#0E0F12] p-5 sm:p-6 relative overflow-hidden group hover:border-[#2D3038] transition-all flex flex-col justify-between">
      <!-- Technical Corner Ticks -->
      <span class="absolute top-1.5 left-1.5 w-1.5 h-1.5 border-t border-l border-white/10 group-hover:border-white/25 transition-colors"></span>
      <span class="absolute top-1.5 right-1.5 w-1.5 h-1.5 border-t border-r border-white/10 group-hover:border-white/25 transition-colors"></span>
      <span class="absolute bottom-1.5 left-1.5 w-1.5 h-1.5 border-b border-l border-white/10 group-hover:border-white/25 transition-colors"></span>
      <span class="absolute bottom-1.5 right-1.5 w-1.5 h-1.5 border-b border-r border-white/10 group-hover:border-white/25 transition-colors"></span>

      <!-- Header -->
      <div class="flex items-start justify-between pb-6 border-b border-white/[0.04]">
        <div>
          <div class="text-2xl sm:text-[28px] font-bold text-white tracking-tight font-sans tabular-nums">
            ₦87.6K
          </div>
          <p class="text-xs text-white/50 mt-0.5">Revenue from campaigns in Feb</p>
        </div>

        <div class="text-[11px] font-medium text-emerald-400 flex items-center gap-1">
          <span>↑</span>
          <span>5.2%</span>
        </div>
      </div>

      <!-- Segmented Dashed Monthly Bars -->
      <div class="relative pt-6 pb-2 h-56 flex flex-col justify-between">
        <!-- Bars Container -->
        <div class="flex-1 flex items-end justify-between gap-3 px-2 pb-5 relative">
          <div
            v-for="(m, mIdx) in monthlyBars"
            :key="m.month"
            class="group/mbar flex flex-col items-center flex-1 h-full justify-end relative cursor-pointer"
          >
            <!-- Callout Pill for highlighted month -->
            <div
              v-if="m.highlight"
              class="absolute -top-6 left-1/2 -translate-x-1/2 rounded bg-[#1B1D22] border border-white/10 px-1.5 py-0.5 text-[10px] font-bold text-white shadow whitespace-nowrap"
            >
              ₦{{ m.val }}K
            </div>

            <!-- Dashed Segmented Bar -->
            <div class="flex flex-col-reverse gap-[2px] w-3 sm:w-3.5">
              <span
                v-for="seg in m.segments"
                :key="'mseg-' + seg"
                class="w-full h-[5px] rounded-[1px] transition-colors"
                :class="m.highlight ? 'bg-white' : 'bg-[#3A3D46] group-hover/mbar:bg-[#4F535E]'"
              ></span>
            </div>
          </div>
        </div>

        <!-- Month Labels -->
        <div class="flex justify-between text-[10px] text-white/35 font-mono px-2">
          <span v-for="m in monthlyBars" :key="'lbl-' + m.month">{{ m.month }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  revenueTrend: { type: Array, default: () => [] }
});

const timeframes = ['14D', '1M', '3M', '6M'];
const activeTimeframe = ref('1M');

const formattedTotalSales = computed(() => {
  return '413K';
});

const xDates = ['Mar 22', 'Mar 26', 'Mar 30', 'Apr 3', 'Apr 7', 'Apr 11', 'Apr 15', 'Apr 19'];

// Generate 26 columns matching the exact visual pattern in the reference image
const equalizerColumns = computed(() => {
  const heights = [
    { white: 7, total: 12, label: 'Mar 22', totalVal: 12 },
    { white: 11, total: 16, label: 'Mar 23', totalVal: 16 },
    { white: 8, total: 13, label: 'Mar 24', totalVal: 13 },
    { white: 6, total: 10, label: 'Mar 25', totalVal: 10 },
    { white: 12, total: 18, label: 'Mar 26', totalVal: 18 },
    { white: 9, total: 14, label: 'Mar 27', totalVal: 14 },
    { white: 7, total: 11, label: 'Mar 28', totalVal: 11 },
    { white: 13, total: 19, label: 'Mar 29', totalVal: 19 },
    { white: 10, total: 15, label: 'Mar 30', totalVal: 15 },
    { white: 8, total: 13, label: 'Mar 31', totalVal: 13 },
    { white: 14, total: 20, label: 'Apr 1', totalVal: 20 },
    { white: 11, total: 17, label: 'Apr 2', totalVal: 17 },
    { white: 9, total: 14, label: 'Apr 3', totalVal: 14 },
    { white: 7, total: 11, label: 'Apr 4', totalVal: 11 },
    { white: 12, total: 18, label: 'Apr 5', totalVal: 18 },
    { white: 15, total: 22, label: 'Apr 6', totalVal: 22 },
    { white: 10, total: 16, label: 'Apr 7', totalVal: 16 },
    { white: 8, total: 13, label: 'Apr 8', totalVal: 13 },
    { white: 13, total: 19, label: 'Apr 9', totalVal: 19 },
    { white: 11, total: 17, label: 'Apr 10', totalVal: 17 },
    { white: 14, total: 21, label: 'Apr 11', totalVal: 21 },
    { white: 12, total: 18, label: 'Apr 12', totalVal: 18 },
    { white: 9, total: 15, label: 'Apr 13', totalVal: 15 },
    { white: 11, total: 17, label: 'Apr 14', totalVal: 17 },
    { white: 10, total: 16, label: 'Apr 15', totalVal: 16 },
    { white: 12, total: 18, label: 'Apr 16', totalVal: 18 }
  ];

  return heights.map(h => ({
    whiteBricks: h.white,
    totalBricks: h.total,
    label: h.label,
    totalVal: h.totalVal
  }));
});

const monthlyBars = [
  { month: 'Jan', segments: 18, highlight: true, val: 87.6 },
  { month: 'Mar', segments: 14, highlight: false, val: 62.4 },
  { month: 'May', segments: 16, highlight: false, val: 71.0 },
  { month: 'Jul', segments: 12, highlight: false, val: 54.2 },
  { month: 'Sep', segments: 15, highlight: false, val: 68.5 },
  { month: 'Nov', segments: 13, highlight: false, val: 59.1 }
];
</script>

