<template>
  <AdminLayout>
    <section class="space-y-6 animate-fade-in pb-10">
      <!-- Premium Header Banner -->
      <AppointmentsHeader
        v-model="search"
        :isRefreshing="isRefreshing"
        @refresh="loadAppointments(true)"
        @open-settings="showSettingsModal = true"
      />

      <!-- Filters & Stats -->
      <AppointmentsFilters
        :tabs="tabs"
        v-model:currentFilter="currentFilter"
        v-model:sortOrder="sortOrder"
      />

      <!-- Main Content Area: Appointments Table -->
      <AppointmentsTable 
        :appointments="filteredAppointments"
        :loading="loading"
        :search="search"
        :currentFilter="currentFilter"
        @details="openDetails"
        @approve="approve"
        @force-approve="forceApprove"
        @cancel="cancel"
        @view-receipt="viewReceipt"
        @reset-filters="resetFilters"
      />
    </section>

    <!-- Modals -->
    <AppointmentDetailsModal 
      v-if="detailsModal"
      :booking="detailsModal"
      @close="detailsModal = null"
      @approve="handleApproveFromDetails"
      @cancel="handleCancelFromDetails"
      @view-receipt="handleViewReceiptFromDetails"
    />

    <ReceiptViewer 
      v-if="selectedBooking" 
      :booking="selectedBooking" 
      @close="selectedBooking = null" 
      @approve="handleReceiptApproved" 
      @reject="handleReceiptRejected"
      portal="admin"
    />

    <PaymentSettingsModal 
      :show="showSettingsModal"
      :loading="savingSettings"
      :initialData="settingsForm"
      @close="showSettingsModal = false"
      @save="saveSettings"
    />
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import AdminLayout from '@/portals/admin/layouts/AdminLayout.vue';
import { useToast } from '@/core/composables/useToast';
import ReceiptViewer from '@/core/components/ReceiptViewer.vue';

// Extracted UI Components
import AppointmentsHeader from '@/features/appointments/components/AppointmentsHeader.vue';
import AppointmentsFilters from '@/features/appointments/components/AppointmentsFilters.vue';
import AppointmentsTable from '@/features/appointments/components/AppointmentsTable.vue';
import AppointmentDetailsModal from '@/features/appointments/components/AppointmentDetailsModal.vue';
import PaymentSettingsModal from '@/features/appointments/components/PaymentSettingsModal.vue';

// Composables
import { useAppointments } from '@/features/appointments/composables/useAppointments';
import { usePaymentSettings } from '@/features/appointments/composables/usePaymentSettings';

const toast = useToast();

const {
  appointments,
  loading,
  isRefreshing,
  loadAppointments,
  approve,
  forceApprove,
  cancel
} = useAppointments();

const {
  showSettingsModal,
  savingSettings,
  settingsForm,
  loadSettings,
  saveSettings
} = usePaymentSettings();

const search = ref('');
const currentFilter = ref('all');
const sortOrder = ref('newest_created');
const selectedBooking = ref(null);
const detailsModal = ref(null);

function openDetails(appt) {
  detailsModal.value = appt;
}

const tabs = computed(() => {
  const list = appointments.value || [];
  return [
    { label: 'All', value: 'all', count: list.length },
    { label: 'Pending', value: 'pending', count: list.filter(a => a.status === 'pending').length },
    { label: 'Confirmed', value: 'confirmed', count: list.filter(a => a.status === 'confirmed').length },
    { label: 'Completed', value: 'completed', count: list.filter(a => a.status === 'completed').length },
    { label: 'Cancelled', value: 'cancelled', count: list.filter(a => a.status === 'cancelled').length },
    { label: '🟣 Walk-In', value: 'walk_in', count: list.filter(a => a.booking_type === 'walk_in').length },
    { label: '🔵 Online', value: 'online', count: list.filter(a => a.booking_type !== 'walk_in').length }
  ];
});

const filteredAppointments = computed(() => {
  const list = appointments.value || [];
  if (!Array.isArray(list)) return [];
  
  let result = list;
  
  // Apply tab filter
  if (currentFilter.value !== 'all') {
    if (currentFilter.value === 'walk_in') {
      result = result.filter(a => a.booking_type === 'walk_in');
    } else if (currentFilter.value === 'online') {
      result = result.filter(a => a.booking_type !== 'walk_in');
    } else {
      result = result.filter(a => a.status === currentFilter.value);
    }
  }
  
  // Apply search filter
  if (search.value) {
    const q = search.value.toLowerCase();
    result = result.filter(a => {
      const name = a.customer?.name || a.client_name || '';
      return name.toLowerCase().includes(q) || String(a.id).includes(q);
    });
  }
  
  // Apply sorting
  result = [...result].sort((a, b) => {
    if (sortOrder.value === 'newest_created') {
      return b.id - a.id;
    } else if (sortOrder.value === 'oldest_created') {
      return a.id - b.id;
    } else if (sortOrder.value === 'upcoming_date') {
      const dateA = new Date(a.appointment_date + 'T' + a.appointment_time);
      const dateB = new Date(b.appointment_date + 'T' + b.appointment_time);
      return dateA - dateB;
    } else if (sortOrder.value === 'past_date') {
      const dateA = new Date(a.appointment_date + 'T' + a.appointment_time);
      const dateB = new Date(b.appointment_date + 'T' + b.appointment_time);
      return dateB - dateA;
    }
    return 0;
  });
  
  return result;
});

function resetFilters() {
  search.value = '';
  currentFilter.value = 'all';
}

const viewReceipt = (booking) => {
  selectedBooking.value = booking;
};

const handleReceiptApproved = async () => {
  selectedBooking.value = null;
  await loadAppointments();
  toast.success('Payment approved and booking confirmed!');
};

const handleReceiptRejected = async () => {
  selectedBooking.value = null;
  await loadAppointments();
  toast.error('Payment rejected and booking cancelled');
};

const handleApproveFromDetails = async (id) => {
  await approve(id);
  detailsModal.value = null;
};

const handleCancelFromDetails = async (id) => {
  await cancel(id);
  detailsModal.value = null;
};

const handleViewReceiptFromDetails = (booking) => {
  viewReceipt(booking);
  detailsModal.value = null;
};

let refreshInterval;

onMounted(() => {
  loadAppointments();
  loadSettings();
  
  // Auto-reload every 30 seconds silently
  refreshInterval = setInterval(() => {
    loadAppointments(true);
  }, 30000);
});

onUnmounted(() => {
  if (refreshInterval) {
    clearInterval(refreshInterval);
  }
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
