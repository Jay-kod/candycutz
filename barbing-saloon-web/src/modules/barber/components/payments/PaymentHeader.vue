<template>
  <div class="relative overflow-hidden rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-4 xs:p-6 sm:p-8 md:p-10 shadow-sm dark:shadow-2xl flex flex-col md:flex-row md:items-end justify-between gap-6 group">
    <div class="absolute -top-36 -right-36 w-80 h-80 bg-gold/10 rounded-full blur-[100px] pointer-events-none group-hover:bg-gold/15 transition-colors duration-700"></div>
    <div class="absolute -bottom-36 -left-36 w-80 h-80 bg-amber-500/10 rounded-full blur-[100px] pointer-events-none group-hover:bg-amber-500/15 transition-colors duration-700"></div>

    <div class="relative z-10">
      <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-gold/10 border border-gold/20 mb-4">
        <BanknotesIcon class="h-3.5 w-3.5 text-gold" />
        <span class="text-[10px] font-bold text-gold tracking-widest uppercase">Revenue Audit</span>
      </div>
      <h1 class="text-3xl sm:text-4xl md:text-5xl font-display font-bold text-slate-900 dark:text-white tracking-tight mb-2">
        Payment <span class="text-transparent bg-clip-text bg-gradient-to-r from-gold via-amber-400 to-amber-200">Verification</span>
      </h1>
      <p class="text-slate-600 dark:text-slate-400 text-xs sm:text-sm max-w-lg leading-relaxed">
        Review uploaded customer transfer receipts, verify bank transactions, and configure payment payout destination.
      </p>
    </div>

    <div class="relative z-10 flex flex-wrap items-center gap-3 shrink-0">
      <button
        @click="$emit('refresh')"
        :disabled="loading"
        class="flex items-center gap-2 rounded-2xl bg-slate-100 hover:bg-slate-200 dark:bg-white/[0.03] border border-slate-200 dark:border-white/[0.08] px-4 py-3 text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white dark:hover:bg-white/[0.06] transition-all disabled:opacity-50"
        data-test="refresh-payments"
      >
        <ArrowPathIcon class="h-4 w-4" :class="{ 'animate-spin': loading }" />
        Refresh
      </button>

      <button
        @click="$emit('open-account')"
        class="flex items-center gap-2 rounded-2xl bg-gradient-to-r from-gold via-amber-400 to-amber-500 px-5 py-3 text-xs font-bold uppercase tracking-wider text-obsidian shadow-lg shadow-gold/20 hover:shadow-[0_8px_30px_rgba(255,153,0,0.35)] transition-all hover:-translate-y-0.5 active:translate-y-0"
        data-test="open-bank-modal"
      >
        <BuildingLibraryIcon class="h-4 w-4" />
        Bank Details
      </button>

      <div class="flex items-center gap-2 rounded-2xl bg-slate-50 dark:bg-[#0B0F13] border border-slate-300 dark:border-white/[0.08] px-4 py-2.5">
        <MagnifyingGlassIcon class="h-4 w-4 text-slate-400" />
        <input
          :value="search"
          @input="$emit('update:search', $event.target.value)"
          type="text"
          placeholder="Search guest or cut..."
          class="bg-transparent text-xs text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 outline-none w-36 sm:w-44"
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
