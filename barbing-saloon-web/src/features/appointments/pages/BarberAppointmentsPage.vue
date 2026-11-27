<template>
  <BarberLayout>
    <section class="space-y-6 animate-fade-in pb-10">
      <!-- Header -->
      <BarberAppointmentsHeader
        v-model="search"
        :loading="loading"
        @refresh="loadAppointments"
      />

      <!-- Filters -->
      <BarberAppointmentsFilters
        :tabs="tabs"
        v-model:currentFilter="currentFilter"
      />

      <!-- Appointments Grid -->
      <BarberAppointmentsGrid
        :appointments="filteredAppointments"
        @viewReceipt="viewReceipt"
        @approve="approve"
        @cancel="cancel"
        @promptComplete="promptComplete"
        @markNoShow="markNoShow"
        @forfeitDeposit="forfeitDeposit"
      />
    </section>

    <!-- Receipt Viewer Modal -->
    <ReceiptViewer 
      v-if="selectedBooking" 
      :booking="selectedBooking" 
      @close="selectedBooking = null" 
      @approve="handleReceiptApproved" 
      @reject="handleReceiptRejected"
      portal="barber"
    />

    <!-- Verification Code Modal -->
    <ServiceVerificationModal
      :open="Boolean(completingBooking)"
      :booking="completingBooking"
      :is-verifying="isVerifying"
      :error-message="verificationError"
      @close="closeCompleteModal"
      @submit="submitComplete"
      @clear-error="verificationError = ''"
    />
  </BarberLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import BarberLayout from '@/portals/barber/layouts/BarberLayout.vue';
import { useToast } from '@/core/composables/useToast';
import ReceiptViewer from '@/core/components/ReceiptViewer.vue';

// Subcomponents
import BarberAppointmentsHeader from '@/features/appointments/components/BarberAppointmentsHeader.vue';
import BarberAppointmentsFilters from '@/features/appointments/components/BarberAppointmentsFilters.vue';
import BarberAppointmentsGrid from '@/features/appointments/components/BarberAppointmentsGrid.vue';
import ServiceVerificationModal from '@/features/appointments/components/ServiceVerificationModal.vue';

// Composables & Utils
import { useBarberAppointments } from '@/features/appointments/composables/useBarberAppointments';
import { extractVerificationError } from '@/features/appointments/utils/verificationCode';

const toast = useToast();

const {
  appointments,
  loading,
  loadAppointments,
  approve,
  cancel,
  complete,
  markNoShow,
  forfeitDeposit,
} = useBarberAppointments();

const search = ref('');
const currentFilter = ref('all');
const selectedBooking = ref(null);

const completingBooking = ref(null);
const verificationError = ref('');
const isVerifying = ref(false);

const tabs = computed(() => {
  const list = appointments.value.data || appointments.value || [];
  if (!Array.isArray(list)) return [];
  return [
    { label: 'All', value: 'all', count: list.length },
    { label: 'Pending', value: 'pending', count: list.filter(a => a.status === 'pending').length },
    { label: 'Confirmed', value: 'confirmed', count: list.filter(a => a.status === 'confirmed').length },
    { label: 'Completed', value: 'completed', count: list.filter(a => a.status === 'completed').length },
    { label: 'No-Show', value: 'no_show', count: list.filter(a => a.status === 'no_show').length },
    { label: 'Cancelled', value: 'cancelled', count: list.filter(a => a.status === 'cancelled').length },
    { label: '🟣 Walk-In', value: 'walk_in', count: list.filter(a => a.booking_type === 'walk_in').length },
    { label: '🔵 Online', value: 'online', count: list.filter(a => a.booking_type !== 'walk_in').length }
  ];
});

const filteredAppointments = computed(() => {
  let list = appointments.value.data || appointments.value;
  if (!Array.isArray(list)) return [];
  
  if (currentFilter.value !== 'all') {
    if (currentFilter.value === 'walk_in') {
      list = list.filter(a => a.booking_type === 'walk_in');
    } else if (currentFilter.value === 'online') {
      list = list.filter(a => a.booking_type !== 'walk_in');
    } else {
      list = list.filter(a => a.status === currentFilter.value);
    }
  }
  
  if (!search.value) return list;
  const q = search.value.toLowerCase();
  return list.filter(a => (a.customer_name || '').toLowerCase().includes(q));
});

const viewReceipt = (booking) => {
  const mapped = {
    ...booking,
    customer: {
      name: booking.customer_name,
      email: booking.customer_email,
      phone: booking.customer_phone
    },
    service: {
      name: booking.service_name,
      price: booking.price,
      duration_minutes: booking.duration_minutes
    },
    barber: {
      name: 'You'
    }
  };
  selectedBooking.value = mapped;
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

async function promptComplete(id) {
  const list = appointments.value.data || appointments.value || [];
  const appointment = list.find(a => a.id === id);
  // Walk-in bookings skip verification code entirely
  if (appointment && appointment.booking_type === 'walk_in') {
    isVerifying.value = true;
    try {
      await complete(id, '');
    } catch {
      // Error is handled by composable toast
    } finally {
      isVerifying.value = false;
    }
    return;
  }
  verificationError.value = '';
  completingBooking.value = appointment || { id };
}

function closeCompleteModal() {
  completingBooking.value = null;
  verificationError.value = '';
}

async function submitComplete(code) {
  if (!completingBooking.value?.id) return;
  verificationError.value = '';
  isVerifying.value = true;
  try {
    await complete(completingBooking.value.id, code || '');
    closeCompleteModal();
  } catch (err) {
    verificationError.value = extractVerificationError(err, 'Invalid verification code. Please try again.');
  } finally {
    isVerifying.value = false;
  }
}

let refreshInterval;

onMounted(() => {
  loadAppointments();
  
  // Auto-reload every 30 seconds silently
  refreshInterval = setInterval(() => {
    loadAppointments();
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
.hide-scrollbar::-webkit-scrollbar {
  display: none;
}
.hide-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
