<script setup>
import {
  BeakerIcon,
  PlayIcon,
  CircleStackIcon,
  CpuChipIcon,
  FolderIcon,
  ArrowPathIcon,
  CheckCircleIcon,
  XCircleIcon,
} from '@heroicons/vue/24/outline'

defineProps({
  selfTestRunning: {
    type: Boolean,
    default: false,
  },
  selfTestResults: {
    type: Object,
    default: null,
  },
})

defineEmits(['runDiagnostic'])
</script>

<template>
  <div class="rounded-2xl bg-[#12161C] border border-white/[0.08] overflow-hidden shadow-xl">
    <!-- Header -->
    <div class="px-5 py-4 border-b border-white/[0.08] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 bg-white/[0.02]">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-lg bg-orange-500/10 border border-[#FF6700]/30 flex items-center justify-center">
          <BeakerIcon class="w-4 h-4 text-[#FF6700]" />
        </div>
        <div>
          <h2 class="text-sm font-bold text-white tracking-tight">Active Self-Diagnostics Probes</h2>
          <p class="text-xs text-zinc-400">Execute on-demand round-trip verification across infrastructure primitives</p>
        </div>
      </div>
      <span class="text-[11px] text-zinc-400 font-mono">
        Probe Engine v2.4
      </span>
    </div>

    <!-- Controls Body -->
    <div class="p-5 space-y-6">
      <div class="flex flex-wrap gap-3">
        <button
          type="button"
          @click="$emit('runDiagnostic', 'all')"
          :disabled="selfTestRunning"
          class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-[#FF6700] to-amber-600 text-white font-semibold text-xs transition-all hover:brightness-110 shadow-lg shadow-[#FF6700]/20 disabled:opacity-50"
        >
          <PlayIcon class="w-4 h-4" />
          <span>Execute Full Diagnostic Suite</span>
        </button>

        <button
          type="button"
          @click="$emit('runDiagnostic', 'database')"
          :disabled="selfTestRunning"
          class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-white/[0.03] border border-white/[0.08] text-zinc-300 hover:text-white hover:bg-white/[0.06] hover:border-white/[0.16] transition-all text-xs font-semibold disabled:opacity-50"
        >
          <CircleStackIcon class="w-3.5 h-3.5 text-emerald-400" />
          <span>Probe Database</span>
        </button>

        <button
          type="button"
          @click="$emit('runDiagnostic', 'cache')"
          :disabled="selfTestRunning"
          class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-white/[0.03] border border-white/[0.08] text-zinc-300 hover:text-white hover:bg-white/[0.06] hover:border-white/[0.16] transition-all text-xs font-semibold disabled:opacity-50"
        >
          <CpuChipIcon class="w-3.5 h-3.5 text-teal-400" />
          <span>Probe Cache</span>
        </button>

        <button
          type="button"
          @click="$emit('runDiagnostic', 'storage')"
          :disabled="selfTestRunning"
          class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-white/[0.03] border border-white/[0.08] text-zinc-300 hover:text-white hover:bg-white/[0.06] hover:border-white/[0.16] transition-all text-xs font-semibold disabled:opacity-50"
        >
          <FolderIcon class="w-3.5 h-3.5 text-amber-400" />
          <span>Probe File Storage</span>
        </button>
      </div>

      <!-- Test Running Indicator -->
      <div v-if="selfTestRunning" class="flex flex-col items-center justify-center py-8 rounded-xl bg-white/[0.02] border border-white/[0.06] space-y-3">
        <ArrowPathIcon class="w-7 h-7 text-[#FF6700] animate-spin" />
        <div class="text-center">
          <p class="text-xs font-semibold text-white">Running Subsystem Probes…</p>
          <p class="text-[11px] text-zinc-400">Performing round-trip latency checks and integrity verification</p>
        </div>
      </div>

      <!-- Results Grid -->
      <div v-if="selfTestResults && !selfTestRunning" class="space-y-4 pt-2">
        <div
          :class="[
            'p-4 rounded-xl border flex items-center justify-between gap-3',
            selfTestResults.all_passed
              ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-300'
              : 'bg-rose-500/10 border-rose-500/30 text-rose-300'
          ]"
        >
          <div class="flex items-center gap-2.5">
            <component
              :is="selfTestResults.all_passed ? CheckCircleIcon : XCircleIcon"
              class="w-5 h-5 shrink-0"
            />
            <span class="text-xs font-bold uppercase tracking-wider">
              {{ selfTestResults.all_passed ? 'All Diagnostic Probes Passed' : 'Diagnostic Anomalies Encountered' }}
            </span>
          </div>
          <span class="text-[11px] font-mono text-zinc-300">
            {{ Object.keys(selfTestResults.results || {}).length }} Probes Executed
          </span>
        </div>

        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
          <div
            v-for="(result, key) in selfTestResults.results"
            :key="key"
            :class="[
              'rounded-xl p-4 border transition-all duration-300',
              result.passed
                ? 'bg-emerald-500/5 border-emerald-500/20'
                : 'bg-rose-500/5 border-rose-500/20'
            ]"
          >
            <div class="flex items-center justify-between mb-2">
              <div class="flex items-center gap-2">
                <component
                  :is="result.passed ? CheckCircleIcon : XCircleIcon"
                  :class="['w-4 h-4', result.passed ? 'text-emerald-400' : 'text-rose-400']"
                />
                <h4 class="text-xs font-bold text-white uppercase tracking-wider font-mono">{{ key }}</h4>
              </div>
              <span
                :class="[
                  'text-[10px] px-2 py-0.5 rounded-full font-bold uppercase tracking-wider',
                  result.passed ? 'bg-emerald-500/10 text-emerald-400' : 'bg-rose-500/10 text-rose-400'
                ]"
              >
                {{ result.passed ? 'PASS' : 'FAIL' }}
              </span>
            </div>

            <p class="text-xs text-zinc-300">{{ result.message }}</p>

            <div v-if="result.latency_ms !== undefined" class="mt-2 text-[11px] text-zinc-400 flex items-center justify-between">
              <span>Execution Latency:</span>
              <span class="font-mono text-white font-semibold">{{ result.latency_ms }}ms</span>
            </div>

            <div v-if="result.error" class="mt-2 p-2 rounded bg-rose-500/10 border border-rose-500/20 text-rose-300 text-[11px] font-mono break-all">
              {{ result.error }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
