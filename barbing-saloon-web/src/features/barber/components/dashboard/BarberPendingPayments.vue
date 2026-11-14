<template>
  <div v-if="payments?.length > 0" class="rounded-3xl border border-gold/30 bg-white dark:bg-[#12161C] overflow-hidden shadow-2xl relative">
    <div class="flex items-center justify-between border-b border-slate-200 dark:border-white/[0.08] px-6 py-4 bg-amber-50 dark:bg-gold/10">
      <div class="flex items-center gap-3">
        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gold/20 border border-gold/30">
          <BanknotesIcon class="h-5 w-5 text-gold" />
        </div>
        <div>
          <h2 class="font-display text-sm font-bold text-slate-900 dark:text-white tracking-wide">
            Action Required: Verify Customer Payments
          </h2>
          <p class="text-[10px] text-amber-700 dark:text-gold uppercase tracking-widest font-semibold">
            {{ payments.length }} receipt(s) awaiting review
          </p>
        </div>
      </div>
    </div>

    <div class="divide-y divide-slate-100 dark:divide-white/[0.06]">
      <div
        v-for="booking in payments"
        :key="'pay-' + booking.id"
        class="group flex items-center justify-between gap-4 px-6 py-4 transition-colors hover:bg-slate-50 dark:hover:bg-white/[0.02]"
        data-test="pending-payment-item"
      >
        <div class="flex-1 min-w-0">
          <div class="flex items-center justify-between gap-2">
            <p class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ booking.customer_name }}</p>
            <span class="shrink-0 text-sm text-gold font-mono font-bold tracking-tight">
              ₦{{ Number(booking.price || 0).toLocaleString() }}
            </span>
          </div>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 truncate">{{ booking.service_name || 'Saloon Treatment' }}</p>
        </div>

        <button
          @click="$emit('view-receipt', booking)"
          class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-gold/10 border border-gold/20 px-4 py-2 text-xs font-bold text-gold uppercase tracking-wider hover:bg-gold hover:text-obsidian hover:shadow-[0_0_15px_rgba(255,153,0,0.35)] transition-all shrink-0"
        >
          <DocumentTextIcon class="h-3.5 w-3.5" />
          Inspect Receipt
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { BanknotesIcon, DocumentTextIcon } from '@heroicons/vue/24/outline';

defineProps({
  payments: {
    type: Array,
    required: true,
  },
});

defineEmits(['view-receipt']);
</script>
