<template>
  <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <!-- Client Ingress Distribution -->
    <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C]/90 p-5 sm:p-6 backdrop-blur-xl shadow-xl flex flex-col justify-between">
      <div>
        <div class="flex items-center justify-between">
          <h3 class="text-xs font-bold uppercase tracking-wider text-white/80">Client Ingress Distribution</h3>
          <span class="text-[10px] text-white/40 font-mono">24h telemetry</span>
        </div>
        <p class="mt-1 text-xs text-white/50">Breakdown of requests handled across the browser website and signed webhooks.</p>

        <div class="mt-5 space-y-4">
          <!-- Web Portal -->
          <div>
            <div class="flex items-center justify-between text-xs mb-1.5">
              <span class="flex items-center gap-1.5 text-sky-400 font-medium">
                <GlobeAltIcon class="w-3.5 h-3.5" /> Web Portal
              </span>
              <span class="font-mono text-white/70">
                {{ metrics?.client_distribution?.web ?? 0 }} calls ({{ distributionPercent.web }}%)
              </span>
            </div>
            <div class="h-2 w-full rounded-full bg-white/[0.05] overflow-hidden">
              <div
                class="h-full bg-gradient-to-r from-sky-400 to-blue-500 rounded-full transition-all duration-700"
                :style="{ width: `${distributionPercent.web}%` }"
              ></div>
            </div>
          </div>

          <!-- Webhooks -->
          <div>
            <div class="flex items-center justify-between text-xs mb-1.5">
              <span class="flex items-center gap-1.5 text-emerald-400 font-medium">
                <BoltIcon class="w-3.5 h-3.5" /> Signed Webhooks
              </span>
              <span class="font-mono text-white/70">
                {{ metrics?.client_distribution?.webhook ?? 0 }} calls ({{ distributionPercent.webhook }}%)
              </span>
            </div>
            <div class="h-2 w-full rounded-full bg-white/[0.05] overflow-hidden">
              <div
                class="h-full bg-gradient-to-r from-emerald-400 to-teal-500 rounded-full transition-all duration-700"
                :style="{ width: `${distributionPercent.webhook}%` }"
              ></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Gate Summary Protection Alert -->
      <div class="mt-6 rounded-xl border border-[#FF6700]/20 bg-[#FF6700]/5 p-3.5 text-xs text-white/70 flex items-start gap-2.5">
        <ShieldCheckIcon class="w-4 h-4 text-[#FF6700] shrink-0 mt-0.5" />
        <div>
          <span class="font-bold text-[#FF6700]">Security Guard Active:</span>
          Rate limiters neutralize DDoS traffic while DB Query Guard stops N+1 performance regressions.
        </div>
      </div>
    </div>

    <!-- Top Endpoints Hotspots -->
    <div class="lg:col-span-2 relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C]/90 p-5 sm:p-6 backdrop-blur-xl shadow-xl flex flex-col justify-between">
      <div>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <div>
            <h3 class="text-xs font-bold uppercase tracking-wider text-white/80">Route Diagnostics &amp; Hotspots</h3>
            <p class="mt-1 text-xs text-white/50">Trace high-latency endpoints and heavy database query operations.</p>
          </div>
          <div class="flex items-center rounded-xl bg-white/[0.04] border border-white/10 p-1 self-start sm:self-auto">
            <button
              type="button"
              @click="$emit('update:hotspotTab', 'slow')"
              class="px-3 py-1 text-xs font-semibold rounded-lg transition-colors"
              :class="hotspotTab === 'slow' ? 'bg-[#FF6700] text-white shadow' : 'text-white/60 hover:text-white'"
            >
              Slowest Routes
            </button>
            <button
              type="button"
              @click="$emit('update:hotspotTab', 'queries')"
              class="px-3 py-1 text-xs font-semibold rounded-lg transition-colors"
              :class="hotspotTab === 'queries' ? 'bg-[#FF6700] text-white shadow' : 'text-white/60 hover:text-white'"
            >
              High DB Queries
            </button>
          </div>
        </div>

        <!-- Hotspot Content Lists -->
        <div class="mt-4">
          <!-- Tab 1: Slowest Routes -->
          <div v-if="hotspotTab === 'slow'">
            <div v-if="!metrics?.top_slow_endpoints?.length" class="p-8 text-center text-xs text-white/40">
              No slow endpoint anomalies detected in the last 24 hours.
            </div>
            <div v-else class="space-y-2">
              <div
                v-for="(ep, idx) in metrics.top_slow_endpoints"
                :key="'slow-' + idx"
                class="flex items-center justify-between p-2.5 rounded-xl bg-[#0B0F13]/60 border border-white/[0.06] hover:border-white/10 transition-colors"
              >
                <div class="flex items-center gap-2.5 overflow-hidden min-w-0">
                  <span class="px-2 py-0.5 text-[10px] font-mono font-bold rounded-md border shrink-0" :class="getMethodBadgeClass(ep.method)">
                    {{ ep.method }}
                  </span>
                  <span class="text-xs font-mono text-white/90 truncate">{{ ep.path }}</span>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                  <span class="text-xs font-mono text-white/40">{{ ep.calls }} calls</span>
                  <span class="px-2 py-0.5 text-xs font-mono font-bold rounded-md border text-amber-400 bg-amber-500/10 border-amber-500/20">
                    {{ ep.avg_ms }} ms avg
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Tab 2: High DB Queries -->
          <div v-else>
            <div v-if="!metrics?.top_queried_endpoints?.length" class="p-8 text-center text-xs text-white/40">
              No high-query route anomalies recorded.
            </div>
            <div v-else class="space-y-2">
              <div
                v-for="(ep, idx) in metrics.top_queried_endpoints"
                :key="'qry-' + idx"
                class="flex items-center justify-between p-2.5 rounded-xl bg-[#0B0F13]/60 border border-white/[0.06] hover:border-white/10 transition-colors"
              >
                <div class="flex items-center gap-2.5 overflow-hidden min-w-0">
                  <span class="px-2 py-0.5 text-[10px] font-mono font-bold rounded-md border shrink-0" :class="getMethodBadgeClass(ep.method)">
                    {{ ep.method }}
                  </span>
                  <span class="text-xs font-mono text-white/90 truncate">{{ ep.path }}</span>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                  <span class="text-xs font-mono text-white/40">{{ ep.calls }} calls</span>
                  <span
                    class="px-2 py-0.5 text-xs font-mono font-bold rounded-md border"
                    :class="ep.avg_queries > 25 ? 'text-rose-400 bg-rose-500/10 border-rose-500/20' : 'text-purple-400 bg-purple-500/10 border-purple-500/20'"
                  >
                    {{ ep.avg_queries }} DB queries
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Threshold Note -->
      <div class="mt-4 pt-3 border-t border-white/5 flex flex-col sm:flex-row items-start sm:items-center justify-between text-[11px] text-white/40 gap-1">
        <span>Thresholds: Latency Warning &gt; 150ms | Query Warning &gt; 25 queries</span>
        <span>Updated live with API Gate</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import {
  GlobeAltIcon,
  BoltIcon,
  ShieldCheckIcon
} from '@heroicons/vue/24/outline';

defineProps({
  metrics: {
    type: Object,
    default: () => ({})
  },
  distributionPercent: {
    type: Object,
    default: () => ({ web: 0, webhook: 0 })
  },
  hotspotTab: {
    type: String,
    default: 'slow'
  },
  getMethodBadgeClass: {
    type: Function,
    required: true
  }
});

defineEmits(['update:hotspotTab']);
</script>

