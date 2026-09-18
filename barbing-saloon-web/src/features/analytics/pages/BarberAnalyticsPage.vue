<template>
  <BarberLayout>
    <section class="space-y-8 animate-fade-in">
      <!-- Page Header -->
      <div class="relative overflow-hidden rounded-2xl border border-gold/20 bg-gradient-to-br from-obsidian via-charcoal to-[#1A1A00] p-8 shadow-2xl flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-gold/5 blur-3xl pointer-events-none"></div>
        <div class="absolute -left-16 -bottom-16 h-48 w-48 rounded-full bg-gold/5 blur-3xl pointer-events-none"></div>
        
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
  SparklesIcon
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
