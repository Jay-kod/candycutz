<template>
  <div class="space-y-6">
    <!-- Virtual Glass Card -->
    <div class="relative group perspective-1000">
      <div class="relative rounded-3xl overflow-hidden border border-white/15 bg-gradient-to-br from-[#1F242D] via-[#12161C] to-[#0A0D11] p-7 sm:p-8 shadow-2xl backdrop-blur-2xl transition-all duration-500 hover:scale-[1.02] hover:border-[var(--color-accent)]/40">
        <!-- Subtle card background glow & pattern -->
        <div class="absolute -right-16 -top-16 h-48 w-48 rounded-full bg-[var(--color-accent)]/20 blur-3xl pointer-events-none"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_bottom_left,rgba(212,175,55,0.08),transparent_70%)] pointer-events-none"></div>

        <!-- Top Row: Chip & Contactless Wave -->
        <div class="relative z-10 flex justify-between items-center mb-8">
          <!-- Gold EMV Chip Simulation -->
          <div class="h-9 w-12 rounded-lg bg-gradient-to-r from-amber-300 via-amber-400 to-amber-500 shadow-md border border-amber-200/50 flex flex-col justify-around p-1.5 opacity-90">
            <div class="h-0.5 w-full bg-amber-700/40 rounded-full"></div>
            <div class="h-0.5 w-3/4 bg-amber-700/40 rounded-full"></div>
            <div class="h-0.5 w-full bg-amber-700/40 rounded-full"></div>
          </div>

          <!-- Contactless Payment Icon -->
          <div class="flex items-center gap-2">
            <span class="text-[9px] uppercase tracking-widest font-mono text-[var(--color-accent)] bg-[var(--color-accent)]/15 border border-[var(--color-accent)]/30 px-2 py-0.5 rounded-full font-bold">
              Official Payout
            </span>
            <svg class="h-6 w-6 text-white/30" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.5 16.5a5 5 0 010-9m3.5 11a8 8 0 000-13m3.5 15a11 11 0 000-17" />
            </svg>
          </div>
        </div>

        <!-- Middle: Account Number with 1-click Copy -->
        <div class="relative z-10 space-y-2 mb-8">
          <p class="text-[10px] uppercase tracking-[0.25em] text-white/50 font-bold font-mono">
            Account Number
          </p>
          <div class="flex items-center justify-between bg-black/40 rounded-2xl px-4 py-3 border border-white/10 group-hover:border-[var(--color-accent)]/30 transition-colors">
            <span class="font-mono text-xl sm:text-2xl font-bold tracking-[0.18em] text-[var(--color-accent)] drop-shadow-md">
              {{ formattedAccountNumber || '•••• •••• ••' }}
            </span>
            <button
              type="button"
              @click="$emit('copy', accountNumber, 'Account Number')"
              class="p-2 text-[var(--color-accent)] hover:bg-[var(--color-accent)]/20 rounded-xl transition-all active:scale-90 cursor-pointer"
              title="Copy Account Number"
            >
              <DocumentDuplicateIcon class="h-5 w-5" />
            </button>
          </div>
        </div>

        <!-- Bottom Row: Account Holder & Bank Name -->
        <div class="relative z-10 flex items-end justify-between border-t border-white/10 pt-4">
          <div class="space-y-0.5 max-w-[60%]">
            <p class="text-[9px] uppercase tracking-wider text-white/40 font-bold">Account Holder</p>
            <p class="font-display text-sm font-bold text-white tracking-wide truncate">
              {{ accountName || 'CANDY CUTZ SALOON' }}
            </p>
          </div>
          <div class="text-right space-y-0.5 max-w-[40%]">
            <p class="text-[9px] uppercase tracking-wider text-white/40 font-bold">Destination Bank</p>
            <p class="text-xs font-bold text-[var(--color-accent)] tracking-wider uppercase truncate">
              {{ bankName || 'SELECT BANK' }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Live Display Surfaces Indicator -->
    <div class="rounded-2xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-6 shadow-xl space-y-4">
      <div class="flex items-center gap-2 pb-3 border-b border-slate-100 dark:border-white/[0.06]">
        <GlobeAltIcon class="h-4 w-4 text-[var(--color-accent)]" />
        <h2 class="text-xs uppercase tracking-widest font-bold text-slate-900 dark:text-white">Live Display Surfaces</h2>
      </div>

      <p class="text-xs text-slate-600 dark:text-[var(--color-text-secondary)]">
        This account is dynamically queried by these live application endpoints:
      </p>

      <ul class="space-y-2.5 text-xs">
        <li class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-white/[0.02] border border-slate-200 dark:border-white/[0.04]">
          <div class="flex items-center gap-2">
            <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
            <span class="text-slate-900 dark:text-white font-medium">Custom Payment Modal</span>
          </div>
          <span class="text-[10px] text-slate-500 dark:text-zinc-400 font-mono">/payment/:id</span>
        </li>
        <li class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-white/[0.02] border border-slate-200 dark:border-white/[0.04]">
          <div class="flex items-center gap-2">
            <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
            <span class="text-slate-900 dark:text-white font-medium">Customer Checkout & Bookings</span>
          </div>
          <span class="text-[10px] text-slate-500 dark:text-zinc-400 font-mono">/customer/dashboard</span>
        </li>
        <li class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-white/[0.02] border border-slate-200 dark:border-white/[0.04]">
          <div class="flex items-center gap-2">
            <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
            <span class="text-slate-900 dark:text-white font-medium">Barber & Admin Payouts</span>
          </div>
          <span class="text-[10px] text-slate-500 dark:text-zinc-400 font-mono">/api/v1/payments/checkout-data</span>
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { DocumentDuplicateIcon, GlobeAltIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  bankName: { type: String, default: '' },
  accountName: { type: String, default: '' },
  accountNumber: { type: String, default: '' }
});

defineEmits(['copy']);

const formattedAccountNumber = computed(() => {
  if (!props.accountNumber) return '';
  const clean = String(props.accountNumber).replace(/\D/g, '');
  if (clean.length === 10) {
    return `${clean.slice(0, 3)} ${clean.slice(3, 6)} ${clean.slice(6)}`;
  }
  return clean;
});
</script>

<style scoped>
.perspective-1000 {
  perspective: 1000px;
}
</style>
