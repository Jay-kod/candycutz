<template>
  <div class="w-full max-w-lg mx-auto rounded-xl border border-emerald-500/30 bg-[#121720]/95 p-4 sm:p-5 backdrop-blur-2xl text-center space-y-3 shadow-2xl">
    <!-- Success Badge -->
    <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500/20 to-emerald-600/10 text-emerald-400 border border-emerald-500/40 shadow-md shadow-emerald-500/10">
      <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
      </svg>
    </div>

    <!-- Title & Subtitle -->
    <div class="space-y-0.5">
      <h2 class="font-display text-base sm:text-lg font-bold text-white tracking-tight">
        Payment Confirmed & Saved
      </h2>
      <p class="text-[11px] text-white/60 max-w-xs mx-auto leading-relaxed">
        Your payment has been successfully recorded in the database. Your appointment and stylist slot are secured.
      </p>
    </div>

    <!-- Receipt Details Table -->
    <div class="rounded-lg border border-white/10 bg-white/[0.02] divide-y divide-white/[0.06] text-xs text-left overflow-hidden">
      <div class="flex justify-between px-3 py-2">
        <span class="text-white/50 text-[11px]">Amount Paid</span>
        <span class="font-bold text-white font-mono text-xs">{{ formattedAmount }}</span>
      </div>

      <div class="flex justify-between px-3 py-2">
        <span class="text-white/50 text-[11px]">Booking Reference</span>
        <span class="font-bold text-amber-400 font-mono text-xs">{{ result?.reference || reference }}</span>
      </div>

      <div class="flex justify-between px-3 py-2">
        <span class="text-white/50 text-[11px]">Selected Master Barber</span>
        <span class="font-semibold text-white text-xs">
          {{ result?.barber?.name || selectedBarber?.name || 'Assigned Stylist' }} (Chair #{{ result?.barber?.chair_number || selectedBarber?.chair_number || 1 }})
        </span>
      </div>

      <div class="flex justify-between px-3 py-2">
        <span class="text-white/50 text-[11px]">Channel / Method</span>
        <span class="font-medium text-white/90 uppercase text-[11px]">{{ channelLabel }}</span>
      </div>

      <div class="flex justify-between px-3 py-2">
        <span class="text-white/50 text-[11px]">Database Status</span>
        <span class="inline-flex items-center gap-1 text-emerald-400 font-bold text-[11px]">
          <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
          Confirmed & Verified
        </span>
      </div>
    </div>

    <!-- Actions -->
    <div class="flex flex-col sm:flex-row gap-2 pt-1">
      <button
        type="button"
        @click="$emit('go-bookings')"
        class="flex-1 rounded-lg bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-500 hover:to-amber-600 active:scale-[0.99] py-2 px-3 text-xs font-bold text-slate-950 transition-all shadow-md cursor-pointer"
      >
        View in My Bookings
      </button>
      <button
        type="button"
        @click="$emit('print')"
        class="flex-1 rounded-lg border border-white/10 bg-white/[0.05] hover:bg-white/[0.1] active:scale-[0.99] py-2 px-3 text-xs font-semibold text-white transition-all cursor-pointer"
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
