<template>
  <div class="bg-[#12161C] rounded-3xl border border-white/[0.08] overflow-hidden shadow-2xl relative">
    <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-emerald-500 via-[#FF6700] to-teal-400"></div>
    <div class="absolute top-0 right-0 p-32 bg-emerald-500/10 blur-[80px] pointer-events-none rounded-full"></div>

    <div class="p-6 sm:p-8 pb-10 relative z-10">
      <div class="text-center mb-6">
        <div class="w-14 h-14 mx-auto bg-white/[0.03] rounded-2xl border border-white/[0.08] flex items-center justify-center mb-3">
          <TicketIcon class="h-7 w-7 text-emerald-400 opacity-90" />
        </div>
        <h3 class="text-lg font-display font-bold text-white tracking-widest uppercase">Walk-In Voucher</h3>
        <p class="text-[11px] text-slate-400 font-mono mt-1">{{ currentDateFormatted }}</p>
      </div>

      <div class="space-y-4 text-xs">
        <div class="flex justify-between items-end border-b border-white/[0.06] pb-3">
          <div>
            <p class="text-[10px] text-slate-400 uppercase tracking-widest font-bold mb-0.5">Guest</p>
            <p class="text-sm text-white font-medium truncate max-w-[150px]">{{ form.customer_name || '—' }}</p>
          </div>
          <div class="text-right">
            <p class="text-[10px] text-slate-400 uppercase tracking-widest font-bold mb-0.5">Contact</p>
            <p class="text-sm text-white font-medium">{{ form.customer_phone || '—' }}</p>
          </div>
        </div>

        <div class="flex justify-between items-end border-b border-white/[0.06] pb-3">
          <div>
            <p class="text-[10px] text-slate-400 uppercase tracking-widest font-bold mb-0.5">Selected Cut</p>
            <p class="text-sm text-white font-medium truncate max-w-[150px]">{{ selectedServiceName || 'Not Selected' }}</p>
          </div>
          <div class="text-right">
            <p class="text-[10px] text-slate-400 uppercase tracking-widest font-bold mb-0.5">Date & Time</p>
            <p class="text-sm text-white font-medium">{{ form.appointment_time }}</p>
          </div>
        </div>

        <div class="flex justify-between items-end border-b border-white/[0.06] pb-3">
          <div>
            <p class="text-[10px] text-slate-400 uppercase tracking-widest font-bold mb-0.5">Settlement</p>
            <p class="text-sm text-white font-medium capitalize">{{ form.payment_method }}</p>
          </div>
          <div class="text-right">
            <p class="text-[10px] text-slate-400 uppercase tracking-widest font-bold mb-0.5">Chair Status</p>
            <span class="inline-block px-2 py-0.5 text-[9px] font-bold rounded-md uppercase bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
              Immediate
            </span>
          </div>
        </div>

        <div class="flex justify-between items-center bg-[#0B0F13] rounded-2xl p-4 border border-white/[0.08] mt-6 relative overflow-hidden">
          <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/5 to-transparent pointer-events-none"></div>
          <span class="text-xs font-bold text-slate-400 uppercase tracking-widest relative z-10">Total Due</span>
          <span class="text-2xl font-bold text-emerald-400 font-mono relative z-10" data-test="ticket-price">
            ₦{{ selectedServicePrice.toLocaleString() }}
          </span>
        </div>
      </div>
    </div>

    <!-- Ticket Tear-Off Marks -->
    <div class="w-full flex justify-between px-3 pb-3">
      <div v-for="i in 18" :key="i" class="w-2.5 h-2.5 rounded-full bg-[#0B0F13] border border-white/[0.04]"></div>
    </div>

    <!-- Actions Area -->
    <div class="p-6 pt-2 space-y-3 bg-[#0B0F13]/40">
      <div class="flex gap-3">
        <button
          @click="$emit('reset')"
          type="button"
          class="flex-1 px-4 py-3 rounded-2xl text-xs font-bold text-slate-400 hover:text-white border border-white/[0.08] hover:bg-white/[0.04] transition-all text-center uppercase tracking-widest"
          data-test="reset-button"
        >
          Reset
        </button>
        <button
          @click="$emit('submit')"
          :disabled="submitting || !isFormValid"
          type="button"
          class="flex-[2] flex items-center justify-center gap-2 px-6 py-3.5 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-400 text-xs font-bold text-[#0B0F13] uppercase tracking-widest shadow-lg shadow-emerald-500/20 transition-all hover:shadow-[0_8px_30px_rgba(16,185,129,0.35)] disabled:opacity-40 disabled:cursor-not-allowed hover:-translate-y-0.5 active:translate-y-0"
          data-test="submit-walk-in"
        >
          <span v-if="submitting" class="h-4 w-4 animate-spin rounded-full border-2 border-[#0B0F13]/30 border-t-[#0B0F13]"></span>
          <CheckBadgeIcon v-else class="h-4 w-4" />
          {{ submitting ? 'Booking...' : 'Confirm Walk-In' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { TicketIcon, CheckBadgeIcon } from '@heroicons/vue/24/outline';

defineProps({
  form: {
    type: Object,
    required: true,
  },
  currentDateFormatted: {
    type: String,
    required: true,
  },
  selectedServiceName: {
    type: String,
    default: '',
  },
  selectedServicePrice: {
    type: Number,
    default: 0,
  },
  isFormValid: {
    type: Boolean,
    default: false,
  },
  submitting: {
    type: Boolean,
    default: false,
  },
});

defineEmits(['reset', 'submit']);
</script>
