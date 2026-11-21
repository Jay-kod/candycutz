<script setup>
import {
  FingerPrintIcon,
  DocumentMagnifyingGlassIcon,
  ArrowPathIcon,
  XMarkIcon,
  ClockIcon,
  GlobeAltIcon,
} from '@heroicons/vue/24/outline'

defineProps({
  traceId: {
    type: String,
    default: '',
  },
  traceLoading: {
    type: Boolean,
    default: false,
  },
  traceResults: {
    type: Object,
    default: null,
  },
  showTracePanel: {
    type: Boolean,
    default: false,
  },
  severityColor: {
    type: Function,
    required: true,
  },
  formatTime: {
    type: Function,
    required: true,
  },
})

defineEmits(['update:traceId', 'executeTrace', 'closeTrace'])
</script>

<template>
  <div class="rounded-2xl bg-[#12161C] border border-white/[0.08] p-5 shadow-xl">
    <div class="flex items-center gap-3 mb-3">
      <div class="w-8 h-8 rounded-lg bg-violet-500/10 border border-violet-500/25 flex items-center justify-center">
        <FingerPrintIcon class="w-4 h-4 text-violet-400" />
      </div>
      <div>
        <h2 class="text-sm font-bold text-white tracking-tight">Request ID Trace & Correlation</h2>
        <p class="text-xs text-zinc-400">Trace end-to-end exception timeline and network logs for a specific request ID</p>
      </div>
    </div>

    <!-- Trace Input Form -->
    <div class="flex flex-col sm:flex-row gap-3">
      <div class="relative flex-1">
        <input
          :value="traceId"
          @input="$emit('update:traceId', $event.target.value)"
          type="text"
          placeholder="Paste correlation Request ID (e.g. req-89ab12cd34ef or cc-trace-123)"
          class="w-full px-4 py-2.5 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white text-xs font-mono placeholder-zinc-500 focus:outline-none focus:border-violet-500/40 focus:ring-1 focus:ring-violet-500/30 transition-all"
          @keyup.enter="$emit('executeTrace')"
        />
      </div>
      <button
        type="button"
        @click="$emit('executeTrace')"
        :disabled="traceLoading || !traceId.trim()"
        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-violet-600/20 border border-violet-500/35 text-violet-200 text-xs font-semibold hover:bg-violet-600/30 transition-all disabled:opacity-40 shadow-sm"
      >
        <ArrowPathIcon v-if="traceLoading" class="w-3.5 h-3.5 animate-spin" />
        <DocumentMagnifyingGlassIcon v-else class="w-3.5 h-3.5" />
        <span>{{ traceLoading ? 'Tracing Request…' : 'Trace Request' }}</span>
      </button>
    </div>

    <!-- Trace Results Container -->
    <div v-if="showTracePanel" class="mt-4 pt-4 border-t border-white/[0.06] animate-fade-in">
      <div v-if="traceLoading" class="flex items-center gap-3 py-6 justify-center">
        <ArrowPathIcon class="w-5 h-5 text-violet-400 animate-spin" />
        <p class="text-xs text-zinc-400">Correlating distributed logs and exceptions…</p>
      </div>

      <div v-else-if="traceResults" class="space-y-4">
        <div class="flex items-center justify-between bg-white/[0.02] p-3 rounded-xl border border-white/[0.04]">
          <div class="flex items-center gap-2">
            <span class="text-[10px] uppercase font-bold tracking-wider text-zinc-400">Target Request:</span>
            <span class="font-mono text-xs text-violet-300 font-semibold">{{ traceResults.request_id }}</span>
          </div>
          <button
            type="button"
            @click="$emit('closeTrace')"
            class="text-xs text-zinc-400 hover:text-white flex items-center gap-1 transition-colors px-2 py-1 rounded hover:bg-white/[0.04]"
          >
            <XMarkIcon class="w-3.5 h-3.5" />
            <span>Close Trace</span>
          </button>
        </div>

        <!-- Error Events List -->
        <div v-if="traceResults.events?.length" class="space-y-2">
          <p class="text-xs font-semibold text-rose-300 flex items-center gap-1.5">
            <span>Captured Exception Events</span>
            <span class="px-1.5 py-0.2 rounded-full bg-rose-500/10 text-[10px] font-mono border border-rose-500/20">
              {{ traceResults.events.length }}
            </span>
          </p>
          <div
            v-for="ev in traceResults.events"
            :key="ev.id"
            class="rounded-xl bg-rose-500/5 border border-rose-500/15 p-3.5 space-y-1.5"
          >
            <div class="flex items-center justify-between gap-2">
              <div class="flex items-center gap-2">
                <span :class="['text-[10px] px-2 py-0.5 rounded-full border font-bold uppercase tracking-wider', severityColor(ev.severity)]">
                  {{ ev.severity }}
                </span>
                <span v-if="ev.http_status" class="text-[11px] font-mono text-zinc-400">
                  HTTP {{ ev.http_status }}
                </span>
              </div>
              <span class="text-[10px] text-zinc-500 font-mono">{{ formatTime(ev.occurred_at) }}</span>
            </div>
            <p class="text-xs text-zinc-200 font-mono break-all">{{ ev.message }}</p>
            <p v-if="ev.url" class="text-[10px] text-zinc-400 font-mono flex items-center gap-1">
              <GlobeAltIcon class="w-3 h-3 text-zinc-500 shrink-0" />
              <span class="truncate">{{ ev.url }}</span>
            </p>
          </div>
        </div>

        <!-- Request Logs -->
        <div v-if="traceResults.request_logs?.length" class="space-y-2">
          <p class="text-xs font-semibold text-zinc-300 flex items-center gap-1.5">
            <span>HTTP Request Logs</span>
            <span class="px-1.5 py-0.2 rounded-full bg-white/[0.05] text-[10px] font-mono border border-white/[0.08]">
              {{ traceResults.request_logs.length }}
            </span>
          </p>
          <div
            v-for="log in traceResults.request_logs"
            :key="log.id"
            class="rounded-xl bg-white/[0.02] border border-white/[0.06] p-3 flex items-center justify-between text-xs font-mono"
          >
            <div class="flex items-center gap-2.5 truncate">
              <span class="text-[10px] px-1.5 py-0.5 rounded font-bold bg-blue-500/15 text-blue-400 border border-blue-500/25">
                {{ log.method }}
              </span>
              <span class="text-zinc-300 truncate">{{ log.url }}</span>
            </div>
            <div class="flex items-center gap-3 shrink-0 ml-3">
              <span :class="log.status_code >= 400 ? 'text-rose-400 font-bold' : 'text-emerald-400'">
                {{ log.status_code }}
              </span>
              <span class="text-zinc-500 text-[11px] flex items-center gap-1">
                <ClockIcon class="w-3 h-3 text-zinc-500" />
                {{ log.duration_ms }}ms
              </span>
            </div>
          </div>
        </div>

        <!-- Empty Results -->
        <div
          v-if="!traceResults.events?.length && !traceResults.request_logs?.length"
          class="text-xs text-zinc-400 text-center py-6 bg-white/[0.01] rounded-xl border border-white/[0.04]"
        >
          No correlated exception events or request log rows found for this Request ID.
        </div>
      </div>
    </div>
  </div>
</template>

