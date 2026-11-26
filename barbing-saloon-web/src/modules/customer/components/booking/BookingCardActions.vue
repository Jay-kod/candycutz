<template>
  <div>
    <!-- Bank Transfer Payment Details Box -->
    <div
      v-if="showBankDetails"
      class="mt-3.5 sm:mt-4 rounded-lg md:rounded-xl border border-[var(--color-accent)]/30 bg-[var(--color-accent)]/[0.05] p-3 sm:p-4"
    >
      <div
        class="flex flex-wrap items-center justify-between gap-2 mb-2.5 pb-2 border-b border-[var(--color-border)] md:border-slate-200 md:dark:border-white/[0.08]"
      >
        <div class="flex items-center gap-2 min-w-0">
          <BuildingLibraryIcon class="h-4 w-4 text-[var(--color-accent)] shrink-0" />
          <p class="text-[10px] font-bold uppercase tracking-widest text-[var(--color-accent)] text-left-forced">
            Transfer Payment To
          </p>
        </div>
        <span
          class="text-xs font-bold tabular-nums text-[var(--color-text-primary)] md:text-slate-900 md:dark:text-white bg-[var(--color-accent)]/15 border border-[var(--color-accent)]/30 px-2 py-0.5 rounded-md md:rounded-full shrink-0"
        >
          {{ formattedAmount }}
        </span>
      </div>

      <div v-if="bankDetails" class="space-y-2 text-xs">
        <div class="flex items-start justify-between gap-3 text-[var(--color-text-secondary)]">
          <span class="shrink-0">Bank Name:</span>
          <span class="text-[var(--color-text-primary)] md:text-slate-900 md:dark:text-white font-medium text-right min-w-0 break-words">
            {{ bankDetails.bank_name }}
          </span>
        </div>
        <div class="flex items-start justify-between gap-3 text-[var(--color-text-secondary)]">
          <span class="shrink-0">Account Name:</span>
          <span class="text-[var(--color-text-primary)] md:text-slate-900 md:dark:text-white font-medium text-right min-w-0 break-words">
            {{ bankDetails.account_name }}
          </span>
        </div>
        <div
          class="flex flex-wrap items-center justify-between gap-2 bg-[var(--color-bg-tertiary)] md:bg-slate-100 md:dark:bg-black/40 rounded-lg py-2 px-3 border border-[var(--color-border)] md:border-slate-200 md:dark:border-white/[0.08] mt-1.5"
        >
          <span class="text-[var(--color-text-secondary)] text-[11px] shrink-0">Account Number:</span>
          <div class="flex items-center gap-1.5 min-w-0">
            <span class="font-mono text-sm font-bold tracking-wider tabular-nums text-[var(--color-accent)] select-all">
              {{ bankDetails.account_number }}
            </span>
            <button
              type="button"
              @click="$emit('copy', bankDetails.account_number)"
              class="min-h-[36px] min-w-[36px] px-2 py-1 text-[var(--color-accent)] hover:bg-[var(--color-accent)]/15 rounded-md transition-colors inline-flex items-center justify-center gap-1 text-[11px] font-bold shrink-0"
              title="Copy Account Number"
              aria-label="Copy Account Number"
            >
              <DocumentDuplicateIcon class="h-3.5 w-3.5 shrink-0" />
              <span class="sm:hidden">Copy</span>
            </button>
          </div>
        </div>
      </div>

      <p v-else class="text-xs text-amber-600 dark:text-amber-400/90 text-center py-1">
        Payment details configured. Click below to view and upload receipt.
      </p>
    </div>

    <!-- Verification Code Box -->
    <div
      v-if="booking.status === 'confirmed' && booking.verification_code"
      class="mt-3.5 sm:mt-4 rounded-lg md:rounded-xl border border-emerald-500/30 bg-emerald-500/[0.06] p-3 sm:p-4"
    >
      <div class="flex flex-wrap items-center justify-between gap-1.5 mb-2">
        <p class="text-[10px] font-bold uppercase tracking-widest text-emerald-600 dark:text-emerald-400 text-left-forced">
          Service Verification Code
        </p>
        <span class="text-[10px] text-emerald-600/80 dark:text-emerald-400/80">Present at checkout</span>
      </div>
      <div
        class="flex items-center justify-between gap-2 bg-emerald-50 dark:bg-black/40 rounded-lg py-2 px-3 border border-emerald-500/20"
      >
        <span class="font-mono text-base sm:text-lg font-bold tracking-[0.14em] sm:tracking-widest text-emerald-700 dark:text-emerald-300 uppercase break-all select-all">
          {{ booking.verification_code }}
        </span>
        <button
          type="button"
          @click="$emit('copy', booking.verification_code)"
          class="min-h-[38px] px-2.5 py-1.5 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-500/20 rounded-md transition-colors shrink-0 inline-flex items-center gap-1.5 text-xs font-bold"
          title="Copy Code"
          aria-label="Copy verification code"
        >
          <DocumentDuplicateIcon class="h-4 w-4 shrink-0" />
          <span class="sm:hidden">Copy</span>
        </button>
      </div>
    </div>

    <!-- Actions Footer -->
    <div
      v-if="['draft', 'pending', 'confirmed'].includes(booking.status)"
      class="mt-4 sm:mt-6 pt-3.5 sm:pt-4 border-t border-[var(--color-border)] md:border-slate-100 md:dark:border-white/[0.06] flex flex-col gap-2.5"
    >
      <!-- Draft Unpaid Action Group -->
      <template v-if="booking.status === 'draft'">
        <div class="space-y-2">
          <RouterLink
            :to="`/customer/dashboard/payment/${booking.id}`"
            class="w-full min-h-[44px] px-3.5 py-2.5 rounded-lg md:rounded-xl bg-[var(--color-accent)] text-[var(--color-accent-foreground)] md:bg-gradient-to-r md:from-amber-500 md:to-amber-600 md:text-slate-950 hover:brightness-110 text-sm md:text-xs font-bold transition-all flex items-center justify-center gap-2 text-center leading-snug shadow-none md:shadow-md md:shadow-amber-500/20 active:scale-[0.98]"
          >
            <CreditCardIcon class="h-4 w-4 shrink-0" />
            <span>Complete Payment ({{ formattedAmount }})</span>
          </RouterLink>
          <button
            type="button"
            class="w-full min-h-[44px] sm:min-h-[40px] px-3.5 py-2 rounded-lg md:rounded-xl border border-red-500/30 bg-red-500/10 hover:bg-red-500/20 text-sm md:text-xs font-semibold text-red-600 dark:text-red-400 transition-all flex items-center justify-center gap-1.5 cursor-pointer"
            @click="$emit('discard-draft', booking.id)"
          >
            <TrashIcon class="h-4 w-4 shrink-0" />
            <span>Discard Draft (Release Slot)</span>
          </button>
        </div>
      </template>

      <!-- Pending Verification Action Group -->
      <template v-else-if="booking.status === 'pending'">
        <div
          v-if="!booking.payment_status || booking.payment_status === 'pending' || booking.payment_status === 'unpaid'"
          class="space-y-2"
        >
          <RouterLink
            :to="`/customer/dashboard/payment/${booking.id}`"
            class="w-full min-h-[44px] px-3.5 py-2.5 rounded-lg md:rounded-xl bg-[var(--color-accent)] text-[var(--color-accent-foreground)] md:bg-[#FDB913] md:hover:bg-[#F2AC07] md:text-slate-950 text-sm md:text-xs font-bold transition-all flex items-center justify-center gap-2 text-center leading-snug shadow-none md:shadow-md md:shadow-[#FDB913]/20 active:scale-[0.98]"
          >
            <CreditCardIcon class="h-4 w-4 shrink-0" />
            <span>Pay Now • Transfer / USSD ({{ formattedAmount }})</span>
          </RouterLink>
          <button
            v-if="!isUploaderOpen"
            type="button"
            class="w-full min-h-[44px] sm:min-h-[40px] px-3.5 py-2 rounded-lg md:rounded-xl border border-[var(--color-border)] md:dark:border-white/10 bg-[var(--color-bg-tertiary)] md:dark:bg-white/[0.04] hover:border-[var(--color-accent)]/40 hover:bg-[var(--color-bg-tertiary)]/80 md:dark:hover:bg-white/[0.08] text-sm md:text-xs font-semibold text-[var(--color-text-primary)] md:dark:text-white/85 transition-all flex items-center justify-center gap-1.5 cursor-pointer"
            @click="$emit('start-payment', booking)"
          >
            <span>Upload Manual Receipt Slip</span>
          </button>
          <ReceiptUploader
            v-else
            :appointmentId="booking.id"
            :initialBankDetails="bankDetails"
            @uploaded="$emit('receipt-uploaded')"
          />
        </div>
        <div
          v-else-if="booking.payment_status === 'awaiting_verification'"
          class="w-full min-h-[42px] px-3.5 py-2 rounded-lg md:rounded-xl bg-blue-500/10 text-xs font-bold text-blue-600 dark:text-blue-400 border border-blue-500/25 text-center flex items-center justify-center gap-2"
        >
          <ArrowPathIcon class="h-4 w-4 animate-spin shrink-0" />
          <span>Receipt Under Review</span>
        </div>
      </template>

      <button
        v-if="hasPaidReceipt"
        type="button"
        @click="$emit('open-receipt', booking)"
        class="w-full min-h-[44px] sm:min-h-[40px] px-3.5 py-2.5 rounded-lg md:rounded-xl border border-amber-500/35 bg-amber-500/10 hover:bg-amber-500/20 text-sm md:text-xs font-bold text-amber-700 dark:text-amber-300 transition-all flex items-center justify-center gap-2 cursor-pointer"
      >
        <ReceiptPercentIcon class="h-4 w-4 shrink-0" />
        <span>View Official Receipt</span>
      </button>

      <button
        v-if="booking.status === 'confirmed' && booking.can_extend_grace"
        type="button"
        @click="$emit('extend-grace', booking)"
        class="w-full min-h-[44px] sm:min-h-[40px] px-3.5 py-2.5 rounded-lg md:rounded-xl border border-amber-500/35 bg-amber-500/10 hover:bg-amber-500/20 text-sm md:text-xs font-bold text-amber-700 dark:text-amber-300 transition-all flex items-center justify-center gap-2 cursor-pointer"
      >
        <span>Running Late? Extend Grace Once</span>
      </button>

      <RouterLink
        v-if="booking.status === 'confirmed' && booking.appointment_type !== 'home_service'"
        to="/customer/dashboard/directions"
        class="w-full min-h-[44px] sm:min-h-[40px] px-3.5 py-2.5 rounded-lg md:rounded-xl border border-emerald-500/35 bg-emerald-500/10 hover:bg-emerald-500/20 text-sm md:text-xs font-bold text-emerald-700 dark:text-emerald-300 transition-all flex items-center justify-center gap-2"
      >
        <span>Direct Me to Shop</span>
      </RouterLink>

      <button
        type="button"
        class="w-full min-h-[44px] sm:min-h-[40px] px-3.5 py-2.5 rounded-lg md:rounded-xl bg-red-500/10 text-sm md:text-xs font-bold text-red-600 dark:text-red-400 hover:bg-red-500 hover:text-white transition-all border border-red-500/25 cursor-pointer"
        @click="$emit('cancel', booking.id)"
      >
        Cancel Booking
      </button>
    </div>

    <div
      v-else-if="booking.status === 'no_show' && booking.no_show_record && !booking.no_show_record.appealed"
      class="mt-4 sm:mt-6 pt-3.5 sm:pt-4 border-t border-[var(--color-border)] md:border-slate-100 md:dark:border-white/[0.06]"
    >
      <button
        type="button"
        @click="$emit('appeal-no-show', booking)"
        class="w-full min-h-[44px] sm:min-h-[40px] px-3.5 py-2.5 rounded-lg md:rounded-xl border border-purple-500/35 bg-purple-500/10 hover:bg-purple-500/20 text-sm md:text-xs font-bold text-purple-600 dark:text-purple-300 transition-all flex items-center justify-center gap-2 cursor-pointer"
      >
        <span>Appeal No-Show</span>
      </button>
    </div>

    <div
      v-else-if="booking.status === 'cancelled' && hasPaidReceipt && !booking.latest_refund_request && !booking.deposit_forfeited"
      class="mt-4 sm:mt-6 pt-3.5 sm:pt-4 border-t border-[var(--color-border)] md:border-slate-100 md:dark:border-white/[0.06]"
    >
      <button
        type="button"
        @click="$emit('request-refund', booking)"
        class="w-full min-h-[44px] sm:min-h-[40px] px-3.5 py-2.5 rounded-lg md:rounded-xl border border-sky-500/35 bg-sky-500/10 hover:bg-sky-500/20 text-sm md:text-xs font-bold text-sky-700 dark:text-sky-300 transition-all flex items-center justify-center gap-2 cursor-pointer"
      >
        <span>Request Compassionate Refund</span>
      </button>
    </div>

    <div
      v-else-if="booking.status === 'completed' && hasPaidReceipt"
      class="mt-4 sm:mt-6 pt-3.5 sm:pt-4 border-t border-[var(--color-border)] md:border-slate-100 md:dark:border-white/[0.06]"
    >
      <button
        type="button"
        @click="$emit('open-receipt', booking)"
        class="w-full min-h-[44px] sm:min-h-[40px] px-3.5 py-2.5 rounded-lg md:rounded-xl border border-amber-500/35 bg-amber-500/10 hover:bg-amber-500/20 text-sm md:text-xs font-bold text-amber-700 dark:text-amber-300 transition-all flex items-center justify-center gap-2 cursor-pointer"
      >
        <ReceiptPercentIcon class="h-4 w-4 shrink-0" />
        <span>View Official Receipt</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { RouterLink } from 'vue-router';
import ReceiptUploader from '@/core/components/ReceiptUploader.vue';
import { formatNairaAmount } from '@/core/utils';
import {
  ArrowPathIcon,
  BuildingLibraryIcon,
  CreditCardIcon,
  DocumentDuplicateIcon,
  ReceiptPercentIcon,
  TrashIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
  booking: { type: Object, required: true },
  bankDetails: { type: Object, default: null },
  showBankDetails: { type: Boolean, default: false },
  isUploaderOpen: { type: Boolean, default: false },
});

defineEmits([
  'copy',
  'discard-draft',
  'start-payment',
  'receipt-uploaded',
  'open-receipt',
  'extend-grace',
  'appeal-no-show',
  'request-refund',
  'cancel',
]);

const formattedAmount = computed(() => {
  return formatNairaAmount(props.booking?.grand_total || props.booking?.service?.price || 0);
});

const hasPaidReceipt = computed(() => {
  return Boolean(props.booking?.deposit_paid || props.booking?.payment_status === 'paid');
});
</script>
