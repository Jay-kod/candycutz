<template>
  <div
    v-if="booking"
    role="dialog"
    aria-modal="true"
    aria-label="Official Grooming Payment Receipt"
    class="fixed inset-0 z-50 flex items-start sm:items-center justify-center p-3 sm:p-4 py-4 pb-safe bg-black/80 backdrop-blur-md overflow-y-auto"
    @click.self="$emit('close')"
  >
    <div class="w-full max-w-lg relative my-auto space-y-3">
      <div class="flex justify-end print:hidden">
        <button
          type="button"
          @click="$emit('close')"
          class="min-h-[44px] min-w-[44px] flex items-center justify-center rounded-full border border-white/15 bg-[#12161F] p-2 text-white/70 hover:text-white transition-colors cursor-pointer"
          aria-label="Close receipt"
        >
          <XMarkIcon class="h-5 w-5" />
        </button>
      </div>

      <PaymentSuccessReceipt
        :appointment="booking"
        :result="receiptResult"
        :formatted-amount="formattedAmount"
        :reference="booking.transaction_ref || booking.booking_reference"
        :channel="booking.payment_method || 'bank_transfer'"
        @go-bookings="$emit('close')"
        @print="handlePrint"
      />
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted } from 'vue';
import { XMarkIcon } from '@heroicons/vue/24/outline';
import PaymentSuccessReceipt from './PaymentSuccessReceipt.vue';

const props = defineProps({
  booking: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['close']);

const onKeydown = (e) => {
  if (e.key === 'Escape' && props.booking) {
    emit('close');
  }
};

onMounted(() => {
  if (typeof document !== 'undefined') {
    document.addEventListener('keydown', onKeydown);
  }
});

onUnmounted(() => {
  if (typeof document !== 'undefined') {
    document.removeEventListener('keydown', onKeydown);
  }
});

const receiptResult = computed(() => {
  if (!props.booking) return null;
  return {
    reference: props.booking.transaction_ref || props.booking.booking_reference,
    payment_method: props.booking.payment_method,
    paid_at: props.booking.paid_at,
    payment: props.booking.payment,
    barber: props.booking.barber,
    appointment: props.booking
  };
});

const formattedAmount = computed(() => {
  const raw = Number(props.booking?.grand_total || props.booking?.total_price || props.booking?.service?.price || 0);
  const naira = raw >= 100000 ? raw / 100 : raw;
  return new Intl.NumberFormat('en-NG', {
    style: 'currency',
    currency: 'NGN',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(naira);
});

const handlePrint = () => {
  if (typeof window !== 'undefined') {
    window.print();
  }
};
</script>
