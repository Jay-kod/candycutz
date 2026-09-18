<template>
  <transition name="fade">
    <div v-if="booking" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-[#050505]/90 backdrop-blur-md" @click="$emit('close')"></div>
      <div class="relative w-full max-w-lg rounded-[2rem] border border-white/10 bg-[#151515] shadow-[0_30px_100px_rgba(0,0,0,0.8)] overflow-hidden animate-slide-up p-8 text-white z-10">
        <div class="flex justify-between items-start mb-6">
          <h3 class="text-2xl font-display font-bold text-white">Booking Details</h3>
          <button @click="$emit('close')" class="p-2 bg-white/5 hover:bg-white/10 rounded-full transition-colors border border-white/5 text-white/50 hover:text-white">
            <XMarkIcon class="h-5 w-5" />
          </button>
        </div>

        <div class="space-y-1 bg-black/30 rounded-2xl border border-white/[0.05] p-2">
          <div class="flex justify-between items-center px-4 py-3 border-b border-white/[0.05]">
            <span class="text-white/40 uppercase tracking-wider text-[10px] font-bold">Client</span>
            <span class="font-bold text-sm">{{ booking.customer?.name || booking.client_name }} (ID: {{ String(booking.id).padStart(5, '0') }})</span>
          </div>
          <div class="flex justify-between items-center px-4 py-3 border-b border-white/[0.05]">
            <span class="text-white/40 uppercase tracking-wider text-[10px] font-bold">Service</span>
            <span class="font-bold text-sm">{{ booking.service?.name || 'General Booking' }}</span>
          </div>
          <div class="flex justify-between items-center px-4 py-3 border-b border-white/[0.05]">
            <span class="text-white/40 uppercase tracking-wider text-[10px] font-bold">Price</span>
            <span class="font-bold text-emerald-400 text-sm">₦{{ (booking.service?.price || 0).toLocaleString() }}</span>
          </div>
          <div class="flex justify-between items-center px-4 py-3 border-b border-white/[0.05]">
            <span class="text-white/40 uppercase tracking-wider text-[10px] font-bold">Provider</span>
            <span class="font-medium text-admin text-sm">{{ booking.barber?.name || 'Any Barber' }}</span>
          </div>
          <div class="flex justify-between items-center px-4 py-3 border-b border-white/[0.05]">
            <span class="text-white/40 uppercase tracking-wider text-[10px] font-bold">Schedule</span>
            <span class="font-medium text-sm">{{ formatDate(booking.appointment_date) }} at {{ booking.appointment_time }}</span>
          </div>
          <div class="flex justify-between items-center px-4 py-3 border-b border-white/[0.05]">
            <span class="text-white/40 uppercase tracking-wider text-[10px] font-bold">Type</span>
            <span v-if="booking.booking_type === 'walk_in'" class="font-bold text-sm text-purple-400">Walk-In</span>
            <span v-else class="font-bold text-sm text-cyan-400">Online</span>
          </div>
          <div class="flex justify-between items-center px-4 py-3 border-b border-white/[0.05]">
            <span class="text-white/40 uppercase tracking-wider text-[10px] font-bold">Status</span>
            <span class="px-2.5 py-1 rounded-full text-[9px] font-bold uppercase tracking-widest border" :class="statusClass(booking.status)">{{ booking.status }}</span>
          </div>
          <div class="flex justify-between items-center px-4 py-3">
            <span class="text-white/40 uppercase tracking-wider text-[10px] font-bold">Payment</span>
            <div class="flex items-center gap-2">
              <span v-if="booking.payment_method" class="text-[10px] font-bold uppercase text-white/60">{{ booking.payment_method === 'pos' ? 'POS' : booking.payment_method }}</span>
              <span class="px-2.5 py-1 rounded-full text-[9px] font-bold uppercase tracking-widest border bg-[#111]" :class="paymentStatusClass(booking.payment_status)">{{ (booking.payment_status || 'Unpaid').replace('_', ' ') }}</span>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="mt-6 flex flex-wrap gap-3">
          <template v-if="booking.status === 'pending'">
            <button v-if="booking.receipt_image && booking.payment_status === 'awaiting_verification'" @click="$emit('view-receipt', booking)" class="flex-1 rounded-xl bg-blue-500/10 border border-blue-500/20 py-3.5 text-xs font-bold uppercase tracking-widest text-blue-400 hover:bg-blue-500 hover:text-white transition-all flex items-center justify-center gap-2 shadow-sm">
              <DocumentTextIcon class="h-4 w-4" /> Verify Receipt
            </button>
            <button v-else-if="!booking.receipt_image || booking.payment_status !== 'awaiting_verification'" @click="$emit('approve', booking.id)" class="flex-1 rounded-xl bg-emerald-500/10 border border-emerald-500/20 py-3.5 text-xs font-bold uppercase tracking-widest text-emerald-400 hover:bg-emerald-500 hover:text-white transition-all flex items-center justify-center gap-2 shadow-sm">
              <CheckIcon class="h-4 w-4" /> Approve Booking
            </button>
            <button @click="$emit('cancel', booking.id)" class="px-5 rounded-xl border border-red-500/20 text-red-400 hover:bg-red-500 hover:text-white hover:border-red-500 transition-all flex items-center justify-center" title="Cancel Booking">
              <XMarkIcon class="h-5 w-5" />
            </button>
          </template>
          <template v-else-if="(booking.status === 'confirmed' || booking.status === 'completed') && booking.receipt_image">
            <button @click="$emit('view-receipt', booking)" class="flex-1 rounded-xl bg-white/5 border border-white/10 py-3.5 text-xs font-bold uppercase tracking-widest text-white/60 hover:bg-white/10 hover:text-white transition-all flex items-center justify-center gap-2">
              <DocumentTextIcon class="h-4 w-4" /> View Receipt
            </button>
            <button v-if="booking.status === 'confirmed'" @click="$emit('cancel', booking.id)" class="px-5 rounded-xl border border-red-500/20 text-red-400 hover:bg-red-500 hover:text-white hover:border-red-500 transition-all flex items-center justify-center" title="Cancel Booking">
              <XMarkIcon class="h-5 w-5" />
            </button>
          </template>
          <button v-else @click="$emit('close')" class="flex-1 rounded-xl bg-white/5 border border-white/10 py-3.5 text-xs font-bold uppercase tracking-widest text-white hover:bg-white/10 transition-all">
            Close Details
          </button>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { XMarkIcon, DocumentTextIcon, CheckIcon } from '@heroicons/vue/24/outline';

defineProps({
  booking: Object
});

defineEmits(['close', 'approve', 'cancel', 'view-receipt']);

function formatDate(dateStr) {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

const statusClass = (status) => {
  const map = {
    completed: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
    confirmed: 'bg-blue-500/10 text-blue-400 border-blue-500/20',
    pending: 'bg-amber-500/10 text-amber-400 border-amber-500/20',
    cancelled: 'bg-red-500/10 text-red-400 border-red-500/20',
    no_show: 'bg-purple-500/10 text-purple-400 border-purple-500/20'
  };
  return map[status] || 'bg-white/5 text-white/50 border-white/10';
};

const paymentStatusClass = (status) => {
  const map = {
    verified: 'text-emerald-400 border-emerald-500/30',
    approved: 'text-emerald-400 border-emerald-500/30',
    successful: 'text-emerald-400 border-emerald-500/30',
    awaiting_verification: 'text-amber-400 border-amber-500/30',
    pending: 'text-amber-400 border-amber-500/30',
    rejected: 'text-red-400 border-red-500/30',
    failed: 'text-red-400 border-red-500/30'
  };
  return map[status] || 'text-white/40 border-white/10';
};
</script>

<style scoped>
.animate-slide-up { animation: slideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
@keyframes slideUp {
  from { opacity: 0; transform: translateY(30px) scale(0.95); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
