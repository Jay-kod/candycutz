<script setup>
import {
  ExclamationCircleIcon,
  ShieldCheckIcon,
} from '@heroicons/vue/24/outline'

defineProps({
  errorGroups: {
    type: Array,
    default: () => [],
  },
})
</script>

<template>
  <div class="rounded-2xl bg-[#12161C] border border-white/[0.08] overflow-hidden shadow-xl">
    <!-- Header -->
    <div class="px-5 py-4 border-b border-white/[0.08] flex items-center justify-between bg-white/[0.02]">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-lg bg-rose-500/10 border border-rose-500/20 flex items-center justify-center">
          <ExclamationCircleIcon class="w-4 h-4 text-rose-400" />
        </div>
        <div>
          <h2 class="text-sm font-bold text-white tracking-tight">Active Error Registry & Signatures</h2>
          <p class="text-xs text-zinc-400">Captured exceptions aggregated by pattern signature</p>
        </div>
      </div>
      <span
        v-if="errorGroups?.length"
        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-rose-500/10 text-rose-400 border border-rose-500/25"
      >
        {{ errorGroups.length }} Active Signatures
      </span>
    </div>

    <!-- Active Errors List -->
    <div v-if="errorGroups && errorGroups.length > 0" class="divide-y divide-white/[0.06]">
      <div
        v-for="err in errorGroups"
        :key="err.id"
        class="px-5 py-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 hover:bg-white/[0.02] transition-colors"
      >
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-2 mb-1">
            <span class="text-xs font-mono font-semibold text-rose-300 bg-rose-500/10 px-2 py-0.5 rounded border border-rose-500/20">
              {{ err.error_code }}
            </span>
          </div>
          <p class="text-xs text-zinc-400 truncate max-w-2xl font-mono" :title="err.sample_message">
            {{ err.sample_message }}
          </p>
        </div>

        <div class="flex items-center gap-3 shrink-0 self-end sm:self-center">
          <span class="text-xs text-zinc-400 font-mono">
            <strong class="text-white">{{ err.occurrences }}</strong> hits
          </span>
          <span
            :class="[
              'text-[10px] px-2.5 py-0.5 rounded-full border font-bold uppercase tracking-wider',
              err.severity === 'critical'
                ? 'bg-rose-500/15 border-rose-500/30 text-rose-400'
                : err.severity === 'error'
                  ? 'bg-orange-500/15 border-orange-500/30 text-orange-400'
                  : 'bg-amber-500/15 border-amber-500/30 text-amber-400'
            ]"
          >
            {{ err.severity }}
          </span>
        </div>
      </div>
    </div>

    <!-- Zero Errors Nominal State -->
    <div v-else class="p-8 text-center flex flex-col items-center justify-center">
      <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center mb-3 text-emerald-400">
        <ShieldCheckIcon class="w-6 h-6" />
      </div>
      <h3 class="text-sm font-bold text-white mb-1">Zero Active Anomaly Signatures</h3>
      <p class="text-xs text-zinc-400 max-w-md">
        All subsystems are operating within nominal thresholds. No recurring unhandled exceptions are currently logged.
      </p>
    </div>
  </div>
</template>

