<template>
  <BarberLayout>
    <div class="max-w-6xl mx-auto pb-16 pt-4 px-4 sm:px-6 lg:px-8 space-y-6">
      <div class="flex items-center gap-4">
        <button
          @click="router.push('/barber/payments')"
          class="p-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-white/[0.03] border border-slate-200 dark:border-white/[0.08] text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-white/[0.06] dark:hover:text-white transition-all"
        >
          <ArrowLeftIcon class="w-5 h-5" />
        </button>
        <div>
          <h1 class="text-2xl sm:text-3xl font-display font-bold text-slate-900 dark:text-white">Payment Receipt Audit</h1>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Inspect uploaded bank transfer evidence and settle booking status.</p>
        </div>
      </div>

      <div v-if="isLoading" class="flex justify-center p-16">
        <ArrowPathIcon class="w-8 h-8 text-gold animate-spin" />
      </div>

      <div v-else-if="error" class="bg-red-500/10 border border-red-500/20 text-red-600 dark:text-red-400 p-6 rounded-3xl text-sm">
        {{ error }}
      </div>

      <div v-else-if="booking" class="bg-white dark:bg-[#12161C] border border-slate-200 dark:border-white/[0.08] rounded-3xl p-4 xs:p-6 sm:p-8 shadow-sm dark:shadow-2xl grid grid-cols-1 md:grid-cols-2 gap-6 sm:gap-8">
        <!-- Left Column: Receipt Document Preview -->
        <div class="flex flex-col h-full min-h-[260px] sm:min-h-[380px] md:min-h-[420px]">
          <div class="flex-1 overflow-hidden bg-slate-100 dark:bg-[#0B0F13] rounded-2xl border border-slate-200 dark:border-white/[0.08] flex items-center justify-center p-3 sm:p-4">
            <template v-if="!receiptSrc">
              <div class="text-center p-8">
                <DocumentTextIcon class="w-12 h-12 text-slate-400 dark:text-slate-600 mx-auto mb-2" />
                <p class="text-xs text-slate-500 font-medium">No transfer receipt image attached to this booking.</p>
              </div>
            </template>
            <template v-else-if="isPdf">
              <div class="text-center p-8">
                <div class="w-16 h-16 mx-auto mb-4 bg-red-500/10 border border-red-500/20 rounded-2xl flex items-center justify-center text-red-600 dark:text-red-400 font-bold">
                  PDF
                </div>
                <p class="text-sm font-semibold text-slate-900 dark:text-white mb-3">Customer Uploaded a PDF Document</p>
                <a
                  :href="receiptSrc"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="inline-flex items-center gap-2 text-xs font-bold text-amber-800 dark:text-gold hover:underline"
                >
                  Open PDF Receipt in New Tab →
                </a>
              </div>
            </template>
            <template v-else>
              <img
                :src="receiptSrc"
                alt="Customer Bank Transfer Receipt"
                class="max-h-full max-w-full h-auto w-auto object-contain rounded-xl shadow-lg"
              />
            </template>
          </div>
        </div>

        <!-- Right Column: Booking Details & Verification Settle Actions -->
        <div class="flex flex-col justify-between space-y-6">
          <div>
            <div class="mb-6 pb-6 border-b border-slate-200 dark:border-white/[0.06]">
              <span class="inline-flex items-center rounded-md px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider bg-slate-100 dark:bg-white/[0.04] text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-white/[0.08] mb-3">
                Booking #{{ booking.id }}
              </span>
              <h2 class="text-2xl font-display font-bold text-slate-900 dark:text-white mb-1">
                {{ booking.customer?.name || booking.customer_name || booking.client_name }}
              </h2>
              <p class="text-xs text-slate-500 dark:text-slate-400">
                Service: <span class="text-slate-800 dark:text-slate-200 font-medium">{{ booking.service?.name || booking.service_name }}</span>
              </p>
              <p class="text-2xl font-bold font-mono text-amber-700 dark:text-gold mt-2">
                ₦{{ Number(booking.service?.price || booking.price || 0).toLocaleString() }}
              </p>
            </div>

            <div class="bg-slate-50 dark:bg-[#0B0F13] rounded-2xl p-5 border border-slate-200 dark:border-white/[0.06] space-y-3.5 text-xs">
              <div class="flex justify-between items-center border-b border-slate-200 dark:border-white/[0.04] pb-2.5">
                <span class="text-slate-500 font-semibold uppercase tracking-wider text-[10px]">Date & Time</span>
                <span class="text-slate-900 dark:text-white font-medium">
                  {{ formatDate(booking.appointment_date) }} at {{ booking.appointment_time }}
                </span>
              </div>

              <div class="flex justify-between items-center border-b border-slate-200 dark:border-white/[0.04] pb-2.5">
                <span class="text-slate-500 font-semibold uppercase tracking-wider text-[10px]">Client Phone</span>
                <span class="text-slate-900 dark:text-white font-medium">
                  {{ booking.customer?.phone || booking.customer_phone || booking.client_phone || '—' }}
                </span>
              </div>

              <div v-if="booking.transaction_ref" class="flex justify-between items-center border-b border-slate-200 dark:border-white/[0.04] pb-2.5">
                <span class="text-slate-500 font-semibold uppercase tracking-wider text-[10px]">Reference</span>
                <span class="text-amber-800 dark:text-gold font-mono text-[11px] bg-gold/10 px-2 py-0.5 rounded border border-gold/20">
                  {{ booking.transaction_ref }}
                </span>
              </div>

              <div class="flex justify-between items-center pt-1">
                <span class="text-slate-500 font-semibold uppercase tracking-wider text-[10px]">Payment Status</span>
                <span
                  class="font-bold uppercase tracking-wider text-[9px] px-2.5 py-0.5 rounded-full border"
                  :class="
                    booking.payment_status === 'completed' || booking.payment_status === 'successful'
                      ? 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-500/20'
                      : 'bg-amber-500/10 text-amber-700 dark:text-amber-400 border-amber-500/20'
                  "
                >
                  {{ booking.payment_status || 'Awaiting Verification' }}
                </span>
              </div>
            </div>
          </div>

          <!-- Actions Area -->
          <div v-if="isPending" class="flex flex-col sm:flex-row items-center gap-3 pt-6 border-t border-slate-200 dark:border-white/[0.06]">
            <button
              @click="verifyPayment('reject')"
              :disabled="isProcessing"
              class="w-full sm:flex-1 py-3.5 px-5 rounded-2xl border border-red-500/30 text-red-600 dark:text-red-400 hover:bg-red-500/10 text-xs font-bold uppercase tracking-wider transition-all flex items-center justify-center gap-2 disabled:opacity-50"
            >
              <XCircleIcon class="w-4 h-4" />
              <span>Decline Receipt</span>
            </button>

            <button
              @click="verifyPayment('approve')"
              :disabled="isProcessing"
              class="w-full sm:flex-1 py-3.5 px-5 rounded-2xl bg-gradient-to-r from-gold via-amber-400 to-amber-500 text-obsidian text-xs font-bold uppercase tracking-wider shadow-lg shadow-gold/20 hover:shadow-[0_8px_30px_rgba(255,153,0,0.35)] transition-all flex items-center justify-center gap-2 disabled:opacity-50 hover:-translate-y-0.5 active:translate-y-0"
            >
              <ArrowPathIcon v-if="isProcessing" class="w-4 h-4 animate-spin" />
              <CheckIcon v-else class="w-4 h-4" />
              <span>Approve & Confirm</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </BarberLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import BarberLayout from '@/portals/barber/layouts/BarberLayout.vue';
