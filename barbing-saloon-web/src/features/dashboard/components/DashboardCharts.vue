<template>
  <div class="rounded-2xl border border-white/[0.07] bg-[#0A0F12]/80 backdrop-blur-xl p-5 lg:p-6 shadow-xl flex flex-col justify-between">
    <!-- Header with Metric Toggles -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-white/[0.05]">
      <div class="flex items-center gap-3">
        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-admin/10 border border-admin/20 text-admin shrink-0">
          <component :is="activeTab === 'revenue' ? BanknotesIcon : CalendarDaysIcon" class="h-5 w-5" />
        </div>
        <div>
          <h2 class="text-sm font-bold text-white tracking-wide">Performance Trends</h2>
          <p class="text-[11px] text-white/40">Last 7 days performance</p>
        </div>
      </div>

      <!-- Segmented Tabs & 7-Day Total -->
      <div class="flex items-center gap-3 self-end sm:self-auto">
        <span class="hidden md:inline-block text-xs font-semibold text-white/60 tabular-nums">
          {{ activeTab === 'revenue' ? `Total: ₦${formatCurrency(totalRevenue)}` : `Total: ${totalBookings} bookings` }}
        </span>

        <div class="inline-flex rounded-xl bg-white/[0.04] p-1 border border-white/[0.06]">
          <button 
            type="button"
            @click="activeTab = 'revenue'"
            :class="[
              'rounded-lg px-3 py-1 text-xs font-semibold transition-all cursor-pointer',
              activeTab === 'revenue' 
                ? 'bg-admin text-white shadow-sm font-bold' 
                : 'text-white/50 hover:text-white'
            ]"
          >
            Revenue
          </button>
          <button 
            type="button"
            @click="activeTab = 'bookings'"
            :class="[
              'rounded-lg px-3 py-1 text-xs font-semibold transition-all cursor-pointer',
              activeTab === 'bookings' 
                ? 'bg-sky-500 text-white shadow-sm font-bold' 
                : 'text-white/50 hover:text-white'
            ]"
          >
            Bookings
          </button>
        </div>
      </div>
    </div>

    <!-- Chart Visualization Canvas -->
    <div class="relative pt-6 pb-2 h-52 sm:h-56">
      <div class="absolute inset-x-2 top-6 bottom-8">
        <!-- Horizontal Reference Gridlines -->
        <div class="absolute inset-0 flex flex-col justify-between pointer-events-none opacity-20">
          <div class="border-b border-dashed border-white/20 w-full"></div>
          <div class="border-b border-dashed border-white/20 w-full"></div>
          <div class="border-b border-dashed border-white/20 w-full"></div>
        </div>

        <!-- SVG Curve Canvas -->
        <svg class="w-full h-full overflow-visible" viewBox="0 0 100 100" preserveAspectRatio="none">
          <defs>
            <!-- Revenue Gradient Fill -->
            <linearGradient id="revenueGradient" x1="0%" y1="0%" x2="0%" y2="100%">
              <stop offset="0%" stop-color="#FF6700" stop-opacity="0.45" />
              <stop offset="60%" stop-color="#FF6700" stop-opacity="0.1" />
              <stop offset="100%" stop-color="#FF6700" stop-opacity="0" />
            </linearGradient>

            <!-- Bookings Gradient Fill -->
            <linearGradient id="bookingsGradient" x1="0%" y1="0%" x2="0%" y2="100%">
              <stop offset="0%" stop-color="#38BDF8" stop-opacity="0.45" />
              <stop offset="60%" stop-color="#38BDF8" stop-opacity="0.1" />
              <stop offset="100%" stop-color="#38BDF8" stop-opacity="0" />
            </linearGradient>
          </defs>

          <!-- Smooth Bézier Area Fill -->
          <path 
            :d="activeAreaPath" 
            :fill="activeTab === 'revenue' ? 'url(#revenueGradient)' : 'url(#bookingsGradient)'"
            class="transition-all duration-700 ease-out" 
          />

          <!-- Smooth Bézier Spline Line -->
          <path 
            :d="activeLinePath" 
            fill="none" 
            :stroke="activeTab === 'revenue' ? '#FF6700' : '#38BDF8'" 
            stroke-width="2.5" 
            stroke-linecap="round" 
            stroke-linejoin="round" 
            class="transition-all duration-700 ease-out" 
          />
        </svg>

        <!-- Interactive Data Points with Tooltips -->
        <div 
          v-for="(p, idx) in activePoints" 
          :key="'pt-' + activeTab + '-' + idx"
          class="group/pt absolute -ml-2 -mt-2 w-4 h-4 rounded-full flex items-center justify-center cursor-pointer z-20"
          :style="{ left: `${p.x}%`, top: `${p.y}%` }"
        >
          <!-- Outer pulsing ring on hover -->
          <span 
            class="absolute inset-0 rounded-full scale-100 group-hover/pt:scale-150 transition-all duration-300 opacity-60"
            :class="activeTab === 'revenue' ? 'bg-admin/30' : 'bg-sky-400/30'"
          ></span>
          
          <!-- Inner core dot -->
          <span 
            class="relative w-2 h-2 rounded-full border border-black shadow-md transition-transform duration-300 group-hover/pt:scale-125"
            :class="activeTab === 'revenue' ? 'bg-admin' : 'bg-sky-400'"
          ></span>

          <!-- Tooltip Popup -->
          <div 
            class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2.5 opacity-0 group-hover/pt:opacity-100 pointer-events-none transition-all duration-200 transform scale-90 group-hover/pt:scale-100 z-30"
          >
            <div class="rounded-lg bg-[#0E151A] border border-white/10 px-2.5 py-1 text-[11px] font-bold text-white shadow-xl whitespace-nowrap tabular-nums">
              <span class="text-white/40 uppercase mr-1">{{ p.day }}:</span>
              <span :class="activeTab === 'revenue' ? 'text-admin' : 'text-sky-400'">
                {{ activeTab === 'revenue' ? `₦${formatCurrency(p.val)}` : `${p.val} bookings` }}
              </span>
            </div>
          </div>
        </div>

        <!-- X-Axis Labels -->
        <div 
          v-for="(p, idx) in activePoints" 
          :key="'lbl-' + idx"
          class="absolute -bottom-6 w-12 -ml-6 flex justify-center text-center"
          :style="{ left: `${p.x}%` }"
        >
          <span 
            class="text-[10px] font-bold tracking-wider uppercase transition-colors"
            :class="p.isToday ? (activeTab === 'revenue' ? 'text-admin' : 'text-sky-400') : 'text-white/35'"
          >
            {{ p.day }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { BanknotesIcon, CalendarDaysIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  revenueTrend: { type: Array, default: () => [] },
  bookingTrend: { type: Array, default: () => [] }
});

