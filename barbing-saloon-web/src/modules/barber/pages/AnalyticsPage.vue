<template>
  <BarberLayout>
    <section class="space-y-8 animate-fade-in">
      <!-- Page Header -->
      <div class="relative overflow-hidden rounded-2xl border border-gold/20 bg-gradient-to-br from-obsidian via-charcoal to-[#1A1A00] p-8 shadow-2xl flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-gold/5 blur-3xl"></div>
        <div class="absolute -left-16 -bottom-16 h-48 w-48 rounded-full bg-gold/5 blur-3xl"></div>
        
        <div class="relative z-10">
          <p class="text-xs uppercase tracking-[0.3em] text-gold/70 font-bold">Barber Dashboard</p>
          <h1 class="mt-2 font-display text-3xl lg:text-4xl text-theme-text drop-shadow-lg">
            Performance <span class="text-transparent bg-clip-text bg-gradient-to-r from-gold to-gold-light">Analytics</span>
          </h1>
          <p class="mt-2 text-sm text-ivory/60 max-w-xl">
            Track your revenue, client retention, and service popularity over time.
          </p>
        </div>

        <!-- Time Range Selector -->
        <div class="relative z-10 flex items-center gap-2 bg-theme-bg/80 backdrop-blur p-1 rounded-xl border border-theme-border shadow-inner w-max">
          <button v-for="r in ranges" :key="r.value"
                  @click="setRange(r.value)"
                  :class="[
                    'px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-300',
                    range === r.value 
                      ? 'bg-gold text-obsidian shadow-md' 
                      : 'text-theme-muted hover:text-theme-text hover:bg-white/5'
                  ]">
            {{ r.label }}
          </button>
        </div>
      </div>

      <div v-if="loading" class="grid gap-6 grid-cols-1 md:grid-cols-2 lg:grid-cols-4">
        <div v-for="i in 4" :key="i" class="h-32 rounded-2xl bg-white/5 animate-pulse border border-white/5"></div>
      </div>
      
      <div v-else-if="error" class="rounded-2xl border border-red-500/20 bg-red-500/5 p-8 text-center text-red-400">
        Failed to load analytics data.
      </div>

      <template v-else>
        <!-- Stats Grid -->
        <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-4">
          <div class="group relative overflow-hidden rounded-2xl border border-theme-border bg-theme-surface/80 p-6 backdrop-blur-sm transition-all duration-500 hover:-translate-y-1 hover:border-gold/30 hover:shadow-[0_10px_40px_-10px_rgba(212,175,55,0.15)]">
            <div class="absolute -right-6 -top-6 text-emerald-400/5 group-hover:text-emerald-400/10 transition-colors duration-500 pointer-events-none">
              <BanknotesIcon class="w-32 h-32" />
            </div>
            <div class="relative z-10 flex flex-col gap-4">
              <div class="flex items-center gap-3">
                <BanknotesIcon class="w-8 h-8 text-emerald-400" />
                <p class="text-xs font-semibold uppercase tracking-widest text-ivory/50">Total Revenue</p>
              </div>
              <p class="font-display text-4xl text-theme-text group-hover:text-emerald-400 transition-colors">₦{{ Number(analytics.performance_metrics?.total_revenue || 0).toLocaleString() }}</p>
            </div>
          </div>

          <div class="group relative overflow-hidden rounded-2xl border border-theme-border bg-theme-surface/80 p-6 backdrop-blur-sm transition-all duration-500 hover:-translate-y-1 hover:border-gold/30 hover:shadow-[0_10px_40px_-10px_rgba(212,175,55,0.15)]">
            <div class="absolute -right-6 -top-6 text-blue-400/5 group-hover:text-blue-400/10 transition-colors duration-500 pointer-events-none">
              <UsersIcon class="w-32 h-32" />
            </div>
            <div class="relative z-10 flex flex-col gap-4">
              <div class="flex items-center gap-3">
                <UsersIcon class="w-8 h-8 text-blue-400" />
                <p class="text-xs font-semibold uppercase tracking-widest text-ivory/50">Clients Served</p>
              </div>
              <p class="font-display text-4xl text-theme-text group-hover:text-blue-400 transition-colors">{{ analytics.performance_metrics?.clients_served || 0 }}</p>
            </div>
          </div>

          <div class="group relative overflow-hidden rounded-2xl border border-theme-border bg-theme-surface/80 p-6 backdrop-blur-sm transition-all duration-500 hover:-translate-y-1 hover:border-gold/30 hover:shadow-[0_10px_40px_-10px_rgba(212,175,55,0.15)]">
            <div class="absolute -right-6 -top-6 text-indigo-400/5 group-hover:text-indigo-400/10 transition-colors duration-500 pointer-events-none">
              <CheckBadgeIcon class="w-32 h-32" />
            </div>
            <div class="relative z-10 flex flex-col gap-4">
              <div class="flex items-center gap-3">
                <CheckBadgeIcon class="w-8 h-8 text-indigo-400" />
                <p class="text-xs font-semibold uppercase tracking-widest text-ivory/50">Completion Rate</p>
              </div>
              <p class="font-display text-4xl text-theme-text group-hover:text-indigo-400 transition-colors">{{ analytics.performance_metrics?.completion_rate || 0 }}%</p>
            </div>
          </div>

          <div class="group relative overflow-hidden rounded-2xl border border-theme-border bg-theme-surface/80 p-6 backdrop-blur-sm transition-all duration-500 hover:-translate-y-1 hover:border-gold/30 hover:shadow-[0_10px_40px_-10px_rgba(212,175,55,0.15)]">
            <div class="absolute -right-6 -top-6 text-amber-400/5 group-hover:text-amber-400/10 transition-colors duration-500 pointer-events-none">
              <StarIcon class="w-32 h-32" />
            </div>
            <div class="relative z-10 flex flex-col gap-4">
              <div class="flex items-center gap-3">
                <StarIcon class="w-8 h-8 text-amber-400" />
                <p class="text-xs font-semibold uppercase tracking-widest text-ivory/50">Avg Rating</p>
              </div>
              <p class="font-display text-4xl text-theme-text group-hover:text-amber-400 transition-colors">{{ Number(analytics.performance_metrics?.avg_rating || 0).toFixed(1) }}</p>
            </div>
          </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
          <!-- Revenue Trends Chart (SVG Line Graph) -->
          <article class="lg:col-span-2 rounded-2xl border border-theme-border bg-theme-surface/80 p-6 backdrop-blur-sm">
            <h2 class="font-display text-2xl text-theme-text mb-6">Revenue <span class="text-gold">Trends</span></h2>
            
            <div class="relative h-64 mt-4">
              <div class="absolute left-8 right-6 top-6 bottom-12 z-10">
                <svg class="w-full h-full overflow-visible" viewBox="0 0 100 100" preserveAspectRatio="none">
                  <!-- Area fill -->
                  <path :d="revenueGraphAreaPath" fill="url(#revLineGradient)" class="transition-all duration-500 ease-out opacity-40" />
                  <!-- Line -->
                  <path :d="revenueGraphPath" fill="none" stroke="#D4AF37" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="transition-all duration-500 ease-out" style="filter: drop-shadow(0 0 8px rgba(212,175,55,0.5))" />
                  <defs>
                    <linearGradient id="revLineGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                      <stop offset="0%" stop-color="#D4AF37" stop-opacity="0.8" />
                      <stop offset="100%" stop-color="#D4AF37" stop-opacity="0" />
                    </linearGradient>
                  </defs>
                </svg>
                
                <!-- Interactive Data Points -->
                <div v-for="(p, idx) in revenueGraphPoints" :key="'rev-point-'+idx" 
                     class="absolute w-3.5 h-3.5 -ml-[7px] -mt-[7px] rounded-full bg-charcoal border-2 border-gold shadow-[0_0_10px_rgba(212,175,55,0.6)] group/point cursor-pointer transition-all duration-300 hover:scale-[1.8] hover:bg-gold z-20"
                     :style="{ left: `${p.x}%`, top: `${p.y}%` }">
                  <!-- Tooltip -->
                  <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-3 opacity-0 group-hover/point:opacity-100 transition-all duration-200 bg-obsidian border border-gold/30 text-xs px-3 py-1.5 rounded-lg shadow-xl text-gold font-bold whitespace-nowrap z-30 pointer-events-none scale-50 group-hover/point:scale-100 origin-bottom">
                    ₦{{ Number(p.revenue).toLocaleString() }}
                  </div>
                </div>

                <!-- X-axis labels -->
                <div v-for="(p, idx) in revenueGraphPoints" :key="'rev-label-'+idx"
                     class="absolute -bottom-8 w-16 -ml-8 flex justify-center"
                     :style="{ left: `${p.x}%` }">
                  <span class="text-[10px] text-ivory/40 uppercase tracking-wider font-bold">{{ p.month }}</span>
                </div>
              </div>
              <div v-if="normalizedRevenueTrends.length === 0" class="w-full h-full flex items-center justify-center text-ivory/30 text-sm absolute inset-0 z-0">
                No revenue data available for this range.
              </div>
            </div>
          </article>

          <!-- Status Breakdown Donut Chart -->
          <article class="rounded-2xl border border-theme-border bg-theme-surface/80 p-6 backdrop-blur-sm flex flex-col items-center">
            <h2 class="font-display text-2xl text-theme-text mb-6 w-full text-left">Appointment <span class="text-gold">Status</span></h2>
            
            <div class="relative w-48 h-48 mb-6">
              <svg viewBox="0 0 100 100" class="w-full h-full transform -rotate-90">
                <circle cx="50" cy="50" r="40" fill="transparent" stroke="rgba(255,255,255,0.05)" stroke-width="20" />
                <circle v-for="(segment, idx) in donutSegments" :key="idx"
                        cx="50" cy="50" r="40" fill="transparent"
                        :stroke="segment.color"
                        stroke-width="20"
                        :stroke-dasharray="`${segment.length} ${segment.gap}`"
                        :stroke-dashoffset="segment.offset"
                        class="transition-all duration-1000 ease-out" />
              </svg>
              <div class="absolute inset-0 flex flex-col items-center justify-center">
                <span class="text-3xl font-display text-theme-text">{{ totalDonutCount }}</span>
                <span class="text-[10px] uppercase tracking-widest text-ivory/40">Total</span>
              </div>
            </div>

            <div class="w-full space-y-2">
              <div v-for="legend in legendItems" :key="legend.label" class="flex items-center justify-between text-sm">
                <div class="flex items-center gap-2">
                  <div class="w-3 h-3 rounded-full" :class="legend.bgClass"></div>
                  <span class="text-ivory/70 capitalize">{{ legend.label.replace('_', ' ') }}</span>
                </div>
                <span class="font-bold text-theme-text">{{ legend.count }}</span>
              </div>
            </div>
          </article>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
          <!-- Top Services Table -->
          <article class="rounded-2xl border border-theme-border bg-theme-surface/80 p-6 backdrop-blur-sm">
            <h2 class="font-display text-2xl text-theme-text mb-6">Top <span class="text-gold">Services</span></h2>
            <div class="overflow-hidden rounded-xl border border-theme-border bg-theme-bg/50">
              <table class="w-full text-left text-sm">
                <thead class="bg-white/5 text-ivory/60 border-b border-theme-border text-xs uppercase tracking-wider">
                  <tr>
                    <th class="p-4 font-semibold">Service</th>
                    <th class="p-4 font-semibold text-center">Bookings</th>
                    <th class="p-4 font-semibold text-right">Revenue</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-theme-border">
                  <tr v-for="service in analytics.top_services" :key="service.name" class="hover:bg-white/5 transition-colors">
                    <td class="p-4 font-medium text-theme-text flex items-center gap-3">
                      <div class="h-8 w-8 rounded-full bg-gold/10 flex items-center justify-center border border-gold/20 text-gold">
                        <SparklesIcon class="h-4 w-4" />
                      </div>
                      {{ service.name }}
                    </td>
                    <td class="p-4 text-center text-ivory/80">{{ service.count }}</td>
                    <td class="p-4 text-right text-emerald-400 font-semibold">₦{{ Number(service.revenue).toLocaleString() }}</td>
                  </tr>
                  <tr v-if="!analytics.top_services?.length">
                    <td colspan="3" class="p-8 text-center text-ivory/40 text-sm">No service data available.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </article>

          <!-- Busiest Days Chart -->
          <article class="rounded-2xl border border-theme-border bg-theme-surface/80 p-6 backdrop-blur-sm">
            <h2 class="font-display text-2xl text-theme-text mb-6">Busiest <span class="text-gold">Days</span></h2>
            
            <div class="space-y-4">
              <div v-for="day in normalizedBusiestDays" :key="day.day" class="group">
                <div class="flex justify-between text-sm mb-1">
                  <span class="text-ivory/80 font-medium">{{ day.day }}</span>
                  <span class="text-gold font-bold">{{ day.count }} appts</span>
                </div>
                <div class="h-2 w-full bg-theme-bg rounded-full overflow-hidden border border-theme-border">
                  <div class="h-full bg-gradient-to-r from-gold to-gold-light transition-all duration-1000 ease-out"
                       :style="{ width: `${day.percentage}%` }"></div>
                </div>
              </div>
              <div v-if="normalizedBusiestDays.length === 0" class="p-8 text-center text-ivory/40 text-sm">
                No day data available.
              </div>
            </div>
          </article>
        </div>

      </template>
    </section>
  </BarberLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import BarberLayout from '../layouts/BarberLayout.vue';
