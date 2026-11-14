<template>
  <div class="rounded-3xl border border-white/[0.08] bg-[#161B22]/90 p-5 sm:p-7 backdrop-blur-xl space-y-5 shadow-2xl">
    <!-- Header -->
    <h3 class="text-sm sm:text-base font-bold text-white text-center sm:text-left">
      Transfer to account details below:
    </h3>

    <!-- Blue Info Alert Banner -->
    <div class="flex items-start gap-2.5 rounded-xl bg-blue-500/10 border border-blue-500/20 p-3 text-xs text-blue-300">
      <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-blue-500/20 text-blue-300 font-bold text-[11px]">
        ℹ
      </span>
      <p class="leading-relaxed">
        {{ noticeText }}
      </p>
    </div>

    <!-- White / Light High-Contrast Bank Details Box -->
    <div class="divide-y divide-gray-200 overflow-hidden rounded-2xl bg-white text-gray-900 shadow-md">
      <!-- Amount to Send -->
      <div class="flex items-center justify-between p-3.5 sm:px-4">
        <span class="text-xs text-gray-600 font-medium">Amount to send</span>
        <div class="flex items-center gap-2">
          <span class="font-bold text-sm sm:text-base text-gray-950 font-mono tracking-tight">
            {{ formattedAmount }}
          </span>
          <button
            type="button"
            @click="$emit('copy', amount, 'Amount')"
            class="p-1 text-gray-500 hover:text-gray-900 hover:bg-gray-100 rounded transition-colors cursor-pointer"
            title="Copy amount"
            aria-label="Copy amount"
          >
            <!-- Copy Icon -->
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Account Number -->
      <div class="flex items-center justify-between p-3.5 sm:px-4">
        <span class="text-xs text-gray-600 font-medium">Account Number</span>
        <div class="flex items-center gap-2">
          <span class="font-bold text-sm sm:text-base font-mono tracking-wider text-gray-950">
            {{ bankDetails.account_number }}
          </span>
          <button
            type="button"
            @click="$emit('copy', bankDetails.account_number, 'Account Number')"
            class="p-1 text-gray-500 hover:text-gray-900 hover:bg-gray-100 rounded transition-colors cursor-pointer"
            title="Copy account number"
            aria-label="Copy account number"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Bank Name -->
      <div class="flex items-center justify-between p-3.5 sm:px-4">
        <span class="text-xs text-gray-600 font-medium">Bank Name</span>
        <div class="flex items-center gap-2">
          <span class="font-semibold text-xs sm:text-sm text-gray-950">
            {{ bankDetails.bank_name }}
          </span>
          <button
            type="button"
            @click="$emit('copy', bankDetails.bank_name, 'Bank Name')"
            class="p-1 text-gray-500 hover:text-gray-900 hover:bg-gray-100 rounded transition-colors cursor-pointer"
            title="Copy bank name"
            aria-label="Copy bank name"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Account Name -->
      <div class="flex items-center justify-between p-3.5 sm:px-4">
        <span class="text-xs text-gray-600 font-medium">Account Name</span>
        <span class="font-semibold text-xs sm:text-sm text-gray-950">
          {{ bankDetails.account_name }}
        </span>
      </div>
    </div>

    <!-- Countdown Timer Notice -->
    <div class="flex items-center gap-2 text-xs text-white/70">
      <svg class="h-4 w-4 shrink-0 text-white/50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <p>
        These account details are valid for this transaction only and will expire in
        <strong class="text-white font-mono font-bold">{{ formattedTimeRemaining }}</strong>.
      </p>
    </div>

    <div class="border-t border-white/[0.08] pt-3">
      <p class="text-xs text-white/60 text-center">
        Tap the "I have paid" button below after completing your transfer.
      </p>
    </div>

    <!-- Main Action Button: I Have Paid -->
    <button
      type="button"
      @click="$emit('confirm')"
      :disabled="submitting"
      class="w-full rounded-2xl bg-[#FDB913] hover:bg-[#F2AC07] active:scale-[0.99] py-3.5 px-6 font-bold text-slate-950 text-sm sm:text-base transition-all shadow-lg shadow-[#FDB913]/20 flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
    >
      <span v-if="submitting" class="h-4 w-4 animate-spin rounded-full border-2 border-slate-950/30 border-t-slate-950"></span>
      <span>{{ submitting ? 'Verifying & Saving to DB...' : 'I Have Paid' }}</span>
    </button>

    <!-- Cancel Transaction Link -->
    <div class="text-center pt-1">
      <button
        type="button"
        @click="$emit('cancel')"
        class="text-xs text-white/40 hover:text-white/80 transition-colors cursor-pointer"
      >
        Cancel Transaction
      </button>
    </div>
  </div>
</template>

<script setup>
defineProps({
  bankDetails: {
    type: Object,
    required: true
  },
  formattedAmount: {
    type: String,
    required: true
  },
  amount: {
    type: [Number, String],
    required: true
  },
  formattedTimeRemaining: {
    type: String,
    required: true
  },
  submitting: {
    type: Boolean,
    default: false
  },
  noticeText: {
    type: String,
    default: 'Before transferring, make sure the account name shown by your bank matches the one provided below.'
  }
});

defineEmits(['copy', 'confirm', 'cancel']);
</script>
