<template>
  <AdminLayout>
    <div class="space-y-5 pb-12 animate-fade-in print:p-0 print:m-0 print:space-y-0">
      <!-- High-Visibility Primary Navigation Tabs (Executive Dossier vs Audit History) -->
      <ReportsNavigationTabs
        :active-tab="activeTab"
        :history-count="reportHistory.length"
        :last-refreshed="lastRefreshed"
        @update:active-tab="setTab"
      />

      <!-- Tab 1: Executive Dossier View -->
      <template v-if="activeTab === 'dossier'">
        <!-- Screen Toolbar: Range Controls & Export Actions (Hidden on Print & PDF) -->
        <ReportsDocumentToolbar
          :loading="loading"
          :selected-range="selectedRange"
          :range-options="rangeOptions"
          :is-generating-pdf="isGeneratingPdf"
          :zoom-level="zoomLevel"
          @update:selected-range="setRange"
          @update:zoom-level="zoomLevel = $event"
          @refresh="fetchAnalytics"
          @export-csv="exportCsv"
          @print="printReport"
          @download-pdf="downloadPdf"
          @copy-summary="copyExecutiveSummary"
          @open-history="setTab('history')"
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
            class="mt-4 inline-flex items-center gap-2 rounded-xl bg-rose-500/20 px-4 py-2 text-xs font-semibold text-rose-300 hover:bg-rose-500/30 transition-colors cursor-pointer"
          >
            Retry Connection
          </button>
        </div>

        <!-- WYSIWYG Executive Dossier Document (Exact Document Downloaded & Printed) -->
        <div v-else class="relative w-full overflow-x-auto print:overflow-visible flex justify-center">
          <div
            class="w-full transition-transform duration-200 print:transform-none"
            :style="zoomLevel !== 100 ? { transform: `scale(${zoomLevel / 100})`, transformOrigin: 'top center' } : {}"
          >
            <ReportsDossierDocument
              ref="dossierRef"
              :active-range-label="activeRangeLabel"
              :business-stats="businessStats"
              :platform-stats="platformStats"
              :daily-ledger="dailyLedger"
              :top-barbers="topBarbers"
              :top-services="topServices"
              :status-breakdown="statusBreakdown"
            />
          </div>
        </div>
      </template>

      <!-- Tab 2: Export & Audit History View -->
      <template v-else>
        <ReportsHistoryTab
          :history-list="reportHistory"
          :loading="historyLoading"
          @refresh="fetchReportHistory"
          @switch-to-dossier="setTab('dossier')"
        />
      </template>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import AdminLayout from '@/portals/admin/layouts/AdminLayout.vue';
import ReportsNavigationTabs from '../components/ReportsNavigationTabs.vue';
import ReportsSkeleton from '../components/ReportsSkeleton.vue';
import ReportsDocumentToolbar from '../components/ReportsDocumentToolbar.vue';
import ReportsDossierDocument from '../components/ReportsDossierDocument.vue';
import ReportsHistoryTab from '../components/ReportsHistoryTab.vue';

import { useAnalytics } from '../composables/useAnalytics';
import { useToast } from '@/core/composables/useToast';

const route = useRoute();
const router = useRouter();
const toast = useToast();

const initialTab = route?.query?.tab === 'history' ? 'history' : 'dossier';
const activeTab = ref(initialTab);
const dossierRef = ref(null);
const isGeneratingPdf = ref(false);
const zoomLevel = ref(100);

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
  dailyLedger,
  reportHistory,
  historyLoading,
  fetchAnalytics,
  fetchReportHistory,
  logReportEvent,
  setRange,
  exportCsv,
  printReport,
  copyExecutiveBriefing
} = useAnalytics();

const setTab = (tab) => {
  activeTab.value = tab;
  if (router && route) {
    router.replace({ query: { ...route.query, tab } }).catch(() => {});
  }
  if (tab === 'history') {
    fetchReportHistory();
  }
};

watch(
  () => route?.query?.tab,
  (newTab) => {
    if (newTab === 'history' || newTab === 'dossier') {
      activeTab.value = newTab;
    }
  }
);

const downloadPdf = async () => {
  const el = dossierRef.value?.$el || dossierRef.value;
  if (!el) return;

  isGeneratingPdf.value = true;
  try {
    const html2pdf = (await import('html2pdf.js')).default;
    const dateStr = new Date().toISOString().slice(0, 10);
    const reportId = `REF-CCZ-${dateStr.replace(/-/g, '')}-${Math.random().toString(36).substring(2, 6).toUpperCase()}`;
    const filename = `CandyCutz_Executive_Report_${selectedRange.value}_${dateStr}.pdf`;

    const opt = {
      margin: [6, 6, 6, 6],
      filename,
      image: { type: 'jpeg', quality: 0.98 },
      html2canvas: {
        scale: 2,
        useCORS: true,
        backgroundColor: '#ffffff',
        letterRendering: true,
        logging: false,
        scrollY: 0
      },
      jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
      pagebreak: { mode: ['avoid-all', 'css', 'legacy'] }
    };

    await html2pdf().set(opt).from(el).save();

    await logReportEvent({
      action: 'report_pdf_download',
      reportId,
      range: selectedRange.value,
      rangeLabel: activeRangeLabel.value,
      title: 'Executive Operational Report (PDF)',
      metadata: {
        filename,
        realized_revenue: businessStats.value.realized_revenue || businessStats.value.total_revenue || 0,
        appointments_count: businessStats.value.completed_count || businessStats.value.total_appointments || 0
      }
    });

    toast.success('Executive PDF downloaded and logged in audit history');
  } catch (err) {
    console.error('Executive PDF generation failed:', err);
    toast.error('Failed to generate PDF document');
  } finally {
    isGeneratingPdf.value = false;
  }
};

const copyExecutiveSummary = async () => {
  try {
    await copyExecutiveBriefing();
    toast.success('Executive briefing copied and saved to audit history');
  } catch {
    toast.info('Briefing copied to clipboard');
  }
};

onMounted(() => {
  fetchAnalytics();
  fetchReportHistory();
});
</script>

<style>
.animate-fade-in {
  animation: fadeIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(6px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@media print {
  aside,
  header,
  nav,
  .print\:hidden,
  button,
  [data-no-print] {
    display: none !important;
  }

  body, html, #app, .h-screen, main, #main-scroll-container {
    overflow: visible !important;
    height: auto !important;
    min-height: 0 !important;
    background: #ffffff !important;
    color: #111827 !important;
    padding: 0 !important;
    margin: 0 !important;
  }

  .report-dossier-document {
    box-shadow: none !important;
    border: none !important;
    max-width: 100% !important;
    width: 100% !important;
    padding: 0 !important;
    margin: 0 !important;
    background: #ffffff !important;
    color: #111827 !important;
  }

  .page-break-avoid {
    page-break-inside: avoid !important;
    break-inside: avoid !important;
  }

  @page {
    size: A4 portrait;
    margin: 10mm;
  }
}
</style>