const activeTab = ref('revenue');

const formatCurrency = (val) => {
  return new Intl.NumberFormat('en-NG', { maximumFractionDigits: 0 }).format(Number(val || 0));
};

const totalRevenue = computed(() => {
  return (props.revenueTrend || []).reduce((acc, t) => acc + Number(t.revenue || 0), 0);
});

const totalBookings = computed(() => {
  return (props.bookingTrend || []).reduce((acc, t) => acc + Number(t.count || 0), 0);
});

// Normalized points for current active tab
const activePoints = computed(() => {
  const isRev = activeTab.value === 'revenue';
  const data = isRev ? (props.revenueTrend || []) : (props.bookingTrend || []);
  if (!data.length) return [];

  const rawValues = data.map(d => Number(isRev ? d.revenue : d.count) || 0);
  const max = Math.max(...rawValues, 1);
  const today = new Date().toISOString().slice(0, 10);

  return data.map((d, idx) => {
    const val = Number(isRev ? d.revenue : d.count) || 0;
    // Map percentage so points stay in clean visual range (15% to 85% of height)
    const normalizedY = 85 - (val / max) * 65;
    const x = data.length > 1 ? (idx / (data.length - 1)) * 100 : 50;
    return {
      x,
      y: normalizedY,
      val,
      day: d.day,
      date: d.date,
      isToday: d.date === today
    };
  });
});

// Cubic Bézier spline interpolation for a silky-smooth curve
const getBezierSpline = (pts) => {
  if (pts.length === 0) return '';
  if (pts.length === 1) return `M 0,${pts[0].y} L 100,${pts[0].y}`;

  let path = `M ${pts[0].x.toFixed(1)},${pts[0].y.toFixed(1)}`;
  for (let i = 0; i < pts.length - 1; i++) {
    const p0 = pts[i === 0 ? i : i - 1];
    const p1 = pts[i];
    const p2 = pts[i + 1];
    const p3 = pts[i + 2] || p2;

    const cp1x = p1.x + (p2.x - p0.x) / 6;
    const cp1y = p1.y + (p2.y - p0.y) / 6;
    const cp2x = p2.x - (p3.x - p1.x) / 6;
    const cp2y = p2.y - (p3.y - p1.y) / 6;

    path += ` C ${cp1x.toFixed(1)},${cp1y.toFixed(1)} ${cp2x.toFixed(1)},${cp2y.toFixed(1)} ${p2.x.toFixed(1)},${p2.y.toFixed(1)}`;
  }
  return path;
};

const activeLinePath = computed(() => {
  return getBezierSpline(activePoints.value);
});

const activeAreaPath = computed(() => {
  const line = activeLinePath.value;
  if (!line || !activePoints.value.length) return '';
  const lastX = activePoints.value[activePoints.value.length - 1].x;
  const firstX = activePoints.value[0].x;
  return `${line} L ${lastX.toFixed(1)},100 L ${firstX.toFixed(1)},100 Z`;
});
</script>
