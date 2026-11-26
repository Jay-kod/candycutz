<template>
  <CustomerLayout>
    <div class="max-w-7xl mx-auto space-y-4 sm:space-y-6 lg:space-y-8 animate-fade-in pb-6 sm:pb-12 pt-1 sm:pt-4">
      <!-- Header -->
      <BookingsHeader :loading="loadingBookings" @refresh="loadBookings" />

      <!-- Mandatory Deposit Notice -->
      <div
        v-if="authStore?.user?.requires_deposit"
        class="rounded-xl border border-amber-500/35 bg-amber-500/10 p-4 text-xs text-amber-700 dark:text-amber-300"
      >
        <p class="font-bold">Mandatory Deposit Policy Active</p>
        <p class="mt-0.5 opacity-90">
          Your account currently requires an upfront booking deposit due to prior missed appointments.
        </p>
      </div>

      <!-- Active Draft Reservation Banner -->
      <DraftBookingBanner @discarded="loadBookings" @resumed="loadBookings" />

      <!-- Filters & Bookings List -->
      <div class="space-y-4 sm:space-y-6">
        <BookingFilterBar
          v-model:currentFilter="currentFilter"
          :filters="filters"
          :filtered-count="filteredBookings.length"
          :total-count="allBookings.length"
        />

        <!-- Loading Skeletons -->
        <div v-if="loadingBookings" class="grid gap-4 sm:gap-5 md:gap-6 md:grid-cols-2 lg:grid-cols-3">
          <div
            v-for="i in 6"
            :key="i"
            class="h-56 md:h-64 rounded-xl md:rounded-3xl bg-[var(--color-bg-secondary)] md:bg-slate-100 md:dark:bg-white/[0.02] border border-[var(--color-border)] md:border-slate-200 md:dark:border-white/[0.06] animate-pulse"
          ></div>
        </div>

        <!-- Bookings Grid -->
        <div v-else class="grid gap-4 sm:gap-5 md:gap-6 md:grid-cols-2 lg:grid-cols-3">
          <BookingCard
            v-for="booking in filteredBookings"
            :key="booking.id"
            :booking="booking"
            :bank-details="getBankDetails(booking)"
            :show-bank-details="shouldShowBankDetails(booking)"
            :is-uploader-open="Boolean(showUploader[booking.id])"
            @delete="deleteBooking"
            @copy="copyCode"
            @discard-draft="discardDraft"
            @start-payment="startPayment"
            @receipt-uploaded="onReceiptUploaded"
            @open-receipt="activeReceiptBooking = $event"
            @extend-grace="graceModalBooking = $event"
            @appeal-no-show="appealModalBooking = $event"
            @request-refund="refundModalBooking = $event"
            @cancel="cancel"
          />

          <BookingsEmptyState
            v-if="filteredBookings.length === 0"
            :has-bookings="allBookings.length > 0"
            :active-filter="currentFilter"
            @reset-filter="currentFilter = 'all'"
          />
        </div>
      </div>
    </div>

    <BookingReceiptModal :booking="activeReceiptBooking" @close="activeReceiptBooking = null" />
    <CustomerNoShowModals
      :grace-booking="graceModalBooking"
      :appeal-booking="appealModalBooking"
      :submitting="submittingNoShowAction"
      @close-grace="graceModalBooking = null"
      @close-appeal="appealModalBooking = null"
      @submit-grace="handleGraceExtension"
      @submit-appeal="handleNoShowAppeal"
    />
    <CancelWithRefundModal
      :booking="cancelModalBooking"
      :submitting="submittingRefundAction"
      @close="cancelModalBooking = null"
      @confirm="confirmCancellation"
    />
    <CompassionateRefundModal
      :booking="refundModalBooking"
      :submitting="submittingRefundAction"
      @close="refundModalBooking = null"
      @submit="submitCompassionateRefund"
    />
  </CustomerLayout>
</template>

<script setup>
import { onMounted, reactive, ref, computed } from 'vue';
import CustomerLayout from '@/portals/customer/layouts/CustomerLayout.vue';
import BookingsHeader from '../components/booking/BookingsHeader.vue';
import DraftBookingBanner from '../components/booking/DraftBookingBanner.vue';
import BookingFilterBar from '../components/booking/BookingFilterBar.vue';
import BookingCard from '../components/booking/BookingCard.vue';
import BookingsEmptyState from '../components/booking/BookingsEmptyState.vue';
import BookingReceiptModal from '@/features/payment/components/BookingReceiptModal.vue';
import CustomerNoShowModals from '@/features/no-shows/components/CustomerNoShowModals.vue';
import CancelWithRefundModal from '@/features/refunds/components/CancelWithRefundModal.vue';
import CompassionateRefundModal from '@/features/refunds/components/CompassionateRefundModal.vue';
import { noShowApi } from '@/features/no-shows/api/noShowApi';
import { useCustomerBookingRefunds } from '@/features/refunds/composables/useCustomerBookingRefunds';
import { getActivePinia } from 'pinia';
import { useAuthStore } from '@/modules/auth/store/auth.store';
import { client } from '@/shared/api/client';
import { useToast } from '@/core/composables/useToast';
import { useConfirm } from '@/core/composables/useConfirm';

const authStore = getActivePinia() ? useAuthStore() : null;
const toast = useToast();
const { confirm } = useConfirm();

const bookings = ref([]);
const loadingBookings = ref(true);
const showUploader = reactive({});
const currentFilter = ref('all');
const activeReceiptBooking = ref(null);
const graceModalBooking = ref(null);
const appealModalBooking = ref(null);
const submittingNoShowAction = ref(false);
const {
  cancelModalBooking,
  refundModalBooking,
  submittingRefundAction,
  confirmCancellation,
  submitCompassionateRefund,
} = useCustomerBookingRefunds(loadBookings);

