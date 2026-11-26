<template>
  <div class="w-full max-w-lg mx-auto space-y-4">
    <!-- Printable Official Atelier Receipt Ticket -->
    <div
      id="printable-receipt"
      class="relative rounded-2xl border border-amber-400/30 bg-gradient-to-b from-[#151822] via-[#101219] to-[#0B0C11] text-white shadow-[0_24px_60px_-15px_rgba(0,0,0,0.85)] overflow-hidden"
    >
      <!-- Top Champagne-Gold Hairline Rule -->
      <div class="h-1 w-full bg-gradient-to-r from-amber-600/60 via-amber-300 to-amber-600/60"></div>

      <!-- Brand Crest & Status Header -->
      <div class="px-3.5 sm:px-5 pt-4 sm:pt-5 pb-3.5 sm:pb-4 border-b border-white/[0.07] flex items-start justify-between gap-3">
        <div class="text-left space-y-1 min-w-0">
          <div class="flex flex-wrap items-center gap-2">
            <span class="text-sm font-black tracking-[0.22em] sm:tracking-[0.26em] uppercase text-amber-300 font-display">CANDYCUTZ</span>
            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-500/15 border border-emerald-500/35 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-emerald-300">
              <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
              PAID
            </span>
          </div>
          <p class="text-[11px] text-white/50 text-left-forced">Official Grooming Payment Receipt</p>
        </div>

        <div class="text-right font-mono shrink-0">
          <p class="text-[10px] uppercase tracking-wider text-white/40">Receipt No.</p>
          <p class="text-xs font-bold text-white/90 tabular-nums">{{ receiptNumber }}</p>
        </div>
      </div>

      <!-- Confirmation Headline & Email Sent Note -->
      <div class="px-3.5 sm:px-5 pt-4 pb-3 text-center space-y-1.5">
        <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/15 text-emerald-400 border border-emerald-500/35 shadow-inner">
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
          </svg>
        </div>
        <h2 class="font-display text-lg font-bold text-white tracking-tight">
          Payment Confirmed &amp; Saved
        </h2>
        <p class="text-[11px] text-white/60 max-w-sm mx-auto leading-relaxed text-center">
          Your appointment is secured. A copy of this official receipt and your booking code has been sent to your email.
        </p>
      </div>

      <!-- Prominent Booking & Service Verification Code Passcard -->
      <div class="mx-3.5 sm:mx-5 my-3 rounded-xl border border-amber-400/40 bg-gradient-to-br from-amber-500/[0.12] via-[#15130E] to-[#101218] p-3.5 sm:p-4 text-center space-y-2 shadow-inner">
        <p class="text-[10px] font-bold uppercase tracking-[0.2em] sm:tracking-[0.22em] text-amber-300 text-center">
          Booking &amp; Service Verification Code
        </p>
        <div class="flex flex-wrap items-center justify-center gap-2 sm:gap-2.5">
          <span class="rounded-lg border border-dashed border-amber-400/50 bg-black/50 px-3 sm:px-4 py-1.5 font-mono text-lg sm:text-2xl font-extrabold tracking-[0.14em] sm:tracking-[0.22em] text-amber-200 uppercase break-all select-all">
            {{ bookingCode }}
          </span>
          <button
            type="button"
            @click="copyCode"
            class="print:hidden min-h-[40px] rounded-lg border border-amber-400/30 bg-amber-400/15 hover:bg-amber-400/25 px-3 py-2 text-[11px] font-bold text-amber-200 transition-colors cursor-pointer shrink-0"
            title="Copy Booking Code"
          >
            {{ copied ? '✓ Copied' : 'Copy' }}
          </button>
        </div>
        <p class="text-[10px] text-white/55 text-center">
          Present this code to your barber at session completion to verify your cut.
        </p>
      </div>

      <!-- Perforated Ticket Divider -->
      <div class="relative my-4 flex items-center">
        <div class="h-4 w-2.5 rounded-r-full bg-[#07090E] border-y border-r border-white/10"></div>
        <div class="flex-1 border-t border-dashed border-white/15 mx-1"></div>
        <div class="h-4 w-2.5 rounded-l-full bg-[#07090E] border-y border-l border-white/10"></div>
      </div>

      <!-- Itemized Receipt Ledger -->
      <div class="px-3.5 sm:px-5 pb-4 sm:pb-5 space-y-3">
        <div class="rounded-xl border border-white/[0.08] bg-white/[0.02] divide-y divide-white/[0.06] text-xs">
          <div v-if="serviceName" class="flex justify-between items-start gap-3 px-3 sm:px-3.5 py-2.5">
            <span class="text-white/50 text-[11px] shrink-0">Service</span>
            <span class="font-semibold text-white text-right min-w-0 break-words">{{ serviceName }}</span>
          </div>

          <div class="flex justify-between items-start gap-3 px-3 sm:px-3.5 py-2.5">
            <span class="text-white/50 text-[11px] shrink-0">Selected Master Barber</span>
            <span class="font-semibold text-white text-right min-w-0 break-words">{{ barberLabel }}</span>
          </div>

          <div v-if="slotLabel" class="flex justify-between items-start gap-3 px-3 sm:px-3.5 py-2.5">
            <span class="text-white/50 text-[11px] shrink-0">Appointment Slot</span>
            <span class="font-mono text-white/90 text-[11px] text-right min-w-0 tabular-nums">{{ slotLabel }}</span>
          </div>

          <div class="flex justify-between items-start gap-3 px-3 sm:px-3.5 py-2.5">
            <span class="text-white/50 text-[11px] shrink-0">Payment Reference</span>
            <span class="font-mono text-amber-300 text-[11px] text-right min-w-0 truncate">{{ transactionRef }}</span>
          </div>

          <div class="flex justify-between items-start gap-3 px-3 sm:px-3.5 py-2.5">
            <span class="text-white/50 text-[11px] shrink-0">Channel / Method</span>
            <span class="font-medium text-white/90 uppercase text-[11px] text-right min-w-0">{{ channelLabel }}</span>
          </div>

          <div class="flex justify-between items-start gap-3 px-3 sm:px-3.5 py-2.5">
            <span class="text-white/50 text-[11px] shrink-0">Issued / Paid At</span>
            <span class="font-mono text-white/75 text-[11px] text-right min-w-0 tabular-nums">{{ paidAtLabel }}</span>
          </div>

          <div class="flex justify-between items-center gap-3 px-3 sm:px-3.5 py-2.5">
            <span class="text-white/50 text-[11px] shrink-0">Database Status</span>
            <span class="inline-flex items-center gap-1.5 text-emerald-400 font-bold text-[11px] shrink-0">
              <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
              Confirmed &amp; Verified
            </span>
          </div>

          <div class="flex justify-between items-center gap-3 px-3 sm:px-3.5 py-3 bg-amber-500/[0.06]">
            <span class="text-xs font-bold uppercase tracking-wider text-white/80 shrink-0">Total Amount Paid</span>
            <span class="font-mono text-base font-extrabold text-amber-300 tabular-nums">{{ formattedAmount }}</span>
          </div>
        </div>

        <div class="pt-1 text-center text-[10px] text-white/40 space-y-0.5">
          <p class="text-center">CandyCutz Grooming Ltd. &bull; Keffi Main Hub, Nasarawa State</p>
          <p class="text-center">Reminder sent 30 minutes prior to appointment start.</p>
        </div>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex flex-col sm:flex-row gap-2.5 print:hidden">
      <button
        type="button"
        @click="$emit('go-bookings')"
        class="flex-1 min-h-[44px] rounded-xl bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-500 hover:to-amber-600 active:scale-[0.99] py-2.5 px-4 text-xs font-bold text-slate-950 transition-all shadow-lg shadow-amber-500/20 cursor-pointer"
      >
        View in My Bookings
      </button>
      <button
        type="button"
        @click="downloadPdf"
        :disabled="downloading"
        class="min-h-[44px] rounded-xl border border-amber-400/30 bg-amber-400/10 hover:bg-amber-400/20 active:scale-[0.99] py-2.5 px-4 text-xs font-semibold text-amber-200 transition-all cursor-pointer disabled:opacity-50"
      >
        {{ downloading ? 'Exporting...' : 'Download PDF' }}
      </button>
      <button
        type="button"
        @click="$emit('print')"
        class="min-h-[44px] rounded-xl border border-white/15 bg-white/[0.05] hover:bg-white/[0.1] active:scale-[0.99] py-2.5 px-4 text-xs font-semibold text-white transition-all cursor-pointer"
      >
        Print Receipt
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useReceiptExport } from '../composables/useReceiptExport';

