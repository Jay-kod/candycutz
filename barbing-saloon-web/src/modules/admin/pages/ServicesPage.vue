<template>
  <AdminLayout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <!-- Loading Skeleton Preloader for Zero CLS -->
      <ServicesSkeleton v-if="loading" />

      <!-- Interactive Services Content -->
      <div v-else class="space-y-6 animate-fade-in pb-10">
        <!-- 1. Luxury Control Header & Live Clock -->
        <ServicesHeader
          v-model="search"
          :total-services="services.length"
          :is-refreshing="isRefreshing"
          @refresh="loadServices(true)"
        />

        <!-- 2. High-Level KPI Metric Cards -->
        <ServicesStats :stats="stats" />

        <!-- 3. Status Tabs, Categories & Sorting Filters -->
        <ServicesFilters
          v-model:approvalFilter="approvalFilter"
          v-model:activeCategory="activeCategory"
          v-model:sortOrder="sortOrder"
          :categories="categories"
          :total-services="services.length"
          :pending-count="pendingCount"
          :approved-count="approvedCount"
          :active-count="activeCount"
          :inactive-count="inactiveCount"
        />

        <!-- 4. Services Grid with Empty States & Actions -->
        <ServicesGrid
          :services="filteredServices"
          :total-services="services.length"
          :search="search"
          :approval-filter="approvalFilter"
          :active-category="activeCategory"
          :approving-id="approvingId"
          :toggling-id="togglingId"
          @approve="approveService"
          @reject="rejectService"
          @toggle-active="toggleServiceActive"
          @delete="deleteService"
          @reset-filters="resetFilters"
        />
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { onMounted } from 'vue';
import AdminLayout from '@/portals/admin/layouts/AdminLayout.vue';
import ServicesHeader from '@/features/services/components/ServicesHeader.vue';
import ServicesStats from '@/features/services/components/ServicesStats.vue';
import ServicesFilters from '@/features/services/components/ServicesFilters.vue';
import ServicesGrid from '@/features/services/components/ServicesGrid.vue';
import ServicesSkeleton from '@/features/services/components/ServicesSkeleton.vue';
import { useAdminServices } from '@/features/services/composables/useAdminServices';

const {
  services,
  loading,
  isRefreshing,
  search,
  activeCategory,
  approvalFilter,
  sortOrder,
  approvingId,
  togglingId,
  pendingCount,
  approvedCount,
  activeCount,
  inactiveCount,
  categories,
  stats,
  filteredServices,
  loadServices,
  approveService,
  rejectService,
  toggleServiceActive,
  deleteService,
  resetFilters
} = useAdminServices();

onMounted(() => {
  loadServices();
});
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(8px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
