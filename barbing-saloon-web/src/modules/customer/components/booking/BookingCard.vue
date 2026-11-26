<template>
  <article
    class="group relative flex flex-col justify-between rounded-xl md:rounded-3xl border border-[var(--color-border)] md:border-slate-200 md:dark:border-white/[0.08] bg-[var(--color-bg-secondary)] md:bg-white md:dark:bg-[var(--color-bg-secondary)] p-4 sm:p-5 md:p-6 shadow-none transition-all duration-300 hover:border-[var(--color-accent)]/35 md:hover:shadow-xl md:hover:shadow-[var(--color-accent)]/5 md:hover:-translate-y-1"
  >
    <div>
      <!-- Top Row: Reference, Status Badge & Delete Button -->
      <div
        class="flex items-center justify-between gap-2 mb-3.5 sm:mb-4 pb-3 sm:pb-4 border-b border-[var(--color-border)] md:border-slate-100 md:dark:border-white/[0.06]"
      >
        <div class="min-w-0 flex-1">
          <span class="text-[10px] uppercase tracking-widest text-[var(--color-text-muted)] font-mono block">
            Ref No.
          </span>
          <span class="font-mono text-xs font-bold text-[var(--color-text-primary)] md:text-slate-900 md:dark:text-white tracking-wider truncate block">
            {{ referenceLabel }}
          </span>
        </div>

        <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
          <span
            class="rounded-md md:rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider border inline-flex items-center gap-1.5 leading-none shrink-0"
            :class="statusBadgeClasses"
          >
            <span v-if="booking.status === 'draft'" class="h-1.5 w-1.5 rounded-full bg-amber-400 animate-ping"></span>
            {{ booking.status === 'draft' ? 'Draft • Unpaid' : booking.status }}
          </span>

          <button
            type="button"
            @click.stop="$emit('delete', booking.id)"
            class="min-h-[36px] min-w-[36px] inline-flex items-center justify-center rounded-lg text-slate-400 dark:text-white/40 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors border border-transparent hover:border-red-500/20 shrink-0"
            title="Remove from history"
            aria-label="Remove booking from history"
          >
            <TrashIcon class="h-4 w-4" />
          </button>
        </div>
      </div>

      <!-- Main Details -->
      <div class="flex gap-3.5 sm:gap-4 items-start">
        <div
          class="flex h-12 w-12 sm:h-14 sm:w-14 shrink-0 flex-col items-center justify-center rounded-lg md:rounded-xl bg-[var(--color-bg-tertiary)] md:bg-slate-100 md:dark:bg-white/[0.04] border border-[var(--color-border)] md:border-slate-200 md:dark:border-white/[0.08] text-[var(--color-accent)] shadow-none md:shadow-inner"
        >
          <span class="font-display text-lg sm:text-xl leading-none font-bold tabular-nums">
            {{ dayOfMonth }}
          </span>
          <span class="text-[9px] uppercase tracking-wider font-bold text-[var(--color-text-muted)] md:text-slate-500 md:dark:text-white/50 mt-0.5">
            {{ monthShort }}
          </span>
        </div>

        <div class="flex-1 min-w-0">
          <p
            class="font-display text-base sm:text-lg font-bold md:font-normal text-[var(--color-text-primary)] md:text-slate-900 md:dark:text-white group-hover:text-[var(--color-accent)] transition-colors text-left-forced break-words leading-snug md:truncate"
          >
            {{ booking.service?.name || 'Grooming Appointment' }}
          </p>

          <div class="mt-1.5 sm:mt-2 space-y-1.5 text-xs text-[var(--color-text-secondary)]">
            <p class="flex items-start gap-2 text-left-forced tabular-nums">
              <ClockIcon class="h-4 w-4 text-[var(--color-accent)] shrink-0 mt-0.5" />
              <span class="min-w-0">
                {{ formattedDate }} at
                <span class="text-[var(--color-text-primary)] md:text-slate-900 md:dark:text-white font-bold">
                  {{ booking.appointment_time }}
                </span>
              </span>
            </p>

            <p class="flex items-start gap-2 text-left-forced">
              <UserIcon class="h-4 w-4 text-[var(--color-accent)] shrink-0 mt-0.5" />
              <span class="min-w-0 break-words">
                With
                <strong class="text-[var(--color-text-primary)] md:text-slate-900 md:dark:text-white font-medium">
                  {{ booking.barber?.name || 'Assigned Barber' }}
                </strong>
              </span>
            </p>

            <p v-if="bookingAmount > 0" class="flex items-center gap-2 text-left-forced tabular-nums">
              <BanknotesIcon class="h-4 w-4 text-[var(--color-accent)] shrink-0" />
              <span>
                Total:
                <strong class="text-[var(--color-text-primary)] md:text-slate-900 md:dark:text-white font-bold">
                  {{ formattedPrice }}
                </strong>
              </span>
            </p>
          </div>
        </div>
      </div>

      <!-- Customer Notes -->
      <div
        v-if="booking.notes"
        class="mt-3.5 sm:mt-4 rounded-lg md:rounded-xl bg-[var(--color-bg-tertiary)]/60 md:bg-slate-50 md:dark:bg-white/[0.03] p-3 text-xs text-[var(--color-text-secondary)] border border-[var(--color-border)] md:border-slate-200 md:dark:border-white/[0.06] border-l-2 border-l-[var(--color-accent)] italic text-left-forced break-words"
      >
        "{{ booking.notes }}"
      </div>

      <!-- Grace Period Extended Banner -->
      <div
        v-if="booking.grace_extended_until && booking.status === 'confirmed'"
        class="mt-3 rounded-lg md:rounded-xl bg-amber-500/10 border border-amber-500/30 p-2.5 text-xs text-amber-600 dark:text-amber-300"
      >
        <p class="font-bold">Grace Period Active</p>
        <p class="text-[11px] opacity-90">Reason: {{ booking.grace_extension_reason }}</p>
      </div>

      <!-- No-Show Status & Appeal Banner -->
      <div
        v-if="booking.status === 'no_show'"
        class="mt-3 rounded-lg md:rounded-xl bg-purple-500/10 border border-purple-500/30 p-3 text-xs space-y-1"
      >
        <p class="font-bold text-purple-600 dark:text-purple-300">
          Marked as No-Show {{ booking.no_show_auto ? '(Automatic)' : '' }}
        </p>
        <p v-if="booking.deposit_forfeited" class="text-red-500 dark:text-red-400 font-semibold">
          Deposit marked as forfeited.
        </p>
        <p v-if="booking.no_show_record?.appealed" class="text-sky-600 dark:text-sky-300 font-semibold capitalize">
          Appeal status: {{ booking.no_show_record.appeal_outcome }}
        </p>
        <p v-if="booking.no_show_record?.resolution_note" class="text-[11px] text-[var(--color-text-secondary)]">
          Admin note: {{ booking.no_show_record.resolution_note }}
        </p>
      </div>

      <!-- Refund Status Banner -->
      <div
        v-if="booking.latest_refund_request"
        class="mt-3 rounded-lg md:rounded-xl bg-sky-500/10 border border-sky-500/30 p-3 text-xs space-y-1"
      >
        <p class="font-bold text-sky-700 dark:text-sky-300 capitalize">
          Refund {{ booking.latest_refund_request.status }} • ₦{{ Number(booking.latest_refund_request.refund_amount || 0).toLocaleString() }} ({{ booking.latest_refund_request.refund_percentage }}%)
        </p>
        <p v-if="booking.latest_refund_request.decision_note" class="text-[11px] text-[var(--color-text-secondary)]">
          Note: {{ booking.latest_refund_request.decision_note }}
        </p>
      </div>
    </div>

    <BookingCardActions
      :booking="booking"
      :bank-details="bankDetails"
      :show-bank-details="showBankDetails"
      :is-uploader-open="isUploaderOpen"
      @copy="$emit('copy', $event)"
      @discard-draft="$emit('discard-draft', $event)"
      @start-payment="$emit('start-payment', $event)"
      @receipt-uploaded="$emit('receipt-uploaded')"
      @open-receipt="$emit('open-receipt', $event)"
      @extend-grace="$emit('extend-grace', $event)"
      @appeal-no-show="$emit('appeal-no-show', $event)"
      @request-refund="$emit('request-refund', $event)"
      @cancel="$emit('cancel', $event)"
    />
  </article>
