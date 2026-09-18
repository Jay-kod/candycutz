<template>
  <AdminLayout>
    <section class="space-y-6 animate-fade-in pb-10">
      <CustomersHeader
        v-model="search"
      />

      <CustomersList
        :loading="loading"
        :filteredCustomers="filteredCustomers"
        :tabs="tabs"
        :currentFilter="currentFilter"
        :totalCustomers="customers.length"
        :search="search"
        @update:currentFilter="currentFilter = $event"
        @viewProfile="viewProfile"
        @resetFilters="resetFilters"
      />
    </section>

    <!-- Customer Profile Slide-Out Panel -->
    <CustomerProfilePanel
      :profilePanel="profilePanel"
      :profileLoading="profileLoading"
      :profileData="profileData"
      @close="closeProfile"
    />
  </AdminLayout>
</template>

<script setup>
import { onMounted } from 'vue';
import AdminLayout from '@/portals/Admin/layouts/Adminlayout.vue';
import CustomersHeader from '../components/CustomersHeader.vue';
import CustomersList from '../components/CustomersList.vue';
import CustomerProfilePanel from '../components/CustomerProfilePanel.vue';
import { useCustomers } from '../composables/useCustomers';

const {
  customers,
  loading,
  search,
  currentFilter,
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
  animation: fadeIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(12px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
