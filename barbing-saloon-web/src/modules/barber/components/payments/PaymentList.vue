<template>
  <div class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] overflow-hidden shadow-sm dark:shadow-2xl">
    
    <!-- Top Filter Tabs Bar (if filterTabs prop provided) -->
    <div v-if="filterTabs && filterTabs.length" class="border-b border-slate-200 dark:border-white/[0.08] px-6 py-3.5 bg-slate-50 dark:bg-[#0B0F13]/60 flex flex-wrap items-center gap-2">
      <button
        v-for="tab in filterTabs"
        :key="tab.id"
        type="button"
        @click="$emit('update:currentTab', tab.id)"
        class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all"
        :class="currentTab === tab.id
          ? 'bg-gold text-obsidian font-bold shadow-md shadow-gold/20'
          : 'bg-slate-100 dark:bg-white/[0.03] text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-white/[0.07] hover:text-slate-900 dark:hover:text-white border border-slate-200 dark:border-white/[0.06]'"
      >
        <span>{{ tab.label }}</span>
        <span 
          class="px-1.5 py-0.5 rounded-full text-[10px] font-mono font-bold leading-none"
          :class="currentTab === tab.id ? 'bg-black/20 text-obsidian' : 'bg-slate-200 dark:bg-white/10 text-slate-700 dark:text-slate-400'"
        >
          {{ tab.count }}
        </span>
      </button>
    </div>

    <!-- Table Header (Desktop) -->
    <div class="hidden md:grid grid-cols-[1.2fr_1fr_0.9fr_auto] gap-4 border-b border-slate-200 dark:border-white/[0.08] px-6 py-4 text-[10px] uppercase tracking-widest text-slate-500 dark:text-slate-400 font-bold bg-slate-50 dark:bg-[#0B0F13]/40">
      <span>Customer & Service</span>
      <span>Appointment Window</span>
      <span>Amount & Method</span>
      <span class="text-right">Payment / Verification Action</span>
    </div>

    <!-- Empty State -->
    <div v-if="paymentsList.length === 0" class="px-6 py-20 text-center">
      <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-gold/10 border border-gold/20 mb-4">
        <CheckBadgeIcon class="h-7 w-7 text-gold" />
      </div>
      <h3 class="text-base font-display font-bold text-slate-900 dark:text-white mb-1">All Caught Up!</h3>
      <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto">
        There are no customer payment receipts or services matching this category at this time.
      </p>
    </div>

    <!-- Rows -->
    <div v-else class="divide-y divide-slate-100 dark:divide-white/[0.06]">
      <div
        v-for="booking in paymentsList"
        :key="booking.id"
        class="group md:grid md:grid-cols-[1.2fr_1fr_0.9fr_auto] md:items-center gap-4 px-6 py-5 transition-colors hover:bg-slate-50 dark:hover:bg-white/[0.02]"
        data-test="payment-row"
      >
        <!-- Customer & Service -->
        <div class="flex items-center gap-3">
          <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gold/10 text-amber-800 dark:text-gold text-xs font-bold border border-gold/20 shadow-inner">
            {{ getInitials(booking.customer?.name || booking.customer_name || booking.client_name) }}
          </div>
          <div class="min-w-0">
            <p class="text-sm font-bold text-slate-900 dark:text-white truncate">
              {{ booking.customer?.name || booking.customer_name || booking.client_name || 'Guest Client' }}
            </p>
            <p class="text-xs text-slate-600 dark:text-slate-300 truncate font-medium">
              {{ booking.service?.name || booking.service_name || 'Saloon Service' }}
            </p>
            <span v-if="booking.booking_reference" class="inline-block mt-0.5 text-[10px] font-mono text-amber-800 dark:text-gold/80 font-bold">
              {{ booking.booking_reference }}
            </span>
          </div>
        </div>

        <!-- Date & Slot -->
        <div class="mt-2 md:mt-0">
          <p class="text-xs text-slate-800 dark:text-slate-200 font-medium">
            {{ formatDate(booking.appointment_date) }}
          </p>
          <div class="flex items-center gap-1.5 mt-0.5 text-slate-500 dark:text-slate-400 text-[11px]">
            <ClockIcon class="h-3.5 w-3.5 text-gold/70" />
            <span>{{ booking.appointment_time || 'Immediate' }}</span>
          </div>
        </div>

        <!-- Amount & Method & Status Badge -->
        <div class="mt-2 md:mt-0 flex flex-col gap-1 items-start">
          <span class="text-sm font-bold text-amber-700 dark:text-gold font-mono">
            ₦{{ Number(booking.grand_total || booking.total_price || booking.service?.price || booking.price || 0).toLocaleString() }}
          </span>
          <div class="flex items-center gap-1.5 flex-wrap">
            <span
              class="inline-flex items-center rounded-md px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider border"
              :class="getStatusClasses(booking.payment_status || booking.status)"
            >
              {{ formatStatus(booking.payment_status || booking.status) }}
            </span>
            <span v-if="booking.payment_method" class="text-[9px] uppercase tracking-wider text-slate-600 dark:text-slate-400 font-medium bg-slate-100 dark:bg-white/[0.04] px-1.5 py-0.5 rounded border border-slate-200 dark:border-white/[0.06]">
              {{ formatMethod(booking.payment_method) }}
            </span>
          </div>
        </div>

        <!-- Actions -->
        <div class="mt-4 md:mt-0 flex items-center justify-end gap-3 shrink-0">
          <div v-if="booking.verified_by" class="flex flex-col items-end gap-1">
            <span class="text-[9px] font-bold text-emerald-700 dark:text-emerald-400 border border-emerald-500/20 bg-emerald-500/10 px-2 py-0.5 rounded uppercase tracking-wider">
              Verified by {{ booking.verified_by === 'admin' ? 'Admin' : 'You' }}
            </span>
            <button
              @click="$emit('view-receipt', booking)"
              class="text-[11px] text-slate-500 dark:text-slate-400 hover:text-gold transition-colors"
            >
              Inspect Receipt
            </button>
          </div>

          <!-- Has receipt to review or inspect -->
          <button
            v-else-if="hasReceipt(booking) || isAwaitingReview(booking)"
            @click="$emit('view-receipt', booking)"
            class="inline-flex items-center gap-1.5 rounded-xl bg-gold/10 border border-gold/20 px-3.5 py-2 text-xs font-bold text-amber-800 dark:text-gold hover:bg-gold hover:text-obsidian transition-all shadow-sm hover:shadow-[0_0_15px_rgba(255,153,0,0.3)]"
            data-test="view-receipt-btn"
          >
            <DocumentTextIcon class="h-3.5 w-3.5" />
            Inspect Receipt
          </button>

          <!-- Paid without receipt (e.g. online Paystack or venue cash) -->
          <div v-else-if="isPaid(booking)" class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1 rounded-xl bg-emerald-500/10 border border-emerald-500/20 px-3 py-1.5 text-xs font-bold text-emerald-700 dark:text-emerald-400">
              <CheckCircleIcon class="h-3.5 w-3.5" />
              <span>Paid & Confirmed</span>
            </span>
          </div>

          <!-- Pending in-shop / walk-in -> Collect at Venue -->
          <div v-else class="flex items-center gap-2">
            <button
              type="button"
              @click="$emit('collect-payment', booking)"
              class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-500/15 border border-emerald-500/30 px-3 py-1.5 text-xs font-bold text-emerald-700 dark:text-emerald-400 hover:bg-emerald-500 hover:text-obsidian transition-all shadow-sm"
              title="Collect cash or card at chair"
            >
              <BanknotesIcon class="h-3.5 w-3.5" />
              <span>Collect at Venue</span>
            </button>
            <button
              @click="$emit('view-receipt', booking)"
              class="inline-flex items-center gap-1 text-xs text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white px-2 py-1.5 transition-colors"
              data-test="view-receipt-btn"
            >
              <span>Inspect</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import {
  ClockIcon,
  CheckBadgeIcon,
  CheckCircleIcon,
  DocumentTextIcon,
  BanknotesIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
  paymentsList: {
    type: Array,
    default: () => [],
  },
  currentTab: {
    type: String,
    default: 'all',
  },
  filterTabs: {
    type: Array,
    default: () => [],
  },
});

