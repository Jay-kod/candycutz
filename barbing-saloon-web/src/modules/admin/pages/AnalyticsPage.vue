<template>
  <AdminLayout>
    <section class="space-y-8 animate-fade-in">
      <!-- Page Header -->
      <div class="relative overflow-hidden rounded-3xl border border-admin/20 bg-gradient-to-br from-obsidian via-charcoal to-[#2A1100] p-8 shadow-2xl flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-admin/5 blur-3xl"></div>
        <div class="absolute -left-16 -bottom-16 h-48 w-48 rounded-full bg-admin/5 blur-3xl"></div>
        
        <div class="relative z-10">
          <p class="text-xs uppercase tracking-[0.3em] text-admin/70 font-bold">Admin Dashboard</p>
          <h1 class="mt-2 font-display text-4xl text-theme-text drop-shadow-lg">
            Platform <span class="text-transparent bg-clip-text bg-gradient-to-r from-admin to-admin-light">Analytics</span>
          </h1>
          <p class="mt-2 text-sm text-ivory/60 max-w-xl">
            High-level overview of saloon performance, growth, and barber metrics.
          </p>
        </div>

        <!-- Time Range Selector -->
        <div class="relative z-10 flex items-center gap-2 bg-black/20 backdrop-blur p-1 rounded-xl border border-admin/10 shadow-inner w-max">
          <button v-for="r in ranges" :key="r.value"
                  @click="setRange(r.value)"
                  :class="[
                    'px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-300',
                    range === r.value 
                      ? 'bg-admin text-white shadow-md' 
                      : 'text-ivory/50 hover:text-white hover:bg-white/5'
                  ]">
            {{ r.label }}
          </button>
        </div>
      </div>

      <div v-if="loading" class="grid gap-6 grid-cols-1 md:grid-cols-2 xl:grid-cols-4">
        <div v-for="i in 4" :key="i" class="h-32 rounded-2xl bg-white/5 animate-pulse border border-white/5"></div>
      </div>
      
      <div v-else-if="error" class="rounded-2xl border border-red-500/20 bg-red-500/5 p-8 text-center text-red-400">
        Failed to load analytics data.
      </div>

      <template v-else>
        <!-- Stats Grid -->
        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
          <div class="group relative overflow-hidden rounded-2xl border border-admin/20 bg-black/20 p-6 backdrop-blur-sm transition-all duration-300 hover:border-admin/40 hover:bg-black/40 hover:shadow-[0_10px_30px_rgba(255,103,0,0.12)]">
            <BanknotesIcon class="absolute -right-4 -top-4 h-32 w-32 text-emerald-500/5 group-hover:text-emerald-500/10 transition-colors duration-500 transform -rotate-12" />
            <div class="relative z-10">
              <div class="flex items-center gap-3 mb-4">
                <BanknotesIcon class="h-8 w-8 text-emerald-400 group-hover:scale-110 transition-transform" />
                <p class="text-[11px] font-bold uppercase tracking-[0.25em] text-emerald-400/80">Total Revenue</p>
              </div>
              <p class="font-display text-4xl text-emerald-400 group-hover:text-emerald-300 transition-colors">₦{{ Number(analytics.business_stats?.total_revenue || 0).toLocaleString() }}</p>
            </div>
          </div>

          <div class="group relative overflow-hidden rounded-2xl border border-admin/20 bg-black/20 p-6 backdrop-blur-sm transition-all duration-300 hover:border-admin/40 hover:bg-black/40 hover:shadow-[0_10px_30px_rgba(255,103,0,0.12)]">
            <CalendarDaysIcon class="absolute -right-4 -top-4 h-32 w-32 text-admin/5 group-hover:text-admin/10 transition-colors duration-500 transform rotate-12" />
            <div class="relative z-10">
              <div class="flex items-center gap-3 mb-4">
                <CalendarDaysIcon class="h-8 w-8 text-admin group-hover:scale-110 transition-transform" />
                <p class="text-[11px] font-bold uppercase tracking-[0.25em] text-admin/80">Total Bookings</p>
              </div>
              <p class="font-display text-4xl text-admin-light group-hover:text-admin transition-colors">{{ analytics.business_stats?.total_appointments || 0 }}</p>
            </div>
          </div>

          <div class="group relative overflow-hidden rounded-2xl border border-admin/20 bg-black/20 p-6 backdrop-blur-sm transition-all duration-300 hover:border-admin/40 hover:bg-black/40 hover:shadow-[0_10px_30px_rgba(255,103,0,0.12)]">
            <UserGroupIcon class="absolute -right-4 -top-4 h-32 w-32 text-blue-500/5 group-hover:text-blue-500/10 transition-colors duration-500 transform -rotate-12" />
            <div class="relative z-10">
              <div class="flex items-center gap-3 mb-4">
                <UserGroupIcon class="h-8 w-8 text-blue-400 group-hover:scale-110 transition-transform" />
                <p class="text-[11px] font-bold uppercase tracking-[0.25em] text-blue-400/80">Customers</p>
              </div>
              <p class="font-display text-4xl text-blue-400 group-hover:text-blue-300 transition-colors">{{ analytics.business_stats?.new_customers || 0 }}</p>
            </div>
          </div>

          <div class="group relative overflow-hidden rounded-2xl border border-admin/20 bg-black/20 p-6 backdrop-blur-sm transition-all duration-300 hover:border-admin/40 hover:bg-black/40 hover:shadow-[0_10px_30px_rgba(255,103,0,0.12)]">
            <UsersIcon class="absolute -right-4 -top-4 h-32 w-32 text-purple-500/5 group-hover:text-purple-500/10 transition-colors duration-500 transform rotate-12" />
            <div class="relative z-10">
              <div class="flex items-center gap-3 mb-4">
                <UsersIcon class="h-8 w-8 text-purple-400 group-hover:scale-110 transition-transform" />
                <p class="text-[11px] font-bold uppercase tracking-[0.25em] text-purple-400/80">Barbers</p>
              </div>
              <p class="font-display text-4xl text-purple-400 group-hover:text-purple-300 transition-colors">{{ analytics.top_barbers?.length || 0 }}</p>
            </div>
          </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
          <!-- Revenue Trends Chart (SVG Line) -->
          <article class="rounded-2xl border border-white/5 bg-black/20 p-6 backdrop-blur-sm">
            <h2 class="font-display text-2xl text-theme-text mb-6">Revenue <span class="text-admin">Trends</span></h2>
            
            <div class="p-6 relative h-64">
              <div class="absolute left-8 right-6 top-6 bottom-12 z-10">
                <svg viewBox="0 0 100 100" preserveAspectRatio="none" class="w-full h-full overflow-visible">
                  <defs>
                    <linearGradient id="revGrad" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="0%" stop-color="rgb(16 185 129)" stop-opacity="0.3" />
                      <stop offset="100%" stop-color="rgb(16 185 129)" stop-opacity="0" />
                    </linearGradient>
                  </defs>
                  
                  <!-- Grid lines -->
                  <line x1="0" y1="0" x2="100" y2="0" stroke="rgba(255,255,255,0.05)" stroke-width="0.5" />
                  <line x1="0" y1="50" x2="100" y2="50" stroke="rgba(255,255,255,0.05)" stroke-width="0.5" />
                  <line x1="0" y1="100" x2="100" y2="100" stroke="rgba(255,255,255,0.05)" stroke-width="0.5" />
                  
                  <path :d="revenueGraphAreaPath" fill="url(#revGrad)" class="transition-all duration-1000" />
                  <path :d="revenueGraphPath" fill="none" stroke="rgb(16 185 129)" stroke-width="1.5" class="transition-all duration-1000 drop-shadow-[0_0_8px_rgba(16,185,129,0.5)]" />
                  
                  <circle v-for="(p, i) in revenueGraphPoints" :key="`pt-${i}`"
                          :cx="p.x" :cy="p.y" r="2.5" fill="#111" stroke="rgb(16 185 129)" stroke-width="1.5"
                          class="transition-all duration-1000 hover:r-3 hover:fill-emerald-400 cursor-crosshair group z-20" />
                </svg>

                <div v-for="(p, i) in revenueGraphPoints" :key="`tooltip-${i}`"
                     class="absolute w-2 h-full -ml-1 group z-30 cursor-crosshair"
                     :style="{ left: `${p.x}%`, top: 0 }">
                  <div class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-opacity bg-black/90 backdrop-blur border border-emerald-500/30 text-xs px-2 py-1 rounded shadow-lg text-emerald-400 font-bold whitespace-nowrap pointer-events-none">
                    ₦{{ Number(p.revenue).toLocaleString() }}
                  </div>
                </div>
              </div>
              
              <div class="absolute bottom-4 left-8 right-6 flex justify-between text-[10px] uppercase font-bold tracking-wider text-ivory/40">
                <span v-for="(p, i) in revenueGraphPoints" :key="`label-${i}`">{{ p.month }}</span>
              </div>
              
              <div v-if="revenueGraphPoints.length === 0" class="absolute inset-0 flex items-center justify-center text-ivory/30 text-sm">
                No revenue data available for this range.
              </div>
            </div>
          </article>

          <!-- Booking Trends Chart (SVG Line) -->
          <article class="rounded-2xl border border-white/5 bg-black/20 p-6 backdrop-blur-sm">
            <h2 class="font-display text-2xl text-theme-text mb-6">Booking <span class="text-admin">Trends</span></h2>
            
            <div class="p-6 relative h-64">
              <div class="absolute left-8 right-6 top-6 bottom-12 z-10">
                <svg viewBox="0 0 100 100" preserveAspectRatio="none" class="w-full h-full overflow-visible">
                  <defs>
                    <linearGradient id="bookGrad" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="0%" stop-color="rgb(255 103 0)" stop-opacity="0.3" />
                      <stop offset="100%" stop-color="rgb(255 103 0)" stop-opacity="0" />
                    </linearGradient>
                  </defs>
                  
                  <line x1="0" y1="0" x2="100" y2="0" stroke="rgba(255,255,255,0.05)" stroke-width="0.5" />
                  <line x1="0" y1="50" x2="100" y2="50" stroke="rgba(255,255,255,0.05)" stroke-width="0.5" />
                  <line x1="0" y1="100" x2="100" y2="100" stroke="rgba(255,255,255,0.05)" stroke-width="0.5" />
                  
                  <path :d="bookingGraphAreaPath" fill="url(#bookGrad)" class="transition-all duration-1000" />
                  <path :d="bookingGraphPath" fill="none" stroke="rgb(255 103 0)" stroke-width="1.5" class="transition-all duration-1000 drop-shadow-[0_0_8px_rgba(255,103,0,0.5)]" />
                  
                  <circle v-for="(p, i) in bookingGraphPoints" :key="`bpt-${i}`"
                          :cx="p.x" :cy="p.y" r="2.5" fill="#111" stroke="rgb(255 103 0)" stroke-width="1.5"
                          class="transition-all duration-1000 hover:r-3 hover:fill-admin cursor-crosshair group z-20" />
                </svg>

                <div v-for="(p, i) in bookingGraphPoints" :key="`b-tooltip-${i}`"
                     class="absolute w-2 h-full -ml-1 group z-30 cursor-crosshair"
                     :style="{ left: `${p.x}%`, top: 0 }">
                  <div class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-opacity bg-black/90 backdrop-blur border border-admin/30 text-xs px-2 py-1 rounded shadow-lg text-admin font-bold pointer-events-none">
                    {{ p.count }} Bookings
                  </div>
                </div>
              </div>
              
              <div class="absolute bottom-4 left-8 right-6 flex justify-between text-[10px] uppercase font-bold tracking-wider text-ivory/40">
                <span v-for="(p, i) in bookingGraphPoints" :key="`b-label-${i}`">{{ p.month }}</span>
              </div>
              
              <div v-if="bookingGraphPoints.length === 0" class="absolute inset-0 flex items-center justify-center text-ivory/30 text-sm">
                No booking data available for this range.
              </div>
            </div>
          </article>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
          <!-- Status Breakdown Donut Chart -->
          <article class="rounded-2xl border border-white/5 bg-black/20 p-6 backdrop-blur-sm flex flex-col items-center">
            <h2 class="font-display text-xl text-theme-text mb-6 w-full text-left">Overall <span class="text-admin">Status</span></h2>
            
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

          <!-- Top Barbers Table -->
          <article class="lg:col-span-2 rounded-2xl border border-white/5 bg-black/20 p-6 backdrop-blur-sm flex flex-col">
            <h2 class="font-display text-xl text-theme-text mb-6">Top Performing <span class="text-admin">Barbers</span></h2>
            <div class="overflow-x-auto rounded-xl border border-white/5 bg-black/20 flex-1">
              <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-white/5 text-ivory/60 border-b border-white/10 text-xs uppercase tracking-wider">
                  <tr>
                    <th class="p-4 font-semibold">Barber</th>
                    <th class="p-4 font-semibold text-center">Rating</th>
                    <th class="p-4 font-semibold text-center">Completed</th>
                    <th class="p-4 font-semibold text-right">Revenue</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                  <tr v-for="barber in analytics.top_barbers" :key="barber.id" class="hover:bg-white/5 transition-colors">
                    <td class="p-4 font-medium text-theme-text">{{ barber.name }}</td>
                    <td class="p-4 text-center">
                      <div class="inline-flex items-center gap-1 bg-amber-500/10 text-amber-400 px-2 py-0.5 rounded text-xs font-bold">
                        <StarIcon class="w-3 h-3 fill-amber-400" />
                        {{ Number(barber.rating).toFixed(1) }}
                      </div>
                    </td>
                    <td class="p-4 text-center text-ivory/80">{{ barber.bookings }}</td>
                    <td class="p-4 text-right text-emerald-400 font-semibold">₦{{ Number(barber.revenue).toLocaleString() }}</td>
                  </tr>
                  <tr v-if="!analytics.top_barbers?.length">
                    <td colspan="4" class="p-8 text-center text-ivory/40 text-sm">No barber data available.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </article>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
          <!-- Top Services Table -->
          <article class="rounded-2xl border border-white/5 bg-black/20 p-6 backdrop-blur-sm">
            <h2 class="font-display text-xl text-theme-text mb-6">Most Popular <span class="text-admin">Services</span></h2>
            <div class="overflow-hidden rounded-xl border border-white/5 bg-black/20">
              <table class="w-full text-left text-sm">
                <thead class="bg-white/5 text-ivory/60 border-b border-white/10 text-xs uppercase tracking-wider">
                  <tr>
                    <th class="p-4 font-semibold">Service</th>
                    <th class="p-4 font-semibold text-center">Bookings</th>
                    <th class="p-4 font-semibold text-right">Revenue</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                  <tr v-for="service in analytics.top_services" :key="service.name" class="hover:bg-white/5 transition-colors">
                    <td class="p-4 font-medium text-theme-text">{{ service.name }}</td>
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

          <!-- Customer Growth Chart (SVG Line) -->
          <article class="rounded-2xl border border-white/5 bg-black/20 p-6 backdrop-blur-sm">
            <h2 class="font-display text-xl text-theme-text mb-6">Customer <span class="text-admin">Growth</span></h2>
            
            <div class="p-6 relative h-64">
              <div class="absolute left-8 right-6 top-6 bottom-12 z-10">
                <svg viewBox="0 0 100 100" preserveAspectRatio="none" class="w-full h-full overflow-visible">
                  <defs>
                    <linearGradient id="growthGrad" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="0%" stop-color="rgb(59 130 246)" stop-opacity="0.3" />
                      <stop offset="100%" stop-color="rgb(59 130 246)" stop-opacity="0" />
                    </linearGradient>
                  </defs>
                  
                  <line x1="0" y1="0" x2="100" y2="0" stroke="rgba(255,255,255,0.05)" stroke-width="0.5" />
                  <line x1="0" y1="50" x2="100" y2="50" stroke="rgba(255,255,255,0.05)" stroke-width="0.5" />
                  <line x1="0" y1="100" x2="100" y2="100" stroke="rgba(255,255,255,0.05)" stroke-width="0.5" />
                  
                  <path :d="growthGraphAreaPath" fill="url(#growthGrad)" class="transition-all duration-1000" />
                  <path :d="growthGraphPath" fill="none" stroke="rgb(59 130 246)" stroke-width="1.5" class="transition-all duration-1000 drop-shadow-[0_0_8px_rgba(59,130,246,0.5)]" />
                  
                  <circle v-for="(p, i) in growthGraphPoints" :key="`gpt-${i}`"
                          :cx="p.x" :cy="p.y" r="2.5" fill="#111" stroke="rgb(59 130 246)" stroke-width="1.5"
                          class="transition-all duration-1000 cursor-crosshair" />
                </svg>

                <div v-for="(p, i) in growthGraphPoints" :key="`g-tooltip-${i}`"
                     class="absolute w-2 h-full -ml-1 group z-30 cursor-crosshair"
                     :style="{ left: `${p.x}%`, top: 0 }">
                  <div class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-opacity bg-black/90 backdrop-blur border border-blue-500/30 text-xs px-2 py-1 rounded shadow-lg text-blue-400 font-bold pointer-events-none">
                    +{{ p.count }} Customers
                  </div>
                </div>
              </div>
              
              <div class="absolute bottom-4 left-8 right-6 flex justify-between text-[10px] uppercase font-bold tracking-wider text-ivory/40">
                <span v-for="(p, i) in growthGraphPoints" :key="`g-label-${i}`">{{ p.month }}</span>
              </div>
              
              <div v-if="growthGraphPoints.length === 0" class="absolute inset-0 flex items-center justify-center text-ivory/30 text-sm">
                No growth data available.
              </div>
            </div>
          </article>
        </div>

      </template>
    </section>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import AdminLayout from '../layouts/AdminLayout.vue';
