<template>
  <AdminLayout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <!-- Loading Skeleton Preloader -->
      <BarbersSkeleton v-if="loading" />

      <!-- Interactive Content -->
      <div v-else class="space-y-6 animate-fade-in pb-10">
        <!-- 1. Luxury Control Header -->
        <BarbersHeader
          v-model="search"
          :is-refreshing="isRefreshing"
          @refresh="loadBarbers(true)"
        />

        <!-- 2. High-Level KPI Metric Cards -->
        <BarbersStats :barbers="barbers" />

        <!-- 3. Filter Tabs & Specialty Selectors -->
        <BarbersFilters
          :tabs="tabs"
          v-model:currentFilter="currentFilter"
          v-model:specialtyFilter="specialtyFilter"
          v-model:sortOrder="sortOrder"
          :specialties="specialtiesList"
          :total-barbers="barbers.length"
        />

        <!-- 4. Stylist Roster Grid -->
        <BarbersGrid
          :barbers="filteredBarbers"
          :search="search"
          :current-filter="currentFilter"
          :specialty-filter="specialtyFilter"
          @delete="deleteBarber"
          @status-change="changeStatus"
          @reset-filters="resetFilters"
        />
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { onMounted } from 'vue';
import AdminLayout from '@/portals/admin/layouts/AdminLayout.vue';
import BarbersHeader from '@/features/barbers/components/BarbersHeader.vue';
import BarbersStats from '@/features/barbers/components/BarbersStats.vue';
import BarbersSkeleton from '@/features/barbers/components/BarbersSkeleton.vue';
import BarbersFilters from '@/features/barbers/components/BarbersFilters.vue';
import BarbersGrid from '@/features/barbers/components/BarbersGrid.vue';
import { useBarbers } from '@/features/barbers/composables/useBarbers';

const {
  barbers,
  loading,
  isRefreshing,
  search,
  currentFilter,
  specialtyFilter,
  sortOrder,
  specialtiesList,
  filteredBarbers,
  tabs,
  resetFilters,
  loadBarbers,
  deleteBarber,
  changeStatus
} = useBarbers();

onMounted(() => {
  loadBarbers();
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