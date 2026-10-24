<template>
  <AdminLayout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <!-- Loading Skeleton Preloader -->
      <CustomersSkeleton v-if="loading" />

      <!-- Interactive Content -->
      <div v-else class="space-y-6 animate-fade-in pb-10">
        <!-- 1. Luxury Control Header -->
        <CustomersHeader
          v-model="search"
          :is-refreshing="isRefreshing"
          @refresh="loadCustomers(true)"
        />

        <!-- 2. High-Level KPI Metric Cards -->
        <CustomersStats :customers="customers" />

        <!-- 3. Filter Tabs & Controls -->
        <CustomersFilters
          :tabs="tabs"
          v-model:currentFilter="currentFilter"
          v-model:channelFilter="channelFilter"
          v-model:sortOrder="sortOrder"
          :total-customers="customers.length"
        />

        <!-- 4. Content Area: Mobile Cards (< md) & Desktop Table (>= md) -->
        <div>
          <!-- Mobile Cards -->
          <CustomersMobileCards
            :customers="filteredCustomers"
            @view-profile="viewProfile"
          />

          <!-- Desktop Table -->
          <CustomersTable
            :customers="filteredCustomers"
            :search="search"
            :current-filter="currentFilter"
            @view-profile="viewProfile"
            @reset-filters="resetFilters"
          />
        </div>
      </div>
    </div>

    <!-- Customer Profile Slide-Out Drawer -->
    <CustomerProfilePanel
      :profile-panel="profilePanel"
      :profile-loading="profileLoading"
      :profile-data="profileData"
      @close="closeProfile"
    />
  </AdminLayout>
</template>

<script setup>
import { onMounted } from 'vue';
import AdminLayout from '@/portals/admin/layouts/AdminLayout.vue';
import CustomersHeader from '../components/CustomersHeader.vue';
import CustomersStats from '../components/CustomersStats.vue';
import CustomersSkeleton from '../components/CustomersSkeleton.vue';
import CustomersFilters from '../components/CustomersFilters.vue';
import CustomersTable from '../components/CustomersTable.vue';
import CustomersMobileCards from '../components/CustomersMobileCards.vue';
import CustomerProfilePanel from '../components/CustomerProfilePanel.vue';
import { useCustomers } from '../composables/useCustomers';

const {
  customers,
  loading,
  isRefreshing,
  search,
  currentFilter,
  channelFilter,
  sortOrder,
  filteredCustomers,
  tabs,
  resetFilters,
  loadCustomers,
  profilePanel,
  profileLoading,
  profileData,
  viewProfile,
  closeProfile
} = useCustomers();

onMounted(() => {
  loadCustomers();
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