const props = defineProps({
  result: {
    type: Object,
    default: null
  },
  appointment: {
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

const emit = defineEmits(['go-bookings', 'print']);

const resolvedAppointment = computed(() => props.result?.appointment || props.appointment || null);

const bookingCode = computed(() => {
  return (
    resolvedAppointment.value?.verification_code ||
    resolvedAppointment.value?.booking_reference ||
    props.result?.reference ||
    props.reference ||
    'CC-BOOKING'
  );
});

const { copied, downloading, copyCode, downloadPdf } = useReceiptExport(bookingCode, () => emit('print'));

const transactionRef = computed(() => {
  return (
    props.result?.payment?.transaction_ref ||
    props.result?.payment?.reference ||
    props.result?.reference ||
    resolvedAppointment.value?.transaction_ref ||
    props.reference ||
    bookingCode.value
  );
});

const receiptNumber = computed(() => {
  const rawId = props.result?.payment?.id || resolvedAppointment.value?.id || 1;
  return `RCP-${String(rawId).padStart(6, '0')}`;
});

const serviceName = computed(() => {
  return resolvedAppointment.value?.service?.name || props.result?.service?.name || '';
});

const barberLabel = computed(() => {
  const name =
    props.result?.barber?.name ||
    resolvedAppointment.value?.barber?.name ||
    props.selectedBarber?.name ||
    'Assigned Stylist';
  const chair =
    props.result?.barber?.chair_number ||
    resolvedAppointment.value?.barber?.chair_number ||
    props.selectedBarber?.chair_number ||
    1;
  return `${name} (Chair #${chair})`;
});

const slotLabel = computed(() => {
  const date = resolvedAppointment.value?.appointment_date;
  const time = resolvedAppointment.value?.appointment_time;
  if (!date && !time) return '';
  return [date, time].filter(Boolean).join(' · ');
});

const paidAtLabel = computed(() => {
  const raw = props.result?.paid_at || props.result?.payment?.paid_at || resolvedAppointment.value?.paid_at;
  const dt = raw ? new Date(raw) : new Date();
  if (Number.isNaN(dt.getTime())) return 'Just now';
  return dt.toLocaleString('en-NG', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: 'numeric',
    minute: '2-digit'
  });
});

const channelLabel = computed(() => {
  const method = props.result?.payment_method || props.result?.payment?.payment_method || props.channel;
  const map = {
    bank_transfer: 'Bank Transfer',
    manual_transfer: 'Verified Transfer',
    ussd: 'Bank USSD',
    bank_account: 'Bank Account',
    nqr: 'NQR Scan',
    card: 'Paystack · Card',
    paystack: 'Paystack Checkout',
    pay_at_venue: 'Paid at Venue'
  };
  return map[String(method || '').toLowerCase()] || method || 'Online Payment';
});
</script>
