<template>
  <AdminLayout>
    <section class="space-y-8 animate-fade-in">
      <!-- Page Header -->
      <div class="relative overflow-hidden rounded-xl border border-theme-border bg-theme-surface p-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
          <p class="text-xs uppercase tracking-[0.3em] text-admin/70 font-bold">Admin Dashboard</p>
          <h1 class="mt-2 font-display text-3xl lg:text-4xl text-theme-text">
            Platform <span class="text-admin">Analytics</span>
          </h1>
          <p class="mt-2 text-sm text-theme-muted max-w-xl">
            Track your revenue, client retention, and service popularity over time.
          </p>
        </div>

        <!-- Time Range Selector -->
        <div class="flex items-center gap-2 bg-theme-bg p-1 rounded-lg border border-theme-border w-max">
          <button v-for="r in ranges" :key="r.value"
                  @click="setRange(r.value)"
                  :class="[
                    'px-4 py-2 rounded-md text-sm font-semibold transition-all duration-300',
                    range === r.value 
                      ? 'bg-gold text-obsidian' 
                      : 'text-theme-muted hover:text-theme-text hover:bg-white/5'
                  ]">
            {{ r.label }}
          </button>
        </div>
      </div>

      <div v-if="loading" class="grid gap-6 grid-cols-1 md:grid-cols-2 lg:grid-cols-4">
        <div v-for="i in 4" :key="i" class="h-32 rounded-xl bg-white/5 animate-pulse border border-white/5"></div>
      </div>
      
      <div v-else-if="error" class="rounded-xl border border-red-500/20 bg-red-500/5 p-8 text-center text-red-400">
        Failed to load analytics data.
      </div>

      <template v-else>
        <!-- Stats Grid -->
        <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-4">
          <DashboardStatCard 
            label="Total Revenue" 
            :value="analytics.business_stats?.total_revenue || 0" 
            :icon="BanknotesIcon" 
            colorClass="text-emerald-400" 
            hoverBorder="hover:border-gold/30" 
            prefix="₦" />
          
          <DashboardStatCard 
            label="Total Appointments" 
            :value="analytics.business_stats?.total_appointments || 0" 
            :icon="UsersIcon" 
            colorClass="text-blue-400" 
            hoverBorder="hover:border-gold/30" />
            
          <DashboardStatCard 
            label="New Customers" 
            :value="analytics.business_stats?.new_customers || 0" 
            :icon="CheckBadgeIcon" 
            colorClass="text-indigo-400" 
            hoverBorder="hover:border-gold/30" />
            
          <DashboardStatCard 
            label="Total Customers" 
            :value="analytics.business_stats?.total_customers || 0" 
            :icon="StarIcon" 
            colorClass="text-amber-400" 
            hoverBorder="hover:border-gold/30" />
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
          <!-- Revenue Trends Chart -->
          <article class="lg:col-span-2 rounded-xl border border-theme-border bg-theme-surface p-6">
            <h2 class="font-display text-2xl text-theme-text mb-6">Revenue <span class="text-gold">Trends</span></h2>
            <TrendsLineChart 
              :trends="analytics.revenue_trends || []" 
              valueKey="revenue" 
              labelKey="month" 
              tooltipPrefix="₦" 
              emptyMessage="No revenue data available for this range." />
          </article>

          <!-- Status Breakdown Donut Chart -->
          <article class="rounded-xl border border-theme-border bg-theme-surface p-6 flex flex-col items-center">
            <h2 class="font-display text-2xl text-theme-text mb-6 w-full text-left">Appointment <span class="text-gold">Status</span></h2>
            
            <StatusDonutChart 
              :donutSegments="donutSegments" 
              :total="totalDonutCount" 
              label="Total" />

            <div class="w-full space-y-2 mt-4">
              <div v-for="legend in legendItems" :key="legend.label" class="flex items-center justify-between text-sm">
                <div class="flex items-center gap-2">
                  <div class="w-3 h-3 rounded-full" :class="legend.bgClass"></div>
                  <span class="text-theme-muted capitalize">{{ legend.label.replace('_', ' ') }}</span>
                </div>
                <span class="font-bold text-theme-text tabular-nums">{{ legend.count }}</span>
              </div>
            </div>
          </article>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
          <!-- Top Services Table -->
          <article class="rounded-xl border border-theme-border bg-theme-surface p-6">
            <h2 class="font-display text-2xl text-theme-text mb-6">Top <span class="text-gold">Services</span></h2>
            <div class="overflow-hidden rounded-lg border border-theme-border bg-theme-bg/50">
              <table class="w-full text-left text-sm">
                <thead class="bg-white/5 text-theme-muted border-b border-theme-border text-xs uppercase tracking-wider">
                  <tr>
                    <th class="p-4 font-semibold">Service</th>
                    <th class="p-4 font-semibold text-center">Bookings</th>
                    <th class="p-4 font-semibold text-right">Revenue</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-theme-border">
                  <tr v-for="service in analytics.top_services" :key="service.name" class="hover:bg-white/5 transition-colors">
                    <td class="p-4 font-medium text-theme-text flex items-center gap-3">
                      <div class="h-8 w-8 rounded-md bg-gold/10 flex items-center justify-center border border-gold/20 text-gold">
                        <SparklesIcon class="h-4 w-4" />
                      </div>
                      {{ service.name }}
                    </td>
                    <td class="p-4 text-center text-theme-muted tabular-nums">{{ service.count }}</td>
                    <td class="p-4 text-right text-emerald-400 font-semibold tabular-nums">₦{{ formatCurrency(service.revenue) }}</td>
                  </tr>
                  <tr v-if="!analytics.top_services?.length">
                    <td colspan="3" class="p-8 text-center text-theme-muted text-sm">No service data available.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </article>

          <!-- Booking Trends Chart -->
          <article class="rounded-xl border border-theme-border bg-theme-surface p-6">
            <h2 class="font-display text-2xl text-theme-text mb-6">Booking <span class="text-admin">Trends</span></h2>
            <ProgressBarsChart 
              :items="analytics.booking_trends || []" 
              labelKey="month" 
              valueKey="count" 
              unit="appts" 
              emptyMessage="No trend data available." 
              valueClass="text-admin" 
              barClass="bg-admin" />
          </article>
        </div>

      </template>
    </section>
  </AdminLayout>
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue';
import AdminLayout from '@/portals/admin/layouts/AdminLayout.vue';
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
} = useDashboardAnalytics('admin');

const formatCurrency = (val) => new Intl.NumberFormat('en-NG').format(Number(val || 0));

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
