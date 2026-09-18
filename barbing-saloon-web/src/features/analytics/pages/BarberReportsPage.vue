<template>
  <BarberLayout>
    <section class="space-y-6 animate-fade-in pb-12">
      <ReportsHeader
        title="Operational"
        titleHighlight="Analytics"
        subtitle="Comprehensive overview of revenue, performance, and trends."
        theme="gold"
        :activeRangeLabel="activeRangeLabel"
        :selectedRange="selectedRange"
        :loading="loading"
        @update:selectedRange="setRange"
        @export="showReportPreview = true"
      />

      <div v-if="loading" class="flex justify-center items-center py-20">
        <div class="h-12 w-12 rounded-full border-4 border-gold/20 border-t-gold animate-spin"></div>
      </div>

      <template v-else>
        <BarberReportsKPICards
          :performanceMetrics="performanceMetrics"
        />

        <div class="grid gap-6 lg:grid-cols-1">
          <BarberReportsTopServices
            :topServices="topServices"
          />
        </div>

        <ReportsStatusBreakdown
          :statusBreakdown="statusBreakdown"
        />
      </template>
    </section>

    <!-- PDF Report Preview Modal -->
    <BarberReportsPDFPreview
      :show="showReportPreview"
      :performanceMetrics="performanceMetrics"
      :topServices="topServices"
      :statusBreakdown="statusBreakdown"
      :activeRangeLabel="activeRangeLabel"
      :selectedRange="selectedRange"
      @close="showReportPreview = false"
    />
  </BarberLayout>
</template>

<script setup>
import { onMounted } from 'vue';
import BarberLayout from '@/portals/barber/layouts/BarberLayout.vue';
import ReportsHeader from '../components/ReportsHeader.vue';
import BarberReportsKPICards from '../components/BarberReportsKPICards.vue';
import BarberReportsTopServices from '../components/BarberReportsTopServices.vue';
import ReportsStatusBreakdown from '../components/ReportsStatusBreakdown.vue';
import BarberReportsPDFPreview from '../components/BarberReportsPDFPreview.vue';

import { useBarberAnalytics } from '../composables/useBarberAnalytics';

const {
  loading,
  showReportPreview,
  selectedRange,
  activeRangeLabel,
  performanceMetrics,
  topServices,
  statusBreakdown,
  setRange,
  fetchAnalytics
} = useBarberAnalytics();

onMounted(() => {
  fetchAnalytics();
});
</script>
