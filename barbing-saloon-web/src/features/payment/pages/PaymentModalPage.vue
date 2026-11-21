<template>
  <div class="min-h-[100dvh] sm:h-screen sm:max-h-[99vh] w-full bg-[#07090E] text-white flex items-center justify-center p-2 sm:p-3 md:p-4 box-border overflow-y-auto sm:overflow-hidden relative selection:bg-amber-500/30 selection:text-amber-200">
    <!-- Ambient luxury background glows -->
    <div class="pointer-events-none absolute -top-40 left-1/4 h-72 w-72 rounded-full bg-amber-500/10 blur-[100px]"></div>
    <div class="pointer-events-none absolute -bottom-40 right-1/4 h-72 w-72 rounded-full bg-blue-600/10 blur-[100px]"></div>

    <!-- Main Modal Card (Auto-fitting height on desktop up to 99vh, perfectly responsive on mobile) -->
    <div class="w-full max-w-4xl max-h-[99dvh] sm:max-h-[96vh] rounded-2xl sm:rounded-3xl border border-white/[0.08] bg-[#0E1217]/95 backdrop-blur-2xl shadow-[0_25px_60px_-15px_rgba(0,0,0,0.8)] flex flex-col relative overflow-hidden my-auto">
      <!-- Top Gold Ambient Highlight Line -->
      <div class="h-[1.5px] w-full bg-gradient-to-r from-transparent via-amber-500/80 to-transparent shrink-0"></div>

      <!-- Loading Preloader State -->
      <div v-if="loading" class="flex-1 flex flex-col items-center justify-center p-8 space-y-3 min-h-[300px]">
        <div class="relative flex items-center justify-center">
          <div class="h-10 w-10 animate-spin rounded-full border-2 border-amber-500/20 border-t-amber-500"></div>
          <span class="absolute text-xs select-none">✂️</span>
        </div>
        <div class="text-center space-y-0.5">
          <p class="text-xs font-bold text-white/90">Securing Payment Channel</p>
          <p class="text-[11px] text-white/40 font-mono">Loading gateway parameters & booking details...</p>
        </div>
      </div>

      <!-- Success Receipt State -->
      <div v-else-if="success" class="flex-1 overflow-y-auto p-4 sm:p-6 flex items-center justify-center custom-scrollbar">
        <PaymentSuccessReceipt
          :result="paymentResult"
          :formatted-amount="formattedAmount"
          :reference="reference"
          :selected-barber="selectedBarber"
          :channel="selectedChannel"
          @go-bookings="cancelTransaction"
          @print="windowPrint"
        />
      </div>

      <!-- Main Payment Interface -->
      <div v-else class="flex-1 flex flex-col min-h-0 overflow-hidden">
        <!-- Top Header Bar (Fixed / shrink-0) -->
        <PaymentHeader
          class="shrink-0 px-3.5 sm:px-5 py-2.5 sm:py-3 border-b border-white/[0.06] bg-[#0E1217]"
          :title="gatewayTitle"
          :reference="reference"
          :transaction-code="transactionCode"
          :formatted-amount="formattedAmount"
          :appointment="appointment"
          :appointment-id="appointmentId"
          @close="cancelTransaction"
          @copy="copyToClipboard"
        />

        <!-- Selected Barber & Booking Strip (Fixed / shrink-0) -->
        <div class="shrink-0 px-3.5 sm:px-5 py-1.5 bg-black/25 border-b border-white/[0.04]">
          <PaymentBarberSelectStrip
            :barbers="barbers"
            :selected-barber="selectedBarber"
            :selected-barber-id="selectedBarberId"
            :appointment="appointment"
            @update:selected-barber-id="selectedBarberId = $event"
          />
        </div>

        <!-- Scrollable Two-Column Body Area -->
        <div class="flex-1 min-h-0 overflow-y-auto px-3.5 sm:px-5 py-3 sm:py-4 custom-scrollbar">
          <div class="grid grid-cols-1 md:grid-cols-12 gap-3.5 lg:gap-4.5 items-start">
            <!-- Left Column (5 cols on desktop): Choose Payment Option -->
            <div class="md:col-span-5 space-y-2.5">
              <PaymentChannelSelector
                :selected-channel="selectedChannel"
                :enabled-channels="enabledChannels"
                @update:selected-channel="selectedChannel = $event"
              />

              <!-- Trust / Security Micro-card -->
              <div class="rounded-lg border border-white/[0.06] bg-[#12161F]/60 p-2.5 text-white/50 text-[11px] space-y-1 hidden sm:block">
                <div class="flex items-center gap-1.5 text-amber-400 font-bold text-[10px] uppercase tracking-wider">
                  <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 012.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.35-.166-2.001A11.954 11.954 0 0110 1.944zM11 14a1 1 0 11-2 0 1 1 0 012 0zm0-7a1 1 0 10-2 0v3a1 1 0 102 0V7z" clip-rule="evenodd" />
                  </svg>
                  Instant Slot Locking
                </div>
                <p class="text-[10px] leading-relaxed text-white/40">
                  Transfers are matched with your appointment record. Your selected master stylist chair will be automatically secured upon payment.
                </p>
              </div>
            </div>

            <!-- Right Column (7 cols on desktop): Transfer Details or Other Channel Details -->
            <div class="md:col-span-7">
              <!-- Bank Transfer Card -->
              <PaymentTransferCard
                v-if="selectedChannel === 'bank_transfer'"
                :bank-details="bankDetails"
                :formatted-amount="formattedAmount"
                :amount="rawAmount"
                :formatted-time-remaining="formattedTimeRemaining"
                :notice-text="noticeText"
                :submitting="submitting"
                :reference="reference"
                @copy="copyToClipboard"
                @confirm="submitPayment"
                @cancel="cancelTransaction"
              />

              <!-- Other Channel Card (USSD, Bank Account, NQR, Card) -->
              <PaymentOtherChannels
                v-else
                :selected-channel="selectedChannel"
                :formatted-time-remaining="formattedTimeRemaining"
                :submitting="submitting"
                @copy="copyToClipboard"
                @confirm="submitPayment"
                @cancel="cancelTransaction"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import { usePaymentCheckout } from '../composables/usePaymentCheckout';
import PaymentHeader from '../components/PaymentHeader.vue';
import PaymentBarberSelectStrip from '../components/PaymentBarberSelectStrip.vue';
import PaymentChannelSelector from '../components/PaymentChannelSelector.vue';
import PaymentTransferCard from '../components/PaymentTransferCard.vue';
import PaymentOtherChannels from '../components/PaymentOtherChannels.vue';
import PaymentSuccessReceipt from '../components/PaymentSuccessReceipt.vue';

const route = useRoute();

const {
  loading,
  submitting,
  success,
  selectedChannel,
  gatewayTitle,
  noticeText,
  enabledChannels,
  bankDetails,
  barbers,
  selectedBarberId,
  selectedBarber,
  appointment,
  paymentResult,
  rawAmount,
  formattedAmount,
  reference,
  transactionCode,
  formattedTimeRemaining,
  copyToClipboard,
  submitPayment,
  cancelTransaction,
} = usePaymentCheckout();

const appointmentId = computed(() => {
  return appointment.value?.id || route?.params?.appointmentId || route?.query?.appointment_id || null;
});

const windowPrint = () => {
  if (typeof window !== 'undefined') {
    window.print();
  }
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.02);
  border-radius: 6px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.12);
  border-radius: 6px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(245, 158, 11, 0.4);
}
</style>
