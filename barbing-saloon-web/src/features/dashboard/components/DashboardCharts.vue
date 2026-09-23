<template>
  <div class="space-y-6">
    <!-- Weekly Revenue Chart -->
    <div class="rounded-xl border border-theme-border bg-theme-surface overflow-hidden">
      <div class="flex items-center justify-between border-b border-theme-border px-6 py-4">
        <div class="flex items-center gap-3">
          <ChartBarIcon class="h-5 w-5 text-admin" />
          <div>
            <h2 class="text-sm font-bold text-theme-text">Weekly Revenue</h2>
            <p class="text-[10px] text-ivory/35 mt-0.5">Last 7 days performance</p>
          </div>
        </div>
        <span class="text-xs text-ivory/30 font-mono tabular-nums">{{ todayFormatted }}</span>
      </div>

      <div class="p-6 relative h-56">
        <!-- Line Graph Container -->
        <div class="absolute left-8 right-6 top-6 bottom-12 z-10">
          <svg class="w-full h-full overflow-visible" viewBox="0 0 100 100" preserveAspectRatio="none">
            <!-- Area fill -->
            <path :d="revenueGraphAreaPath" fill="url(#revLineGradient)" class="transition-all duration-500 ease-out opacity-40" />
            <!-- Line -->
            <path :d="revenueGraphPath" fill="none" stroke="#FF6700" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="transition-all duration-500 ease-out" />
            <defs>
              <linearGradient id="revLineGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                <stop offset="0%" stop-color="#FF6700" stop-opacity="0.8" />
                <stop offset="100%" stop-color="#FF6700" stop-opacity="0" />
              </linearGradient>
            </defs>
          </svg>
          
          <!-- Interactive Data Points -->
          <div v-for="(p, idx) in revenueGraphPoints" :key="'rev-point-'+idx" 
                class="absolute w-3.5 h-3.5 -ml-[7px] -mt-[7px] rounded-full bg-charcoal border-2 border-admin group/point cursor-pointer transition-all duration-300 hover:scale-[1.8] hover:bg-admin z-20"
                :style="{ left: `${p.x}%`, top: `${p.y}%` }">
            <!-- Tooltip -->
            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-3 opacity-0 group-hover/point:opacity-100 transition-all duration-200 bg-obsidian border border-admin/30 text-xs px-3 py-1.5 rounded-lg text-admin font-bold whitespace-nowrap z-30 pointer-events-none scale-50 group-hover/point:scale-100 origin-bottom tabular-nums">
              ₦{{ formatCurrency(p.revenue) }}
            </div>
          </div>

          <!-- X-axis labels -->
          <div v-for="(p, idx) in revenueGraphPoints" :key="'rev-label-'+idx"
                class="absolute -bottom-8 w-16 -ml-8 flex justify-center"
                :style="{ left: `${p.x}%` }">
            <span class="text-[10px] text-ivory/40 uppercase tracking-wider font-bold">{{ p.day }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Weekly Bookings Chart -->
    <div class="rounded-xl border border-theme-border bg-theme-surface overflow-hidden">
      <div class="flex items-center justify-between border-b border-theme-border px-6 py-4">
        <div class="flex items-center gap-3">
          <CalendarDaysIcon class="h-5 w-5 text-blue-400" />
          <div>
            <h2 class="text-sm font-bold text-theme-text">Booking Activity</h2>
            <p class="text-[10px] text-ivory/35 mt-0.5">Appointments per day</p>
          </div>
        </div>
      </div>

      <div class="p-6 relative h-48">
        <!-- Line Graph Container -->
        <div class="absolute left-8 right-6 top-6 bottom-10 z-10">
          <svg class="w-full h-full overflow-visible" viewBox="0 0 100 100" preserveAspectRatio="none">
            <!-- Area fill -->
            <path :d="bookingGraphAreaPath" fill="url(#bookLineGradient)" class="transition-all duration-500 ease-out opacity-40" />
            <!-- Line -->
            <path :d="bookingGraphPath" fill="none" stroke="#60A5FA" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="transition-all duration-500 ease-out" />
            <defs>
              <linearGradient id="bookLineGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                <stop offset="0%" stop-color="#60A5FA" stop-opacity="0.8" />
                <stop offset="100%" stop-color="#60A5FA" stop-opacity="0" />
              </linearGradient>
            </defs>
          </svg>
          
          <!-- Interactive Data Points -->
          <div v-for="(p, idx) in bookingGraphPoints" :key="'book-point-'+idx" 
                class="absolute w-3.5 h-3.5 -ml-[7px] -mt-[7px] rounded-full bg-charcoal border-2 border-blue-400 group/point cursor-pointer transition-all duration-300 hover:scale-[1.8] hover:bg-blue-400 z-20"
                :style="{ left: `${p.x}%`, top: `${p.y}%` }">
            <!-- Tooltip -->
            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-3 opacity-0 group-hover/point:opacity-100 transition-all duration-200 bg-obsidian border border-blue-400/30 text-xs px-3 py-1.5 rounded-lg text-blue-400 font-bold whitespace-nowrap z-30 pointer-events-none scale-50 group-hover/point:scale-100 origin-bottom tabular-nums">
              {{ p.count }} bookings
            </div>
          </div>

          <!-- X-axis labels -->
          <div v-for="(p, idx) in bookingGraphPoints" :key="'book-label-'+idx"
                class="absolute -bottom-8 w-16 -ml-8 flex justify-center"
                :style="{ left: `${p.x}%` }">
            <span class="text-[10px] text-ivory/40 uppercase tracking-wider font-bold">{{ p.day }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { ChartBarIcon, CalendarDaysIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  revenueTrend: { type: Array, default: () => [] },
  bookingTrend: { type: Array, default: () => [] }
});

const todayFormatted = computed(() => {
  return new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
});

const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-NG', { maximumFractionDigits: 0 }).format(Number(value || 0));
};

