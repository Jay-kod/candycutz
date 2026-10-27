<script setup>
import {
  BugAntIcon,
  ArrowPathIcon,
  ExclamationCircleIcon,
  ShieldExclamationIcon,
  CheckCircleIcon,
  FireIcon,
} from '@heroicons/vue/24/outline'

defineProps({
  loading: {
    type: Boolean,
    default: false,
  },
  stats: {
    type: Object,
    default: () => ({ total: 0, open: 0, critical: 0, resolved: 0 }),
  },
})

defineEmits(['refresh'])
</script>

<template>
  <div class="space-y-6">
    <!-- Executive Title & Actions Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-rose-500/20 via-orange-500/10 to-transparent border border-rose-500/30 flex items-center justify-center shadow-lg shadow-rose-500/10">
          <BugAntIcon class="w-6 h-6 text-rose-400" />
        </div>
        <div>
          <div class="flex items-center gap-2.5">
            <h1 class="text-2xl font-bold tracking-tight text-white">System Errors & Incidents</h1>
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider bg-rose-500/10 text-rose-400 border border-rose-500/25">
              Incident Response
            </span>
          </div>
          <p class="text-xs text-zinc-400 mt-0.5">Observability error groups, anomaly patterns, and distributed request tracing</p>
        </div>
      </div>

      <button
        type="button"
        @click="$emit('refresh')"
        :disabled="loading"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#12161C] border border-white/[0.08] text-zinc-200 hover:text-white hover:bg-white/[0.06] hover:border-white/[0.16] transition-all text-xs font-semibold disabled:opacity-50 shadow-sm"
      >
        <ArrowPathIcon class="w-3.5 h-3.5" :class="{ 'animate-spin': loading }" />
        <span>{{ loading ? 'Scanning Records…' : 'Refresh Incidents' }}</span>
      </button>
    </div>

    <!-- Quick Incident Counters Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
      <!-- Total Groups -->
      <div class="rounded-2xl p-4 bg-[#12161C] border border-white/[0.08] relative overflow-hidden group hover:border-white/[0.18] transition-all">
        <div class="flex items-center justify-between mb-2">
          <span class="text-[11px] font-semibold uppercase tracking-wider text-zinc-400">Total Signatures</span>
          <div class="w-7 h-7 rounded-lg bg-white/[0.04] border border-white/[0.06] flex items-center justify-center">
            <ExclamationCircleIcon class="w-4 h-4 text-zinc-400" />
          </div>
        </div>
        <p class="text-2xl font-bold text-white tracking-tight">{{ stats.total }}</p>
        <span class="text-[10px] text-zinc-500 mt-1 block">Aggregated error signatures</span>
      </div>

      <!-- Open Incidents -->
      <div class="rounded-2xl p-4 bg-[#12161C] border border-rose-500/20 relative overflow-hidden group hover:border-rose-500/40 transition-all">
        <div class="flex items-center justify-between mb-2">
          <span class="text-[11px] font-semibold uppercase tracking-wider text-rose-400">Active Open</span>
          <div class="w-7 h-7 rounded-lg bg-rose-500/10 border border-rose-500/20 flex items-center justify-center">
            <FireIcon class="w-4 h-4 text-rose-400" />
          </div>
        </div>
        <p class="text-2xl font-bold text-rose-400 tracking-tight">{{ stats.open }}</p>
        <span class="text-[10px] text-zinc-500 mt-1 block">Require operator triage</span>
      </div>

      <!-- Critical Alarms -->
      <div class="rounded-2xl p-4 bg-[#12161C] border border-amber-500/20 relative overflow-hidden group hover:border-amber-500/40 transition-all">
        <div class="flex items-center justify-between mb-2">
          <span class="text-[11px] font-semibold uppercase tracking-wider text-amber-400">Critical Alarms</span>
          <div class="w-7 h-7 rounded-lg bg-amber-500/10 border border-amber-500/20 flex items-center justify-center">
            <ShieldExclamationIcon class="w-4 h-4 text-amber-400" />
          </div>
        </div>
        <p class="text-2xl font-bold text-amber-400 tracking-tight">{{ stats.critical }}</p>
        <span class="text-[10px] text-zinc-500 mt-1 block">High impact anomalies</span>
      </div>

      <!-- Resolved -->
      <div class="rounded-2xl p-4 bg-[#12161C] border border-emerald-500/20 relative overflow-hidden group hover:border-emerald-500/40 transition-all">
        <div class="flex items-center justify-between mb-2">
          <span class="text-[11px] font-semibold uppercase tracking-wider text-emerald-400">Resolved</span>
          <div class="w-7 h-7 rounded-lg bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center">
            <CheckCircleIcon class="w-4 h-4 text-emerald-400" />
          </div>
        </div>
        <p class="text-2xl font-bold text-emerald-400 tracking-tight">{{ stats.resolved }}</p>
        <span class="text-[10px] text-zinc-500 mt-1 block">Closed incident cycles</span>
      </div>
    </div>
  </div>
</template>
