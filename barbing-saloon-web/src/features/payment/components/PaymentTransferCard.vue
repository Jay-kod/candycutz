<template>
  <div class="rounded-xl border border-white/[0.08] bg-[#121720]/90 p-3 sm:p-4 backdrop-blur-xl space-y-2.5 shadow-2xl">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <h3 class="text-xs font-bold text-white flex items-center gap-1.5 uppercase tracking-wide">
        <span class="flex h-4 w-4 items-center justify-center rounded bg-amber-500/10 text-amber-400 text-[10px]">🏦</span>
        Transfer to account details below:
      </h3>
      <span class="text-[9px] font-mono px-1.5 py-0.2 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-semibold flex items-center gap-1">
        <span class="h-1 w-1 rounded-full bg-emerald-400 animate-pulse"></span>
        Active
      </span>
    </div>

    <!-- Blue/Info Alert Banner -->
    <div class="flex items-start gap-1.5 rounded-lg bg-blue-500/10 border border-blue-500/20 p-2 text-[10px] sm:text-[11px] text-blue-300">
      <span class="flex h-3.5 w-3.5 shrink-0 items-center justify-center rounded-full bg-blue-500/20 text-blue-300 font-bold text-[9px] mt-0.5">
        ℹ
      </span>
      <p class="leading-relaxed">
        {{ noticeText }}
      </p>
    </div>

    <!-- REALISTIC BANK CARD ILLUSTRATION (Replaces the plain white data table) -->
    <div class="relative w-full rounded-2xl p-4 sm:p-5 overflow-hidden border border-amber-500/35 shadow-[0_12px_30px_-5px_rgba(0,0,0,0.8),_0_0_20px_rgba(245,158,11,0.12)] bg-gradient-to-br from-[#1C2333] via-[#101522] to-[#0A0D15] space-y-3.5 text-white selection:bg-amber-400 selection:text-slate-950">
      <!-- Metallic Card Texture Overlays -->
      <div class="pointer-events-none absolute -inset-full bg-gradient-to-tr from-transparent via-white/[0.03] to-transparent rotate-12"></div>
      <div class="pointer-events-none absolute -top-16 -right-16 h-44 w-44 rounded-full bg-amber-500/15 blur-2xl"></div>
      <div class="pointer-events-none absolute -bottom-16 -left-16 h-44 w-44 rounded-full bg-blue-500/10 blur-2xl"></div>

      <!-- Card Top Bar: EMV Chip + Contactless Waves + Bank Name -->
      <div class="relative z-10 flex items-center justify-between gap-3">
        <!-- EMV Smart Chip & Contactless Waves -->
        <div class="flex items-center gap-2">
          <!-- Realistic Golden Microchip -->
          <div class="relative flex h-8 w-11 sm:h-8.5 sm:w-11.5 shrink-0 items-center justify-center rounded bg-gradient-to-br from-amber-200 via-amber-400 to-amber-600 p-1 border border-amber-300/80 shadow-md overflow-hidden">
            <div class="w-full h-full border border-amber-900/40 rounded-sm grid grid-cols-2 grid-rows-3 gap-0.5 opacity-75">
              <div class="border-b border-r border-amber-900/40"></div>
              <div class="border-b border-amber-900/40"></div>
              <div class="border-b border-r border-amber-900/40"></div>
              <div class="border-b border-amber-900/40"></div>
              <div class="border-r border-amber-900/40"></div>
              <div></div>
            </div>
          </div>

          <!-- Contactless Payment Wave Symbol -->
          <svg class="h-4 w-4 text-amber-300/70 rotate-90" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M8.5 16.5a5 5 0 010-9m3.5 11a8 8 0 000-13m3.5 15a11 11 0 000-17" />
          </svg>
        </div>

        <!-- Bank Name Pill with Copy -->
        <div class="flex items-center gap-1.5 bg-black/45 backdrop-blur-md px-2.5 py-1 rounded-lg border border-white/10 shadow-inner">
          <div class="text-right">
            <span class="text-[8px] uppercase tracking-wider text-white/50 block font-mono leading-none">Bank Name</span>
            <span class="text-xs sm:text-sm font-black text-amber-100 font-display tracking-wide">{{ bankDetails.bank_name }}</span>
          </div>
          <button
            type="button"
            @click="handleCopy(bankDetails.bank_name, 'Bank Name')"
            class="p-0.5 text-white/50 hover:text-amber-400 transition-colors cursor-pointer"
            title="Copy Bank Name"
            aria-label="Copy Bank Name"
          >
            <span v-if="copiedField === 'Bank Name'" class="text-emerald-400 font-bold text-[9px]">✓</span>
            <svg v-else class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Card Middle: Account Number (Embossed Card Number Style) -->
      <div class="relative z-10 space-y-1">
        <div class="flex items-center justify-between text-[8px] sm:text-[9px] uppercase tracking-wider font-mono text-white/50">
          <span>Account Number</span>
          <span class="text-amber-300 font-semibold">Recipient Bank Account</span>
        </div>

        <div class="flex items-center justify-between gap-2 bg-black/40 backdrop-blur-md px-3 py-2 sm:py-2.5 rounded-xl border border-white/10">
          <span class="font-mono text-sm sm:text-lg md:text-xl font-black tracking-[0.14em] sm:tracking-[0.2em] text-white drop-shadow-[0_2px_4px_rgba(0,0,0,0.8)]">
            {{ bankDetails.account_number }}
          </span>

          <!-- Prominent Gold Copy Button -->
          <button
            type="button"
            @click="handleCopy(bankDetails.account_number, 'Account Number')"
            class="flex items-center gap-1 px-2.5 py-1 rounded-lg bg-amber-400 hover:bg-amber-300 active:bg-amber-500 text-slate-950 font-bold text-[10px] sm:text-xs transition-all shadow-md shadow-amber-400/20 cursor-pointer shrink-0"
            title="Copy account number"
            aria-label="Copy account number"
          >
            <span v-if="copiedField === 'Account Number'" class="text-slate-950 font-black">✓ Copied!</span>
            <template v-else>
              <svg class="h-3 w-3 sm:h-3.5 sm:w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
              </svg>
              <span>Copy</span>
            </template>
          </button>
        </div>
      </div>

      <!-- Card Bottom Row: Account Name (Cardholder) + Amount To Send -->
      <div class="relative z-10 flex flex-wrap sm:flex-nowrap items-end justify-between gap-2.5 pt-1 border-t border-white/[0.08]">
        <!-- Account Name / Cardholder -->
        <div class="space-y-0.5 min-w-0">
          <span class="text-[8px] uppercase tracking-widest text-white/50 block font-mono">Account Name</span>
          <div class="flex items-center gap-1.5">
            <span class="text-xs sm:text-sm font-bold text-white tracking-wide uppercase truncate font-mono drop-shadow-sm">
              {{ bankDetails.account_name }}
            </span>
            <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[8px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 shrink-0">
              ✓ Verified
            </span>
          </div>
        </div>

        <!-- Amount To Send -->
        <div class="space-y-0.5 text-right shrink-0 ml-auto sm:ml-0">
          <span class="text-[8px] uppercase tracking-widest text-amber-400 font-bold block font-mono">Amount to send</span>
          <div class="flex items-center justify-end gap-1.5">
            <span class="text-xs sm:text-sm font-black text-amber-300 font-mono tracking-tight drop-shadow-sm">
              {{ formattedAmount }}
            </span>
            <button
              type="button"
              @click="handleCopy(amount, 'Amount')"
              class="flex items-center gap-1 px-1.5 py-0.5 rounded bg-white/10 hover:bg-white/20 text-white/80 text-[10px] font-semibold transition-colors cursor-pointer"
              title="Copy amount"
              aria-label="Copy amount"
            >
              <span v-if="copiedField === 'Amount'" class="text-emerald-400 font-bold text-[9px]">✓</span>
              <template v-else>
                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                </svg>
                <span class="text-[9px]">Copy</span>
              </template>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Countdown Timer Notice -->
    <div class="flex items-center gap-1.5 rounded-lg bg-amber-500/[0.08] border border-amber-500/20 px-2.5 py-1.5 text-[10px] sm:text-[11px] text-amber-200/90">
      <svg class="h-3.5 w-3.5 shrink-0 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <p class="leading-tight">
        These account details are valid for this transaction only and will expire in
        <strong class="text-white font-mono font-bold">{{ formattedTimeRemaining }}</strong>.
      </p>
    </div>

    <!-- Instruction Subtext -->
    <div class="border-t border-white/[0.06] pt-1.5">
      <p class="text-[10px] text-white/50 text-center">
        Tap the "I have paid" button below after completing your transfer.
      </p>
    </div>

    <!-- Main Action Button: I Have Paid -->
    <button
      type="button"
      @click="$emit('confirm')"
      :disabled="submitting"
      class="w-full rounded-lg bg-[#FDB913] hover:bg-[#F2AC07] active:scale-[0.99] py-2.5 px-4 font-bold text-slate-950 text-xs sm:text-sm transition-all shadow-md shadow-[#FDB913]/25 flex items-center justify-center gap-1.5 cursor-pointer disabled:opacity-50"
    >
      <span v-if="submitting" class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-slate-950/30 border-t-slate-950"></span>
      <span>{{ submitting ? 'Verifying & Saving to DB...' : 'I Have Paid' }}</span>
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
import { ref } from 'vue';

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

const emit = defineEmits(['confirm', 'cancel', 'copy']);

const copiedField = ref(null);

function handleCopy(val, label) {
  copiedField.value = label;
  emit('copy', val, label);
  setTimeout(() => {
    if (copiedField.value === label) {
      copiedField.value = null;
    }
  }, 1800);
}
</script>
