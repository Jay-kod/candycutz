<script setup>
import {
  ArchiveBoxIcon,
  CheckCircleIcon,
  FolderIcon,
  ClockIcon,
} from '@heroicons/vue/24/outline'

defineProps({
  summary: {
    type: Object,
    default: () => ({ total: 0, valid: 0, totalSize: '0 MB', latestTime: null }),
  },
  loading: {
    type: Boolean,
    default: false,
  },
  formatTime: {
    type: Function,
    required: true,
  },
})
</script>

<template>
  <div v-if="loading" class="grid gap-4 grid-cols-2 lg:grid-cols-4">
    <div
      v-for="i in 4"
      :key="i"
      class="h-24 rounded-2xl bg-[#12161C] border border-white/[0.06] p-4 flex flex-col justify-between animate-pulse"
    >
      <div class="flex items-center justify-between">
        <div class="w-16 h-3 rounded bg-white/[0.05]"></div>
        <div class="w-7 h-7 rounded-lg bg-white/[0.05]"></div>
      </div>
      <div class="w-20 h-6 rounded bg-white/[0.08]"></div>
    </div>
  </div>

  <div v-else class="grid gap-4 grid-cols-2 lg:grid-cols-4">
    <!-- Total Snapshots -->
    <div class="rounded-2xl p-4 bg-[#12161C] border border-white/[0.08] hover:border-white/[0.18] transition-all relative overflow-hidden group shadow-lg">
      <div class="absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r from-blue-500 to-transparent opacity-60 group-hover:opacity-100 transition-opacity"></div>
      <div class="flex items-center justify-between mb-2">
        <span class="text-[11px] font-semibold uppercase tracking-wider text-zinc-400">Total Snapshots</span>
        <div class="w-7 h-7 rounded-lg bg-blue-500/10 border border-blue-500/25 flex items-center justify-center">
          <ArchiveBoxIcon class="w-4 h-4 text-blue-400" />
        </div>
      </div>
      <p class="text-2xl font-bold font-mono text-white tracking-tight">{{ summary.total }}</p>
      <span class="text-[10px] text-zinc-500 mt-1 block">Registered in volume storage</span>
    </div>

    <!-- Valid Verified -->
    <div class="rounded-2xl p-4 bg-[#12161C] border border-emerald-500/20 hover:border-emerald-500/40 transition-all relative overflow-hidden group shadow-lg">
      <div class="absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r from-emerald-500 to-transparent opacity-60 group-hover:opacity-100 transition-opacity"></div>
      <div class="flex items-center justify-between mb-2">
        <span class="text-[11px] font-semibold uppercase tracking-wider text-emerald-400">Verified Archives</span>
        <div class="w-7 h-7 rounded-lg bg-emerald-500/10 border border-emerald-500/25 flex items-center justify-center">
          <CheckCircleIcon class="w-4 h-4 text-emerald-400" />
        </div>
      </div>
      <p class="text-2xl font-bold font-mono text-emerald-400 tracking-tight">{{ summary.valid }}</p>
      <span class="text-[10px] text-zinc-500 mt-1 block">Integrity checks confirmed</span>
    </div>

    <!-- Total Footprint -->
    <div class="rounded-2xl p-4 bg-[#12161C] border border-purple-500/20 hover:border-purple-500/40 transition-all relative overflow-hidden group shadow-lg">
      <div class="absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r from-purple-500 to-transparent opacity-60 group-hover:opacity-100 transition-opacity"></div>
      <div class="flex items-center justify-between mb-2">
        <span class="text-[11px] font-semibold uppercase tracking-wider text-purple-400">Total Footprint</span>
        <div class="w-7 h-7 rounded-lg bg-purple-500/10 border border-purple-500/25 flex items-center justify-center">
          <FolderIcon class="w-4 h-4 text-purple-400" />
        </div>
      </div>
      <p class="text-xl font-bold font-mono text-white tracking-tight">{{ summary.totalSize }}</p>
      <span class="text-[10px] text-zinc-500 mt-1 block">Combined disk capacity</span>
    </div>

    <!-- Latest Snapshot Time -->
    <div class="rounded-2xl p-4 bg-[#12161C] border border-amber-500/20 hover:border-amber-500/40 transition-all relative overflow-hidden group shadow-lg">
      <div class="absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r from-amber-500 to-transparent opacity-60 group-hover:opacity-100 transition-opacity"></div>
      <div class="flex items-center justify-between mb-2">
        <span class="text-[11px] font-semibold uppercase tracking-wider text-amber-400">Latest Recovery Point</span>
        <div class="w-7 h-7 rounded-lg bg-amber-500/10 border border-amber-500/25 flex items-center justify-center">
          <ClockIcon class="w-4 h-4 text-amber-400" />
        </div>
      </div>
      <p class="text-sm font-semibold text-white truncate" :title="summary.latestTime ? formatTime(summary.latestTime) : 'None'">
        {{ summary.latestTime ? formatTime(summary.latestTime) : 'None Available' }}
      </p>
      <span class="text-[10px] text-zinc-500 mt-1 block">Most recent point-in-time</span>
    </div>
  </div>
</template>
