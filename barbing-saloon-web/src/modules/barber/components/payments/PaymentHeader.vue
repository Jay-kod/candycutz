<template>
  <div class="relative overflow-hidden rounded-3xl border border-white/[0.08] bg-[#12161C] p-6 sm:p-8 md:p-10 shadow-2xl flex flex-col md:flex-row md:items-end justify-between gap-6 group">
    <div class="absolute -top-36 -right-36 w-80 h-80 bg-emerald-500/10 rounded-full blur-[100px] pointer-events-none group-hover:bg-emerald-500/15 transition-colors duration-700"></div>
    <div class="absolute -bottom-36 -left-36 w-80 h-80 bg-[#FF6700]/10 rounded-full blur-[100px] pointer-events-none group-hover:bg-[#FF6700]/15 transition-colors duration-700"></div>

    <div class="relative z-10">
      <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 mb-4">
        <BanknotesIcon class="h-3.5 w-3.5 text-emerald-400" />
        <span class="text-[10px] font-bold text-emerald-400 tracking-widest uppercase">Revenue Audit</span>
      </div>
      <h1 class="text-3xl sm:text-4xl md:text-5xl font-display font-bold text-white tracking-tight mb-2">
        Payment <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 via-teal-300 to-[#FF6700]">Verification</span>
      </h1>
      <p class="text-slate-400 text-xs sm:text-sm max-w-lg leading-relaxed">
        Review uploaded customer transfer receipts, verify bank transactions, and configure payment payout destination.
      </p>
    </div>

    <div class="relative z-10 flex flex-wrap items-center gap-3 shrink-0">
      <button
        @click="$emit('refresh')"
        :disabled="loading"
        class="flex items-center gap-2 rounded-2xl bg-white/[0.03] border border-white/[0.08] px-4 py-3 text-xs font-bold uppercase tracking-wider text-slate-300 hover:text-white hover:bg-white/[0.06] transition-all disabled:opacity-50"
        data-test="refresh-payments"
      >
        <ArrowPathIcon class="h-4 w-4" :class="{ 'animate-spin': loading }" />
        Refresh
      </button>

      <button
        @click="$emit('open-account')"
        class="flex items-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-400 px-5 py-3 text-xs font-bold uppercase tracking-wider text-[#0B0F13] shadow-lg shadow-emerald-500/20 hover:shadow-[0_8px_30px_rgba(16,185,129,0.35)] transition-all hover:-translate-y-0.5 active:translate-y-0"
        data-test="open-bank-modal"
      >
        <BuildingLibraryIcon class="h-4 w-4" />
        Bank Details
      </button>

      <div class="flex items-center gap-2 rounded-2xl bg-[#0B0F13] border border-white/[0.08] px-4 py-2.5">
        <MagnifyingGlassIcon class="h-4 w-4 text-slate-400" />
        <input
          :value="search"
          @input="$emit('update:search', $event.target.value)"
          type="text"
          placeholder="Search guest or cut..."
          class="bg-transparent text-xs text-white placeholder:text-slate-500 outline-none w-36 sm:w-44"
          data-test="payment-search-input"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import {
  BanknotesIcon,
  ArrowPathIcon,
  BuildingLibraryIcon,
  MagnifyingGlassIcon,
} from '@heroicons/vue/24/outline';

defineProps({
  loading: {
    type: Boolean,
    default: false,
  },
  search: {
    type: String,
    default: '',
  },
});

defineEmits(['refresh', 'open-account', 'update:search']);
</script>
