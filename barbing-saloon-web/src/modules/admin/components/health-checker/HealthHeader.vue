<script setup>
import { computed } from 'vue'
import {
  HeartIcon,
  SignalIcon,
  ArrowPathIcon,
  ShieldCheckIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  overview: {
    type: Object,
    default: null,
  },
  overviewLoading: {
    type: Boolean,
    default: false,
  },
  detailLoading: {
    type: Boolean,
    default: false,
  },
  autoRefresh: {
    type: Boolean,
    default: false,
  },
  lastRefreshed: {
    type: [Date, String],
    default: null,
  },
  statusColor: {
    type: Function,
    required: true,
  },
  statusBg: {
    type: Function,
    required: true,
  },
  statusIcon: {
    type: Function,
    required: true,
  },
  formatTime: {
    type: Function,
    required: true,
  },
})

defineEmits(['refresh', 'toggleAutoRefresh'])

const isRefreshing = computed(() => props.overviewLoading || props.detailLoading)
const statusName = computed(() => {
  const s = props.overview?.status || 'operational'
  return s.charAt(0).toUpperCase() + s.slice(1)
})
</script>

<template>
  <div class="space-y-6">
    <!-- Executive Title & Telemetry Actions -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-3">
          <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-emerald-500/20 via-teal-500/10 to-transparent border border-emerald-500/30 flex items-center justify-center shadow-lg shadow-emerald-500/10">
            <HeartIcon class="w-6 h-6 text-emerald-400" />
          </div>
          <div>
            <div class="flex items-center gap-2.5">
              <h1 class="text-2xl font-bold tracking-tight text-white">Health & Diagnostics</h1>
              <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                Live Telemetry
              </span>
            </div>
            <p class="text-xs text-zinc-400 mt-0.5">Real-time subsystem vitality, gateway performance & self-diagnostics</p>
          </div>
        </div>
      </div>

      <div class="flex items-center flex-wrap gap-2.5">
        <span v-if="lastRefreshed" class="text-xs text-zinc-400 hidden sm:inline-block font-mono">
          Refreshed: {{ formatTime(lastRefreshed instanceof Date ? lastRefreshed.toISOString() : lastRefreshed) }}
        </span>

        <button
          type="button"
          @click="$emit('toggleAutoRefresh')"
          :class="[
            'inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold transition-all border shadow-sm',
            autoRefresh
              ? 'bg-emerald-500/15 border-emerald-500/40 text-emerald-300 hover:bg-emerald-500/25 shadow-emerald-500/10'
              : 'bg-[#12161C] border-white/[0.08] text-zinc-400 hover:text-white hover:bg-white/[0.04]'
          ]"
        >
          <span class="relative flex h-2 w-2">
            <span v-if="autoRefresh" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
            <span :class="['relative inline-flex rounded-full h-2 w-2', autoRefresh ? 'bg-emerald-400' : 'bg-zinc-600']"></span>
          </span>
          <SignalIcon class="w-3.5 h-3.5" />
          <span>{{ autoRefresh ? 'Streaming Live' : 'Auto-Refresh' }}</span>
        </button>

        <button
          type="button"
          @click="$emit('refresh')"
          :disabled="isRefreshing"
          class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#12161C] border border-white/[0.08] text-zinc-200 hover:text-white hover:bg-white/[0.06] hover:border-white/[0.16] transition-all text-xs font-semibold disabled:opacity-50 shadow-sm"
        >
          <ArrowPathIcon class="w-3.5 h-3.5" :class="{ 'animate-spin': isRefreshing }" />
          <span>{{ isRefreshing ? 'Scanning…' : 'Refresh Telemetry' }}</span>
        </button>
      </div>
    </div>

    <!-- Overall System Vitality Banner -->
    <div
      v-if="overview"
      :class="[
        'rounded-2xl p-5 border flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 transition-all duration-300 shadow-xl backdrop-blur-md',
        statusBg(overview.status)
      ]"
    >
      <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-black/20 border border-white/[0.08] shrink-0">
          <component :is="statusIcon(overview.status)" :class="['w-7 h-7', statusColor(overview.status)]" />
        </div>
        <div>
          <div class="flex items-center gap-2">
            <h2 :class="['text-base font-bold tracking-tight', statusColor(overview.status)]">
              Overall Status: {{ statusName }}
            </h2>
            <span class="relative flex h-2 w-2">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-400"></span>
            </span>
          </div>
          <p class="text-xs text-zinc-300 mt-0.5">
            Core application services, relational persistence layer, and asynchronous pipelines operational.
          </p>
        </div>
      </div>

      <div class="flex items-center gap-4 border-t sm:border-t-0 border-white/[0.08] pt-3 sm:pt-0 w-full sm:w-auto justify-between sm:justify-end text-xs">
        <div class="text-left sm:text-right">
          <span class="text-[10px] uppercase font-semibold tracking-wider text-zinc-400 block">Server Master Clock</span>
          <span class="font-mono text-zinc-200 font-medium">{{ formatTime(overview.server_time) }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

