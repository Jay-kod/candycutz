<template>
  <div class="rounded-2xl border border-white/[0.08] bg-[#12161C]/90 backdrop-blur-xl shadow-xl overflow-hidden flex flex-col">
    <!-- Filter Controls Bar -->
    <div class="border-b border-white/[0.06] p-4 flex flex-col lg:flex-row gap-3.5 justify-between items-stretch lg:items-center bg-[#0B0F13]/60">
      <div class="flex flex-wrap items-center gap-2.5">
        <!-- Client Filter Dropdown -->
        <div class="flex items-center gap-2 rounded-xl bg-white/[0.03] border border-white/10 px-3 py-1.5">
          <FunnelIcon class="h-3.5 w-3.5 text-[#FF6700] shrink-0" />
          <select
            :value="clientFilter"
            @change="$emit('update:clientFilter', $event.target.value)"
            class="bg-transparent text-white/80 text-xs outline-none cursor-pointer"
          >
            <option value="web" class="bg-[#12161C]">Web Portal</option>
            <option value="webhook" class="bg-[#12161C]">Signed Webhooks</option>
          </select>
        </div>

        <!-- Slow Only Toggle -->
        <button
          type="button"
          @click="$emit('update:isSlow', !isSlow)"
          class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold border transition-colors"
          :class="isSlow
            ? 'bg-amber-500/20 border-amber-500/40 text-amber-300'
            : 'bg-white/[0.03] border-white/10 text-white/60 hover:text-white'"
        >
          <ClockIcon class="w-3.5 h-3.5" />
          <span>Slow Only (&gt;150ms)</span>
        </button>

        <!-- Budget Exceeded Toggle -->
        <button
          type="button"
          @click="$emit('update:budgetExceeded', !budgetExceeded)"
          class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold border transition-colors"
          :class="budgetExceeded
            ? 'bg-rose-500/20 border-rose-500/40 text-rose-300'
            : 'bg-white/[0.03] border-white/10 text-white/60 hover:text-white'"
        >
          <CircleStackIcon class="w-3.5 h-3.5" />
          <span>Budget Exceeded (&gt;25 DB)</span>
        </button>
      </div>

      <!-- Search, Prune & Refresh -->
      <div class="flex items-center gap-2">
        <div class="flex items-center gap-2 rounded-xl bg-white/[0.03] border border-white/10 px-3 py-1.5 w-full sm:w-60">
          <MagnifyingGlassIcon class="h-3.5 w-3.5 text-white/40 shrink-0" />
          <input
            :value="search"
            @input="$emit('update:search', $event.target.value)"
            type="text"
            placeholder="Filter path, IP, method..."
            class="bg-transparent text-white text-xs outline-none w-full placeholder:text-white/30"
          />
        </div>

        <!-- Prune Button -->
        <button
          type="button"
          @click="$emit('open-prune')"
          class="inline-flex items-center gap-1.5 rounded-xl border border-rose-500/20 bg-rose-500/10 px-3 py-1.5 text-xs font-semibold text-rose-300 hover:bg-rose-500/20 transition-colors shrink-0"
          title="Prune telemetry data"
        >
          <TrashIcon class="w-3.5 h-3.5" />
          <span class="hidden sm:inline">Prune</span>
        </button>

        <!-- Refresh Button -->
        <button
          type="button"
          @click="$emit('refresh')"
          class="inline-flex items-center gap-1.5 rounded-xl border border-white/10 bg-white/[0.04] px-3 py-1.5 text-xs font-semibold text-white/70 hover:bg-white/[0.08] hover:text-white transition-colors shrink-0"
          title="Refresh gate data"
        >
          <ArrowPathIcon class="w-3.5 h-3.5" :class="{ 'animate-spin text-[#FF6700]': loading }" />
          <span class="hidden sm:inline">Refresh</span>
        </button>
      </div>
    </div>

    <!-- Data Table -->
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="border-b border-white/[0.06] bg-[#0B0F13]/40">
            <th class="p-3.5 text-[10px] font-bold uppercase tracking-widest text-white/40">Timestamp</th>
            <th class="p-3.5 text-[10px] font-bold uppercase tracking-widest text-white/40">Method &amp; Path</th>
            <th class="p-3.5 text-[10px] font-bold uppercase tracking-widest text-white/40">Client</th>
            <th class="p-3.5 text-[10px] font-bold uppercase tracking-widest text-white/40">Status</th>
            <th class="p-3.5 text-[10px] font-bold uppercase tracking-widest text-white/40">Latency</th>
            <th class="p-3.5 text-[10px] font-bold uppercase tracking-widest text-white/40">DB Queries</th>
            <th class="p-3.5 text-[10px] font-bold uppercase tracking-widest text-white/40">Origin &amp; User</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-white/[0.03]">
          <tr v-if="loading">
            <td colspan="7" class="p-12 text-center">
              <div class="inline-flex items-center gap-2.5 text-white/50">
                <ArrowPathIcon class="w-5 h-5 animate-spin text-[#FF6700]" />
                <span class="text-xs font-medium">Streaming telemetry events...</span>
              </div>
            </td>
          </tr>
          <tr v-else-if="logs.length === 0">
            <td colspan="7" class="p-16 text-center">
              <CpuChipIcon class="w-10 h-10 mx-auto mb-2 text-white/20" />
              <p class="text-white/60 font-semibold text-sm">No telemetry records match query</p>
              <p class="text-xs text-white/40 mt-0.5">Events will stream here in real-time as requests are dispatched.</p>
            </td>
          </tr>
          <tr v-for="log in logs" :key="log.id" class="hover:bg-white/[0.02] transition-colors">
            <!-- Time -->
            <td class="p-3.5 text-xs text-white/50 font-mono whitespace-nowrap">
              {{ formatDateTime(log.created_at) }}
            </td>

            <!-- Method & Path -->
            <td class="p-3.5">
              <div class="flex items-center gap-2 max-w-xs sm:max-w-md">
                <span class="px-2 py-0.5 text-[10px] font-mono font-bold rounded-md border shrink-0" :class="getMethodBadgeClass(log.method)">
                  {{ log.method }}
                </span>
                <span class="text-xs font-mono text-white/90 truncate" :title="log.path">
                  {{ log.path }}
                </span>
              </div>
            </td>

            <!-- Client -->
            <td class="p-3.5 whitespace-nowrap">
              <span class="px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider rounded-md border" :class="getClientBadgeClass(log.client_type)">
                {{ log.client_type }}
              </span>
            </td>

            <!-- Status -->
            <td class="p-3.5 whitespace-nowrap">
              <span class="px-2 py-0.5 text-xs font-mono font-bold rounded-lg border" :class="getStatusCodeClass(log.status_code)">
                {{ log.status_code }}
              </span>
            </td>

            <!-- Latency -->
            <td class="p-3.5 whitespace-nowrap">
              <div class="flex items-center gap-1.5">
                <span class="text-xs font-mono" :class="log.is_slow ? 'text-amber-400 font-bold' : 'text-white/70'">
                  {{ log.duration_ms }} ms
                </span>
                <span v-if="log.is_slow" class="px-1.5 py-0.2 rounded text-[9px] uppercase font-bold text-amber-300 bg-amber-500/20 border border-amber-500/30">
                  Slow
                </span>
              </div>
            </td>

            <!-- DB Queries -->
            <td class="p-3.5 whitespace-nowrap">
              <div class="flex flex-col">
                <div class="flex items-center gap-1.5">
                  <span class="text-xs font-mono" :class="log.budget_exceeded ? 'text-rose-400 font-bold' : 'text-white/70'">
                    {{ log.query_count }} queries
                  </span>
                  <span v-if="log.budget_exceeded" class="px-1.5 py-0.2 rounded text-[9px] uppercase font-bold text-rose-300 bg-rose-500/20 border border-rose-500/30">
                    &gt;25
                  </span>
                </div>
                <span class="text-[10px] font-mono text-white/40">
                  {{ log.query_duration_ms }} ms DB
                </span>
              </div>
            </td>

            <!-- Origin & User -->
            <td class="p-3.5 whitespace-nowrap">
              <div class="flex flex-col">
                <span class="text-xs font-medium text-white/80">
                  {{ log.user_name || 'Guest / Public' }}
                </span>
                <span class="text-[10px] font-mono text-white/40">
                  {{ log.ip_address || '0.0.0.0' }} &bull; {{ log.memory_kb }} KB
                </span>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination Footer -->
    <div class="border-t border-white/[0.06] p-4 flex flex-col sm:flex-row items-center justify-between gap-3 bg-[#0B0F13]/40">
      <div class="text-xs text-white/50">
        Showing page <span class="font-mono text-white/90">{{ pagination.current_page }}</span> of <span class="font-mono text-white/90">{{ pagination.last_page }}</span>
        (<span class="font-mono text-white/90">{{ pagination.total }}</span> total conversations)
      </div>
      <div class="flex items-center gap-2">
        <button
          type="button"
          @click="$emit('page-change', pagination.current_page - 1)"
          :disabled="pagination.current_page <= 1 || loading"
          class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs border border-white/10 text-white/70 hover:bg-white/5 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
        >
          <ChevronLeftIcon class="w-3.5 h-3.5" />
          <span>Previous</span>
        </button>
        <button
          type="button"
          @click="$emit('page-change', pagination.current_page + 1)"
          :disabled="pagination.current_page >= pagination.last_page || loading"
          class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs border border-white/10 text-white/70 hover:bg-white/5 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
        >
          <span>Next</span>
          <ChevronRightIcon class="w-3.5 h-3.5" />
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import {
  FunnelIcon,
  ClockIcon,
  CircleStackIcon,
  MagnifyingGlassIcon,
  TrashIcon,
  ArrowPathIcon,
  CpuChipIcon,
  ChevronLeftIcon,
  ChevronRightIcon
} from '@heroicons/vue/24/outline';

defineProps({
  logs: {
    type: Array,
    required: true
  },
  loading: {
    type: Boolean,
    default: false
  },
  pagination: {
    type: Object,
    default: () => ({ current_page: 1, last_page: 1, total: 0, per_page: 25 })
  },
  clientFilter: {
    type: String,
    default: 'web'
  },
  isSlow: {
    type: Boolean,
    default: false
  },
  budgetExceeded: {
    type: Boolean,
    default: false
  },
  search: {
    type: String,
    default: ''
  },
  getMethodBadgeClass: {
    type: Function,
    required: true
  },
  getClientBadgeClass: {
    type: Function,
    required: true
  },
  getStatusCodeClass: {
    type: Function,
    required: true
  },
  formatDateTime: {
    type: Function,
    required: true
  }
});

defineEmits([
  'update:clientFilter',
  'update:isSlow',
  'update:budgetExceeded',
  'update:search',
  'open-prune',
  'refresh',
  'page-change'
]);
</script>

