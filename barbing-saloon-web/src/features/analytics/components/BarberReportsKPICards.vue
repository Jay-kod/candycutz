<template>
  <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
    <!-- Revenue -->
    <div class="group rounded-xl border border-theme-border bg-theme-surface p-6 transition-colors duration-300 hover:border-gold/30">
      <div class="flex items-center gap-3 mb-4">
        <BanknotesIcon class="h-5 w-5 text-emerald-400" />
        <p class="text-sm text-ivory/60 font-medium">Total Revenue</p>
      </div>
      <p class="font-display text-3xl text-emerald-400 tabular-nums">₦{{ formatCurrency(performanceMetrics.total_revenue) }}</p>
      <p class="mt-2 text-xs text-ivory/40">From completed bookings</p>
    </div>

    <!-- Appointments -->
    <div class="group rounded-xl border border-theme-border bg-theme-surface p-6 transition-colors duration-300 hover:border-gold/30">
      <div class="flex items-center gap-3 mb-4">
        <CalendarDaysIcon class="h-5 w-5 text-gold" />
        <p class="text-sm text-ivory/60 font-medium">Appointments</p>
      </div>
      <p class="font-display text-3xl text-gold-light tabular-nums">{{ performanceMetrics.clients_served || 0 }}</p>
      <p class="mt-2 text-xs text-ivory/40">Successfully completed</p>
    </div>

    <!-- Completion Rate -->
    <div class="group rounded-xl border border-theme-border bg-theme-surface p-6 transition-colors duration-300 hover:border-gold/30">
      <div class="flex items-center gap-3 mb-4">
        <UsersIcon class="h-5 w-5 text-blue-400" />
        <p class="text-sm text-ivory/60 font-medium">Completion Rate (%)</p>
      </div>
      <p class="font-display text-3xl text-blue-400 tabular-nums">{{ performanceMetrics.completion_rate || 0 }}</p>
      <p class="mt-2 text-xs text-ivory/40">Appointments completed</p>
    </div>

    <!-- Avg Rating -->
    <div class="group rounded-xl border border-theme-border bg-theme-surface p-6 transition-colors duration-300 hover:border-gold/30">
      <div class="flex items-center gap-3 mb-4">
        <UserGroupIcon class="h-5 w-5 text-sky-400" />
        <p class="text-sm text-ivory/60 font-medium">Average Rating</p>
      </div>
      <p class="font-display text-3xl text-sky-400 tabular-nums">⭐ {{ performanceMetrics.average_rating || 0 }}</p>
      <p class="mt-2 text-xs text-ivory/40">From customer reviews</p>
    </div>
  </div>
</template>

<script setup>
import { BanknotesIcon, CalendarDaysIcon, UsersIcon, UserGroupIcon } from '@heroicons/vue/24/outline';

defineProps({
  performanceMetrics: { type: Object, required: true }
});

const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-NG', { maximumFractionDigits: 0 }).format(Number(value || 0));
};
</script>
