<template>
  <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 sm:gap-5">
    <!-- KPI 1: Ingress Requests -->
    <div class="group relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C]/90 p-5 backdrop-blur-xl transition-all duration-300 hover:border-sky-500/30 hover:shadow-xl hover:shadow-sky-500/5">
      <div class="flex items-center justify-between">
        <span class="text-xs font-semibold uppercase tracking-wider text-white/50">24h Ingress Requests</span>
        <div class="flex h-10 w-10 items-center justify-center rounded-xl border border-sky-500/20 bg-sky-500/10 text-sky-400 group-hover:scale-105 transition-transform">
          <GlobeAltIcon class="h-5 w-5" />
        </div>
      </div>
      <div class="mt-4">
        <div class="flex items-baseline gap-2">
          <span class="font-mono text-2xl sm:text-3xl font-bold tracking-tight text-white tabular-nums">
            {{ Number(metrics?.total_requests || 0).toLocaleString() }}
          </span>
          <span class="text-xs text-white/40">calls</span>
        </div>
        <p class="mt-1 text-xs text-emerald-400 flex items-center gap-1.5">
          <ShieldCheckIcon class="h-3.5 w-3.5" />
          <span>Calibrated Rate Limiting Active</span>
        </p>
      </div>
    </div>

    <!-- KPI 2: Response Time -->
    <div class="group relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C]/90 p-5 backdrop-blur-xl transition-all duration-300 hover:border-amber-500/30 hover:shadow-xl hover:shadow-amber-500/5">
      <div class="flex items-center justify-between">
        <span class="text-xs font-semibold uppercase tracking-wider text-white/50">Avg Response Time</span>
        <div class="flex h-10 w-10 items-center justify-center rounded-xl border border-amber-500/20 bg-amber-500/10 text-amber-400 group-hover:scale-105 transition-transform">
          <ClockIcon class="h-5 w-5" />
        </div>
      </div>
      <div class="mt-4">
        <div class="flex items-baseline gap-2">
          <span class="font-mono text-2xl sm:text-3xl font-bold tracking-tight text-white tabular-nums">
            {{ metrics?.avg_latency_ms ?? 0 }}
          </span>
          <span class="text-xs text-white/40">ms</span>
        </div>
        <p
          class="mt-1 text-xs flex items-center gap-1.5"
          :class="(metrics?.slow_requests_count || 0) > 0 ? 'text-amber-400' : 'text-white/40'"
        >
          <BoltIcon class="h-3.5 w-3.5" />
          <span>{{ metrics?.slow_requests_count ?? 0 }} slow requests (>150ms)</span>
        </p>
      </div>
    </div>

    <!-- KPI 3: DB Query Budget -->
    <div class="group relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C]/90 p-5 backdrop-blur-xl transition-all duration-300 hover:border-purple-500/30 hover:shadow-xl hover:shadow-purple-500/5">
      <div class="flex items-center justify-between">
        <span class="text-xs font-semibold uppercase tracking-wider text-white/50">Avg DB Queries / Req</span>
        <div class="flex h-10 w-10 items-center justify-center rounded-xl border border-purple-500/20 bg-purple-500/10 text-purple-400 group-hover:scale-105 transition-transform">
          <CircleStackIcon class="h-5 w-5" />
        </div>
      </div>
      <div class="mt-4">
        <div class="flex items-baseline gap-2">
          <span class="font-mono text-2xl sm:text-3xl font-bold tracking-tight text-white tabular-nums">
            {{ metrics?.avg_query_count ?? 0 }}
          </span>
          <span class="text-xs text-white/40">queries</span>
        </div>
        <p
          class="mt-1 text-xs flex items-center gap-1.5"
          :class="(metrics?.budget_warnings_count || 0) > 0 ? 'text-rose-400' : 'text-emerald-400'"
        >
          <ExclamationTriangleIcon v-if="(metrics?.budget_warnings_count || 0) > 0" class="h-3.5 w-3.5" />
          <CheckCircleIcon v-else class="h-3.5 w-3.5" />
          <span>{{ metrics?.budget_warnings_count ?? 0 }} budget breaches (>25)</span>
        </p>
      </div>
    </div>

    <!-- KPI 4: Error Rate -->
    <div class="group relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C]/90 p-5 backdrop-blur-xl transition-all duration-300 hover:border-rose-500/30 hover:shadow-xl hover:shadow-rose-500/5">
      <div class="flex items-center justify-between">
        <span class="text-xs font-semibold uppercase tracking-wider text-white/50">Error Rate (4xx / 5xx)</span>
        <div class="flex h-10 w-10 items-center justify-center rounded-xl border border-rose-500/20 bg-rose-500/10 text-rose-400 group-hover:scale-105 transition-transform">
          <ExclamationTriangleIcon class="h-5 w-5" />
        </div>
      </div>
      <div class="mt-4">
        <div class="flex items-baseline gap-2">
          <span class="font-mono text-2xl sm:text-3xl font-bold tracking-tight text-white tabular-nums">
            {{ metrics?.error_rate_percent ?? 0 }}%
          </span>
          <span class="text-xs text-white/40">({{ metrics?.error_count ?? 0 }} errors)</span>
        </div>
        <p
          class="mt-1 text-xs flex items-center gap-1.5"
          :class="(metrics?.error_rate_percent || 0) > 5 ? 'text-rose-400' : 'text-white/40'"
        >
          <span
            class="h-2 w-2 rounded-full"
            :class="(metrics?.error_rate_percent || 0) > 5 ? 'bg-rose-400 animate-pulse' : 'bg-emerald-400'"
          ></span>
          <span>Health threshold: &lt; 5.0%</span>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import {
  GlobeAltIcon,
  ShieldCheckIcon,
  ClockIcon,
  BoltIcon,
  CircleStackIcon,
  ExclamationTriangleIcon,
  CheckCircleIcon
} from '@heroicons/vue/24/outline';

defineProps({
  metrics: {
    type: Object,
    default: () => ({})
  }
});
</script>