const salonFallbackBank = ref({
  bank_name: 'Zenith Bank',
  account_name: 'Candy Cutz Luxury Grooming',
  account_number: '1012345678',
});

const allBookings = computed(() => {
  const list = bookings.value?.data || bookings.value;
  return Array.isArray(list) ? list : [];
});

const filters = computed(() => {
  const all = allBookings.value;
  return [
    { label: 'All', value: 'all', count: all.length },
    { label: 'Drafts', value: 'draft', count: all.filter((b) => b.status === 'draft').length },
    { label: 'Pending', value: 'pending', count: all.filter((b) => b.status === 'pending').length },
    { label: 'Confirmed', value: 'confirmed', count: all.filter((b) => b.status === 'confirmed').length },
    { label: 'Completed', value: 'completed', count: all.filter((b) => b.status === 'completed').length },
    { label: 'No-Shows', value: 'no_show', count: all.filter((b) => b.status === 'no_show').length },
    { label: 'Cancelled', value: 'cancelled', count: all.filter((b) => b.status === 'cancelled').length },
  ];
});

const filteredBookings = computed(() => {
  if (currentFilter.value === 'all') return allBookings.value;
  return allBookings.value.filter((b) => b.status === currentFilter.value);
});

async function loadBookings() {
  loadingBookings.value = true;
  try {
    const response = await client.get('/customer/bookings');
    bookings.value = response.data?.data || response.data || [];
  } catch (err) {
    toast.error('Failed to load bookings');
  } finally {
    loadingBookings.value = false;
  }
}

function cancel(idOrBooking) {
  const target = typeof idOrBooking === 'object'
    ? idOrBooking
    : allBookings.value.find((b) => b.id === idOrBooking) || { id: idOrBooking };
  cancelModalBooking.value = target;
}

async function handleGraceExtension(appointmentId, reason) {
  submittingNoShowAction.value = true;
  try {
    await noShowApi.extendGrace(appointmentId, reason);
    toast.success('Arrival grace period extended.');
    graceModalBooking.value = null;
    await loadBookings();
  } catch (err) {
    toast.error(err.response?.data?.message || 'Could not extend grace period.');
  } finally {
    submittingNoShowAction.value = false;
  }
}

async function handleNoShowAppeal(recordId, reason) {
  submittingNoShowAction.value = true;
  try {
    await noShowApi.appealNoShow(recordId, reason);
    toast.success('Appeal submitted for admin review.');
    appealModalBooking.value = null;
    await loadBookings();
  } catch (err) {
    toast.error(err.response?.data?.message || 'Could not submit appeal.');
  } finally {
    submittingNoShowAction.value = false;
  }
}

async function deleteBooking(id) {
  const ok = await confirm({
    title: 'Delete Booking',
    message: 'Are you sure you want to permanently remove this booking from your history? This action cannot be undone.',
    confirmText: 'Delete',
  });
  if (!ok) return;

  try {
    await client.delete(`/customer/bookings/${id}`);
    toast.success('Booking removed successfully');
    await loadBookings();
  } catch (err) {
    toast.error('Failed to delete booking');
  }
}

async function discardDraft(id) {
  const ok = await confirm({
    title: 'Discard Draft Booking',
    message: 'Are you sure you want to discard this draft? Your reserved chair slot will be released immediately.',
    confirmText: 'Discard Draft',
  });
  if (!ok) return;

  try {
    await client.delete(`/customer/drafts/${id}`).catch(() => {
      return client.delete(`/appointments/drafts/${id}`);
    });
    toast.success('Draft booking discarded. Slot released.');
    await loadBookings();
  } catch (err) {
    toast.error(err.response?.data?.message || 'Failed to discard draft');
  }
}

async function startPayment(booking) {
  const amount = booking.grand_total || booking.service?.price || 5000;
  try {
    await client.post('/customer/checkout', {
      appointment_id: booking.id,
      amount,
      payment_method: 'manual_transfer',
    });
  } catch (err) {
    console.warn('Checkout initialization note:', err);
  }
  showUploader[booking.id] = true;
}

async function onReceiptUploaded() {
  toast.success('Receipt uploaded successfully. Awaiting verification.');
  await loadBookings();
}

async function copyCode(code) {
  if (!code) return;
  try {
    await navigator.clipboard.writeText(String(code));
    toast.success('Copied to clipboard!');
  } catch (err) {
    toast.error('Failed to copy');
  }
}

function getBankDetails(booking) {
  if (booking?.bank_details?.bank_name && booking?.bank_details?.account_number) {
    return booking.bank_details;
  }
  return salonFallbackBank.value;
}

function shouldShowBankDetails(booking) {
  if (!booking) return false;
  if (['cancelled', 'completed'].includes(booking.status)) return false;
  if (booking.deposit_paid || booking.payment_status === 'paid' || booking.payment_status === 'awaiting_verification') {
    return false;
  }
  return true;
}

onMounted(async () => {
  await loadBookings();
  try {
    const res = await client.get('/settings');
    const data = res.data?.data || res.data || {};
    if (data.bank_name && data.account_number) {
      salonFallbackBank.value = {
        bank_name: data.bank_name,
        account_name: data.account_name || 'Candy Cutz Luxury Grooming',
        account_number: data.account_number,
      };
    }
  } catch (err) {
    // Non-blocking fallback
  }
});
</script>
