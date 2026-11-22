<script setup>
import {
  ArrowPathIcon,
  CheckCircleIcon,
  ExclamationTriangleIcon,
  ShieldCheckIcon,
  TrashIcon,
  WrenchScrewdriverIcon,
} from '@heroicons/vue/24/outline'

defineProps({
  failedJobs: {
    type: Array,
    default: () => [],
  },
  circuitBreakers: {
    type: Object,
    default: () => ({}),
  },
  loading: {
    type: Boolean,
    default: false,
  },
  retryingJobId: {
    type: [Number, String],
    default: null,
  },
  dismissingJobId: {
    type: [Number, String],
    default: null,
  },
})

defineEmits(['retry-job', 'dismiss-job', 'reset-breaker'])
</script>

<template>
  <div class="rounded-2xl bg-[#12161C] border border-white/[0.08] overflow-hidden shadow-xl space-y-0">
    <!-- Header -->
    <div class="px-5 py-4 border-b border-white/[0.08] flex items-center justify-between bg-white/[0.02]">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-lg bg-amber-500/10 border border-amber-500/20 flex items-center justify-center">
          <WrenchScrewdriverIcon class="w-4 h-4 text-amber-400" />
        </div>
        <div>
          <h2 class="text-sm font-bold text-white tracking-tight">Dead-Letter Incident Vault & Circuit Breakers</h2>
          <p class="text-xs text-zinc-400">Isolated background task quarantine with safe 1-click retry and breaker resets</p>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <span
          v-if="failedJobs?.length > 0"
          class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-rose-500/10 text-rose-400 border border-rose-500/25"
        >
          <span class="w-1.5 h-1.5 rounded-full bg-rose-400 animate-ping"></span>
          {{ failedJobs.length }} Quarantined Tasks
        </span>
        <span
          v-else
          class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-500/10 text-emerald-400 border border-emerald-500/25"
        >
          <CheckCircleIcon class="w-3.5 h-3.5" />
          Vault All Clear
        </span>
      </div>
    </div>

    <!-- Circuit Breaker Status Strip -->
    <div v-if="circuitBreakers && Object.keys(circuitBreakers).length > 0" class="px-5 py-3 bg-black/20 border-b border-white/[0.06] flex flex-wrap items-center gap-4 text-xs">
      <span class="text-zinc-500 font-semibold uppercase tracking-wider text-[10px]">Circuit Breakers:</span>
      <div
        v-for="(status, name) in circuitBreakers"
        :key="name"
        class="inline-flex items-center gap-2 px-2.5 py-1 rounded-lg border text-xs"
        :class="status.state === 'closed' ? 'bg-emerald-500/5 border-emerald-500/20 text-emerald-400' : 'bg-rose-500/10 border-rose-500/30 text-rose-300'"
      >
        <span class="capitalize font-semibold text-zinc-300">{{ name }}:</span>
        <span class="font-mono uppercase font-bold text-[11px]">{{ status.state }}</span>
        <span v-if="status.failures_count > 0" class="text-rose-400 font-mono">({{ status.failures_count }} errs)</span>
        <button
          v-if="status.state !== 'closed' || status.failures_count > 0"
          type="button"
          @click="$emit('reset-breaker', name)"
          class="ml-1 px-1.5 py-0.5 rounded bg-white/[0.08] hover:bg-white/[0.15] text-[10px] text-white font-semibold transition-all cursor-pointer"
        >
          Reset
        </button>
      </div>
    </div>

    <!-- Failed Jobs List -->
    <div v-if="failedJobs && failedJobs.length > 0" class="divide-y divide-white/[0.06]">
      <div
        v-for="job in failedJobs"
        :key="job.id"
        class="px-5 py-3.5 flex flex-col md:flex-row items-start md:items-center justify-between gap-3 hover:bg-white/[0.02] transition-colors"
      >
        <div class="flex-1 min-w-0 space-y-1">
          <div class="flex items-center gap-2">
            <span class="text-xs font-mono font-bold text-amber-300 bg-amber-500/10 px-2 py-0.5 rounded border border-amber-500/20">
              {{ job.command_name }}
            </span>
            <span class="text-[11px] text-zinc-500 font-mono">Queue: {{ job.queue }}</span>
          </div>
          <p class="text-xs text-zinc-300 font-mono truncate max-w-3xl" :title="job.error_summary">
            {{ job.error_summary }}
          </p>
          <p class="text-[10px] text-zinc-500">
            Failed at: {{ new Date(job.failed_at).toLocaleString() }}
          </p>
        </div>

        <!-- Safe 1-Click Action Controls -->
        <div class="flex items-center gap-2 shrink-0 self-end md:self-center">
          <button
            type="button"
            @click="$emit('retry-job', job.id)"
            :disabled="retryingJobId === job.id"
            class="px-3 py-1.5 rounded-xl bg-amber-500/15 hover:bg-amber-500/25 border border-amber-500/30 text-amber-300 text-xs font-semibold flex items-center gap-1.5 transition-all cursor-pointer disabled:opacity-50"
          >
            <ArrowPathIcon class="w-3.5 h-3.5" :class="{ 'animate-spin': retryingJobId === job.id }" />
            <span>{{ retryingJobId === job.id ? 'Retrying...' : 'Retry Task' }}</span>
          </button>

          <button
            type="button"
            @click="$emit('dismiss-job', job.id)"
            :disabled="dismissingJobId === job.id"
            class="px-2.5 py-1.5 rounded-xl bg-white/[0.04] hover:bg-rose-500/15 border border-white/[0.08] hover:border-rose-500/30 text-zinc-400 hover:text-rose-400 text-xs font-semibold flex items-center gap-1 transition-all cursor-pointer disabled:opacity-50"
            title="Dismiss from vault"
          >
            <TrashIcon class="w-3.5 h-3.5" />
            <span>{{ dismissingJobId === job.id ? 'Dismissing...' : 'Dismiss' }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="px-5 py-8 text-center bg-white/[0.01]">
      <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center mx-auto mb-3 text-emerald-400">
        <ShieldCheckIcon class="w-6 h-6" />
      </div>
      <p class="text-sm font-semibold text-white">Incident Vault Clear</p>
      <p class="text-xs text-zinc-400 max-w-md mx-auto mt-1">
        All asynchronous queues, email workers, and payment verification tasks are executing cleanly without errors.
      </p>
    </div>
  </div>
</template>
