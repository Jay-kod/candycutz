<template>
  <BarberLayout>
    <section class="space-y-8 animate-fade-in">
      
      <BarberWelcomeBanner 
        :currentTime="currentTime"
        :currentDate="currentDate"
        :myStatus="myStatus"
        :formatStatus="formatStatus"
        @toggle-status="toggleMyStatus"
      />

      <!-- Loading State -->
      <div v-if="loading" class="grid gap-5 grid-cols-2 lg:grid-cols-4">
        <div v-for="i in 8" :key="i" class="h-36 rounded-2xl bg-white/[0.03] animate-pulse border border-white/[0.04]"></div>
      </div>

      <template v-else>
        
        <BarberKpiGrid :stats="dashboard.stats" />

        <div class="grid gap-6 lg:grid-cols-3">
          <!-- Left Column (2/3) - Today's Appointments & Payments -->
          <div class="lg:col-span-2 space-y-6">
            
            <BarberPendingPayments 
              :payments="dashboard.pending_payments" 
              @view-receipt="viewReceipt" 
            />

            <BarberTodayAppointments 
              :appointments="dashboard.today_appointments" 
              @complete="completeAppointment"
              @no-show="markNoShow"
            />

          </div>

          <!-- Right Column (1/3) - Quick Actions & Progress -->
          <div class="space-y-6">
            
            <BarberQuickActions 
              :stats="dashboard.stats"
              :progressWidth="progressWidth"
              :currentDate="currentDate"
              @open-notification="showNotificationModal = true"
            />

          </div>
        </div>
      </template>
      
      <BroadcastNotificationModal 
        :show="showNotificationModal"
        :sending="sendingNotification"
        :form="notificationForm"
        @close="showNotificationModal = false"
        @send="sendNotification"
      />

      <!-- Receipt Viewer Modal -->
      <ReceiptViewer 
        v-if="selectedReceipt" 
        :booking="selectedReceipt" 
        @close="selectedReceipt = null"
        @approve="onPaymentVerified"
        @reject="onPaymentVerified"
        portal="barber"
      />
    </section>
  </BarberLayout>
</template>

<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import BarberLayout from '@/portals/barber/layouts/BarberLayout.vue'; // wait, it was '../layouts/BarberLayout.vue', probably in portals or core
import ReceiptViewer from '@/core/components/ReceiptViewer.vue';

import BarberWelcomeBanner from '@/features/barber/components/dashboard/BarberWelcomeBanner.vue';
import BarberKpiGrid from '@/features/barber/components/dashboard/BarberKpiGrid.vue';
import BarberPendingPayments from '@/features/barber/components/dashboard/BarberPendingPayments.vue';
import BarberTodayAppointments from '@/features/barber/components/dashboard/BarberTodayAppointments.vue';
import BarberQuickActions from '@/features/barber/components/dashboard/BarberQuickActions.vue';
import BroadcastNotificationModal from '@/features/barber/components/dashboard/BroadcastNotificationModal.vue';

import { useBarberDashboard, useBarberStatus, useBarberNotification } from '@/features/barber/composables/useDashboard';

const {
  dashboard, loading, progressWidth,
  loadDashboard, completeAppointment, markNoShow
} = useBarberDashboard();

const { myStatus, loadMyStatus, formatStatus, toggleMyStatus } = useBarberStatus();
const { showNotificationModal, sendingNotification, notificationForm, sendNotification } = useBarberNotification();

const selectedReceipt = ref(null);

const currentTime = ref('');
const currentDate = ref('');
let clockInterval = null;

const updateClock = () => {
  const now = new Date();
  currentTime.value = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
  currentDate.value = now.toLocaleDateString('en-US', { weekday: 'long', month: 'short', day: 'numeric' });
};

function viewReceipt(booking) {
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
    barber: { name: 'You' }
  };
  selectedReceipt.value = mapped;
}

function onPaymentVerified() {
  selectedReceipt.value = null;
  loadDashboard();
}

onMounted(() => {
  updateClock();
  clockInterval = setInterval(updateClock, 1000);
  loadDashboard();
  loadMyStatus();
});

onUnmounted(() => {
  if (clockInterval) clearInterval(clockInterval);
});
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(12px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
