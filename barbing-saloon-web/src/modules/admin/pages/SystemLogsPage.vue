<template>
  <AdminLayout>
    <div class="space-y-6 pb-12 animate-fade-in">
      <!-- Top Control & Tab Header -->
      <SystemLogsHeader
        v-model:tab="activeTab"
        :gateStatus="gateMetrics?.status"
        :gateTotalRequests="gateMetrics?.total_requests"
        :auditLogsCount="filteredAuditLogs.length"
      />

      <!-- Preloader Skeleton -->
      <SystemLogsSkeleton v-if="gateMetricsLoading && gateLogsLoading && auditLoading" />

      <template v-else>
        <!-- TAB 1: API & DB Gate Telemetry -->
        <div v-if="activeTab === 'gate'" class="space-y-6">
          <!-- 4 Gate Metrics KPI Cards -->
          <GateMetricsKPIs :metrics="gateMetrics" />

          <!-- Diagnostics: Client Ingress & Top Hotspots -->
          <GateDiagnosticsSection
            v-model:hotspotTab="hotspotTab"
            :metrics="gateMetrics"
            :distributionPercent="clientDistributionPercent"
            :getMethodBadgeClass="getMethodBadgeClass"
          />

          <!-- Live Telemetry Stream Table -->
          <GateTelemetryTable
            v-model:clientFilter="gateClientFilter"
            v-model:isSlow="gateIsSlow"
            v-model:budgetExceeded="gateBudgetExceeded"
            v-model:search="gateSearch"
            :logs="gateLogs"
            :loading="gateLogsLoading"
            :pagination="gatePagination"
            :getMethodBadgeClass="getMethodBadgeClass"
            :getClientBadgeClass="getClientBadgeClass"
            :getStatusCodeClass="getStatusCodeClass"
            :formatDateTime="formatDateTime"
            @open-prune="showPruneModal = true"
            @refresh="fetchGateData"
            @page-change="fetchGateLogs"
          />
        </div>

        <!-- TAB 2: Administrative Security Audit Trail -->
        <div v-else class="space-y-6">
          <AuditLogsTable
            v-model:filterType="auditFilterType"
            v-model:search="auditSearch"
            :logs="filteredAuditLogs"
            :loading="auditLoading"
            :getIconForAction="getIconForAction"
            :getIconColorForAction="getIconColorForAction"
          />
        </div>
      </template>
    </div>

    <!-- Prune Telemetry Confirmation Modal -->
    <GatePruneModal
      v-model:days="pruneDays"
      :isOpen="showPruneModal"
      :isPruning="pruning"
      @close="showPruneModal = false"
      @confirm="handlePrune"
    />
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/portals/admin/layouts/AdminLayout.vue';

// Decomposed Modular Subcomponents
import SystemLogsHeader from '../components/system-logs/SystemLogsHeader.vue';
import GateMetricsKPIs from '../components/system-logs/GateMetricsKPIs.vue';
import GateDiagnosticsSection from '../components/system-logs/GateDiagnosticsSection.vue';
import GateTelemetryTable from '../components/system-logs/GateTelemetryTable.vue';
import AuditLogsTable from '../components/system-logs/AuditLogsTable.vue';
import GatePruneModal from '../components/system-logs/GatePruneModal.vue';
import SystemLogsSkeleton from '../components/system-logs/SystemLogsSkeleton.vue';

// Canonical Composable
import { useAdminSystemLogs } from '../composables/useAdminSystemLogs';

const {
  activeTab,
  filteredAuditLogs,
  auditLoading,
  auditSearch,
  auditFilterType,
  getIconForAction,
  getIconColorForAction,
  gateMetrics,
  gateMetricsLoading,
  gateLogs,
  gateLogsLoading,
  gatePagination,
  gateClientFilter,
  gateIsSlow,
  gateBudgetExceeded,
  gateSearch,
  hotspotTab,
  showPruneModal,
  pruneDays,
  pruning,
  fetchGateLogs,
  fetchGateData,
  handlePrune,
  getMethodBadgeClass,
  getClientBadgeClass,
  getStatusCodeClass,
  formatDateTime,
  clientDistributionPercent
} = useAdminSystemLogs();
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