</template>

<script setup>
import { computed } from 'vue';
import { BanknotesIcon, ClockIcon, TrashIcon, UserIcon } from '@heroicons/vue/24/outline';
import { formatNairaAmount } from '@/core/utils';
import BookingCardActions from './BookingCardActions.vue';

const props = defineProps({
  booking: { type: Object, required: true },
  bankDetails: { type: Object, default: null },
  showBankDetails: { type: Boolean, default: false },
  isUploaderOpen: { type: Boolean, default: false },
});

defineEmits([
  'delete',
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

const STATUS_STYLE_MAP = {
  draft: 'bg-amber-500/15 text-amber-600 dark:text-amber-300 border-amber-500/40',
  confirmed: 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/30',
  pending: 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/30',
  completed: 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/30',
  cancelled: 'bg-red-500/10 text-red-600 dark:text-red-400 border-red-500/30',
  no_show: 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border-purple-500/30',
};

const statusBadgeClasses = computed(() => {
  return STATUS_STYLE_MAP[props.booking?.status] || 'bg-[var(--color-bg-tertiary)] text-[var(--color-text-secondary)] border-[var(--color-border)]';
});

const referenceLabel = computed(() => {
  if (props.booking?.booking_reference) return props.booking.booking_reference;
  return props.booking?.id ? `CC-${String(props.booking.id).padStart(4, '0')}` : '—';
});

const dayOfMonth = computed(() => {
  if (!props.booking?.appointment_date) return '--';
  const day = new Date(props.booking.appointment_date).getDate();
  return Number.isNaN(day) ? '--' : day;
});

const monthShort = computed(() => {
  if (!props.booking?.appointment_date) return '';
  const dt = new Date(props.booking.appointment_date);
  return Number.isNaN(dt.getTime()) ? '' : dt.toLocaleDateString('en-US', { month: 'short' });
});

const formattedDate = computed(() => {
  if (!props.booking?.appointment_date) return '';
  const dt = new Date(props.booking.appointment_date);
  return Number.isNaN(dt.getTime())
    ? props.booking.appointment_date
    : dt.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
});

const bookingAmount = computed(() => {
  return Number(props.booking?.grand_total || props.booking?.total_price || props.booking?.service?.price || 0);
});

const formattedPrice = computed(() => formatNairaAmount(bookingAmount.value));
</script>