import { barberApi } from '../api/barber.api';
import { 
  BanknotesIcon, 
  UsersIcon, 
  CheckBadgeIcon, 
  StarIcon,
  SparklesIcon
} from '@heroicons/vue/24/outline';

const analytics = ref({});
const loading = ref(true);
const error = ref(false);
const range = ref('7d');

const ranges = [
  { label: '7 Days', value: '7d' },
  { label: '30 Days', value: '30d' },
  { label: 'This Month', value: 'month' },
  { label: 'All Time', value: 'all' }
];

const fetchAnalytics = async (silent = false) => {
  const cacheKey = `barber_analytics_${range.value}`;
  const cachedData = localStorage.getItem(cacheKey);
  
  if (cachedData) {
    try {
      analytics.value = JSON.parse(cachedData);
      if (!silent) loading.value = false; // Instantly remove loading state if we have cache
    } catch (e) {
      console.warn('Failed to parse cached analytics', e);
    }
  }

  if (!silent && !cachedData) loading.value = true;
  error.value = false;
  try {
    const res = await barberApi.analytics(range.value);
    analytics.value = res.data.data;
    localStorage.setItem(cacheKey, JSON.stringify(res.data.data));
  } catch (err) {
    console.error(err);
    if (!silent && !cachedData) error.value = true;
  } finally {
    if (!silent) loading.value = false;
  }
};

