<template>
  <div class="report-dossier-document bg-white text-gray-900 border border-gray-200/90 shadow-2xl rounded-2xl p-5 sm:p-7 md:p-8 max-w-5xl mx-auto my-2 relative overflow-hidden transition-all">
    <!-- Top Luxury Color Brand Ribbon -->
    <div class="h-1.5 w-full bg-gradient-to-r from-[#FF6700] via-amber-500 to-emerald-500 absolute top-0 left-0"></div>

    <!-- Letterhead & Document Info -->
    <DossierHeader
      :active-range-label="activeRangeLabel"
      :report-id="reportId"
      :generated-at="generatedAt"
    />

    <!-- Executive Financial & Scale KPI Strip (5 columns + Secondary Health Diagnostics) -->
    <DossierKPIStrip
      :business-stats="businessStats"
      :platform-stats="platformStats"
    />

    <!-- Chronological Daily Financial & Appointment Ledger Table -->
    <DossierLedgerTable
      :daily-ledger="dailyLedger"
    />

    <!-- Dual Audit: Staff Productivity and Service Catalogue Side-by-Side -->
    <DossierPerformanceAudit
      :top-barbers="topBarbers"
      :top-services="topServices"
      :total-revenue="businessStats.total_revenue"
    />

    <!-- Booking Lifecycle & Retention Distribution -->
    <DossierLifecycleAudit
      :status-breakdown="statusBreakdown"
    />

    <!-- Formal Audit Certification, Facility Metadata & Signature Seal -->
    <DossierCertificationSeal
      :report-id="reportId"
      :generated-at="generatedAt"
    />

    <!-- Discreet Document Footer Stamp (Screen & Print) -->
    <div class="mt-4 pt-2 border-t border-gray-100 flex items-center justify-between text-[9px] font-mono text-gray-400">
      <span>CandyCutz Flagship Operations Dossier • Confidential</span>
      <span>Page 1 of 1 • ISO/IEC Reconciled</span>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import DossierHeader from './dossier/DossierHeader.vue';
import DossierKPIStrip from './dossier/DossierKPIStrip.vue';
import DossierLedgerTable from './dossier/DossierLedgerTable.vue';
import DossierPerformanceAudit from './dossier/DossierPerformanceAudit.vue';
import DossierLifecycleAudit from './dossier/DossierLifecycleAudit.vue';
import DossierCertificationSeal from './dossier/DossierCertificationSeal.vue';

defineProps({
  activeRangeLabel: { type: String, default: '7 Days' },
  businessStats: { type: Object, default: () => ({}) },
  platformStats: { type: Object, default: () => ({}) },
  dailyLedger: { type: Array, default: () => [] },
  topBarbers: { type: Array, default: () => [] },
  topServices: { type: Array, default: () => [] },
  statusBreakdown: { type: Array, default: () => [] }
});

const reportId = computed(() => {
  const d = new Date();
  const dateStr = d.toISOString().slice(0, 10).replace(/-/g, '');
  const rand = Math.random().toString(36).substring(2, 6).toUpperCase();
  return `REF-CCZ-${dateStr}-${rand}`;
});

const generatedAt = computed(() => {
  return new Date().toLocaleDateString('en-GB', {
    weekday: 'short',
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
});
</script>

<style scoped>
.report-dossier-document {
  box-sizing: border-box;
}

@media print {
  .report-dossier-document {
    border: none !important;
    box-shadow: none !important;
    padding: 0 !important;
    margin: 0 !important;
    max-width: 100% !important;
    width: 100% !important;
  }
}
</style>
