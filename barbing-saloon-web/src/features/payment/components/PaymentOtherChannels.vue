<template>
  <div class="rounded-3xl border border-white/[0.08] bg-[#161B22]/90 p-5 sm:p-7 backdrop-blur-xl space-y-5 shadow-2xl">
    <!-- Channel 1: USSD -->
    <template v-if="selectedChannel === 'ussd'">
      <h3 class="text-sm sm:text-base font-bold text-white text-center sm:text-left">
        Pay with Bank USSD Code
      </h3>

      <div class="rounded-2xl bg-white p-4 text-gray-900 space-y-3 shadow-md">
        <p class="text-xs text-gray-600">Select your bank to generate your unique USSD dialing string:</p>
        <div class="flex items-center justify-between border-b pb-2">
          <span class="text-xs font-semibold text-gray-600">Zenith / Sterling Code:</span>
          <span class="font-mono font-bold text-sm text-gray-900">*966*000*5261935770#</span>
        </div>
        <div class="flex items-center justify-between border-b pb-2">
          <span class="text-xs font-semibold text-gray-600">GTBank Code:</span>
          <span class="font-mono font-bold text-sm text-gray-900">*737*000*5261935770#</span>
        </div>
        <button
          type="button"
          @click="$emit('copy', '*966*000*5261935770#', 'USSD Code')"
          class="w-full py-2 bg-gray-100 hover:bg-gray-200 rounded-xl text-xs font-bold text-gray-800 transition-colors flex items-center justify-center gap-1.5 cursor-pointer"
        >
          Copy USSD Code
        </button>
      </div>
    </template>

    <!-- Channel 2: Bank Account -->
    <template v-else-if="selectedChannel === 'bank_account'">
      <h3 class="text-sm sm:text-base font-bold text-white text-center sm:text-left">
        Direct Bank Account Debit
      </h3>
      <div class="rounded-2xl bg-white p-4 text-gray-900 space-y-3 shadow-md">
        <p class="text-xs text-gray-600">Authorize a direct debit from your verified Nigerian bank account.</p>
        <div class="space-y-1.5">
          <label class="text-[11px] font-bold text-gray-600 uppercase">Your Account Number</label>
          <input
            type="text"
            placeholder="0123456789 (10 Digits)"
            class="w-full px-3 py-2 rounded-xl border border-gray-300 text-xs text-gray-900 outline-none focus:border-amber-500 font-mono"
          />
        </div>
      </div>
    </template>

    <!-- Channel 3: NQR -->
    <template v-else-if="selectedChannel === 'nqr'">
      <h3 class="text-sm sm:text-base font-bold text-white text-center sm:text-left">
        Scan to Pay with NQR
      </h3>
      <div class="rounded-2xl bg-white p-6 text-gray-900 text-center space-y-3 shadow-md flex flex-col items-center">
        <!-- QR Code Matrix Graphic -->
        <div class="h-40 w-40 rounded-xl bg-gray-900 p-3 flex items-center justify-center">
          <div class="h-full w-full bg-white p-2 rounded flex flex-col justify-between">
            <div class="flex justify-between"><div class="h-8 w-8 bg-black"></div><div class="h-8 w-8 bg-black"></div></div>
            <div class="text-center font-mono font-bold text-[9px] tracking-wider text-black">CANDYCUTZ NQR</div>
            <div class="flex justify-between"><div class="h-8 w-8 bg-black"></div><div class="h-4 w-4 bg-black"></div></div>
          </div>
        </div>
        <p class="text-xs text-gray-600">Open your mobile banking app, select Scan QR, and point your camera.</p>
      </div>
    </template>

    <!-- Channel 4: Card -->
    <template v-else-if="selectedChannel === 'card'">
      <h3 class="text-sm sm:text-base font-bold text-white text-center sm:text-left">
        Pay with Debit / Credit Card
      </h3>
      <div class="rounded-2xl bg-white p-4 text-gray-900 space-y-3 shadow-md">
        <div class="space-y-1">
          <label class="text-[11px] font-bold text-gray-600 uppercase">Card Number</label>
          <input
            type="text"
            placeholder="5399 •••• •••• ••••"
            class="w-full px-3 py-2 rounded-xl border border-gray-300 text-xs text-gray-900 outline-none focus:border-amber-500 font-mono"
          />
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div class="space-y-1">
            <label class="text-[11px] font-bold text-gray-600 uppercase">Expiry</label>
            <input
              type="text"
              placeholder="MM/YY"
              class="w-full px-3 py-2 rounded-xl border border-gray-300 text-xs text-gray-900 outline-none focus:border-amber-500 font-mono"
            />
          </div>
          <div class="space-y-1">
            <label class="text-[11px] font-bold text-gray-600 uppercase">CVV</label>
            <input
              type="password"
              maxlength="3"
              placeholder="123"
              class="w-full px-3 py-2 rounded-xl border border-gray-300 text-xs text-gray-900 outline-none focus:border-amber-500 font-mono"
            />
          </div>
        </div>
      </div>
    </template>

    <!-- Expiry Timer Strip -->
    <div class="flex items-center gap-2 text-xs text-white/70">
      <svg class="h-4 w-4 shrink-0 text-white/50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <p>
        Session expires in <strong class="text-white font-mono font-bold">{{ formattedTimeRemaining }}</strong>.
      </p>
    </div>

    <!-- Confirm Button -->
    <button
      type="button"
      @click="$emit('confirm')"
      :disabled="submitting"
      class="w-full rounded-2xl bg-[#FDB913] hover:bg-[#F2AC07] active:scale-[0.99] py-3.5 px-6 font-bold text-slate-950 text-sm sm:text-base transition-all shadow-lg shadow-[#FDB913]/20 flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
    >
      <span v-if="submitting" class="h-4 w-4 animate-spin rounded-full border-2 border-slate-950/30 border-t-slate-950"></span>
      <span>{{ submitting ? 'Processing Payment...' : 'I Have Paid' }}</span>
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
  selectedChannel: {
    type: String,
    required: true
  },
  formattedTimeRemaining: {
    type: String,
    required: true
  },
  submitting: {
    type: Boolean,
    default: false
  }
});

defineEmits(['confirm', 'cancel', 'copy']);
</script>
