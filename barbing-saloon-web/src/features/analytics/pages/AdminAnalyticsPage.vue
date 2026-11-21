<template>
  <AdminLayout>
    <div class="space-y-6 pb-12 animate-fade-in">
      <!-- Top Header & Time Range Controls -->
      <AnalyticsHeader
        :currentRange="range"
        :ranges="ranges"
        :refreshing="refreshing"
        :lastRefreshed="lastRefreshed"
        @update:range="setRange"
        @refresh="refreshNow"
      />

      <!-- Loading Preloader -->
      <AnalyticsSkeleton v-if="loading" />

      <!-- Error State -->
      <div
        v-else-if="error"
        class="rounded-2xl border border-rose-500/20 bg-rose-500/5 p-8 text-center"
      >
        <p class="font-display text-lg font-bold text-rose-400">Failed to Load Platform Analytics</p>
        <p class="mt-1 text-xs text-rose-300/70">{{ error }}</p>
        <button
          type="button"
          @click="fetchAnalytics(false)"
          class="mt-4 inline-flex items-center gap-2 rounded-xl bg-rose-500/20 px-4 py-2 text-xs font-semibold text-rose-300 hover:bg-rose-500/30 transition-colors"
        >
          Retry Connection
        </button>
      </div>

      <!-- Main Dashboard Grid -->
      <template v-else>
        <!-- 4 KPI Metrics Cards -->
        <AnalyticsKPICards
          :businessStats="businessStats"
          :platformStats="platformStats"
          :formatCurrency="formatCurrency"
        />

        <!-- Primary Analytics Row: Revenue Trajectory & Status Donut -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div class="lg:col-span-2">
            <AnalyticsRevenueChart
              :trends="revenueTrends"
              :formatCurrency="formatCurrency"
            />
          </div>
          <div>
            <AnalyticsStatusDonut
              :donutSegments="donutSegments"
              :legendItems="legendItems"
              :totalCount="totalDonutCount"
              :completionRate="completionRate"
            />
          </div>
        </div>

        <!-- Secondary Analytics Row: Top Services & Booking Volume -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <AnalyticsTopServices
            :services="topServices"
            :totalRevenue="businessStats.total_revenue"
            :formatCurrency="formatCurrency"
          />

          <AnalyticsBookingTrends
            :trends="bookingTrends"
          />
        </div>

        <!-- Tertiary Analytics Row: Top Barbers Throughput -->
        <div v-if="topBarbers.length > 0">
          <AnalyticsTopBarbers
            :barbers="topBarbers"
            :formatCurrency="formatCurrency"
          />
        </div>
      </template>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/portals/admin/layouts/AdminLayout.vue';
import { useAdminAnalyticsDashboard } from '../composables/useAdminAnalyticsDashboard';

// Modular Subcomponents
import AnalyticsHeader from '../components/admin/AnalyticsHeader.vue';
import AnalyticsKPICards from '../components/admin/AnalyticsKPICards.vue';
import AnalyticsRevenueChart from '../components/admin/AnalyticsRevenueChart.vue';
import AnalyticsStatusDonut from '../components/admin/AnalyticsStatusDonut.vue';
import AnalyticsTopServices from '../components/admin/AnalyticsTopServices.vue';
import AnalyticsTopBarbers from '../components/admin/AnalyticsTopBarbers.vue';
import AnalyticsBookingTrends from '../components/admin/AnalyticsBookingTrends.vue';
import AnalyticsSkeleton from '../components/admin/AnalyticsSkeleton.vue';

const {
  loading,
  refreshing,
  error,
  range,
  ranges,
  lastRefreshed,
  fetchAnalytics,
  setRange,
  refreshNow,
  formatCurrency,
  legendItems,
  totalDonutCount,
  completionRate,
  donutSegments,
  businessStats,
  platformStats,
  topServices,
  topBarbers,
  revenueTrends,
  bookingTrends
} = useAdminAnalyticsDashboard();
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(8px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
