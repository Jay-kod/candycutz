<template>
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- Total Codes -->
    <div class="group relative overflow-hidden rounded-2xl border border-white/5 bg-black/20 p-5 backdrop-blur-sm transition-all duration-300 hover:border-admin/30 hover:bg-black/40">
      <div class="absolute -right-6 -top-6 h-20 w-20 rounded-full bg-admin/5 blur-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
      <div class="relative z-10">
        <div class="flex items-center justify-between mb-3">
          <div class="h-9 w-9 rounded-xl bg-admin/10 border border-admin/20 flex items-center justify-center">
            <HashtagIcon class="h-4 w-4 text-admin" />
          </div>
          <div class="h-6 w-6 rounded-full bg-white/5 flex items-center justify-center">
            <ChevronRightIcon class="h-3 w-3 text-white/20" />
          </div>
        </div>
        <p class="font-display text-3xl text-white mb-1">{{ stats.total }}</p>
        <p class="text-[10px] uppercase tracking-widest text-white/40 font-bold">Total Codes</p>
      </div>
    </div>

    <!-- Verified Today -->
    <div class="group relative overflow-hidden rounded-2xl border border-white/5 bg-black/20 p-5 backdrop-blur-sm transition-all duration-300 hover:border-emerald-500/30 hover:bg-black/40">
      <div class="absolute -right-6 -top-6 h-20 w-20 rounded-full bg-emerald-500/5 blur-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
      <div class="relative z-10">
        <div class="flex items-center justify-between mb-3">
          <div class="h-9 w-9 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center">
            <CheckBadgeIcon class="h-4 w-4 text-emerald-400" />
          </div>
          <span v-if="stats.verified_today > 0" class="flex h-2 w-2">
            <span class="animate-ping absolute h-2 w-2 rounded-full bg-emerald-400 opacity-75"></span>
            <span class="relative rounded-full h-2 w-2 bg-emerald-500"></span>
          </span>
        </div>
        <p class="font-display text-3xl text-emerald-400 mb-1">{{ stats.verified_today }}</p>
        <p class="text-[10px] uppercase tracking-widest text-emerald-400/50 font-bold">Verified Today</p>
      </div>
    </div>

    <!-- Pending -->
    <div class="group relative overflow-hidden rounded-2xl border border-white/5 bg-black/20 p-5 backdrop-blur-sm transition-all duration-300 hover:border-amber-500/30 hover:bg-black/40">
      <div class="absolute -right-6 -top-6 h-20 w-20 rounded-full bg-amber-500/5 blur-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
      <div class="relative z-10">
        <div class="flex items-center justify-between mb-3">
          <div class="h-9 w-9 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center">
            <ClockIcon class="h-4 w-4 text-amber-400" />
          </div>
          <span v-if="stats.pending > 0" class="text-[10px] font-bold text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded-full border border-amber-500/20">{{ stats.pending }} waiting</span>
        </div>
        <p class="font-display text-3xl text-amber-400 mb-1">{{ stats.pending }}</p>
        <p class="text-[10px] uppercase tracking-widest text-amber-400/50 font-bold">Pending</p>
      </div>
    </div>

    <!-- Expired -->
    <div class="group relative overflow-hidden rounded-2xl border border-white/5 bg-black/20 p-5 backdrop-blur-sm transition-all duration-300 hover:border-red-500/30 hover:bg-black/40">
      <div class="absolute -right-6 -top-6 h-20 w-20 rounded-full bg-red-500/5 blur-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
      <div class="relative z-10">
        <div class="flex items-center justify-between mb-3">
          <div class="h-9 w-9 rounded-xl bg-red-500/10 border border-red-500/20 flex items-center justify-center">
            <XCircleIcon class="h-4 w-4 text-red-400" />
          </div>
        </div>
        <p class="font-display text-3xl text-red-400 mb-1">{{ stats.expired }}</p>
        <p class="text-[10px] uppercase tracking-widest text-red-400/50 font-bold">Expired</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import {
  HashtagIcon,
  ChevronRightIcon,
  CheckBadgeIcon,
  ClockIcon,
  XCircleIcon
} from '@heroicons/vue/24/outline';

defineProps({
  stats: {
    type: Object,
    required: true,
    default: () => ({ total: 0, verified_today: 0, pending: 0, expired: 0 })
  }
});
</script>
