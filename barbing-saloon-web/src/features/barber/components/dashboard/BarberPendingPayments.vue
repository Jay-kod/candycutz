<template>
  <div v-if="payments?.length > 0" class="rounded-2xl border border-blue-500/30 bg-blue-500/5 backdrop-blur-sm overflow-hidden shadow-[0_8px_30px_rgba(59,130,246,0.1)]">
    <div class="flex items-center justify-between border-b border-blue-500/20 px-6 py-4 bg-blue-500/10">
      <div class="flex items-center gap-3">
        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-500/20">
          <CheckIcon class="h-5 w-5 text-blue-400" />
        </div>
        <h2 class="font-display text-lg text-blue-400 drop-shadow-sm">Action Required: Verify Payments</h2>
      </div>
    </div>

    <div class="divide-y divide-blue-500/10">
      <div
        v-for="booking in payments"
        :key="'pay-' + booking.id"
        class="group flex items-start gap-4 px-6 py-4 transition-colors hover:bg-blue-500/10"
      >
        <div class="flex-1 min-w-0">
          <div class="flex items-center justify-between gap-2">
            <p class="text-sm font-semibold text-theme-text truncate">{{ booking.customer_name }}</p>
            <span class="shrink-0 text-sm text-blue-400 font-bold tracking-tight">₦{{ booking.price?.toLocaleString() }}</span>
          </div>
          <p class="mt-0.5 text-xs text-ivory/40">{{ booking.service_name || 'General' }}</p>
          <div class="mt-3 flex items-center gap-2">
            <button
              @click="$emit('view-receipt', booking)"
              class="inline-flex items-center justify-center gap-1 rounded-xl bg-blue-500/20 px-4 py-1.5 text-xs font-bold text-blue-400 uppercase tracking-wider hover:bg-blue-500 hover:text-white hover:shadow-[0_0_15px_rgba(59,130,246,0.5)] transition-all"
            >
              View Receipt
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { CheckIcon } from '@heroicons/vue/24/outline';

defineProps({
  payments: { type: Array, required: true }
});

defineEmits(['view-receipt']);
</script>
