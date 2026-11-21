<template>
  <div class="rounded-xl border border-white/[0.08] bg-[#121720]/90 p-3 sm:p-4 backdrop-blur-xl space-y-2.5 shadow-2xl">
    <!-- Channel 1: USSD -->
    <template v-if="selectedChannel === 'ussd'">
      <div class="flex items-center justify-between">
        <h3 class="text-xs font-bold text-white flex items-center gap-1.5 uppercase tracking-wide">
          <span class="flex h-4 w-4 items-center justify-center rounded bg-amber-500/10 text-amber-400 text-[10px]">📱</span>
          Pay with Bank USSD Code
        </h3>
        <span class="text-[9px] font-mono px-1.5 py-0.2 rounded-full bg-amber-500/10 text-amber-300 border border-amber-500/20 font-semibold">
          Instant Dial
        </span>
      </div>

      <div class="rounded-lg bg-white p-3 text-gray-900 space-y-2 shadow-md">
        <p class="text-[10px] text-gray-600 font-medium">Select your bank to dial your secure transaction code:</p>
        <div class="flex items-center justify-between border-b border-gray-100 pb-1.5 gap-2">
          <span class="text-[10px] font-semibold text-gray-700 truncate">Zenith / Sterling:</span>
          <div class="flex items-center gap-1 shrink-0">
            <span class="font-mono font-bold text-xs text-gray-900">*966*000*5261935770#</span>
            <button
              type="button"
              @click="$emit('copy', '*966*000*5261935770#', 'Zenith USSD Code')"
              class="p-0.5 text-gray-500 hover:text-gray-900 rounded cursor-pointer"
              title="Copy code"
            >
              <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
              </svg>
            </button>
          </div>
        </div>
        <div class="flex items-center justify-between border-b border-gray-100 pb-1.5 gap-2">
          <span class="text-[10px] font-semibold text-gray-700 truncate">GTBank:</span>
          <div class="flex items-center gap-1 shrink-0">
            <span class="font-mono font-bold text-xs text-gray-900">*737*000*5261935770#</span>
            <button
              type="button"
              @click="$emit('copy', '*737*000*5261935770#', 'GTBank USSD Code')"
              class="p-0.5 text-gray-500 hover:text-gray-900 rounded cursor-pointer"
              title="Copy code"
            >
              <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
              </svg>
            </button>
          </div>
        </div>
        <button
          type="button"
          @click="$emit('copy', '*966*000*5261935770#', 'USSD Code')"
          class="w-full py-1.5 bg-gray-100 hover:bg-gray-200 active:bg-gray-300 rounded text-[10px] font-bold text-gray-800 transition-colors flex items-center justify-center gap-1 cursor-pointer"
        >
          Copy USSD Code
        </button>
      </div>
    </template>

    <!-- Channel 2: Bank Account -->
    <template v-else-if="selectedChannel === 'bank_account'">
      <div class="flex items-center justify-between">
        <h3 class="text-xs font-bold text-white flex items-center gap-1.5 uppercase tracking-wide">
          <span class="flex h-4 w-4 items-center justify-center rounded bg-amber-500/10 text-amber-400 text-[10px]">🏛️</span>
          Direct Bank Account Debit
        </h3>
        <span class="text-[9px] font-mono px-1.5 py-0.2 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-semibold">
          Automated
        </span>
      </div>
      <div class="rounded-lg bg-white p-3 text-gray-900 space-y-2 shadow-md">
        <p class="text-[10px] text-gray-600">Authorize a direct debit from your verified Nigerian bank account.</p>
        <div class="space-y-1">
          <label class="text-[9px] font-bold text-gray-700 uppercase tracking-wider">Your Account Number</label>
          <input
            type="text"
            placeholder="0123456789 (10 Digits)"
            class="w-full px-2.5 py-1.5 rounded border border-gray-300 text-xs text-gray-900 outline-none focus:border-amber-500 font-mono tracking-wider"
          />
        </div>
      </div>
    </template>

    <!-- Channel 3: NQR -->
    <template v-else-if="selectedChannel === 'nqr'">
      <div class="flex items-center justify-between">
        <h3 class="text-xs font-bold text-white flex items-center gap-1.5 uppercase tracking-wide">
          <span class="flex h-4 w-4 items-center justify-center rounded bg-amber-500/10 text-amber-400 text-[10px]">📷</span>
          Scan to Pay with NQR
        </h3>
        <span class="text-[9px] font-mono px-1.5 py-0.2 rounded-full bg-amber-500/10 text-amber-300 border border-amber-500/20 font-semibold">
          Camera Scan
        </span>
      </div>
      <div class="rounded-lg bg-white p-3 text-gray-900 text-center space-y-2 shadow-md flex flex-col items-center">
        <!-- QR Code Matrix Graphic -->
        <div class="h-28 w-28 rounded-lg bg-gray-950 p-2 flex items-center justify-center shadow-inner">
          <div class="h-full w-full bg-white p-1 rounded flex flex-col justify-between">
            <div class="flex justify-between"><div class="h-5 w-5 bg-black"></div><div class="h-5 w-5 bg-black"></div></div>
            <div class="text-center font-mono font-black text-[7px] tracking-wider text-black">CANDYCUTZ NQR</div>
            <div class="flex justify-between"><div class="h-5 w-5 bg-black"></div><div class="h-2.5 w-2.5 bg-black"></div></div>
          </div>
        </div>
        <p class="text-[10px] text-gray-600">Open your mobile banking app, select Scan QR, and point your camera.</p>
      </div>
    </template>

    <!-- Channel 4: Card -->
    <template v-else-if="selectedChannel === 'card'">
      <div class="flex items-center justify-between">
        <h3 class="text-xs font-bold text-white flex items-center gap-1.5 uppercase tracking-wide">
          <span class="flex h-4 w-4 items-center justify-center rounded bg-amber-500/10 text-amber-400 text-[10px]">💳</span>
          Pay with Debit / Credit Card
        </h3>
        <span class="text-[9px] font-mono px-1.5 py-0.2 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-semibold">
          Secured
        </span>
      </div>
      <div class="rounded-lg bg-white p-3 text-gray-900 space-y-2 shadow-md">
        <div class="space-y-0.5">
          <label class="text-[9px] font-bold text-gray-700 uppercase tracking-wider">Card Number</label>
          <input
            type="text"
            placeholder="5399 •••• •••• ••••"
            class="w-full px-2.5 py-1.5 rounded border border-gray-300 text-xs text-gray-900 outline-none focus:border-amber-500 font-mono"
          />
        </div>
        <div class="grid grid-cols-2 gap-2">
          <div class="space-y-0.5">
            <label class="text-[9px] font-bold text-gray-700 uppercase tracking-wider">Expiry</label>
            <input
              type="text"
              placeholder="MM/YY"
              class="w-full px-2.5 py-1.5 rounded border border-gray-300 text-xs text-gray-900 outline-none focus:border-amber-500 font-mono"
            />
          </div>
          <div class="space-y-0.5">
            <label class="text-[9px] font-bold text-gray-700 uppercase tracking-wider">CVV</label>
            <input
              type="password"
              maxlength="3"
              placeholder="123"
              class="w-full px-2.5 py-1.5 rounded border border-gray-300 text-xs text-gray-900 outline-none focus:border-amber-500 font-mono"
            />
          </div>
        </div>
      </div>
    </template>

    <!-- Expiry Timer Strip -->
    <div class="flex items-center gap-1.5 rounded-lg bg-amber-500/[0.08] border border-amber-500/20 px-2.5 py-1.5 text-[10px] sm:text-[11px] text-amber-200/90">
      <svg class="h-3.5 w-3.5 shrink-0 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
      class="w-full rounded-lg bg-[#FDB913] hover:bg-[#F2AC07] active:scale-[0.99] py-2.5 px-4 font-bold text-slate-950 text-xs sm:text-sm transition-all shadow-md shadow-[#FDB913]/25 flex items-center justify-center gap-1.5 cursor-pointer disabled:opacity-50"
    >
      <span v-if="submitting" class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-slate-950/30 border-t-slate-950"></span>
      <span>{{ submitting ? 'Processing Payment...' : 'I Have Paid' }}</span>
    </button>

    <!-- Cancel Transaction Link -->
    <div class="text-center pt-0.5">
      <button
        type="button"
        @click="$emit('cancel')"
        class="text-[10px] text-white/40 hover:text-white/80 transition-colors cursor-pointer"
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