// Revenue Graph Math
const normalizedRevenueTrend = computed(() => {
  const trend = props.revenueTrend || [];
  if (!trend.length) return [];
  const max = Math.max(...trend.map(t => t.revenue), 1);
  const today = new Date().toISOString().slice(0, 10);
  return trend.map(t => ({
    ...t,
    percentage: Math.max((t.revenue / max) * 100, 4),
    isToday: t.date === today
  }));
});

const revenueGraphPoints = computed(() => {
  const trends = normalizedRevenueTrend.value;
  if (trends.length === 0) return [];
  if (trends.length === 1) {
    return [{ x: 50, y: 100 - trends[0].percentage, revenue: trends[0].revenue, day: trends[0].day }];
  }
  return trends.map((t, idx) => {
    const x = (idx / (trends.length - 1)) * 100;
    const y = 100 - t.percentage;
    return { x, y, revenue: t.revenue, day: t.day };
  });
});

const revenueGraphPath = computed(() => {
  const points = revenueGraphPoints.value;
  if (points.length === 0) return '';
  if (points.length === 1) return `M 0,${points[0].y} L 100,${points[0].y}`;
  return points.map((p, i) => `${i === 0 ? 'M' : 'L'} ${p.x},${p.y}`).join(' ');
});

const revenueGraphAreaPath = computed(() => {
  const points = revenueGraphPoints.value;
  if (points.length === 0) return '';
  const path = revenueGraphPath.value;
  return `${path} L 100,100 L 0,100 Z`;
});

// Booking Graph Math
const normalizedBookingTrend = computed(() => {
  const trend = props.bookingTrend || [];
  if (!trend.length) return [];
  const max = Math.max(...trend.map(t => t.count), 1);
  const today = new Date().toISOString().slice(0, 10);
  return trend.map(t => ({
    ...t,
    percentage: Math.max((t.count / max) * 100, 4),
    isToday: t.date === today
  }));
});

const bookingGraphPoints = computed(() => {
  const trends = normalizedBookingTrend.value;
  if (trends.length === 0) return [];
  if (trends.length === 1) {
    return [{ x: 50, y: 100 - trends[0].percentage, count: trends[0].count, day: trends[0].day }];
  }
  return trends.map((t, idx) => {
    const x = (idx / (trends.length - 1)) * 100;
    const y = 100 - t.percentage;
    return { x, y, count: t.count, day: t.day };
  });
});

const bookingGraphPath = computed(() => {
  const points = bookingGraphPoints.value;
  if (points.length === 0) return '';
  if (points.length === 1) return `M 0,${points[0].y} L 100,${points[0].y}`;
  return points.map((p, i) => `${i === 0 ? 'M' : 'L'} ${p.x},${p.y}`).join(' ');
});

const bookingGraphAreaPath = computed(() => {
  const points = bookingGraphPoints.value;
  if (points.length === 0) return '';
  const path = bookingGraphPath.value;
  return `${path} L 100,100 L 0,100 Z`;
});
</script>
