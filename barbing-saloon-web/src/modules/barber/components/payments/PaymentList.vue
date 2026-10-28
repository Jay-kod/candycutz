<template>
  <div class="rounded-3xl border border-white/[0.08] bg-[#12161C] overflow-hidden shadow-2xl">
    <!-- Table Header (Desktop) -->
    <div class="hidden md:grid grid-cols-[1.2fr_1fr_0.8fr_auto] gap-4 border-b border-white/[0.08] px-6 py-4 text-[10px] uppercase tracking-widest text-slate-400 font-bold bg-[#0B0F13]/50">
      <span>Customer & Service</span>
      <span>Appointment Window</span>
      <span>Amount & Status</span>
      <span class="text-right">Verification Action</span>
    </div>

    <!-- Empty State -->
    <div v-if="paymentsList.length === 0" class="px-6 py-20 text-center">
      <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-500/10 border border-emerald-500/20 mb-4">
        <CheckBadgeIcon class="h-7 w-7 text-emerald-400" />
      </div>
      <h3 class="text-base font-display font-bold text-white mb-1">All Caught Up!</h3>
      <p class="text-xs text-slate-400 max-w-sm mx-auto">
        There are no customer payment receipts awaiting your review at this time.
      </p>
    </div>

    <!-- Rows -->
    <div v-else class="divide-y divide-white/[0.06]">
      <div
        v-for="booking in paymentsList"
        :key="booking.id"
        class="group md:grid md:grid-cols-[1.2fr_1fr_0.8fr_auto] md:items-center gap-4 px-6 py-5 transition-colors hover:bg-white/[0.02]"
        data-test="payment-row"
      >
        <!-- Customer & Service -->
        <div class="flex items-center gap-3">
          <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-emerald-500/10 text-emerald-400 text-xs font-bold border border-emerald-500/20">
            {{ getInitials(booking.customer?.name || booking.customer_name || booking.client_name) }}
          </div>
          <div class="min-w-0">
            <p class="text-sm font-bold text-white truncate">
              {{ booking.customer?.name || booking.customer_name || booking.client_name || 'Guest Client' }}
            </p>
            <p class="text-xs text-slate-400 truncate">
              {{ booking.service?.name || booking.service_name || 'Saloon Service' }}
            </p>
          </div>
        </div>

        <!-- Date & Slot -->
        <div class="mt-2 md:mt-0">
          <p class="text-xs text-slate-300 font-medium">
            {{ formatDate(booking.appointment_date) }}
          </p>
          <div class="flex items-center gap-1.5 mt-0.5 text-slate-400 text-[11px]">
            <ClockIcon class="h-3.5 w-3.5 text-emerald-400/70" />
            <span>{{ booking.appointment_time || 'Immediate' }}</span>
          </div>
        </div>

        <!-- Amount & Status Badge -->
        <div class="mt-2 md:mt-0 flex flex-col gap-1 items-start">
          <span class="text-sm font-bold text-emerald-400 font-mono">
            ₦{{ Number(booking.service?.price || booking.price || 0).toLocaleString() }}
          </span>
          <span
            class="inline-flex items-center rounded-md px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider border"
            :class="getStatusClasses(booking.payment_status || booking.status)"
          >
            {{ formatStatus(booking.payment_status || booking.status) }}
          </span>
        </div>

        <!-- Actions -->
        <div class="mt-4 md:mt-0 flex items-center justify-end gap-3 shrink-0">
          <div v-if="booking.verified_by" class="flex flex-col items-end gap-1">
            <span class="text-[9px] font-bold text-emerald-400 border border-emerald-500/20 bg-emerald-500/10 px-2 py-0.5 rounded uppercase tracking-wider">
              Verified by {{ booking.verified_by === 'admin' ? 'Admin' : 'You' }}
            </span>
            <button
              @click="$emit('view-receipt', booking)"
              class="text-[11px] text-slate-400 hover:text-emerald-400 transition-colors"
            >
              Inspect Receipt
            </button>
          </div>

          <button
            v-else
            @click="$emit('view-receipt', booking)"
            class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 px-3.5 py-2 text-xs font-bold text-emerald-400 hover:bg-emerald-500 hover:text-[#0B0F13] transition-all shadow-sm hover:shadow-[0_0_15px_rgba(16,185,129,0.3)]"
            data-test="view-receipt-btn"
          >
            <DocumentTextIcon class="h-3.5 w-3.5" />
            Inspect Receipt
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import {
  ClockIcon,
  DocumentTextIcon,
  CheckBadgeIcon,
} from '@heroicons/vue/24/outline';

defineProps({
  paymentsList: {
    type: Array,
    default: () => [],
  },
});

defineEmits(['view-receipt']);

function getInitials(name) {
  if (!name) return 'CC';
  return name
    .split(' ')
    .map((w) => w[0])
    .join('')
    .substring(0, 2)
    .toUpperCase();
}

function formatDate(dateStr) {
  if (!dateStr) return 'Today';
  const d = new Date(dateStr);
  return d.toLocaleDateString('en-US', {
    weekday: 'short',
    month: 'short',
    day: 'numeric',
  });
}

function formatStatus(status) {
  if (!status) return 'Pending Review';
  if (status === 'awaiting_verification') return 'Awaiting Verification';
  return status.replace(/_/g, ' ');
}

function getStatusClasses(status) {
  const s = (status || '').toLowerCase();
  if (s === 'completed' || s === 'successful' || s === 'verified') {
    return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20';
  }
  if (s === 'awaiting_verification' || s === 'pending') {
    return 'bg-amber-500/10 text-amber-400 border-amber-500/20';
  }
  return 'bg-red-500/10 text-red-400 border-red-500/20';
}
</script>
