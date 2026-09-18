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
              {{ r.shortLabel || r.label }}
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
              <TrendsLineChart 
                :trends="analytics.booking_trends || []" 
                valueKey="count" 
                labelKey="month" 
                :showYAxis="true" 
                tooltipSuffix="booking" 
                emptyMessage="No booking data available for this range." />
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
                <StatusDonutChart 
                  :donutSegments="donutSegments" 
                  :total="totalDonutCount" 
                  label="Total" 
                  containerClass="w-44 h-44" 
                  :strokeWidth="18" 
                  labelClass="text-[10px] text-theme-muted font-bold" />

                <div class="w-full space-y-2.5 mt-4">
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
import { onMounted, computed } from 'vue';
import CustomerLayout from '@/portals/Customer/layouts/Customerlayout.vue';
import { 
  BanknotesIcon, 
  CalculatorIcon, 
  HeartIcon, 
  StarIcon,
  SparklesIcon,
  CalendarDaysIcon,
  ExclamationTriangleIcon,
  CheckBadgeIcon
} from '@heroicons/vue/24/outline';
import { ChartPieIcon } from '@heroicons/vue/24/solid';
import { useDashboardAnalytics } from '../composables/useDashboardAnalytics';
import TrendsLineChart from '../components/TrendsLineChart.vue';
import StatusDonutChart from '../components/StatusDonutChart.vue';

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
  completionRate,
  donutSegments
} = useDashboardAnalytics('customer');

onMounted(() => {
  fetchAnalytics();
});

// Stat Cards
const statCards = computed(() => [
  {
    label: 'Total Spent',
    value: '₦' + Number(analytics.value.spending_summary?.total_spent || 0).toLocaleString(),
    icon: BanknotesIcon,
    hoverBorder: 'hover:border-emerald-400/30',
    glow: 'bg-emerald-500/20',
  },
  {
    label: 'Average Spend',
    value: '₦' + Number(analytics.value.spending_summary?.avg_spent || 0).toLocaleString(undefined, { maximumFractionDigits: 0 }),
    icon: CalculatorIcon,
    hoverBorder: 'hover:border-blue-400/30',
    glow: 'bg-blue-500/20',
  },
  {
    label: 'Wishlist Items',
    value: String(analytics.value.activity_timeline?.wishlist_items || 0),
    icon: HeartIcon,
    hoverBorder: 'hover:border-rose-400/30',
    glow: 'bg-rose-500/20',
  },
  {
    label: 'Reviews Given',
    value: String(analytics.value.activity_timeline?.reviews_given || 0),
    icon: StarIcon,
    hoverBorder: 'hover:border-amber-400/30',
    glow: 'bg-amber-500/20',
  },
]);
</script>
