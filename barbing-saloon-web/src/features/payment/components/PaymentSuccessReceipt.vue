<template>
  <div class="rounded-3xl border border-emerald-500/30 bg-[#161B22]/95 p-6 sm:p-8 backdrop-blur-2xl text-center space-y-6 shadow-2xl">
    <!-- Success Badge -->
    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/40 shadow-lg shadow-emerald-500/10 animate-bounce">
      <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
      </svg>
    </div>

    <!-- Title & Subtitle -->
    <div class="space-y-1">
      <h2 class="font-display text-2xl font-bold text-white">
        Payment Confirmed & Saved
      </h2>
      <p class="text-xs text-white/60 max-w-md mx-auto">
        Your payment has been successfully written to the database. The booking is now confirmed and reflected for both the Admin and your selected Master Barber.
      </p>
    </div>

    <!-- Receipt Details Table -->
    <div class="rounded-2xl border border-white/10 bg-white/[0.03] divide-y divide-white/[0.06] text-xs text-left">
      <div class="flex justify-between p-3 sm:px-4">
        <span class="text-white/50">Amount Paid</span>
        <span class="font-bold text-white font-mono text-sm">{{ formattedAmount }}</span>
      </div>

      <div class="flex justify-between p-3 sm:px-4">
        <span class="text-white/50">Booking Reference</span>
        <span class="font-bold text-amber-400 font-mono">{{ result?.reference || reference }}</span>
      </div>

      <div class="flex justify-between p-3 sm:px-4">
        <span class="text-white/50">Selected Master Barber</span>
        <span class="font-semibold text-white">
          {{ result?.barber?.name || selectedBarber?.name || 'Assigned Stylist' }} (Chair #{{ result?.barber?.chair_number || selectedBarber?.chair_number || 1 }})
        </span>
      </div>

      <div class="flex justify-between p-3 sm:px-4">
        <span class="text-white/50">Channel / Method</span>
        <span class="font-medium text-white/90 uppercase">{{ channelLabel }}</span>
      </div>

      <div class="flex justify-between p-3 sm:px-4">
        <span class="text-white/50">Database Status</span>
        <span class="inline-flex items-center gap-1.5 text-emerald-400 font-bold">
          <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
          Confirmed & Verified
        </span>
      </div>
    </div>

    <!-- Actions -->
    <div class="flex flex-col sm:flex-row gap-3 pt-2">
      <button
        type="button"
        @click="$emit('go-bookings')"
        class="flex-1 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 py-3 text-xs font-bold text-slate-950 transition-all shadow-md cursor-pointer"
      >
        View in My Bookings
      </button>
      <button
        type="button"
        @click="$emit('print')"
        class="flex-1 rounded-xl border border-white/10 bg-white/[0.05] hover:bg-white/[0.1] py-3 text-xs font-semibold text-white transition-all cursor-pointer"
      >
        Print Receipt
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  result: {
    type: Object,
    default: null
  },
  formattedAmount: {
    type: String,
    required: true
  },
  reference: {
    type: String,
    required: true
  },
  selectedBarber: {
    type: Object,
    default: null
  },
  channel: {
    type: String,
    default: 'bank_transfer'
  }
});

defineEmits(['go-bookings', 'print']);

const channelLabel = computed(() => {
  const map = {
    bank_transfer: 'Bank Transfer',
    ussd: 'Bank USSD',
    bank_account: 'Bank Account',
    nqr: 'NQR Scan',
    card: 'Card Payment'
  };
  return map[props.channel] || props.channel;
});
</script>
