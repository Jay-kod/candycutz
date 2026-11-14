<template>
  <div class="min-h-screen bg-[#0A0D11] text-white flex items-center justify-center p-3 sm:p-6 lg:p-8">
    <!-- Main Modal Card (Matches screenshot layout) -->
    <div class="w-full max-w-4xl rounded-3xl border border-white/[0.08] bg-[#0E1217] p-5 sm:p-8 lg:p-10 shadow-2xl space-y-6 relative overflow-hidden">
      <!-- Ambient Glow Behind Header -->
      <div class="pointer-events-none absolute -top-32 right-1/4 h-64 w-96 rounded-full bg-amber-500/10 blur-3xl"></div>

      <!-- Loading Preloader -->
      <div v-if="loading" class="flex flex-col items-center justify-center py-24 space-y-4">
        <div class="h-10 w-10 animate-spin rounded-full border-4 border-amber-500 border-t-transparent"></div>
        <p class="text-xs text-white/50 font-mono">Loading Payment Gateway & Account Details...</p>
      </div>

      <!-- Success Receipt State -->
      <PaymentSuccessReceipt
        v-else-if="success"
        :result="paymentResult"
        :formatted-amount="formattedAmount"
        :reference="reference"
        :selected-barber="selectedBarber"
        :channel="selectedChannel"
        @go-bookings="cancelTransaction"
        @print="windowPrint"
      />

      <!-- Main Payment Interface -->
      <template v-else>
        <!-- Top Header Bar -->
        <PaymentHeader
          :title="gatewayTitle"
          :reference="reference"
          :transaction-code="transactionCode"
          :formatted-amount="formattedAmount"
          @close="cancelTransaction"
        />

        <!-- Selected Barber Indicator / Switcher -->
        <PaymentBarberSelectStrip
          :barbers="barbers"
          :selected-barber="selectedBarber"
          :selected-barber-id="selectedBarberId"
          @update:selected-barber-id="selectedBarberId = $event"
        />

        <!-- Two-Column Grid: Left Options & Right Transfer Details -->
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
          <!-- Left Column (5 cols): Choose Payment Option -->
          <div class="md:col-span-5">
            <PaymentChannelSelector
              :selected-channel="selectedChannel"
              :enabled-channels="enabledChannels"
              @update:selected-channel="selectedChannel = $event"
            />
          </div>

          <!-- Right Column (7 cols): Transfer Details or Other Channel Details -->
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
      </template>
    </div>
  </div>
</template>

<script setup>
import { usePaymentCheckout } from '../composables/usePaymentCheckout';
import PaymentHeader from '../components/PaymentHeader.vue';
import PaymentBarberSelectStrip from '../components/PaymentBarberSelectStrip.vue';
import PaymentChannelSelector from '../components/PaymentChannelSelector.vue';
import PaymentTransferCard from '../components/PaymentTransferCard.vue';
import PaymentOtherChannels from '../components/PaymentOtherChannels.vue';
import PaymentSuccessReceipt from '../components/PaymentSuccessReceipt.vue';

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

const windowPrint = () => {
  if (typeof window !== 'undefined') {
    window.print();
  }
};
</script>
