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
          @add-customer="openCreateModal"
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
            :action-in-progress="actionInProgress"
            @view-profile="viewProfile"
            @edit-customer="openEditModal"
            @toggle-status="toggleStatus"
            @reset-password="openPasswordModal"
            @delete-customer="deleteCustomer"
          />

          <!-- Desktop Table -->
          <CustomersTable
            :customers="filteredCustomers"
            :search="search"
            :current-filter="currentFilter"
            :action-in-progress="actionInProgress"
            @view-profile="viewProfile"
            @edit-customer="openEditModal"
            @toggle-status="toggleStatus"
            @reset-password="openPasswordModal"
            @delete-customer="deleteCustomer"
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
      @edit-customer="openEditModal"
      @toggle-status="toggleStatus"
      @reset-password="openPasswordModal"
      @delete-customer="deleteCustomer"
    />

    <!-- Customer Create/Edit Modal -->
    <CustomerFormModal
      :is-open="isCustomerModalOpen"
      :mode="customerModalMode"
      :initial-data="customerModalData"
      :loading="customerModalLoading"
      @close="closeCustomerModal"
      @save="saveCustomer"
    />

    <!-- Reset Password Modal -->
    <CustomerPasswordModal
      :is-open="isPasswordModalOpen"
      :customer="passwordModalCustomer"
      :loading="passwordModalLoading"
      @close="closePasswordModal"
      @save="savePassword"
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
import CustomerFormModal from '../components/CustomerFormModal.vue';
import CustomerPasswordModal from '../components/CustomerPasswordModal.vue';
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
  closeProfile,
  isCustomerModalOpen,
  customerModalMode,
  customerModalData,
  customerModalLoading,
  openCreateModal,
  openEditModal,
  closeCustomerModal,
  saveCustomer,
  isPasswordModalOpen,
  passwordModalCustomer,
  passwordModalLoading,
  openPasswordModal,
  closePasswordModal,
  savePassword,
  toggleStatus,
  deleteCustomer,
  actionInProgress
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