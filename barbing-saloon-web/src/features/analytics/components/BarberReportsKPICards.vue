<template>
  <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
    <!-- Revenue -->
    <div class="group relative overflow-hidden rounded-2xl border border-gold/20 bg-black/20 p-6 backdrop-blur-sm transition-all duration-300 hover:border-gold/40 hover:bg-black/40 hover:shadow-[0_10px_30px_rgba(255,103,0,0.12)]">
      <div class="absolute -right-8 -top-8 h-24 w-24 rounded-full bg-emerald-500/10 blur-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
      <div class="relative z-10">
        <div class="flex items-center gap-3 mb-4">
          <div class="h-10 w-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center">
            <BanknotesIcon class="h-5 w-5 text-emerald-400" />
          </div>
          <p class="text-sm text-ivory/60 font-medium">Total Revenue</p>
        </div>
        <p class="font-display text-3xl text-emerald-400">₦{{ Number(performanceMetrics.total_revenue || 0).toLocaleString() }}</p>
        <p class="mt-2 text-xs text-ivory/40">From completed bookings</p>
      </div>
    </div>

    <!-- Appointments -->
    <div class="group relative overflow-hidden rounded-2xl border border-white/5 bg-black/20 p-6 backdrop-blur-sm transition-all duration-300 hover:border-gold/30 hover:bg-black/40">
      <div class="relative z-10">
        <div class="flex items-center gap-3 mb-4">
          <div class="h-10 w-10 rounded-xl bg-gold/10 border border-gold/20 flex items-center justify-center">
            <CalendarDaysIcon class="h-5 w-5 text-gold" />
          </div>
          <p class="text-sm text-ivory/60 font-medium">Appointments</p>
        </div>
        <p class="font-display text-3xl text-gold-light">{{ performanceMetrics.clients_served || 0 }}</p>
        <p class="mt-2 text-xs text-ivory/40">Successfully completed</p>
      </div>
    </div>

    <!-- Completion Rate -->
    <div class="group relative overflow-hidden rounded-2xl border border-white/5 bg-black/20 p-6 backdrop-blur-sm transition-all duration-300 hover:border-gold/30 hover:bg-black/40">
      <div class="relative z-10">
        <div class="flex items-center gap-3 mb-4">
          <div class="h-10 w-10 rounded-xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center">
            <UsersIcon class="h-5 w-5 text-blue-400" />
          </div>
          <p class="text-sm text-ivory/60 font-medium">Completion Rate (%)</p>
        </div>
        <p class="font-display text-3xl text-blue-400">{{ performanceMetrics.completion_rate || 0 }}</p>
        <p class="mt-2 text-xs text-ivory/40">Appointments completed</p>
      </div>
    </div>

    <!-- Avg Rating -->
    <div class="group relative overflow-hidden rounded-2xl border border-white/5 bg-black/20 p-6 backdrop-blur-sm transition-all duration-300 hover:border-gold/30 hover:bg-black/40">
      <div class="relative z-10">
        <div class="flex items-center gap-3 mb-4">
          <div class="h-10 w-10 rounded-xl bg-sky-500/10 border border-sky-500/20 flex items-center justify-center">
            <UserGroupIcon class="h-5 w-5 text-sky-400" />
          </div>
          <p class="text-sm text-ivory/60 font-medium">Average Rating</p>
        </div>
        <p class="font-display text-3xl text-sky-400">⭐ {{ performanceMetrics.average_rating || 0 }}</p>
        <p class="mt-2 text-xs text-ivory/40">From customer reviews</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { BanknotesIcon, CalendarDaysIcon, UsersIcon, UserGroupIcon } from '@heroicons/vue/24/outline';

defineProps({
  performanceMetrics: { type: Object, required: true }
});
</script>
