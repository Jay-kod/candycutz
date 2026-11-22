<script setup>
import AdminLayout from '@/portals/admin/layouts/AdminLayout.vue'
import { useAdminHealthChecker } from '../composables/useAdminHealthChecker'
import HealthHeader from '../components/health-checker/HealthHeader.vue'
import HealthKPICards from '../components/health-checker/HealthKPICards.vue'
import HealthIncidentVault from '../components/health-checker/HealthIncidentVault.vue'
import HealthComponentCards from '../components/health-checker/HealthComponentCards.vue'
import HealthErrorGroups from '../components/health-checker/HealthErrorGroups.vue'
import HealthSelfTestPanel from '../components/health-checker/HealthSelfTestPanel.vue'
import HealthCheckerSkeleton from '../components/health-checker/HealthCheckerSkeleton.vue'

const {
  overview,
  healthDetail,
  failedJobs,
  failedJobsLoading,
  retryingJobId,
  dismissingJobId,
  overviewLoading,
  detailLoading,
  selfTestRunning,
  selfTestResults,
  autoRefresh,
  lastRefreshed,
  refreshAll,
  runDiagnostic,
  toggleAutoRefresh,
  statusColor,
  statusBg,
  statusIcon,
  formatTime,
  overviewCards,
  healthCards,
  retryJob,
  dismissJob,
  resetBreaker,
} = useAdminHealthChecker()
</script>

<template>
  <AdminLayout>
    <div class="space-y-8 animate-fade-in max-w-7xl mx-auto pb-12">
      <!-- Executive Header & Real-Time Status -->
      <HealthHeader
        :overview="overview"
        :overview-loading="overviewLoading"
        :detail-loading="detailLoading"
        :self-test-running="selfTestRunning"
        :auto-refresh="autoRefresh"
        :last-refreshed="lastRefreshed"
        :status-color="statusColor"
        :status-bg="statusBg"
        :status-icon="statusIcon"
        :format-time="formatTime"
        @refresh="refreshAll"
        @toggle-auto-refresh="toggleAutoRefresh"
      />

      <!-- Zero-CLS Skeleton on Initial Load -->
      <HealthCheckerSkeleton v-if="overviewLoading && !overview" />

      <!-- Active Telemetry & Diagnostics Dashboard -->
      <template v-else>
        <!-- 8 Executive KPI Cards -->
        <HealthKPICards
          :cards="overviewCards"
          :loading="overviewLoading"
          :status-color="statusColor"
          :status-bg="statusBg"
          :status-icon="statusIcon"
        />

        <!-- Dead-Letter Incident Vault & Circuit Breaker Remediation -->
        <HealthIncidentVault
          :failed-jobs="failedJobs"
          :circuit-breakers="healthDetail?.checks?.circuit_breakers || {}"
          :loading="failedJobsLoading"
          :retrying-job-id="retryingJobId"
          :dismissing-job-id="dismissingJobId"
          @retry-job="retryJob"
          @dismiss-job="dismissJob"
          @reset-breaker="resetBreaker"
        />

        <!-- Top Active Error Signatures (if any) -->
        <HealthErrorGroups
          :error-groups="overview?.top_errors || []"
        />

        <!-- Deep Subsystem Telemetry Components -->
        <HealthComponentCards
          :cards="healthCards"
          :loading="detailLoading"
          :status-color="statusColor"
          :status-bg="statusBg"
        />

        <!-- Interactive Self-Diagnostics Suite -->
        <HealthSelfTestPanel
          :self-test-running="selfTestRunning"
          :self-test-results="selfTestResults"
          @run-diagnostic="runDiagnostic"
        />
      </template>
    </div>
  </AdminLayout>
</template>