import api from '@/shared/api/client';
import { useToast } from '@/core/composables/useToast';
import { API_V1_BASE_URL } from '@/core/utils/url';
import {
  ArrowLeftIcon,
  CheckIcon,
  XCircleIcon,
  ArrowPathIcon,
  DocumentTextIcon,
} from '@heroicons/vue/24/outline';

const route = useRoute();
const router = useRouter();
const toast = useToast();
const bookingId = route.params.id;

const booking = ref(null);
const isLoading = ref(true);
const isProcessing = ref(false);
const error = ref('');
const receiptSrc = ref(null);
let receiptObjectUrl = null;

const isPending = computed(() => {
  const st = (booking.value?.payment_status || '').toLowerCase();
  return !st || st === 'pending' || st === 'awaiting_verification';
});

const isPdf = computed(() => {
  return booking.value?.receipt_image?.toLowerCase().endsWith('.pdf');
});

function formatDate(dateStr) {
  if (!dateStr) return 'Today';
  return new Date(dateStr).toLocaleDateString('en-US', {
    weekday: 'short',
    month: 'short',
    day: 'numeric',
  });
}

async function fetchBooking() {
  isLoading.value = true;
  try {
    const res = await api.get('/barber/appointments');
    const appointments = res.data?.data || [];
    const list = Array.isArray(appointments) ? appointments : (appointments.items || []);
    const found = list.find((a) => String(a.id) === String(bookingId));

    if (found) {
      booking.value = found;
    } else {
      error.value = 'Booking receipt record not found.';
    }
  } catch {
    error.value = 'Failed to load booking details.';
  } finally {
    isLoading.value = false;
  }
}

async function loadReceipt() {
  if (receiptObjectUrl) {
    URL.revokeObjectURL(receiptObjectUrl);
    receiptObjectUrl = null;
  }
  receiptSrc.value = null;

  if (!booking.value?.receipt_image && !booking.value?.receipt_url) return;

  try {
    const token = localStorage.getItem('candycutz_auth_token');
    const response = await fetch(`${API_V1_BASE_URL}/payments/appointments/${booking.value.id}/receipt`, {
      headers: {
        ...(token ? { Authorization: `Bearer ${token}` } : {}),
        Accept: 'image/*, application/pdf',
      },
    });

    if (response.ok) {
      const blob = await response.blob();
      receiptObjectUrl = URL.createObjectURL(blob);
      receiptSrc.value = receiptObjectUrl;
    } else {
      const raw = booking.value.receipt_url || booking.value.receipt_image;
      if (raw) {
        receiptSrc.value = raw.startsWith('http') ? raw : `${API_V1_BASE_URL}/storage/${raw}`;
      }
    }
  } catch {
    const raw = booking.value.receipt_url || booking.value.receipt_image;
    if (raw) {
      receiptSrc.value = raw.startsWith('http') ? raw : `${API_V1_BASE_URL}/storage/${raw}`;
    }
  }
}

async function verifyPayment(action) {
  isProcessing.value = true;
  try {
    await api.post(`/barber/payments/${bookingId}/verify`, {
      action,
    });
    toast.success(`Payment successfully ${action === 'approve' ? 'approved' : 'rejected'}`);
    await fetchBooking();
  } catch (err) {
    toast.error(err.response?.data?.message || `Failed to ${action} payment.`);
  } finally {
    isProcessing.value = false;
  }
}

watch(
  () => booking.value,
  () => {
    if (booking.value) {
      loadReceipt();
    }
  }
);

onMounted(() => {
  fetchBooking();
});

onUnmounted(() => {
  if (receiptObjectUrl) {
    URL.revokeObjectURL(receiptObjectUrl);
    receiptObjectUrl = null;
  }
});
</script>
