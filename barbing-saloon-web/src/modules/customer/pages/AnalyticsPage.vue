<template>
  <CustomerLayout>
    <section class="space-y-8 animate-fade-in">
      <!-- Page Header -->
      <div class="relative overflow-hidden rounded-3xl border border-gold/20 bg-gradient-to-br from-obsidian via-charcoal to-steel p-8 shadow-2xl">
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-gold/5 blur-3xl pointer-events-none"></div>
        <div class="absolute -left-20 -bottom-20 h-48 w-48 rounded-full bg-gold/5 blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
          <div>
            <p class="text-xs uppercase tracking-[0.3em] text-gold/70 font-bold">Customer Dashboard</p>
            <h1 class="mt-2 font-display text-3xl lg:text-4xl text-white">
              Your <span class="text-transparent bg-clip-text bg-gradient-to-r from-gold to-white">Analytics</span>
            </h1>
            <p class="mt-2 max-w-xl text-sm text-white/60 leading-relaxed">
              Track your spending, booking history, and platform engagement over time.
            </p>
          </div>
          
          <!-- Time Range Selector -->
          <div class="flex items-center gap-1.5 bg-theme-bg p-1.5 rounded-2xl border border-theme-border shadow-inner w-max">
            <button v-for="r in ranges" :key="r.value"
                    @click="setRange(r.value)"
                    :class="[
                      'px-4 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider transition-all duration-300',
                      range === r.value 
                        ? 'bg-gradient-to-r from-gold to-gold-dark text-obsidian shadow-[0_0_15px_rgba(212,175,55,0.3)]' 
                        : 'text-theme-muted hover:text-theme-text hover:bg-white/5'
                    ]">
              {{ r.label }}
            </button>
          </div>
        </div>
      </div>

      <!-- Loading Skeleton -->
      <div v-if="loading" class="space-y-6">
        <div class="grid gap-5 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
          <div v-for="i in 4" :key="i" class="h-36 rounded-2xl bg-theme-border/30 animate-pulse border border-theme-border"></div>
        </div>
        <div class="grid gap-6 lg:grid-cols-3">
          <div class="lg:col-span-2 h-80 rounded-2xl bg-theme-border/30 animate-pulse border border-theme-border"></div>
          <div class="h-80 rounded-2xl bg-theme-border/30 animate-pulse border border-theme-border"></div>
        </div>
      </div>
      
      <div v-else-if="error" class="rounded-2xl border border-red-500/20 bg-red-500/5 p-12 text-center">
        <div class="w-16 h-16 rounded-full bg-red-500/10 border border-red-500/20 flex items-center justify-center mx-auto mb-4">
          <ExclamationTriangleIcon class="w-8 h-8 text-red-400" />
        </div>
        <p class="text-red-400 font-display text-xl">Failed to load analytics data.</p>
        <button @click="fetchAnalytics" class="mt-4 px-6 py-2.5 rounded-xl bg-red-500/20 border border-red-500/30 text-red-300 text-sm font-bold hover:bg-red-500/30 transition-colors">
          Try Again
        </button>
      </div>
      
      <template v-else>
        <!-- Stats Grid -->
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
          <div v-for="(stat, idx) in statCards" :key="stat.label" 
               class="group relative overflow-hidden rounded-2xl border border-theme-border bg-theme-surface/80 p-6 backdrop-blur-sm transition-all duration-500 hover:-translate-y-1 hover:shadow-[0_10px_40px_-10px_rgba(212,175,55,0.15)]"
               :class="stat.hoverBorder"
               :style="{ animationDelay: `${idx * 100}ms` }">
            <!-- Large watermark icon top-right -->
            <div class="absolute -right-3 -top-3 opacity-[0.07] group-hover:opacity-[0.12] transition-opacity duration-500 pointer-events-none">
              <component :is="stat.icon" class="w-28 h-28" />
            </div>
            <!-- Glow background -->
            <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full blur-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500" :class="stat.glow"></div>
            
            <div class="relative z-10">
              <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-theme-muted">{{ stat.label }}</p>
              <p class="mt-3 font-display text-4xl lg:text-5xl text-theme-text group-hover:text-gold transition-colors duration-300 leading-none">{{ stat.value }}</p>
            </div>
          </div>
        </div>

        <!-- Charts Row -->
        <div class="grid gap-6 lg:grid-cols-3">
          <!-- Booking Trends Chart -->
          <article class="lg:col-span-2 rounded-2xl border border-theme-border bg-theme-surface/80 backdrop-blur-sm overflow-hidden">
            <div class="p-6 border-b border-theme-border/30 flex items-center justify-between">
              <div>
                <h2 class="font-display text-xl text-theme-text">Booking <span class="text-gold">Trends</span></h2>
                <p class="text-xs text-theme-muted mt-1">Your appointment frequency over the last 6 months</p>
              </div>
              <div class="flex items-center gap-2 text-xs text-theme-muted">
                <div class="w-3 h-3 rounded-sm bg-gold/80"></div>
                Appointments
              </div>
            </div>
            
            <div class="p-6">
              <div v-if="normalizedBookingTrends.length === 0" class="h-64 flex flex-col items-center justify-center text-center">
                <ChartBarIcon class="w-10 h-10 text-theme-muted/40 mb-3" />
                <p class="text-theme-muted font-medium">No booking data available for this range.</p>
              </div>
              <div v-else class="h-64 relative mt-2">
                <!-- Grid & Y-axis -->
                <div class="absolute inset-x-0 top-0 bottom-8 flex">
                  <div class="w-8 flex flex-col justify-between text-[10px] text-theme-muted/70 font-bold pb-0.5">
                    <span>{{ maxBookingCount }}</span>
                    <span>{{ Math.round(maxBookingCount / 2) }}</span>
                    <span>0</span>
                  </div>
                  <div class="flex-1 flex flex-col justify-between relative pt-1.5 pb-1.5">
                    <div class="border-t border-dashed border-theme-border/30 w-full"></div>
                    <div class="border-t border-dashed border-theme-border/30 w-full"></div>
                    <div class="border-t border-theme-border/50 w-full"></div>
                  </div>
                </div>
                
                <!-- Line Graph Container -->
                <div class="absolute left-8 right-6 top-1.5 bottom-9 z-10">
                  <svg class="w-full h-full overflow-visible" viewBox="0 0 100 100" preserveAspectRatio="none">
                    <!-- Area fill -->
                    <path :d="lineGraphAreaPath" fill="url(#lineGradient)" class="transition-all duration-500 ease-out opacity-40" />
                    <!-- Line -->
                    <path :d="lineGraphPath" fill="none" stroke="#D4AF37" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="transition-all duration-500 ease-out" style="filter: drop-shadow(0 0 8px rgba(212,175,55,0.5))" />
                    <defs>
                      <linearGradient id="lineGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" stop-color="#D4AF37" stop-opacity="0.8" />
                        <stop offset="100%" stop-color="#D4AF37" stop-opacity="0" />
                      </linearGradient>
                    </defs>
                  </svg>
                  
                  <!-- Interactive Data Points -->
                  <div v-for="(p, idx) in lineGraphPoints" :key="'point-'+idx" 
                       class="absolute w-3.5 h-3.5 -ml-[7px] -mt-[7px] rounded-full bg-charcoal border-2 border-gold shadow-[0_0_10px_rgba(212,175,55,0.6)] group/point cursor-pointer transition-all duration-300 hover:scale-[1.8] hover:bg-gold z-20"
                       :style="{ left: `${p.x}%`, top: `${p.y}%` }">
                    <!-- Tooltip -->
                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-3 opacity-0 group-hover/point:opacity-100 transition-all duration-200 bg-obsidian border border-gold/30 text-xs px-3 py-1.5 rounded-lg shadow-xl text-gold font-bold whitespace-nowrap z-30 pointer-events-none scale-50 group-hover/point:scale-100 origin-bottom">
                      {{ p.count }} {{ p.count === 1 ? 'booking' : 'bookings' }}
                    </div>
                  </div>

                  <!-- X-axis labels -->
                  <div v-for="(p, idx) in lineGraphPoints" :key="'label-'+idx"
                       class="absolute -bottom-8 w-16 -ml-8 flex justify-center"
                       :style="{ left: `${p.x}%` }">
                    <span class="text-[10px] text-theme-muted uppercase tracking-wider font-bold">{{ p.month }}</span>
                  </div>
                </div>
              </div>
            </div>
          </article>

          <!-- Status Breakdown Donut Chart -->
          <article class="rounded-2xl border border-theme-border bg-theme-surface/80 backdrop-blur-sm overflow-hidden flex flex-col">
            <div class="p-6 border-b border-theme-border/30">
              <h2 class="font-display text-xl text-theme-text">Status <span class="text-gold">Breakdown</span></h2>
              <p class="text-xs text-theme-muted mt-1">Distribution of your appointment outcomes</p>
            </div>
            
            <div class="flex-1 p-6 flex flex-col items-center justify-center">
              <div v-if="totalDonutCount === 0" class="flex flex-col items-center justify-center text-center py-6">
                <div class="w-32 h-32 rounded-full border-4 border-dashed border-theme-border flex items-center justify-center mb-4">
                  <ChartPieIcon class="w-10 h-10 text-theme-muted/40" />
                </div>
                <p class="text-theme-muted font-medium text-sm">No appointment data yet</p>
              </div>
              <template v-else>
                <div class="relative w-44 h-44 mb-6">
                  <svg viewBox="0 0 100 100" class="w-full h-full transform -rotate-90">
                    <circle cx="50" cy="50" r="40" fill="transparent" stroke="rgba(255,255,255,0.03)" stroke-width="18" />
                    <circle v-for="(segment, idx) in donutSegments" :key="idx"
                            cx="50" cy="50" r="40" fill="transparent"
                            :stroke="segment.color"
                            stroke-width="18"
                            stroke-linecap="round"
                            :stroke-dasharray="`${segment.length} ${segment.gap}`"
                            :stroke-dashoffset="segment.offset"
                            class="transition-all duration-1000 ease-out"
                            :style="{ transitionDelay: `${idx * 150}ms` }" />
                  </svg>
                  <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <span class="text-3xl font-display text-theme-text">{{ totalDonutCount }}</span>
                    <span class="text-[10px] uppercase tracking-widest text-theme-muted font-bold">Total</span>
                  </div>
                </div>

                <div class="w-full space-y-2.5">
                  <div v-for="legend in legendItems" :key="legend.label" 
                       class="flex items-center justify-between text-sm px-3 py-2 rounded-xl hover:bg-theme-bg transition-colors group/legend cursor-default">
                    <div class="flex items-center gap-2.5">
                      <div class="w-3 h-3 rounded-full shadow-sm ring-2 ring-black/20" :class="legend.bgClass"></div>
                      <span class="text-theme-muted capitalize group-hover/legend:text-theme-text transition-colors text-xs font-medium">{{ legend.label.replace('_', ' ') }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                      <span class="text-[10px] text-theme-muted/70 font-bold">{{ Math.round((legend.count / totalDonutCount) * 100) }}%</span>
                      <span class="font-bold text-theme-text text-sm">{{ legend.count }}</span>
                    </div>
                  </div>
                </div>
              </template>
            </div>
          </article>
        </div>

        <!-- Bottom Row: Insights -->
        <div class="grid gap-6 lg:grid-cols-3">
          <!-- Most Booked Service -->
          <article class="group rounded-2xl border border-theme-border bg-theme-surface/80 p-6 backdrop-blur-sm transition-all hover:border-gold/20 hover:shadow-[0_10px_40px_-10px_rgba(212,175,55,0.1)]">
            <div class="flex items-start gap-4">
              <SparklesIcon class="w-9 h-9 text-gold shrink-0 mt-0.5 transition-colors duration-300" />
              <div class="min-w-0">
                <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-theme-muted">Most Booked Service</p>
                <h3 class="font-display text-2xl text-theme-text mt-1 truncate group-hover:text-gold transition-colors">{{ analytics.spending_summary?.most_booked || 'None yet' }}</h3>
                <p class="text-xs text-theme-muted mt-1">Your go-to style preference</p>
              </div>
            </div>
          </article>

          <!-- Total Bookings -->
          <article class="group rounded-2xl border border-theme-border bg-theme-surface/80 p-6 backdrop-blur-sm transition-all hover:border-blue-400/20 hover:shadow-[0_10px_40px_-10px_rgba(96,165,250,0.1)]">
            <div class="flex items-start gap-4">
              <CalendarDaysIcon class="w-9 h-9 text-blue-400 shrink-0 mt-0.5 transition-colors duration-300" />
              <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-theme-muted">Total Appointments</p>
                <h3 class="font-display text-2xl text-theme-text mt-1 group-hover:text-blue-400 transition-colors">{{ totalDonutCount }}</h3>
                <p class="text-xs text-theme-muted mt-1">All-time booking count in this period</p>
              </div>
            </div>
          </article>

          <!-- Completion Rate -->
          <article class="group rounded-2xl border border-theme-border bg-theme-surface/80 p-6 backdrop-blur-sm transition-all hover:border-emerald-400/20 hover:shadow-[0_10px_40px_-10px_rgba(52,211,153,0.1)]">
            <div class="flex items-start gap-4">
              <CheckBadgeIcon class="w-9 h-9 text-emerald-400 shrink-0 mt-0.5 transition-colors duration-300" />
              <div class="flex-1">
                <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-theme-muted">Completion Rate</p>
                <h3 class="font-display text-2xl text-theme-text mt-1 group-hover:text-emerald-400 transition-colors">{{ completionRate }}%</h3>
                <div class="mt-2 h-1.5 w-full rounded-full bg-theme-border/30 overflow-hidden">
                  <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-emerald-400 transition-all duration-1000 ease-out" :style="{ width: `${completionRate}%` }"></div>
                </div>
              </div>
            </div>
          </article>
        </div>
      </template>
    </section>
  </CustomerLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import CustomerLayout from '../layouts/CustomerLayout.vue';
import { customerApi } from '../api/customer.api';
import { 
  BanknotesIcon, 
  CalculatorIcon, 
  HeartIcon, 
  StarIcon,
  SparklesIcon,
  CalendarDaysIcon,
  ChartBarIcon,
  ArrowTrendingUpIcon,
  ArrowTrendingDownIcon,
  ExclamationTriangleIcon,
  CheckBadgeIcon
} from '@heroicons/vue/24/outline';
import { ChartPieIcon } from '@heroicons/vue/24/solid';

const analytics = ref({});
const loading = ref(true);
const error = ref(false);
const range = ref('7d');

const ranges = [
  { label: '7D', value: '7d' },
  { label: '30D', value: '30d' },
  { label: 'Month', value: 'month' },
  { label: 'All', value: 'all' }
];

const fetchAnalytics = async () => {
  loading.value = true;
  error.value = false;
  try {
    const res = await customerApi.analytics(range.value);
    analytics.value = res.data.data;
  } catch (err) {
    console.error(err);
    error.value = true;
  } finally {
    loading.value = false;
  }
};

const setRange = (val) => {
  range.value = val;
  fetchAnalytics();
};

onMounted(() => {
  fetchAnalytics();
});

// Stat Cards
const statCards = computed(() => [
  {
    label: 'Total Spent',
    value: '₦' + Number(analytics.value.spending_summary?.total_spent || 0).toLocaleString(),
    icon: BanknotesIcon,
    iconBg: 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400',
    hoverBorder: 'hover:border-emerald-400/30',
    glow: 'bg-emerald-500/20',
  },
  {
    label: 'Average Spend',
    value: '₦' + Number(analytics.value.spending_summary?.avg_spent || 0).toLocaleString(undefined, { maximumFractionDigits: 0 }),
    icon: CalculatorIcon,
    iconBg: 'bg-blue-500/10 border-blue-500/20 text-blue-400',
    hoverBorder: 'hover:border-blue-400/30',
    glow: 'bg-blue-500/20',
  },
  {
    label: 'Wishlist Items',
    value: String(analytics.value.activity_timeline?.wishlist_items || 0),
    icon: HeartIcon,
    iconBg: 'bg-rose-500/10 border-rose-500/20 text-rose-400',
    hoverBorder: 'hover:border-rose-400/30',
    glow: 'bg-rose-500/20',
  },
  {
    label: 'Reviews Given',
    value: String(analytics.value.activity_timeline?.reviews_given || 0),
    icon: StarIcon,
    iconBg: 'bg-amber-500/10 border-amber-500/20 text-amber-400',
    hoverBorder: 'hover:border-amber-400/30',
    glow: 'bg-amber-500/20',
  },
]);

// Computed logic for Booking Trends CSS Bar Chart
const maxBookingCount = computed(() => {
  const trends = analytics.value.booking_trends || [];
  return Math.max(...trends.map(t => t.count), 1);
});

const normalizedBookingTrends = computed(() => {
  const trends = analytics.value.booking_trends || [];
  if (trends.length === 0) return [];
  
  const maxCount = maxBookingCount.value;
  return trends.map(t => ({
    month: t.month,
    count: t.count,
    percentage: Math.max((t.count / maxCount) * 100, 4)
  }));
});

const lineGraphPoints = computed(() => {
  const trends = normalizedBookingTrends.value;
  if (trends.length === 0) return [];
  if (trends.length === 1) {
    return [{ x: 50, y: 100 - trends[0].percentage, count: trends[0].count, month: trends[0].month }];
  }
  
  return trends.map((t, idx) => {
    const x = (idx / (trends.length - 1)) * 100;
    const y = 100 - t.percentage;
    return { x, y, count: t.count, month: t.month };
  });
});

const lineGraphPath = computed(() => {
  const points = lineGraphPoints.value;
  if (points.length === 0) return '';
  if (points.length === 1) return `M 0,${points[0].y} L 100,${points[0].y}`;
  
  return points.map((p, i) => `${i === 0 ? 'M' : 'L'} ${p.x},${p.y}`).join(' ');
});

const lineGraphAreaPath = computed(() => {
  const points = lineGraphPoints.value;
  if (points.length === 0) return '';
  const path = lineGraphPath.value;
  return `${path} L 100,100 L 0,100 Z`;
});

// Computed logic for Status Breakdown Donut Chart
const legendItems = computed(() => {
  const breakdown = analytics.value.status_breakdown || {};
  return [
    { label: 'completed', count: breakdown.completed || 0, bgClass: 'bg-emerald-400', hex: '#34d399' },
    { label: 'pending', count: breakdown.pending || 0, bgClass: 'bg-amber-400', hex: '#fbbf24' },
    { label: 'confirmed', count: breakdown.confirmed || 0, bgClass: 'bg-blue-400', hex: '#60a5fa' },
    { label: 'cancelled', count: breakdown.cancelled || 0, bgClass: 'bg-red-400', hex: '#f87171' },
    { label: 'no_show', count: breakdown.no_show || 0, bgClass: 'bg-purple-400', hex: '#c084fc' }
  ].filter(item => item.count > 0);
});

const totalDonutCount = computed(() => {
  return legendItems.value.reduce((sum, item) => sum + item.count, 0);
});

const completionRate = computed(() => {
  if (totalDonutCount.value === 0) return 0;
  const completed = (analytics.value.status_breakdown?.completed || 0);
  return Math.round((completed / totalDonutCount.value) * 100);
});

const donutSegments = computed(() => {
  const total = totalDonutCount.value;
  if (total === 0) return [];
  
  const circumference = 2 * Math.PI * 40;
  let currentOffset = 0;
  const gap = 2; // Small gap between segments
  
  return legendItems.value.map(item => {
    const percentage = item.count / total;
    const length = Math.max(percentage * circumference - gap, 1);
    const fullGap = circumference - length;
    
    const offset = -currentOffset;
    currentOffset += length + gap;
    
    return {
      color: item.hex,
      length,
      gap: fullGap,
      offset
    };
  });
});
</script>
