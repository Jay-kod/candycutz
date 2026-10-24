<template>
  <AdminLayout>
    <section class="space-y-4 animate-fade-in pb-10 max-w-[1600px] mx-auto">
      <!-- High-Fidelity Skeletal Preload Matching Exact Dashboard Layout -->
      <DashboardPrecisionSkeleton v-if="loading" />

      <!-- Fully Populated Dashboard Matching Precision Layout -->
      <template v-else>
        <!-- Top Bar: Greeting + Quick Action -->
        <DashboardPrecisionHeader />

        <!-- Row 1: 4 Metric Cards with Mini Equalizer Sparklines -->
        <DashboardPrecisionKpis 
          :stats="dashboard.stats" 
          :revenue="dashboard.revenue" 
        />

        <!-- Row 2: 8-col Segmented Equalizer Chart + 4-col Monthly Segmented Chart -->
        <DashboardPrecisionRevenueCharts 
          :revenue-trend="dashboard.revenue_trend" 
        />

        <!-- Row 3: Funnel Sankey Conversion Flow + Radial-Lined Sunburst & Breakdown Table -->
        <DashboardPrecisionFunnelAndStatus 
          :status-breakdown="dashboard.status_breakdown" 
        />

        <!-- Row 4: 4 Cards (Traffic Sources + Team + Hourly Heatmap Matrix + Quick Actions) -->
        <DashboardPrecisionBottomCards 
          :top-services="dashboard.top_services" 
          :top-barbers="dashboard.top_barbers" 
        />
      </template>
    </section>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/portals/admin/layouts/AdminLayout.vue';
import { useAdminDashboard } from '@/features/dashboard/composables/useAdminDashboard';
import DashboardPrecisionHeader from '@/features/dashboard/components/DashboardPrecisionHeader.vue';
import DashboardPrecisionKpis from '@/features/dashboard/components/DashboardPrecisionKpis.vue';
import DashboardPrecisionRevenueCharts from '@/features/dashboard/components/DashboardPrecisionRevenueCharts.vue';
import DashboardPrecisionFunnelAndStatus from '@/features/dashboard/components/DashboardPrecisionFunnelAndStatus.vue';
import DashboardPrecisionBottomCards from '@/features/dashboard/components/DashboardPrecisionBottomCards.vue';
import DashboardPrecisionSkeleton from '@/features/dashboard/components/DashboardPrecisionSkeleton.vue';

const { dashboard, loading } = useAdminDashboard();
</script>