const setRange = (val) => {
  range.value = val;
  fetchAnalytics();
};

let refreshInterval;

onMounted(() => {
  fetchAnalytics();
  // Auto-refresh data every 30 seconds silently
  refreshInterval = setInterval(() => {
    fetchAnalytics(true);
  }, 30000);
});

onUnmounted(() => {
  if (refreshInterval) {
    clearInterval(refreshInterval);
  }
});

// Computed logic for Revenue Trends CSS Bar Chart
const normalizedRevenueTrends = computed(() => {
  const trends = analytics.value.revenue_trends || [];
  if (trends.length === 0) return [];
  
  const maxRevenue = Math.max(...trends.map(t => Number(t.revenue)), 1); 
  return trends.map(t => ({
    month: t.month,
    revenue: t.revenue,
    percentage: Math.max((Number(t.revenue) / maxRevenue) * 100, 5) 
  }));
});

// Computed SVG paths for Revenue Trends
const revenueGraphPoints = computed(() => {
  const trends = normalizedRevenueTrends.value;
  if (trends.length === 0) return [];
  if (trends.length === 1) {
    return [{ x: 50, y: 100 - trends[0].percentage, revenue: trends[0].revenue, month: trends[0].month }];
  }
  return trends.map((t, idx) => {
    const x = (idx / (trends.length - 1)) * 100;
    const y = 100 - t.percentage;
    return { x, y, revenue: t.revenue, month: t.month };
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

// Computed logic for Busiest Days Progress Bars
const normalizedBusiestDays = computed(() => {
  const days = analytics.value.busiest_days || [];
  if (days.length === 0) return [];
  
  const maxCount = Math.max(...days.map(d => Number(d.count)), 1);
  return days.map(d => ({
    day: d.day,
    count: d.count,
    percentage: (Number(d.count) / maxCount) * 100
  }));
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

const donutSegments = computed(() => {
  const total = totalDonutCount.value;
  if (total === 0) return [];
  
  const circumference = 2 * Math.PI * 40; // r=40
  let currentOffset = 0;
  
  return legendItems.value.map(item => {
    const percentage = item.count / total;
    const length = percentage * circumference;
    const gap = circumference - length;
    
    const offset = -currentOffset;
    currentOffset += length;
    
    return {
      color: item.hex,
      length,
      gap,
      offset
    };
  });
});
</script>
