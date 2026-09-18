<template>
  <AdminLayout>
    <section class="space-y-6 animate-fade-in pb-12">
      <!-- Header Banner & Controls -->
      <ReportsHeader
        :loading="loading"
        :selectedRange="selectedRange"
        :rangeOptions="rangeOptions"
        @update:selectedRange="setRange"
        @generate="showReportPreview = true"
      />

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-20">
        <div class="h-12 w-12 rounded-full border-4 border-admin/20 border-t-admin animate-spin"></div>
      </div>

      <template v-else>
        <!-- KPI Cards -->
        <ReportsKPICards
          :businessStats="businessStats"
          :platformStats="platformStats"
        />

        <!-- Top Barbers & Services -->
        <ReportsTopPerformers
          :topBarbers="topBarbers"
          :topServices="topServices"
        />

        <!-- Booking Status Breakdown -->
        <ReportsStatusBreakdown
          :statusBreakdown="statusBreakdown"
        />
      </template>
    </section>

    <!-- PDF Preview Modal -->
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
import AdminLayout from '@/portals/Admin/layouts/Adminlayout.vue';

// Subcomponents
import ReportsHeader from '@/features/analytics/components/ReportsHeader.vue';
import ReportsKPICards from '@/features/analytics/components/ReportsKPICards.vue';
import ReportsTopPerformers from '@/features/analytics/components/ReportsTopPerformers.vue';
import ReportsStatusBreakdown from '@/features/analytics/components/ReportsStatusBreakdown.vue';
import ReportsPDFPreview from '@/features/analytics/components/ReportsPDFPreview.vue';

// Composable
import { useAnalytics } from '@/features/analytics/composables/useAnalytics';

const showReportPreview = ref(false);

const {
  loading,
  selectedRange,
  rangeOptions,
  activeRangeLabel,
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
  animation: fadeIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(12px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
