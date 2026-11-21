<template>
  <BarberLayout>
    <section class="max-w-7xl mx-auto pb-16 pt-4 px-4 sm:px-6 lg:px-8 space-y-6">
      <!-- Loading Skeleton -->
      <BarberDashboardSkeleton v-if="loading" />

      <template v-else>
        <BarberWelcomeBanner
          :currentTime="currentTime"
          :currentDate="currentDate"
          :myStatus="myStatus"
          :formatStatus="formatStatus"
          @toggle-status="toggleMyStatus"
        />

        <BarberKpiGrid :stats="dashboard.stats" />

        <div class="grid gap-6 lg:grid-cols-3 items-start">
          <!-- Left Column (2/3) - Today's Appointments & Pending Payments -->
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
          <div class="space-y-6 lg:sticky lg:top-8">
            <BarberQuickActions
              :stats="dashboard.stats"
              :progressWidth="progressWidth"
              :currentDate="currentDate"
              @open-notification="showNotificationModal = true"
            />
          </div>
        </div>
      </template>

      <!-- Broadcast Modal -->
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
import BarberLayout from '@/portals/barber/layouts/BarberLayout.vue';
import ReceiptViewer from '@/core/components/ReceiptViewer.vue';

import BarberWelcomeBanner from '@/features/barber/components/dashboard/BarberWelcomeBanner.vue';
import BarberKpiGrid from '@/features/barber/components/dashboard/BarberKpiGrid.vue';
import BarberPendingPayments from '@/features/barber/components/dashboard/BarberPendingPayments.vue';
import BarberTodayAppointments from '@/features/barber/components/dashboard/BarberTodayAppointments.vue';
import BarberQuickActions from '@/features/barber/components/dashboard/BarberQuickActions.vue';
import BroadcastNotificationModal from '@/features/barber/components/dashboard/BroadcastNotificationModal.vue';
import BarberDashboardSkeleton from '@/features/barber/components/dashboard/BarberDashboardSkeleton.vue';

import {
  useBarberDashboard,
  useBarberStatus,
  useBarberNotification,
} from '@/features/barber/composables/useDashboard';

const {
  dashboard,
  loading,
  progressWidth,
  loadDashboard,
  completeAppointment,
  markNoShow,
} = useBarberDashboard();

const { myStatus, loadMyStatus, formatStatus, toggleMyStatus } = useBarberStatus();
const { showNotificationModal, sendingNotification, notificationForm, sendNotification } =
  useBarberNotification();

const selectedReceipt = ref(null);

const currentTime = ref('');
const currentDate = ref('');
let clockInterval = null;

const updateClock = () => {
  const now = new Date();
  currentTime.value = now.toLocaleTimeString('en-US', {
    hour: '2-digit',
    minute: '2-digit',
  });
  currentDate.value = now.toLocaleDateString('en-US', {
    weekday: 'short',
    month: 'short',
    day: 'numeric',
  });
};

const viewReceipt = (booking) => {
  selectedReceipt.value = booking;
};

const onPaymentVerified = () => {
  selectedReceipt.value = null;
  loadDashboard();
};

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
