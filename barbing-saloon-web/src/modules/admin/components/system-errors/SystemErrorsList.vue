<script setup>
import {
  ExclamationCircleIcon,
  CheckCircleIcon,
  ArrowPathIcon,
  EyeIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
} from '@heroicons/vue/24/outline'

defineProps({
  errors: {
    type: Array,
    default: () => [],
  },
  loading: {
    type: Boolean,
    default: false,
  },
  currentPage: {
    type: Number,
    default: 1,
  },
  totalPages: {
    type: Number,
    default: 1,
  },
  totalCount: {
    type: Number,
    default: 0,
  },
  severityColor: {
    type: Function,
    required: true,
  },
  statusColor: {
    type: Function,
    required: true,
  },
  timeAgo: {
    type: Function,
    required: true,
  },
})

defineEmits(['selectError', 'goToPage'])
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
          <h2 class="text-sm font-bold text-white tracking-tight">Active Incident Signatures</h2>
          <p class="text-xs text-zinc-400">Classified error clusters recorded by platform observability</p>
        </div>
      </div>
      <span class="text-xs font-mono text-zinc-400 font-medium">
        {{ totalCount }} Total Records
      </span>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="p-12 flex flex-col items-center justify-center gap-3">
      <ArrowPathIcon class="w-7 h-7 text-rose-400 animate-spin" />
      <p class="text-xs text-zinc-400">Querying incident registry…</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="!errors.length" class="p-12 text-center flex flex-col items-center justify-center">
      <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center mb-3 text-emerald-400">
        <CheckCircleIcon class="w-6 h-6" />
      </div>
      <h3 class="text-sm font-bold text-white mb-1">Zero Incident Matches</h3>
      <p class="text-xs text-zinc-400 max-w-sm">
        No active error signatures match the current filter parameters. All monitored subsystems are operational.
      </p>
    </div>

    <!-- Error Groups List -->
    <div v-else class="divide-y divide-white/[0.06]">
      <div
        v-for="err in errors"
        :key="err.id"
        @click="$emit('selectError', err)"
        class="px-5 py-4 flex items-center justify-between gap-4 hover:bg-white/[0.02] transition-colors cursor-pointer group"
      >
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-2 mb-1.5">
            <span class="text-xs text-white font-mono font-bold tracking-tight group-hover:text-rose-400 transition-colors">
              {{ err.error_code }}
            </span>
            <span :class="['text-[10px] px-2 py-0.5 rounded-full border font-bold uppercase tracking-wider', severityColor(err.severity)]">
              {{ err.severity }}
            </span>
            <span :class="['text-[10px] px-2 py-0.5 rounded-full border font-bold uppercase tracking-wider', statusColor(err.status)]">
              {{ err.status }}
            </span>
            <span v-if="err.category" class="text-[10px] px-2 py-0.5 rounded-full bg-white/[0.04] text-zinc-400 border border-white/[0.06] font-mono">
              {{ err.category }}
            </span>
          </div>

          <p class="text-xs text-zinc-300 truncate max-w-2xl font-mono mb-1.5" :title="err.sample_message">
            {{ err.sample_message }}
          </p>

          <div class="flex items-center gap-3 text-[11px] text-zinc-500">
            <span v-if="err.exception_class" class="font-mono text-zinc-400 truncate max-w-[240px]">
              {{ err.exception_class }}
            </span>
            <span v-if="err.exception_class" class="text-zinc-600">·</span>
            <span>
              <strong class="text-zinc-300 font-mono">{{ err.occurrences }}</strong> occurrences
            </span>
            <span class="text-zinc-600">·</span>
            <span>Last seen {{ timeAgo(err.last_seen_at) }}</span>
          </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
          <span class="text-[11px] text-zinc-500 group-hover:text-zinc-300 transition-colors hidden sm:inline-block">
            Inspect
          </span>
          <EyeIcon class="w-4 h-4 text-zinc-500 group-hover:text-white transition-colors" />
        </div>
      </div>
    </div>

    <!-- Pagination Controls -->
    <div v-if="totalPages > 1" class="px-5 py-3 border-t border-white/[0.06] flex items-center justify-between text-xs bg-white/[0.01]">
      <span class="text-zinc-400 font-mono">
        Page {{ currentPage }} of {{ totalPages }}
      </span>
      <div class="flex items-center gap-2">
        <button
          type="button"
          @click="$emit('goToPage', currentPage - 1)"
          :disabled="currentPage <= 1"
          class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-white/[0.03] border border-white/[0.08] text-zinc-300 hover:text-white hover:bg-white/[0.06] transition-colors disabled:opacity-30 disabled:cursor-not-allowed"
        >
          <ChevronLeftIcon class="w-3.5 h-3.5" />
          <span>Previous</span>
        </button>
        <button
          type="button"
          @click="$emit('goToPage', currentPage + 1)"
          :disabled="currentPage >= totalPages"
          class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-white/[0.03] border border-white/[0.08] text-zinc-300 hover:text-white hover:bg-white/[0.06] transition-colors disabled:opacity-30 disabled:cursor-not-allowed"
        >
          <span>Next</span>
          <ChevronRightIcon class="w-3.5 h-3.5" />
        </button>
      </div>
    </div>
  </div>
</template>