defineEmits(['view-receipt', 'update:currentTab', 'collect-payment']);

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
  if (status === 'paid') return 'Paid & Settled';
  return status.replace(/_/g, ' ');
}

function formatMethod(method) {
  if (!method) return 'Manual Transfer';
  if (method === 'manual_transfer') return 'Bank Transfer';
  if (method === 'pay_at_venue') return 'At Venue';
  if (method === 'paystack') return 'Paystack';
  return method.replace(/_/g, ' ');
}

function getStatusClasses(status) {
  const s = (status || '').toLowerCase();
  if (s === 'completed' || s === 'successful' || s === 'verified' || s === 'paid' || s === 'confirmed') {
    return 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-500/20';
  }
  if (s === 'awaiting_verification' || s === 'under_review' || s === 'pending') {
    return 'bg-amber-500/10 text-amber-800 dark:text-amber-400 border-amber-500/20';
  }
  return 'bg-red-500/10 text-red-600 dark:text-red-400 border-red-500/20';
}

function hasReceipt(booking) {
  return Boolean(booking.receipt_image || booking.receipt_url || booking.payment?.receipt_url);
}

function isAwaitingReview(booking) {
  const st = (booking.payment_status || booking.status || '').toLowerCase();
  return st === 'awaiting_verification' || st === 'under_review';
}

function isPaid(booking) {
  const st = (booking.payment_status || booking.status || '').toLowerCase();
  return booking.deposit_paid || ['paid', 'confirmed', 'completed', 'successful', 'verified'].includes(st);
}
</script>
