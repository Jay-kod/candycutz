<template>
  <div class="grid grid-cols-2 gap-4 lg:grid-cols-4 sm:gap-5">
    <!-- Total Codes -->
    <div class="group relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C]/90 p-5 backdrop-blur-xl transition-all duration-300 hover:border-white/20 hover:shadow-xl">
      <div class="flex items-center justify-between">
        <span class="text-xs font-semibold uppercase tracking-wider text-white/50">Total Codes</span>
        <div class="flex h-10 w-10 items-center justify-center rounded-xl border border-white/10 bg-white/5 text-white/60 group-hover:scale-105 transition-transform">
          <HashtagIcon class="h-5 w-5" />
        </div>
      </div>
      <div class="mt-4">
        <span class="font-display text-2xl sm:text-3xl font-bold tracking-tight text-white tabular-nums">
          {{ Number(stats.total || 0).toLocaleString() }}
        </span>
        <p class="mt-1 text-xs text-white/40">Generated overall</p>
      </div>
    </div>

    <!-- Verified Today -->
    <div class="group relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C]/90 p-5 backdrop-blur-xl transition-all duration-300 hover:border-emerald-500/30 hover:shadow-xl hover:shadow-emerald-500/5">
      <div class="flex items-center justify-between">
        <span class="text-xs font-semibold uppercase tracking-wider text-white/50">Verified Today</span>
        <div class="flex h-10 w-10 items-center justify-center rounded-xl border border-emerald-500/20 bg-emerald-500/10 text-emerald-400 group-hover:scale-105 transition-transform">
          <CheckBadgeIcon class="h-5 w-5" />
        </div>
      </div>
      <div class="mt-4">
        <div class="flex items-baseline gap-2">
          <span class="font-display text-2xl sm:text-3xl font-bold tracking-tight text-emerald-400 tabular-nums">
            {{ Number(stats.verified_today || 0).toLocaleString() }}
          </span>
          <span v-if="stats.verified_today > 0" class="flex h-2 w-2 relative">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
          </span>
        </div>
        <p class="mt-1 text-xs text-white/40">Redeemed service codes</p>
      </div>
    </div>

    <!-- Pending -->
    <div class="group relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C]/90 p-5 backdrop-blur-xl transition-all duration-300 hover:border-amber-500/30 hover:shadow-xl hover:shadow-amber-500/5">
      <div class="flex items-center justify-between">
        <span class="text-xs font-semibold uppercase tracking-wider text-white/50">Pending Codes</span>
        <div class="flex h-10 w-10 items-center justify-center rounded-xl border border-amber-500/20 bg-amber-500/10 text-amber-400 group-hover:scale-105 transition-transform">
          <ClockIcon class="h-5 w-5" />
        </div>
      </div>
      <div class="mt-4">
        <div class="flex items-baseline gap-2">
          <span class="font-display text-2xl sm:text-3xl font-bold tracking-tight text-amber-400 tabular-nums">
            {{ Number(stats.pending || 0).toLocaleString() }}
          </span>
          <span v-if="stats.pending > 0" class="rounded-full bg-amber-500/10 px-2 py-0.5 text-[10px] font-bold text-amber-400 border border-amber-500/20">
            Awaiting check-in
          </span>
        </div>
        <p class="mt-1 text-xs text-white/40">Confirmed appointments</p>
      </div>
    </div>

    <!-- Expired -->
    <div class="group relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C]/90 p-5 backdrop-blur-xl transition-all duration-300 hover:border-rose-500/30 hover:shadow-xl hover:shadow-rose-500/5">
      <div class="flex items-center justify-between">
        <span class="text-xs font-semibold uppercase tracking-wider text-white/50">Expired / Void</span>
        <div class="flex h-10 w-10 items-center justify-center rounded-xl border border-rose-500/20 bg-rose-500/10 text-rose-400 group-hover:scale-105 transition-transform">
          <XCircleIcon class="h-5 w-5" />
        </div>
      </div>
      <div class="mt-4">
        <span class="font-display text-2xl sm:text-3xl font-bold tracking-tight text-rose-400 tabular-nums">
          {{ Number(stats.expired || 0).toLocaleString() }}
        </span>
        <p class="mt-1 text-xs text-white/40">Past schedule window</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import {
  HashtagIcon,
  CheckBadgeIcon,
  ClockIcon,
  XCircleIcon
} from '@heroicons/vue/24/outline';

defineProps({
  stats: {
    type: Object,
    default: () => ({ total: 0, verified_today: 0, pending: 0, expired: 0 })
  }
});
</script>
