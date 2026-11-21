<template>
  <BarberLayout>
    <section class="space-y-8 animate-fade-in">
      <!-- Page Header -->
      <div class="relative overflow-hidden rounded-3xl border border-white/[0.08] bg-[#12161C] p-6 sm:p-8 md:p-10 shadow-2xl flex flex-col md:flex-row md:items-end justify-between gap-6 group">
        <div class="absolute -top-36 -right-36 w-80 h-80 bg-gold/10 rounded-full blur-[100px] pointer-events-none group-hover:bg-gold/15 transition-colors duration-700"></div>
        <div class="absolute -bottom-36 -left-36 w-80 h-80 bg-amber-500/10 rounded-full blur-[100px] pointer-events-none group-hover:bg-amber-500/15 transition-colors duration-700"></div>

        <div class="relative z-10">
          <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-gold/10 border border-gold/20 mb-4">
            <PresentationChartLineIcon class="h-3.5 w-3.5 text-gold" />
            <span class="text-[10px] font-bold text-gold tracking-widest uppercase">Telemetry & Metrics</span>
          </div>
          <h1 class="text-3xl sm:text-4xl md:text-5xl font-display font-bold text-white tracking-tight mb-2">
            Performance <span class="text-transparent bg-clip-text bg-gradient-to-r from-gold via-amber-400 to-amber-200">Analytics</span>
          </h1>
          <p class="text-slate-400 text-xs sm:text-sm max-w-lg leading-relaxed">
            Track revenue trends, guest retention rates, chair occupancy, and haircut volume across time periods.
          </p>
        </div>

        <!-- Time Range Selector -->
        <div class="relative z-10 flex items-center gap-1.5 bg-[#0B0F13] p-1.5 rounded-2xl border border-white/[0.08] shrink-0">
          <button
            v-for="r in ranges"
            :key="r.value"
            @click="setRange(r.value)"
            class="px-3.5 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition-all duration-300"
            :class="
              range === r.value
                ? 'bg-gradient-to-r from-gold via-amber-400 to-amber-500 text-obsidian shadow-md shadow-gold/25 font-black'
                : 'text-slate-400 hover:text-white hover:bg-white/[0.04]'
            "
          >
            {{ r.label }}
          </button>
        </div>
      </div>

      <div v-if="loading" class="grid gap-6 grid-cols-1 md:grid-cols-2 lg:grid-cols-4">
        <div v-for="i in 4" :key="i" class="h-32 rounded-2xl bg-theme-surface/50 animate-pulse border border-theme-border"></div>
      </div>
      
      <div v-else-if="error" class="rounded-2xl border border-red-500/20 bg-red-500/5 p-8 text-center text-red-400">
        Failed to load analytics data.
      </div>

      <template v-else>
        <!-- Stats Grid -->
        <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-4">
          <DashboardStatCard 
            label="Total Revenue" 
            :value="analytics.performance_metrics?.total_revenue || 0" 
            :icon="BanknotesIcon" 
            colorClass="text-emerald-400" 
            hoverBorder="hover:border-gold/30" 
            prefix="₦" />
          
          <DashboardStatCard 
            label="Clients Served" 
            :value="analytics.performance_metrics?.clients_served || 0" 
            :icon="UsersIcon" 
            colorClass="text-blue-400" 
            hoverBorder="hover:border-gold/30" />
            
          <DashboardStatCard 
            label="Completion Rate" 
            :value="analytics.performance_metrics?.completion_rate || 0" 
            :icon="CheckBadgeIcon" 
            colorClass="text-indigo-400" 
            hoverBorder="hover:border-gold/30" 
            suffix="%" />
            
          <DashboardStatCard 
            label="Avg Rating" 
            :value="Number(analytics.performance_metrics?.avg_rating || 0).toFixed(1)" 
            :icon="StarIcon" 
            colorClass="text-amber-400" 
            hoverBorder="hover:border-gold/30"
            :formatNumber="false" />
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
          <!-- Revenue Trends Chart -->
          <article class="lg:col-span-2 rounded-2xl border border-theme-border bg-theme-surface/80 p-6 backdrop-blur-sm">
            <h2 class="font-display text-2xl text-theme-text mb-6">Revenue <span class="text-gold">Trends</span></h2>
            <TrendsLineChart 
              :trends="analytics.revenue_trends || []" 
              valueKey="revenue" 
              labelKey="month" 
              tooltipPrefix="₦" 
              emptyMessage="No revenue data available for this range." />
          </article>

          <!-- Status Breakdown Donut Chart -->
          <article class="rounded-2xl border border-theme-border bg-theme-surface/80 p-6 backdrop-blur-sm flex flex-col items-center">
            <h2 class="font-display text-2xl text-theme-text mb-6 w-full text-left">Appointment <span class="text-gold">Status</span></h2>
            
            <StatusDonutChart 
              :donutSegments="donutSegments" 
              :total="totalDonutCount" 
              label="Total" />

            <div class="w-full space-y-2 mt-4">
              <div v-for="legend in legendItems" :key="legend.label" class="flex items-center justify-between text-sm">
                <div class="flex items-center gap-2">
                  <div class="w-3 h-3 rounded-full" :class="legend.bgClass"></div>
                  <span class="text-theme-text-muted capitalize">{{ legend.label.replace('_', ' ') }}</span>
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
            <div class="overflow-x-auto custom-scrollbar rounded-xl border border-theme-border bg-theme-bg/50">
              <table class="w-full text-left text-sm">
                <thead class="bg-theme-surface text-theme-text-muted border-b border-theme-border text-xs uppercase tracking-wider">
                  <tr>
                    <th class="p-4 font-semibold">Service</th>
                    <th class="p-4 font-semibold text-center">Bookings</th>
                    <th class="p-4 font-semibold text-right">Revenue</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-theme-border">
                  <tr v-for="service in analytics.top_services" :key="service.name" class="hover:bg-theme-surface/50 transition-colors">
                    <td class="p-4 font-medium text-theme-text flex items-center gap-3">
                      <div class="h-8 w-8 rounded-full bg-gold/10 flex items-center justify-center border border-gold/20 text-gold">
                        <SparklesIcon class="h-4 w-4" />
                      </div>
                      {{ service.name }}
                    </td>
                    <td class="p-4 text-center text-theme-text">{{ service.count }}</td>
                    <td class="p-4 text-right text-emerald-400 font-semibold">₦{{ Number(service.revenue).toLocaleString() }}</td>
                  </tr>
                  <tr v-if="!analytics.top_services?.length">
                    <td colspan="3" class="p-8 text-center text-theme-text-muted text-sm">No service data available.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </article>

          <!-- Busiest Days Chart -->
          <article class="rounded-2xl border border-theme-border bg-theme-surface/80 p-6 backdrop-blur-sm">
            <h2 class="font-display text-2xl text-theme-text mb-6">Busiest <span class="text-gold">Days</span></h2>
            <ProgressBarsChart 
              :items="analytics.busiest_days || []" 
              labelKey="day" 
              valueKey="count" 
              unit="appts" 
              emptyMessage="No day data available." 
              valueClass="text-gold" 
              barClass="bg-gradient-to-r from-gold to-gold-light" />
          </article>
        </div>

      </template>
    </section>
  </BarberLayout>
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue';
import BarberLayout from '@/portals/barber/layouts/BarberLayout.vue';
import { 
  BanknotesIcon, 
  UsersIcon, 
  CheckBadgeIcon, 
  StarIcon,
  SparklesIcon,
  PresentationChartLineIcon
} from '@heroicons/vue/24/outline';
import { useDashboardAnalytics } from '../composables/useDashboardAnalytics';
import DashboardStatCard from '../components/DashboardStatCard.vue';
import TrendsLineChart from '../components/TrendsLineChart.vue';
import StatusDonutChart from '../components/StatusDonutChart.vue';
import ProgressBarsChart from '../components/ProgressBarsChart.vue';

const {
  analytics,
  loading,
  error,
  range,
  ranges,
  fetchAnalytics,
  setRange,
  legendItems,
  totalDonutCount,
  donutSegments
} = useDashboardAnalytics('barber');

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
</script>
