<template>
  <div class="grid grid-cols-2 gap-4 lg:grid-cols-4 sm:gap-5">
    <!-- Total Notifications -->
    <div class="group relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C]/90 p-5 backdrop-blur-xl transition-all duration-300 hover:border-white/20 hover:shadow-xl">
      <div class="flex items-center justify-between">
        <span class="text-xs font-semibold uppercase tracking-wider text-white/50">Total Dispatches</span>
        <div class="flex h-10 w-10 items-center justify-center rounded-xl border border-white/10 bg-white/5 text-white/60 group-hover:scale-105 transition-transform">
          <BellIcon class="h-5 w-5" />
        </div>
      </div>
      <div class="mt-4">
        <span class="font-display text-2xl sm:text-3xl font-bold tracking-tight text-white tabular-nums">
          {{ Number(stats.total || 0).toLocaleString() }}
        </span>
        <p class="mt-1 text-xs text-white/40">Logged in stream</p>
      </div>
    </div>

    <!-- Unread Alerts -->
    <div class="group relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C]/90 p-5 backdrop-blur-xl transition-all duration-300 hover:border-amber-500/30 hover:shadow-xl hover:shadow-amber-500/5">
      <div class="flex items-center justify-between">
        <span class="text-xs font-semibold uppercase tracking-wider text-white/50">Unread Alerts</span>
        <div class="flex h-10 w-10 items-center justify-center rounded-xl border border-amber-500/20 bg-amber-500/10 text-amber-400 group-hover:scale-105 transition-transform">
          <ExclamationTriangleIcon class="h-5 w-5" />
        </div>
      </div>
      <div class="mt-4">
        <div class="flex items-baseline gap-2">
          <span class="font-display text-2xl sm:text-3xl font-bold tracking-tight text-amber-400 tabular-nums">
            {{ Number(stats.unread || 0).toLocaleString() }}
          </span>
          <span v-if="stats.unread > 0" class="flex h-2 w-2 relative">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
          </span>
        </div>
        <p class="mt-1 text-xs text-white/40">Require admin attention</p>
      </div>
    </div>

    <!-- Booking Events -->
    <div class="group relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C]/90 p-5 backdrop-blur-xl transition-all duration-300 hover:border-sky-500/30 hover:shadow-xl hover:shadow-sky-500/5">
      <div class="flex items-center justify-between">
        <span class="text-xs font-semibold uppercase tracking-wider text-white/50">Bookings &amp; Service</span>
        <div class="flex h-10 w-10 items-center justify-center rounded-xl border border-sky-500/20 bg-sky-500/10 text-sky-400 group-hover:scale-105 transition-transform">
          <CalendarDaysIcon class="h-5 w-5" />
        </div>
      </div>
      <div class="mt-4">
        <span class="font-display text-2xl sm:text-3xl font-bold tracking-tight text-sky-400 tabular-nums">
          {{ Number(stats.bookings || 0).toLocaleString() }}
        </span>
        <p class="mt-1 text-xs text-white/40">Appointment dispatches</p>
      </div>
    </div>

    <!-- Security & System Alerts -->
    <div class="group relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C]/90 p-5 backdrop-blur-xl transition-all duration-300 hover:border-rose-500/30 hover:shadow-xl hover:shadow-rose-500/5">
      <div class="flex items-center justify-between">
        <span class="text-xs font-semibold uppercase tracking-wider text-white/50">System &amp; Security</span>
        <div class="flex h-10 w-10 items-center justify-center rounded-xl border border-rose-500/20 bg-rose-500/10 text-rose-400 group-hover:scale-105 transition-transform">
          <ShieldExclamationIcon class="h-5 w-5" />
        </div>
      </div>
      <div class="mt-4">
        <span class="font-display text-2xl sm:text-3xl font-bold tracking-tight text-rose-400 tabular-nums">
          {{ Number(stats.alerts || 0).toLocaleString() }}
        </span>
        <p class="mt-1 text-xs text-white/40">Payments &amp; platform signals</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import {
  BellIcon,
  ExclamationTriangleIcon,
  CalendarDaysIcon,
  ShieldAlertIcon
} from '@heroicons/vue/24/outline';

defineProps({
  stats: {
    type: Object,
    default: () => ({ total: 0, unread: 0, bookings: 0, alerts: 0 })
  }
});
</script>
