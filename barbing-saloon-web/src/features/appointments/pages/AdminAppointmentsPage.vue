<template>
  <AdminLayout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <!-- Loading Skeleton Preloader -->
      <AppointmentsSkeleton v-if="loading" />

      <!-- Interactive Content -->
      <div v-else class="space-y-6 animate-fade-in pb-10">
        <!-- 1. Luxury Control Header -->
        <AppointmentsHeader
          v-model="search"
          :is-refreshing="isRefreshing"
          @refresh="loadAppointments(true)"
          @open-settings="showSettingsModal = true"
        />

        <!-- 2. High-Level KPI Metric Cards -->
        <AppointmentsStats :appointments="appointments" />

        <!-- 3. Filter Tabs & Scope Selectors -->
        <AppointmentsFilters
          :tabs="tabs"
          v-model:currentFilter="currentFilter"
          v-model:dateFilter="dateFilter"
          v-model:sourceFilter="sourceFilter"
          v-model:sortOrder="sortOrder"
        />

        <!-- 4. Content Area: Mobile Cards (< md) & Desktop Table (>= md) -->
        <div>
          <!-- Mobile Cards -->
          <AppointmentsMobileCards
            :appointments="filteredAppointments"
            @details="openDetails"
            @approve="approve"
            @cancel="cancel"
            @view-receipt="viewReceipt"
          />

          <!-- Desktop Table -->
          <AppointmentsTable 
            :appointments="filteredAppointments"
            :search="search"
            :currentFilter="currentFilter"
            @details="openDetails"
            @approve="approve"
            @force-approve="forceApprove"
            @cancel="cancel"
            @view-receipt="viewReceipt"
            @reset-filters="resetFilters"
          />
        </div>
      </div>
    </div>

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
      :portal="'admin'"
      @close="selectedBooking = null" 
      @approve="handleReceiptApproved" 
      @reject="handleReceiptRejected"
    />

    <PaymentSettingsModal 
      :show="showSettingsModal"
      :loading="savingSettings"
      :initial-data="settingsForm"
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

// UI Components
import AppointmentsHeader from '@/features/appointments/components/AppointmentsHeader.vue';
import AppointmentsStats from '@/features/appointments/components/AppointmentsStats.vue';
import AppointmentsSkeleton from '@/features/appointments/components/AppointmentsSkeleton.vue';
import AppointmentsFilters from '@/features/appointments/components/AppointmentsFilters.vue';
import AppointmentsTable from '@/features/appointments/components/AppointmentsTable.vue';
import AppointmentsMobileCards from '@/features/appointments/components/AppointmentsMobileCards.vue';
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
const dateFilter = ref('all');
const sourceFilter = ref('all');
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
    { label: 'Walk-In', value: 'walk_in', count: list.filter(a => a.booking_type === 'walk_in' || a.source === 'walk_in').length },
    { label: 'Online', value: 'online', count: list.filter(a => a.booking_type !== 'walk_in' && a.source !== 'walk_in').length }
  ];
});

const filteredAppointments = computed(() => {
  const list = appointments.value || [];
  if (!Array.isArray(list)) return [];
  
  let result = list;
  
  // Status filter
  if (currentFilter.value !== 'all') {
    if (currentFilter.value === 'walk_in') {
      result = result.filter(a => a.booking_type === 'walk_in' || a.source === 'walk_in');
    } else if (currentFilter.value === 'online') {
      result = result.filter(a => a.booking_type !== 'walk_in' && a.source !== 'walk_in');
    } else {
      result = result.filter(a => a.status === currentFilter.value);
    }
  }

  // Source filter
  if (sourceFilter.value !== 'all') {
    result = result.filter(a => {
      const src = a.source || (a.booking_type === 'walk_in' ? 'walk_in' : 'web');
      return src === sourceFilter.value;
    });
  }

  // Date filter
  if (dateFilter.value !== 'all') {
    const today = new Date();
    const todayStr = today.toISOString().split('T')[0];

    if (dateFilter.value === 'today') {
      result = result.filter(a => a.appointment_date === todayStr);
    } else if (dateFilter.value === 'tomorrow') {
      const tomorrow = new Date(today);
      tomorrow.setDate(tomorrow.getDate() + 1);
      const tomorrowStr = tomorrow.toISOString().split('T')[0];
      result = result.filter(a => a.appointment_date === tomorrowStr);
    } else if (dateFilter.value === 'week') {
      const endOfWeek = new Date(today);
      endOfWeek.setDate(endOfWeek.getDate() + 7);
      const endOfWeekStr = endOfWeek.toISOString().split('T')[0];
      result = result.filter(a => a.appointment_date >= todayStr && a.appointment_date <= endOfWeekStr);
    }
  }
  
  // Search query
  if (search.value) {
    const q = search.value.toLowerCase().trim();
    result = result.filter(a => {
      const name = a.customer?.name || a.client_name || '';
      const phone = a.customer?.phone || a.client_phone || '';
      const refCode = a.booking_reference || `cc-${a.id}`;
      const service = a.service?.name || '';
      const barber = a.barber?.name || '';
      return name.toLowerCase().includes(q) || 
             phone.includes(q) || 
             refCode.toLowerCase().includes(q) ||
             service.toLowerCase().includes(q) ||
             barber.toLowerCase().includes(q);
    });
  }
  
  // Sort order
  result = [...result].sort((a, b) => {
    if (sortOrder.value === 'newest_created') {
      return (b.id || 0) - (a.id || 0);
    } else if (sortOrder.value === 'oldest_created') {
      return (a.id || 0) - (b.id || 0);
    } else if (sortOrder.value === 'upcoming_date') {
      const dateA = new Date((a.appointment_date || '') + 'T' + (a.appointment_time || '00:00'));
      const dateB = new Date((b.appointment_date || '') + 'T' + (b.appointment_time || '00:00'));
      return dateA - dateB;
    } else if (sortOrder.value === 'past_date') {
      const dateA = new Date((a.appointment_date || '') + 'T' + (a.appointment_time || '00:00'));
      const dateB = new Date((b.appointment_date || '') + 'T' + (b.appointment_time || '00:00'));
      return dateB - dateA;
    }
    return 0;
  });
  
  return result;
});

function resetFilters() {
  search.value = '';
  currentFilter.value = 'all';
  dateFilter.value = 'all';
  sourceFilter.value = 'all';
}

function viewReceipt(booking) {
  selectedBooking.value = booking;
}

async function handleReceiptApproved() {
  selectedBooking.value = null;
  await loadAppointments(true);
  toast.success('Payment approved and booking confirmed!');
}

async function handleReceiptRejected() {
  selectedBooking.value = null;
  await loadAppointments(true);
  toast.error('Payment rejected and booking cancelled');
}

async function handleApproveFromDetails(id) {
  await approve(id);
  detailsModal.value = null;
}

async function handleCancelFromDetails(id) {
  await cancel(id);
  detailsModal.value = null;
}

function handleViewReceiptFromDetails(booking) {
  viewReceipt(booking);
  detailsModal.value = null;
}

let refreshInterval = null;

onMounted(() => {
  loadAppointments();
  loadSettings();
  
  // Auto-refresh silent polling every 30s
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
  animation: fadeIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(8px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>