<template>
  <AdminLayout>
    <div class="space-y-6 pb-12">
      <!-- 1. Header with Live Clock, Badges, Search & Actions -->
      <FeatureFlagsHeader
        v-model="search"
        :total-flags="stats.totalCount"
        :active-count="stats.activeCount"
        :is-refreshing="isRefreshing"
        @refresh="fetchFlags(true)"
        @create="openCreateModal"
      />

      <!-- 2. Loading Shimmer Skeleton or Main Content -->
      <FeatureFlagsSkeleton v-if="loading" />

      <template v-else>
        <!-- 3. High-Density KPI Metrics -->
        <FeatureFlagsStats :stats="stats" />

        <!-- 4. Filter Toolbar -->
        <FeatureFlagsFilters
          v-model:status-filter="statusFilter"
          v-model:sort-order="sortOrder"
          :stats="stats"
          :has-active-filters="hasActiveFilters"
          @reset="resetFilters"
        />

        <!-- 5. Interactive Flags List & Rich Zero States -->
        <FeatureFlagsList
          :flags="filteredFlags"
          :search-query="search"
          :status-filter="statusFilter"
          :toggling-id="togglingId"
          @toggle="toggleFlag"
          @edit="openEditModal"
          @delete="deleteFlag"
          @reset="resetFilters"
          @create="openCreateModal"
        />
      </template>

      <!-- 6. Create / Edit Feature Flag Modal Dialog -->
      <FeatureFlagModal
        v-model="modalOpen"
        :editing-flag="editingFlag"
        :form="form"
        :saving="saving"
        @save="saveFlag"
      />
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import AdminLayout from '@/portals/admin/layouts/AdminLayout.vue';
import { useAdminFeatureFlags } from '../composables/useAdminFeatureFlags';
import FeatureFlagsHeader from '../components/feature-flags/FeatureFlagsHeader.vue';
import FeatureFlagsStats from '../components/feature-flags/FeatureFlagsStats.vue';
import FeatureFlagsFilters from '../components/feature-flags/FeatureFlagsFilters.vue';
import FeatureFlagsList from '../components/feature-flags/FeatureFlagsList.vue';
import FeatureFlagModal from '../components/feature-flags/FeatureFlagModal.vue';
import FeatureFlagsSkeleton from '../components/feature-flags/FeatureFlagsSkeleton.vue';

const {
  loading,
  isRefreshing,
  saving,
  togglingId,
  search,
  statusFilter,
  sortOrder,
  modalOpen,
  editingFlag,
  form,
  stats,
  filteredFlags,
  fetchFlags,
  openCreateModal,
  openEditModal,
  saveFlag,
  toggleFlag,
  deleteFlag,
  resetFilters,
} = useAdminFeatureFlags();

const hasActiveFilters = computed(() => {
  return (
    search.value.trim().length > 0 ||
    statusFilter.value !== 'all' ||
    sortOrder.value !== 'newest'
  );
});

onMounted(() => {
  fetchFlags();
});
</script>
