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
    <div v-if="completingBookingId" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-obsidian/80 backdrop-blur-sm" @click="closeCompleteModal"></div>
      <div class="relative w-full max-w-sm rounded-3xl border border-theme-border bg-theme-surface/90 p-8 shadow-2xl backdrop-blur-xl animate-fade-in text-center">
        <button @click="closeCompleteModal" class="absolute top-4 right-4 text-ivory/40 hover:text-white transition-colors">
          <XMarkIcon class="h-6 w-6" />
        </button>
        
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-gold/10 border border-gold/30 mb-6">
          <CheckIcon class="h-8 w-8 text-gold" />
        </div>
        
        <h3 class="font-display text-2xl text-theme-text mb-2">Service Verification</h3>
        <p class="text-sm text-ivory/60 mb-6">Ask the customer for their 12-character verification code to complete this booking.</p>
        
        <div class="space-y-4">
          <div>
            <input 
              v-model="verificationCodeInput"
              type="text" 
              placeholder="e.g. ABCD-1234-EFGH"
              class="w-full bg-black/40 border border-theme-border text-center text-lg tracking-[0.2em] font-bold text-white rounded-xl py-4 outline-none transition-all focus:border-gold/50 focus:ring-4 focus:ring-gold/10 uppercase placeholder:text-ivory/20"
              maxlength="14"
            />
          </div>
          
          <button
            @click="pasteCode"
            class="w-full flex items-center justify-center gap-2 rounded-xl border border-white/5 bg-white/[0.02] py-3 text-xs font-bold uppercase tracking-wider text-ivory/70 transition-all hover:bg-white/[0.04] hover:text-white"
          >
            <ClipboardDocumentIcon class="h-4 w-4" />
            Paste from Clipboard
          </button>
          
          <button 
            @click="submitComplete(false)"
            :disabled="!verificationCodeInput || verificationCodeInput.length < 12 || isVerifying"
            class="w-full flex items-center justify-center gap-2 rounded-xl bg-gold py-4 text-sm font-bold text-obsidian transition-all hover:bg-gold-light disabled:opacity-50 disabled:cursor-not-allowed shadow-[0_0_20px_rgba(212,175,55,0.2)]"
          >
            <span v-if="isVerifying" class="w-5 h-5 border-2 border-obsidian/30 border-t-obsidian rounded-full animate-spin"></span>
            <span v-else>Verify & Complete</span>
          </button>
        </div>
      </div>
    </div>
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

// Composables
import { useBarberAppointments } from '@/features/appointments/composables/useBarberAppointments';

import {
  CheckIcon,
  XMarkIcon,
  ClipboardDocumentIcon
} from '@heroicons/vue/24/outline';

const toast = useToast();

const {
  appointments,
  loading,
  loadAppointments,
  approve,
  cancel,
  complete,
  markNoShow
} = useBarberAppointments();

const search = ref('');
const currentFilter = ref('all');
const selectedBooking = ref(null);

const completingBookingId = ref(null);
const verificationCodeInput = ref('');
const isVerifying = ref(false);

const tabs = computed(() => {
  const list = appointments.value.data || appointments.value || [];
  if (!Array.isArray(list)) return [];
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

function promptComplete(id) {
  const list = appointments.value.data || appointments.value || [];
  const appointment = list.find(a => a.id === id);
  // Walk-in bookings skip verification code entirely
  if (appointment && appointment.booking_type === 'walk_in') {
    completingBookingId.value = id;
    submitComplete(true);
    return;
  }
  completingBookingId.value = id;
  verificationCodeInput.value = '';
}

function closeCompleteModal() {
  completingBookingId.value = null;
  verificationCodeInput.value = '';
}

async function pasteCode() {
  try {
    if (!navigator.clipboard || !navigator.clipboard.readText) {
      toast.error('Browser does not support clipboard pasting directly. Please paste manually.');
      return;
    }
    const text = await navigator.clipboard.readText();
    if (text) {
      verificationCodeInput.value = text.trim().toUpperCase();
      toast.success('Code pasted');
    }
  } catch (err) {
    console.error('Clipboard error:', err);
    toast.error('Could not read from clipboard. Please paste manually.');
  }
}

async function submitComplete(isWalkin = false) {
  if (isWalkin !== true && (!verificationCodeInput.value || verificationCodeInput.value.length !== 12)) {
    toast.error('Please enter a valid 12-character code');
    return;
  }
  
  isVerifying.value = true;
  try {
    await complete(completingBookingId.value, verificationCodeInput.value || '');
    closeCompleteModal();
  } catch (err) {
    // Error is handled by composable toast
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
