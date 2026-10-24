<template>
  <transition name="fade">
    <div v-if="booking" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-[#050505]/85 backdrop-blur-md" @click="$emit('close')"></div>
      
      <div class="relative w-full max-w-lg rounded-3xl border border-white/[0.1] bg-[#12161C] shadow-[0_30px_100px_rgba(0,0,0,0.85)] overflow-hidden animate-slide-up p-6 sm:p-8 text-white z-10">
        <!-- Technical Ambient Accent -->
        <div class="absolute -top-16 -right-16 h-40 w-40 rounded-full bg-admin/10 blur-3xl pointer-events-none"></div>

        <!-- Header -->
        <div class="flex justify-between items-start mb-6 pb-4 border-b border-white/[0.08] relative z-10">
          <div>
            <div class="flex items-center gap-2 mb-1">
              <span class="text-[10px] font-mono uppercase tracking-wider text-admin font-bold">
                {{ booking.booking_reference || `CC-${String(booking.id).padStart(4, '0')}` }}
              </span>
              <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider border" :class="statusClass(booking.status)">
                {{ booking.status }}
              </span>
            </div>
            <h3 class="text-xl sm:text-2xl font-display font-bold text-white">Booking Details</h3>
          </div>
          <button 
            @click="$emit('close')" 
            class="p-2 bg-white/5 hover:bg-white/10 rounded-full transition-colors border border-white/5 text-white/50 hover:text-white"
            title="Close"
          >
            <XMarkIcon class="h-5 w-5" />
          </button>
        </div>

        <!-- Details Grid -->
        <div class="space-y-2.5 bg-black/30 rounded-2xl border border-white/[0.06] p-4 text-xs">
          <!-- Client Details -->
          <div class="flex justify-between items-center py-1.5 border-b border-white/[0.04]">
            <span class="text-white/40 uppercase tracking-wider text-[10px] font-semibold">Client Name</span>
            <span class="font-bold text-white text-sm">{{ booking.customer?.name || booking.client_name }}</span>
          </div>

          <div v-if="booking.client_phone || booking.customer?.phone" class="flex justify-between items-center py-1.5 border-b border-white/[0.04]">
            <span class="text-white/40 uppercase tracking-wider text-[10px] font-semibold">Phone Number</span>
            <a 
              :href="`tel:${booking.client_phone || booking.customer?.phone}`" 
              class="font-mono text-admin hover:underline flex items-center gap-1"
            >
              <PhoneIcon class="h-3 w-3" />
              {{ booking.client_phone || booking.customer?.phone }}
            </a>
          </div>

          <!-- Service Details -->
          <div class="flex justify-between items-center py-1.5 border-b border-white/[0.04]">
            <span class="text-white/40 uppercase tracking-wider text-[10px] font-semibold">Service</span>
            <div class="text-right">
              <p class="font-bold text-white">{{ booking.service?.name || 'General Booking' }}</p>
              <p v-if="booking.service?.duration_minutes" class="text-[10px] text-white/40">{{ booking.service.duration_minutes }} minutes</p>
            </div>
          </div>

          <!-- Service Fee -->
          <div class="flex justify-between items-center py-1.5 border-b border-white/[0.04]">
            <span class="text-white/40 uppercase tracking-wider text-[10px] font-semibold">Amount Due</span>
            <span class="font-bold text-emerald-400 font-mono text-sm">
              ₦{{ (booking.total_price || booking.grand_total || booking.service?.price || 0).toLocaleString() }}
            </span>
          </div>

          <!-- Stylist & Chair -->
          <div class="flex justify-between items-center py-1.5 border-b border-white/[0.04]">
            <span class="text-white/40 uppercase tracking-wider text-[10px] font-semibold">Stylist / Chair</span>
            <span class="font-medium text-admin">💈 {{ booking.barber?.name || 'First Available Stylist' }}</span>
          </div>

          <!-- Schedule -->
          <div class="flex justify-between items-center py-1.5 border-b border-white/[0.04]">
            <span class="text-white/40 uppercase tracking-wider text-[10px] font-semibold">Scheduled Time</span>
            <span class="font-medium text-white/90">{{ formatDate(booking.appointment_date) }} at {{ booking.appointment_time }}</span>
          </div>

          <!-- Intake Channel -->
          <div class="flex justify-between items-center py-1.5 border-b border-white/[0.04]">
            <span class="text-white/40 uppercase tracking-wider text-[10px] font-semibold">Channel</span>
            <span v-if="booking.booking_type === 'walk_in' || booking.source === 'walk_in'" class="font-bold text-amber-400">Walk-In Intake</span>
            <span v-else-if="booking.source === 'app'" class="font-bold text-purple-400">Mobile App</span>
            <span v-else class="font-bold text-sky-400">Website Booking</span>
          </div>

          <!-- Payment Details -->
          <div class="flex justify-between items-center py-1.5">
            <span class="text-white/40 uppercase tracking-wider text-[10px] font-semibold">Payment Status</span>
            <div class="flex items-center gap-2">
              <span v-if="booking.payment_method" class="text-[10px] font-bold uppercase text-white/60">
                {{ booking.payment_method === 'pos' ? 'POS' : booking.payment_method }}
              </span>
              <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider border" :class="paymentStatusClass(booking.payment_status)">
                {{ (booking.payment_status || 'unpaid').replace('_', ' ') }}
              </span>
            </div>
          </div>
        </div>

        <!-- Notes if present -->
        <div v-if="booking.notes" class="mt-3 p-3 rounded-xl bg-white/[0.03] border border-white/[0.06] text-xs text-white/60">
          <p class="text-[10px] font-bold uppercase text-white/40 mb-1">Intake Notes:</p>
          <p>{{ booking.notes }}</p>
        </div>

        <!-- Action CTAs -->
        <div class="mt-6 flex flex-wrap gap-2.5">
          <template v-if="booking.status === 'pending'">
            <button 
              v-if="booking.receipt_image && booking.payment_status === 'awaiting_verification'" 
              @click="$emit('view-receipt', booking)" 
              class="flex-1 rounded-xl bg-blue-500/15 border border-blue-500/30 py-3 text-xs font-bold uppercase tracking-wider text-blue-400 hover:bg-blue-500 hover:text-white transition-all flex items-center justify-center gap-2"
            >
              <DocumentTextIcon class="h-4 w-4" /> Verify Receipt
            </button>
            <button 
              v-else 
              @click="$emit('approve', booking.id)" 
              class="flex-1 rounded-xl bg-emerald-500/15 border border-emerald-500/30 py-3 text-xs font-bold uppercase tracking-wider text-emerald-400 hover:bg-emerald-500 hover:text-white transition-all flex items-center justify-center gap-2"
            >
              <CheckIcon class="h-4 w-4" /> Approve Booking
            </button>
            <button 
              @click="$emit('cancel', booking.id)" 
              class="px-4 rounded-xl border border-red-500/30 text-red-400 hover:bg-red-500 hover:text-white transition-all flex items-center justify-center" 
              title="Cancel Booking"
            >
              <XMarkIcon class="h-5 w-5" />
            </button>
          </template>
          <template v-else-if="(booking.status === 'confirmed' || booking.status === 'completed') && booking.receipt_image">
            <button 
              @click="$emit('view-receipt', booking)" 
              class="flex-1 rounded-xl bg-white/5 border border-white/10 py-3 text-xs font-bold uppercase tracking-wider text-white/80 hover:bg-white/10 hover:text-white transition-all flex items-center justify-center gap-2"
            >
              <DocumentTextIcon class="h-4 w-4" /> View Receipt
            </button>
            <button 
              v-if="booking.status === 'confirmed'" 
              @click="$emit('cancel', booking.id)" 
              class="px-4 rounded-xl border border-red-500/30 text-red-400 hover:bg-red-500 hover:text-white transition-all flex items-center justify-center" 
              title="Cancel Booking"
            >
              <XMarkIcon class="h-5 w-5" />
            </button>
          </template>
          <button 
            v-else 
            @click="$emit('close')" 
            class="flex-1 rounded-xl bg-white/5 border border-white/10 py-3 text-xs font-bold uppercase tracking-wider text-white/80 hover:bg-white/10 transition-all"
          >
            Close Details
          </button>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { XMarkIcon, DocumentTextIcon, CheckIcon, PhoneIcon } from '@heroicons/vue/24/outline';

defineProps({
  booking: Object
});

defineEmits(['close', 'approve', 'cancel', 'view-receipt']);

function formatDate(dateStr) {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
}

function statusClass(status) {
  const map = {
    completed: 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30',
    confirmed: 'bg-blue-500/15 text-blue-400 border-blue-500/30',
    pending: 'bg-amber-500/15 text-amber-400 border-amber-500/30',
    cancelled: 'bg-red-500/15 text-red-400 border-red-500/30',
    no_show: 'bg-purple-500/15 text-purple-400 border-purple-500/30'
  };
  return map[status] || 'bg-white/5 text-white/50 border-white/10';
}

function paymentStatusClass(status) {
  const map = {
    verified: 'text-emerald-400 border-emerald-500/30',
    paid: 'text-emerald-400 border-emerald-500/30',
    approved: 'text-emerald-400 border-emerald-500/30',
    successful: 'text-emerald-400 border-emerald-500/30',
    awaiting_verification: 'text-amber-400 border-amber-500/30',
    pending: 'text-amber-400 border-amber-500/30',
    rejected: 'text-red-400 border-red-500/30',
    failed: 'text-red-400 border-red-500/30'
  };
  return map[status] || 'text-white/40 border-white/10';
}
</script>

<style scoped>
.animate-slide-up { animation: slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
@keyframes slideUp {
  from { opacity: 0; transform: translateY(20px) scale(0.97); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.25s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>