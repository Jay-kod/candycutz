<template>
  <AdminLayout>
    <div class="space-y-6 pb-12 animate-fade-in">
      <!-- Header Banner & Range Controls -->
      <ReportsHeader
        :loading="loading"
        :selectedRange="selectedRange"
        :rangeOptions="rangeOptions"
        :lastRefreshed="lastRefreshed"
        @update:selectedRange="setRange"
        @generate="showReportPreview = true"
      />

      <!-- Content-Aware Loading Skeleton -->
      <ReportsSkeleton v-if="loading" />

      <!-- Error State -->
      <div
        v-else-if="error"
        class="rounded-2xl border border-rose-500/20 bg-rose-500/5 p-8 text-center"
      >
        <p class="font-display text-lg font-bold text-rose-400">Failed to Load Operational Report</p>
        <p class="mt-1 text-xs text-rose-300/70">{{ error }}</p>
        <button
          type="button"
          @click="fetchAnalytics"
          class="mt-4 inline-flex items-center gap-2 rounded-xl bg-rose-500/20 px-4 py-2 text-xs font-semibold text-rose-300 hover:bg-rose-500/30 transition-colors"
        >
          Retry Connection
        </button>
      </div>

      <!-- Main Operational Report Dossier -->
      <template v-else>
        <!-- 5 Executive KPI Cards -->
        <ReportsKPICards
          :businessStats="businessStats"
          :platformStats="platformStats"
        />

        <!-- Top Barbers & Popular Services Split -->
        <ReportsTopPerformers
          :topBarbers="topBarbers"
          :topServices="topServices"
        />

        <!-- Booking Lifecycle Status Breakdown -->
        <ReportsStatusBreakdown
          :statusBreakdown="statusBreakdown"
        />
      </template>
    </div>

    <!-- Executive PDF Preview & Generation Modal -->
    <ReportsPDFPreview
      :show="showReportPreview"
      :businessStats="businessStats"
      :platformStats="platformStats"
      :topBarbers="topBarbers"
      :topServices="topServices"
      :statusBreakdown="statusBreakdown"
      :activeRangeLabel="activeRangeLabel"
      :selectedRange="selectedRange"
      @close="showReportPreview = false"
    />
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import AdminLayout from '@/portals/admin/layouts/AdminLayout.vue';

// Decomposed Modular Subcomponents
import ReportsHeader from '../components/ReportsHeader.vue';
import ReportsKPICards from '../components/ReportsKPICards.vue';
import ReportsTopPerformers from '../components/ReportsTopPerformers.vue';
import ReportsStatusBreakdown from '../components/ReportsStatusBreakdown.vue';
import ReportsSkeleton from '../components/ReportsSkeleton.vue';
import ReportsPDFPreview from '../components/ReportsPDFPreview.vue';

// Canonical /api/v1/admin/reports Composable
import { useAnalytics } from '../composables/useAnalytics';

const showReportPreview = ref(false);

const {
  loading,
  error,
  selectedRange,
  rangeOptions,
  activeRangeLabel,
  lastRefreshed,
  businessStats,
  platformStats,
  topBarbers,
  topServices,
  statusBreakdown,
  fetchAnalytics,
  setRange
} = useAnalytics();

onMounted(() => {
  fetchAnalytics();
});
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
