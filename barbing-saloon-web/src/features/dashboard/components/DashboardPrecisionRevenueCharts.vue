<template>
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
    <!-- Left Card: Stacked Segmented Equalizer Chart (8 cols) -->
    <DashboardPrecisionRevenueEqualizer
      :period-revenue-total="periodRevenueTotal"
      :thirty-day-trend="thirtyDayTrend"
      :revenue-trend="revenueTrend"
      :booking-trend="bookingTrend"
    />

    <!-- Right Card: Monthly Segmented Bar Chart (4 cols) -->
    <DashboardPrecisionMonthlyChart
      :monthly-revenue-trend="monthlyRevenueTrend"
      :period-revenue-total="periodRevenueTotal"
    />
  </div>
</template>

<script setup>
import { computed } from 'vue';
import DashboardPrecisionRevenueEqualizer from './DashboardPrecisionRevenueEqualizer.vue';
import DashboardPrecisionMonthlyChart from './DashboardPrecisionMonthlyChart.vue';

const props = defineProps({
  revenueTrend: { type: Array, default: () => [] },
  bookingTrend: { type: Array, default: () => [] },
  thirtyDayTrend: { type: Array, default: () => [] },
  monthlyRevenueTrend: { type: Array, default: () => [] },
  stats: { type: Object, default: () => ({}) },
  revenue: { type: Object, default: () => ({}) }
});

const periodRevenueTotal = computed(() => {
  const r = props.revenue || {};
  const s = props.stats || {};
  return Number(r.total_revenue || s.month_revenue || s.week_revenue || s.today_revenue || 0);
});
</script>
