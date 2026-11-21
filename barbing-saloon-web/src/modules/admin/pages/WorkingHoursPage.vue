<template>
  <AdminLayout>
    <div class="space-y-6 pb-12">
      <!-- 1. Header with Live Digital Clock, Badges, Search & Actions -->
      <WorkingHoursHeader
        v-model="search"
        :total-barbers="stats.totalBarbers"
        :is-refreshing="isRefreshing"
        @refresh="fetchWorkingHours(true)"
      />

      <!-- 2. Loading Shimmer Skeleton or Main Content -->
      <WorkingHoursSkeleton v-if="loading" />

      <template v-else>
        <!-- 3. High-Density KPI Schedule Metrics -->
        <WorkingHoursStats :stats="stats" />

        <!-- 4. Day & Availability Filter Toolbar -->
        <WorkingHoursFilters
          v-model:day-filter="dayFilter"
          v-model:status-filter="statusFilter"
          :today-index="todayIndex"
          :today-name="todayName"
          :has-active-filters="hasActiveFilters"
          @reset="resetFilters"
        />

        <!-- 5. Interactive Barber Schedules List -->
        <WorkingHoursList
          :barbers="filteredBarbers"
          :search-query="search"
          :day-filter="dayFilter"
          :status-filter="statusFilter"
          :editing-barber-id="editingBarberId"
          :saving-barber-id="savingBarberId"
          :today-index="todayIndex"
          @start-edit="startEdit"
          @cancel-edit="cancelEdit"
          @save="saveBarberHours"
          @apply-defaults="applyDefaults"
          @reset="resetFilters"
        />
      </template>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import AdminLayout from '@/portals/admin/layouts/AdminLayout.vue';
import { useAdminWorkingHours } from '../composables/useAdminWorkingHours';
import WorkingHoursHeader from '../components/working-hours/WorkingHoursHeader.vue';
import WorkingHoursStats from '../components/working-hours/WorkingHoursStats.vue';
import WorkingHoursFilters from '../components/working-hours/WorkingHoursFilters.vue';
import WorkingHoursList from '../components/working-hours/WorkingHoursList.vue';
import WorkingHoursSkeleton from '../components/working-hours/WorkingHoursSkeleton.vue';

const {
  loading,
  isRefreshing,
  editingBarberId,
  savingBarberId,
  search,
  dayFilter,
  statusFilter,
  stats,
  filteredBarbers,
  todayIndex,
  todayName,
  fetchWorkingHours,
  startEdit,
  cancelEdit,
  applyDefaults,
  saveBarberHours,
  resetFilters,
} = useAdminWorkingHours();

const hasActiveFilters = computed(() => {
  return (
    search.value.trim().length > 0 ||
    dayFilter.value !== 'all' ||
    statusFilter.value !== 'all'
  );
});

onMounted(() => {
  fetchWorkingHours();
});
</script>
