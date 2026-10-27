<template>
  <AdminLayout>
    <div class="space-y-6 pb-12 animate-fade-in">
      <!-- Premium Verification Header -->
      <VerificationHeader
        v-model:quickCode="quickCode"
        :lastRefreshed="lastRefreshed"
        @quick-verify="quickVerify"
      />

      <!-- Content-Aware Skeleton Preloader -->
      <VerificationSkeleton v-if="loading && verifications.length === 0" />

      <template v-else>
        <!-- KPI Metrics Grid -->
        <VerificationStats :stats="stats" />

        <!-- Filters & Search Bar -->
        <VerificationFilters
          v-model:search="search"
          :currentFilter="currentFilter"
          :tabs="tabs"
          :pendingCount="stats.pending"
          @update:filter="setFilter"
          @update:search="onSearchUpdate"
        />

        <!-- Verifications Table / Registry List -->
        <VerificationList
          :verifications="verifications"
          :loading="loading"
          @verify="confirmVerify"
        />
      </template>
    </div>

    <!-- Confirmation Override Modal -->
    <VerificationConfirmModal
      :isOpen="isModalOpen"
      :item="selectedItem"
      :isVerifying="isVerifying"
      @close="closeModal"
      @confirm="onVerifyConfirm"
    />
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import AdminLayout from '@/portals/admin/layouts/AdminLayout.vue';

// Decomposed Modular Subcomponents
import VerificationHeader from '../components/VerificationHeader.vue';
import VerificationStats from '../components/VerificationStats.vue';
import VerificationFilters from '../components/VerificationFilters.vue';
import VerificationList from '../components/VerificationList.vue';
import VerificationSkeleton from '../components/VerificationSkeleton.vue';
import VerificationConfirmModal from '../components/VerificationConfirmModal.vue';

// Canonical /api/v1/admin/verifications Composable
import { useVerification } from '../composables/useVerification';

const {
  loading,
  isVerifying,
  verifications,
  stats,
  currentFilter,
  search,
  quickCode,
  lastRefreshed,
  loadData,
  handleSearch,
  setFilter,
  quickVerify,
  submitVerification
} = useVerification();

const isModalOpen = ref(false);
const selectedItem = ref(null);

const tabs = [
  { label: 'All Codes', value: 'all' },
  { label: 'Pending Check-in', value: 'confirmed' },
  { label: 'Verified', value: 'completed' },
  { label: 'Expired / Void', value: 'expired' }
];

function onSearchUpdate(val) {
  search.value = val;
  handleSearch();
}

function confirmVerify(item) {
  selectedItem.value = item;
  isModalOpen.value = true;
}

function closeModal() {
  isModalOpen.value = false;
  selectedItem.value = null;
}

async function onVerifyConfirm(id) {
  const success = await submitVerification(id);
  if (success) {
    closeModal();
  }
}

onMounted(loadData);
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
