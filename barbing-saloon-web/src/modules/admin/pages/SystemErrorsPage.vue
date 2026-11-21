<script setup>
import AdminLayout from '@/portals/admin/layouts/AdminLayout.vue'
import { useAdminSystemErrors } from '../composables/useAdminSystemErrors'
import SystemErrorsHeader from '../components/system-errors/SystemErrorsHeader.vue'
import SystemErrorsTraceBar from '../components/system-errors/SystemErrorsTraceBar.vue'
import SystemErrorsFilters from '../components/system-errors/SystemErrorsFilters.vue'
import SystemErrorsList from '../components/system-errors/SystemErrorsList.vue'
import SystemErrorDrawer from '../components/system-errors/SystemErrorDrawer.vue'
import SystemErrorsSkeleton from '../components/system-errors/SystemErrorsSkeleton.vue'

const {
  errors,
  loading,
  totalPages,
  currentPage,
  totalCount,
  search,
  statusFilter,
  severityFilter,
  categoryFilter,
  drawerOpen,
  drawerLoading,
  selectedGroup,
  selectedEvents,
  statusUpdating,
  traceId,
  traceLoading,
  traceResults,
  showTracePanel,
  stats,
  fetchErrors,
  openErrorDetail,
  closeDrawer,
  updateStatus,
  executeTrace,
  closeTrace,
  severityColor,
  statusColor,
  formatTime,
  timeAgo,
  goToPage,
  clearFilters,
} = useAdminSystemErrors()
</script>

<template>
  <AdminLayout>
    <div class="space-y-6 animate-fade-in max-w-7xl mx-auto pb-12">
      <!-- Executive Header & Real-time Stats -->
      <SystemErrorsHeader
        :loading="loading"
        :stats="stats"
        @refresh="fetchErrors"
      />

      <!-- Distributed Request Trace Panel -->
      <SystemErrorsTraceBar
        v-model:trace-id="traceId"
        :trace-loading="traceLoading"
        :trace-results="traceResults"
        :show-trace-panel="showTracePanel"
        :severity-color="severityColor"
        :format-time="formatTime"
        @execute-trace="executeTrace"
        @close-trace="closeTrace"
      />

      <!-- Search & Classification Filters -->
      <SystemErrorsFilters
        v-model:search="search"
        v-model:status-filter="statusFilter"
        v-model:severity-filter="severityFilter"
        v-model:category-filter="categoryFilter"
        @clear-filters="clearFilters"
      />

      <!-- Preloading Shimmer Skeleton on Initial Load -->
      <SystemErrorsSkeleton v-if="loading && !errors.length" />

      <!-- Paginated Error Incident Groups List -->
      <SystemErrorsList
        v-else
        :errors="errors"
        :loading="loading"
        :current-page="currentPage"
        :total-pages="totalPages"
        :total-count="totalCount"
        :severity-color="severityColor"
        :status-color="statusColor"
        :time-ago="timeAgo"
        @select-error="openErrorDetail"
        @go-to-page="goToPage"
      />

      <!-- Incident Deep-Dive Slide-Over Drawer -->
      <SystemErrorDrawer
        :drawer-open="drawerOpen"
        :drawer-loading="drawerLoading"
        :selected-group="selectedGroup"
        :selected-events="selectedEvents"
        :status-updating="statusUpdating"
        :severity-color="severityColor"
        :status-color="statusColor"
        :format-time="formatTime"
        @close-drawer="closeDrawer"
        @update-status="updateStatus"
      />
    </div>
  </AdminLayout>
</template>
