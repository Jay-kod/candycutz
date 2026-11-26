<template>
  <div class="min-h-screen w-full bg-[#07090E] text-white flex items-center justify-center p-4 selection:bg-amber-500/30 selection:text-amber-200">
    <div class="pointer-events-none absolute -top-40 left-1/4 h-72 w-72 rounded-full bg-amber-500/10 blur-[100px]"></div>
    <div class="pointer-events-none absolute -bottom-40 right-1/4 h-72 w-72 rounded-full bg-emerald-500/10 blur-[100px]"></div>

    <div class="w-full max-w-lg relative z-10">
      <!-- State 1: Verifying / Loading -->
      <div
        v-if="verifying"
        class="rounded-3xl border border-white/10 bg-[#0E1217]/95 backdrop-blur-2xl p-8 shadow-2xl text-center py-12 space-y-4"
      >
        <div class="relative flex items-center justify-center">
          <div class="h-12 w-12 animate-spin rounded-full border-2 border-amber-500/20 border-t-amber-500"></div>
          <span class="absolute text-sm">💳</span>
        </div>
        <div class="space-y-1">
          <h2 class="text-base font-bold text-white">Verifying Transaction</h2>
          <p class="text-xs text-white/50 font-mono">Confirming payment with Paystack and securing your chair...</p>
        </div>
      </div>

      <!-- State 2: Redesigned Luxury Receipt -->
      <PaymentSuccessReceipt
        v-else-if="success"
        :result="paymentPayload"
        :appointment="appointment"
        :formatted-amount="formattedAmount"
        :reference="appointment?.booking_reference || reference"
        :channel="paymentPayload?.payment_method || 'card'"
        @go-bookings="router.push('/customer/dashboard/bookings')"
        @print="windowPrint"
      />

      <!-- State 3: Failure / Cancelled -->
      <div
        v-else
        class="rounded-3xl border border-white/10 bg-[#0E1217]/95 backdrop-blur-2xl p-6 sm:p-8 shadow-2xl text-center space-y-5 animate-fade-in"
      >
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-rose-500/10 text-rose-400 border border-rose-500/30 shadow-lg shadow-rose-500/10">
          <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </div>

        <div class="space-y-1">
          <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full bg-rose-500/10 border border-rose-500/20 text-rose-400 text-[11px] font-bold uppercase tracking-wider">
            Payment Not Completed
          </span>
          <h1 class="text-xl sm:text-2xl font-bold font-display text-white mt-2">
            Transaction Incomplete
          </h1>
          <p class="text-xs text-white/60 max-w-sm mx-auto leading-relaxed">
            {{ errorMessage || 'We were unable to verify this transaction with Paystack. Your card was not charged.' }}
          </p>
        </div>

        <div v-if="reference" class="p-3 rounded-xl bg-white/[0.02] border border-white/10 text-xs font-mono text-white/50 text-left">
          <span>Ref: {{ reference }}</span>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 pt-2">
          <button
            type="button"
            @click="recheckStatus"
            class="flex-1 rounded-xl border border-amber-400/35 bg-amber-500/15 hover:bg-amber-500/25 active:scale-[0.98] py-3 px-4 text-xs font-bold text-amber-200 transition-all cursor-pointer"
          >
            Re-check Payment Status
          </button>
          <button
            type="button"
            @click="retryPayment"
            class="flex-1 rounded-xl bg-amber-500 hover:bg-amber-400 active:scale-[0.98] py-3 px-4 text-xs font-bold text-slate-950 transition-all shadow-lg shadow-amber-500/20 cursor-pointer"
          >
            Try Payment Again
          </button>
          <router-link
            to="/customer/dashboard/bookings"
            class="flex-1 rounded-xl border border-white/10 bg-white/[0.04] hover:bg-white/[0.08] active:scale-[0.98] py-3 px-4 text-xs font-semibold text-white transition-all text-center"
          >
            Back to Bookings
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { client } from '@/shared/api/client';
import { useToast } from '@/core/composables/useToast';
import PaymentSuccessReceipt from '../components/PaymentSuccessReceipt.vue';

const route = useRoute();
const router = useRouter();
const toast = useToast();

const verifying = ref(true);
const success = ref(false);
const errorMessage = ref('');
const reference = ref('');
const appointment = ref(null);
const paymentPayload = ref(null);
const amountPaid = ref(0);
let isUnmounted = false;

const formattedAmount = computed(() => {
  return new Intl.NumberFormat('en-NG', {
    style: 'currency',
    currency: 'NGN',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(Number(amountPaid.value || 0));
});

const resolveNaira = (data) => {
  const apptTotal = Number(data.appointment?.total_price || 0);
  if (apptTotal > 0 && apptTotal < 100000) return apptTotal;
  if (data.amount_minor) return Number(data.amount_minor) / 100;
  return apptTotal;
};

const applyVerifiedData = (data) => {
  success.value = true;
  paymentPayload.value = data;
  appointment.value = data.appointment;
  amountPaid.value = resolveNaira(data);
  toast.success('Payment verified successfully!');
};

async function verifyPayment() {
  const refParam = route.query.reference || route.query.trxref;
  if (!refParam) {
    verifying.value = false;
    errorMessage.value = 'No transaction reference found in return URL.';
    return;
  }

  verifying.value = true;
  errorMessage.value = '';
  reference.value = String(refParam);

  const retryDelays = [2000, 3000, 4000];

  try {
    let data = {};
    for (let attempt = 0; attempt <= retryDelays.length; attempt++) {
      if (isUnmounted) return;

      const res = await client.get(`/customer/payments/${reference.value}`);
      data = res.data?.data || res.data || {};
      if (data.appointment) {
        appointment.value = data.appointment;
      }

      if (data.status === 'successful' || data.appointment?.payment_status === 'paid') {
        applyVerifiedData(data);
        return;
      }

      if (data.status === 'failed' || data.status === 'rejected') {
        success.value = false;
        errorMessage.value = data.gateway_response || 'The transaction failed or was declined by the bank.';
        return;
      }

      if (attempt < retryDelays.length) {
        await new Promise((r) => setTimeout(r, retryDelays[attempt]));
      }
    }

    if (!isUnmounted) {
      success.value = false;
      errorMessage.value = 'Payment verification is still settling. Click Re-check Payment Status or check your bookings tab shortly.';
    }
  } catch (err) {
    if (!isUnmounted) {
      console.error('Callback verification failed:', err);
      success.value = false;
      errorMessage.value = err.response?.data?.message || 'Could not verify payment with the server.';
    }
  } finally {
    if (!isUnmounted) {
      verifying.value = false;
    }
  }
}

function recheckStatus() {
  if (!verifying.value) {
    verifyPayment();
  }
}

function retryPayment() {
  if (appointment.value?.id) {
    router.push(`/customer/dashboard/payment/${appointment.value.id}`);
  } else {
    router.push('/customer/dashboard/bookings');
  }
}

function windowPrint() {
  if (typeof window !== 'undefined') {
    window.print();
  }
}

onMounted(() => {
  verifyPayment();
});

onUnmounted(() => {
  isUnmounted = true;
});
</script>