import { adminApi } from '../api/admin.api';
import { 
  BanknotesIcon, 
  CalendarDaysIcon, 
  UsersIcon,
  UserGroupIcon,
  StarIcon
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
  const cacheKey = `admin_analytics_${range.value}`;
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
    const res = await adminApi.analytics(range.value);
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

// Computed logic for Booking Trends CSS Bar Chart
const normalizedBookingTrends = computed(() => {
  const trends = analytics.value.booking_trends || [];
  if (trends.length === 0) return [];
  
  const maxCount = Math.max(...trends.map(t => Number(t.count)), 1); 
  return trends.map(t => ({
    month: t.month,
    count: t.count,
    percentage: Math.max((Number(t.count) / maxCount) * 100, 5)
  }));
});

// SVG Line Graph properties for Revenue Trends
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

// SVG Line Graph properties for Booking Trends
const bookingGraphPoints = computed(() => {
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

// Computed logic for Customer Growth CSS Bar Chart
const normalizedCustomerGrowth = computed(() => {
  const growth = analytics.value.customer_growth || [];
  if (growth.length === 0) return [];
  
  const maxCount = Math.max(...growth.map(g => g.count), 1); 
  return growth.map(g => ({
    month: g.month,
    count: g.count,
    percentage: Math.max((g.count / maxCount) * 100, 5) 
  }));
});

// SVG Line Graph properties for Customer Growth
const growthGraphPoints = computed(() => {
  const trends = normalizedCustomerGrowth.value;
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

const growthGraphPath = computed(() => {
  const points = growthGraphPoints.value;
  if (points.length === 0) return '';
  if (points.length === 1) return `M 0,${points[0].y} L 100,${points[0].y}`;
  return points.map((p, i) => `${i === 0 ? 'M' : 'L'} ${p.x},${p.y}`).join(' ');
});

const growthGraphAreaPath = computed(() => {
  const points = growthGraphPoints.value;
  if (points.length === 0) return '';
  const path = growthGraphPath.value;
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
