<template>
  <div class="min-h-screen w-full bg-[#07090E] text-white flex items-center justify-center p-4 selection:bg-amber-500/30 selection:text-amber-200">
    <!-- Ambient luxury background glow -->
    <div class="pointer-events-none absolute -top-40 left-1/4 h-72 w-72 rounded-full bg-amber-500/10 blur-[100px]"></div>
    <div class="pointer-events-none absolute -bottom-40 right-1/4 h-72 w-72 rounded-full bg-emerald-500/10 blur-[100px]"></div>

    <!-- Main Card Container -->
    <div class="w-full max-w-lg rounded-3xl border border-white/10 bg-[#0E1217]/95 backdrop-blur-2xl p-6 sm:p-8 shadow-2xl relative overflow-hidden text-center space-y-6">
      
      <!-- Top Gold Ambient Highlight Line -->
      <div class="absolute top-0 left-0 h-[2px] w-full bg-gradient-to-r from-transparent via-amber-500/80 to-transparent"></div>

      <!-- State 1: Verifying / Loading -->
      <div v-if="verifying" class="py-12 space-y-4">
        <div class="relative flex items-center justify-center">
          <div class="h-12 w-12 animate-spin rounded-full border-2 border-amber-500/20 border-t-amber-500"></div>
          <span class="absolute text-sm">💳</span>
        </div>
        <div class="space-y-1">
          <h2 class="text-base font-bold text-white">Verifying Transaction</h2>
          <p class="text-xs text-white/50 font-mono">Confirming payment with Paystack and securing your chair...</p>
        </div>
      </div>

      <!-- State 2: Success -->
      <div v-else-if="success" class="space-y-5 animate-fade-in">
        <!-- Success Icon -->
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 shadow-lg shadow-emerald-500/10">
          <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
          </svg>
        </div>

        <div class="space-y-1">
          <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[11px] font-bold uppercase tracking-wider">
            <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-ping"></span>
            Payment Verified & Chair Booked
          </span>
          <h1 class="text-xl sm:text-2xl font-bold font-display text-white mt-2">
            You're All Set!
          </h1>
          <p class="text-xs text-white/60 max-w-sm mx-auto leading-relaxed">
            Your appointment has been confirmed. The barber and admin have been notified.
          </p>
        </div>

        <!-- Appointment Receipt Summary -->
        <div class="rounded-2xl border border-white/10 bg-white/[0.02] p-4 text-xs text-left divide-y divide-white/[0.06] space-y-2.5">
          <div class="flex justify-between items-center pb-2">
            <span class="text-white/50 text-[11px]">Booking Reference</span>
            <span class="font-bold text-amber-400 font-mono">{{ appointment?.booking_reference || reference }}</span>
          </div>

          <div v-if="appointment?.service" class="flex justify-between items-center py-2">
            <span class="text-white/50 text-[11px]">Service</span>
            <span class="font-semibold text-white">{{ appointment.service.name }}</span>
          </div>

          <div v-if="appointment?.barber" class="flex justify-between items-center py-2">
            <span class="text-white/50 text-[11px]">Master Barber</span>
            <span class="font-semibold text-white">{{ appointment.barber.name }} (Chair #1)</span>
          </div>

          <div v-if="appointment?.appointment_date" class="flex justify-between items-center py-2">
            <span class="text-white/50 text-[11px]">Date & Time</span>
            <span class="font-mono text-white font-medium">
              {{ appointment.appointment_date }} at {{ appointment.appointment_time }}
            </span>
          </div>

          <div class="flex justify-between items-center pt-2">
            <span class="text-white/50 text-[11px]">Amount Paid</span>
            <span class="font-bold text-emerald-400 font-mono text-sm">
              ₦{{ Number(amountPaid).toLocaleString() }}
            </span>
          </div>
        </div>

        <!-- Action CTAs -->
        <div class="flex flex-col sm:flex-row gap-3 pt-2">
          <router-link
            to="/customer/dashboard/bookings"
            class="flex-1 rounded-xl bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-500 hover:to-amber-600 active:scale-[0.98] py-3 px-4 text-xs font-bold text-slate-950 transition-all shadow-lg shadow-amber-500/20 text-center flex items-center justify-center gap-1.5"
          >
            <span>View in My Bookings</span>
            <span>&rarr;</span>
          </router-link>
          <router-link
            to="/customer/dashboard"
            class="flex-1 rounded-xl border border-white/10 bg-white/[0.04] hover:bg-white/[0.08] active:scale-[0.98] py-3 px-4 text-xs font-semibold text-white transition-all text-center"
          >
            Go to Dashboard
          </router-link>
        </div>
      </div>

      <!-- State 3: Failure / Cancelled -->
      <div v-else class="space-y-5 animate-fade-in">
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
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { client } from '@/shared/api/client';
import { useToast } from '@/core/composables/useToast';

const route = useRoute();
const router = useRouter();
const toast = useToast();

const verifying = ref(true);
const success = ref(false);
const errorMessage = ref('');
const reference = ref('');
const appointment = ref(null);
const amountPaid = ref(0);

async function verifyPayment() {
  const refParam = route.query.reference || route.query.trxref;
  if (!refParam) {
    verifying.value = false;
    errorMessage.value = 'No transaction reference found in return URL.';
    return;
  }

  reference.value = String(refParam);

  try {
    const res = await client.get(`/customer/payments/${reference.value}`);
    const data = res.data?.data || res.data || {};

    if (data.status === 'successful' || data.appointment?.payment_status === 'paid') {
      success.value = true;
      appointment.value = data.appointment;
      amountPaid.value = data.appointment?.total_price || (data.amount_minor ? data.amount_minor / 100 : 0);
      toast.success('Payment verified successfully!');
    } else if (data.status === 'failed') {
      success.value = false;
      errorMessage.value = data.gateway_response || 'The transaction failed or was declined by the bank.';
    } else {
      // Pending status: give it one more quick poll
      await new Promise(r => setTimeout(r, 2000));
      const retryRes = await client.get(`/customer/payments/${reference.value}`);
      const retryData = retryRes.data?.data || retryRes.data || {};
      if (retryData.status === 'successful' || retryData.appointment?.payment_status === 'paid') {
        success.value = true;
        appointment.value = retryData.appointment;
        amountPaid.value = retryData.appointment?.total_price || (retryData.amount_minor ? retryData.amount_minor / 100 : 0);
        toast.success('Payment verified successfully!');
      } else {
        success.value = false;
        errorMessage.value = 'Payment verification is still pending. Please check your bookings tab in a moment.';
      }
    }
  } catch (err) {
    console.error('Callback verification failed:', err);
    success.value = false;
    errorMessage.value = err.response?.data?.message || 'Could not verify payment with the server.';
  } finally {
    verifying.value = false;
  }
}

function retryPayment() {
  if (appointment.value?.id) {
    router.push(`/customer/dashboard/payment/${appointment.value.id}`);
  } else {
    router.push('/customer/dashboard/bookings');
  }
}

onMounted(() => {
  verifyPayment();
});
</script>
