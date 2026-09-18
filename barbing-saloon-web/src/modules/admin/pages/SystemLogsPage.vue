<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import AdminLayout from '@/portals/admin/layouts/AdminLayout.vue'
import { adminApi } from '@/shared/api/old_adminApi'
import { useToast } from '@/core/composables/useToast'
import {
  ClipboardDocumentListIcon,
  ArrowPathIcon,
  ShieldCheckIcon,
  ExclamationCircleIcon,
  UserIcon,
  CreditCardIcon,
  ArrowRightOnRectangleIcon,
  MagnifyingGlassIcon,
  FunnelIcon,
  CpuChipIcon,
  ClockIcon,
  CircleStackIcon,
  ExclamationTriangleIcon,
  CheckCircleIcon,
  TrashIcon,
  DevicePhoneMobileIcon,
  GlobeAltIcon,
  BoltIcon,
  ChevronLeftIcon,
  ChevronRightIcon
} from '@heroicons/vue/24/outline'

const toast = useToast()

// Tabs: 'gate' | 'audit'
const activeTab = ref('gate')

// ==========================================
// 1. Audit Logs State & Logic
// ==========================================
const auditLogs = ref([])
const auditLoading = ref(true)
const auditSearch = ref('')
const auditFilterType = ref('all')

const fetchAuditLogs = async () => {
  auditLoading.value = true
  try {
    const res = await adminApi.logs()
    auditLogs.value = res.data?.data || []
  } catch (err) {
    console.error('Failed to load audit logs:', err)
  } finally {
    auditLoading.value = false
  }
}

const filteredAuditLogs = computed(() => {
  let result = auditLogs.value

  if (auditFilterType.value !== 'all') {
    result = result.filter(log => log.entity_type === auditFilterType.value || log.action === auditFilterType.value)
  }

  if (auditSearch.value) {
    const q = auditSearch.value.toLowerCase()
    result = result.filter(log =>
      (log.user_name || '').toLowerCase().includes(q) ||
      (log.action || '').toLowerCase().includes(q) ||
      (log.entity_type || '').toLowerCase().includes(q) ||
      (log.ip_address || '').toLowerCase().includes(q)
    )
  }

  return result
})

const getIconForAction = (action) => {
  if (action === 'login') return ArrowRightOnRectangleIcon
  if (action.includes('payment')) return CreditCardIcon
  if (action.includes('booking') || action.includes('walkin') || action.includes('service')) return ShieldCheckIcon
  if (action.includes('barber')) return UserIcon
  if (action.includes('blog')) return ClipboardDocumentListIcon
  if (action.includes('error') || action.includes('failed')) return ExclamationCircleIcon
  if (action.includes('admin') || action.includes('system') || action.includes('update_settings')) return ShieldCheckIcon
  return UserIcon
}

const getIconColorForAction = (action) => {
  if (action === 'login') return 'text-blue-400 bg-blue-500/10 border-blue-500/20'
  if (action.includes('verify') || action.includes('approve') || action.includes('complete') || action.includes('create')) return 'text-emerald-400 bg-emerald-500/10 border-emerald-500/20'
  if (action.includes('reject') || action.includes('cancel') || action.includes('delete') || action.includes('no_show')) return 'text-red-400 bg-red-500/10 border-red-500/20'
  if (action.includes('update')) return 'text-amber-400 bg-amber-500/10 border-amber-500/20'
  return 'text-admin bg-admin/10 border-admin/20'
}

// ==========================================
// 2. Gate Telemetry & Query Monitor State
// ==========================================
const gateMetrics = ref(null)
const gateMetricsLoading = ref(true)
const gateLogs = ref([])
const gateLogsLoading = ref(true)
const gatePagination = ref({ current_page: 1, last_page: 1, total: 0, per_page: 25 })

const gateClientFilter = ref('all')
const gateIsSlow = ref(false)
const gateBudgetExceeded = ref(false)
const gateSearch = ref('')
const hotspotTab = ref('slow')

// Pruning modal
const showPruneModal = ref(false)
const pruneDays = ref(7)
const pruning = ref(false)

const fetchGateMetrics = async () => {
  gateMetricsLoading.value = true
  try {
    const res = await adminApi.gateMetrics()
    gateMetrics.value = res.data?.data || null
  } catch (err) {
    console.error('Failed to load gate metrics:', err)
  } finally {
    gateMetricsLoading.value = false
  }
}

const fetchGateLogs = async (page = 1) => {
  gateLogsLoading.value = true
  try {
    const params = {
      page,
      per_page: 25,
      client_type: gateClientFilter.value !== 'all' ? gateClientFilter.value : undefined,
      is_slow: gateIsSlow.value ? 'true' : undefined,
      budget_exceeded: gateBudgetExceeded.value ? 'true' : undefined,
      search: gateSearch.value.trim() || undefined
    }
    const res = await adminApi.gateLogs(params)
    const data = res.data?.data
    gateLogs.value = data?.items || []
    if (data?.pagination) {
      gatePagination.value = data.pagination
    }
  } catch (err) {
    console.error('Failed to load gate logs:', err)
  } finally {
    gateLogsLoading.value = false
  }
}

const fetchGateData = async () => {
  await Promise.all([fetchGateMetrics(), fetchGateLogs(1)])
}

let searchDebounceTimer = null
watch([gateClientFilter, gateIsSlow, gateBudgetExceeded], () => {
  fetchGateLogs(1)
})

const onGateSearchInput = () => {
  clearTimeout(searchDebounceTimer)
  searchDebounceTimer = setTimeout(() => {
    fetchGateLogs(1)
  }, 350)
}

const handlePrune = async () => {
  pruning.value = true
  try {
    const res = await adminApi.flushGate({ keep_days: Number(pruneDays.value) })
    const pruned = res.data?.data?.pruned_records ?? 0
    toast.success(`Successfully pruned ${pruned} telemetry logs older than ${pruneDays.value} days.`)
    showPruneModal.value = false
    await fetchGateData()
  } catch (err) {
    console.error('Failed to prune gate telemetry:', err)
    toast.error('Failed to prune telemetry logs.')
  } finally {
    pruning.value = false
  }
}

// Helpers for Gate UI
const getMethodBadgeClass = (method) => {
  switch (method?.toUpperCase()) {
    case 'GET': return 'text-sky-400 bg-sky-500/10 border-sky-500/20'
    case 'POST': return 'text-emerald-400 bg-emerald-500/10 border-emerald-500/20'
    case 'PUT':
    case 'PATCH': return 'text-amber-400 bg-amber-500/10 border-amber-500/20'
    case 'DELETE': return 'text-rose-400 bg-rose-500/10 border-rose-500/20'
    default: return 'text-gray-400 bg-gray-500/10 border-gray-500/20'
  }
}

const getClientBadgeClass = (type) => {
  switch (type) {
    case 'mobile': return 'text-purple-400 bg-purple-500/10 border-purple-500/20'
    case 'web': return 'text-blue-400 bg-blue-500/10 border-blue-500/20'
    case 'webhook': return 'text-emerald-400 bg-emerald-500/10 border-emerald-500/20'
    default: return 'text-gray-400 bg-gray-500/10 border-gray-500/20'
  }
}

const getStatusCodeClass = (status) => {
  if (status >= 200 && status < 300) return 'text-emerald-400 bg-emerald-500/10 border-emerald-500/20'
  if (status >= 300 && status < 400) return 'text-blue-400 bg-blue-500/10 border-blue-500/20'
  if (status >= 400 && status < 500) return 'text-amber-400 bg-amber-500/10 border-amber-500/20'
  return 'text-rose-400 bg-rose-500/10 border-rose-500/20'
}

const formatDateTime = (isoString) => {
  if (!isoString) return 'N/A'
  const d = new Date(isoString)
  return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ' ' + d.toLocaleDateString([], { month: 'short', day: 'numeric' })
}

const clientDistributionPercent = computed(() => {
  const dist = gateMetrics.value?.client_distribution || { web: 0, mobile: 0, webhook: 0 }
  const total = (dist.web || 0) + (dist.mobile || 0) + (dist.webhook || 0)
  if (total === 0) return { web: 0, mobile: 0, webhook: 0 }
  return {
    web: Math.round(((dist.web || 0) / total) * 100),
    mobile: Math.round(((dist.mobile || 0) / total) * 100),
    webhook: Math.round(((dist.webhook || 0) / total) * 100)
  }
})

onMounted(() => {
  fetchGateData()
  fetchAuditLogs()
})
</script>

<template>
  <AdminLayout>
    <div class="space-y-6 animate-fade-in">
      <!-- Header Banner -->
      <div class="relative overflow-hidden rounded-3xl border border-admin/20 bg-gradient-to-br from-obsidian via-charcoal to-[#2A1100] p-8 shadow-2xl flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-admin/5 blur-3xl pointer-events-none"></div>
        <div class="absolute -left-16 -bottom-16 h-48 w-48 rounded-full bg-admin/5 blur-3xl pointer-events-none"></div>
        
        <div class="relative z-10">
          <div class="flex items-center gap-2">
            <p class="text-xs uppercase tracking-[0.3em] text-admin/70 font-bold">Control & Telemetry</p>
            <span 
              v-if="gateMetrics" 
              class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider"
              :class="gateMetrics.status === 'optimal' ? 'bg-emerald-500/10 border border-emerald-500/20 text-emerald-400' : 'bg-amber-500/10 border border-amber-500/20 text-amber-400'"
            >
              <span class="h-1.5 w-1.5 rounded-full" :class="gateMetrics.status === 'optimal' ? 'bg-emerald-400 animate-pulse' : 'bg-amber-400 animate-ping'"></span>
              Gate {{ gateMetrics.status }}
            </span>
          </div>
          <h1 class="mt-2 font-display text-4xl text-theme-text drop-shadow-lg flex items-center gap-3">
            System <span class="text-transparent bg-clip-text bg-gradient-to-r from-admin to-admin-light">Logs & Gate</span>
          </h1>
          <p class="mt-2 text-sm text-ivory/60">Monitor ingress traffic, database query budgets, and administrative audit trails.</p>
        </div>
        
        <!-- Tab Switcher -->
        <div class="relative z-10 flex items-center p-1.5 rounded-2xl bg-black/40 border border-admin/20 backdrop-blur-md self-start md:self-auto">
          <button 
            @click="activeTab = 'gate'"
            class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition-all"
            :class="activeTab === 'gate' ? 'bg-admin text-black shadow-lg shadow-admin/20' : 'text-ivory/70 hover:text-ivory hover:bg-white/5'"
          >
            <CpuChipIcon class="w-4 h-4" />
            API & DB Gate
            <span v-if="gateMetrics?.total_requests" class="px-1.5 py-0.2 rounded-full text-[10px] font-mono font-normal" :class="activeTab === 'gate' ? 'bg-black/20 text-black' : 'bg-white/10 text-ivory/60'">
              {{ gateMetrics.total_requests }}
            </span>
          </button>
          <button 
            @click="activeTab = 'audit'"
            class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition-all"
            :class="activeTab === 'audit' ? 'bg-admin text-black shadow-lg shadow-admin/20' : 'text-ivory/70 hover:text-ivory hover:bg-white/5'"
          >
            <ClipboardDocumentListIcon class="w-4 h-4" />
            Audit Logs
            <span v-if="filteredAuditLogs.length" class="px-1.5 py-0.2 rounded-full text-[10px] font-mono font-normal" :class="activeTab === 'audit' ? 'bg-black/20 text-black' : 'bg-white/10 text-ivory/60'">
              {{ filteredAuditLogs.length }}
            </span>
          </button>
        </div>
      </div>

      <!-- ======================================================== -->
      <!-- TAB 1: API & DB GATE MONITOR                             -->
      <!-- ======================================================== -->
      <div v-if="activeTab === 'gate'" class="space-y-6">
        <!-- 4 KPI Metrics Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <!-- KPI 1: 24h Requests -->
          <div class="relative overflow-hidden rounded-2xl border border-white/10 bg-black/30 p-5 backdrop-blur-sm">
            <div class="flex items-center justify-between text-ivory/60">
              <span class="text-xs uppercase tracking-wider font-semibold">24h Ingress Requests</span>
              <GlobeAltIcon class="w-5 h-5 text-sky-400" />
            </div>
            <div class="mt-3 flex items-baseline gap-2">
              <span class="text-3xl font-bold font-mono text-theme-text">{{ gateMetrics?.total_requests ?? 0 }}</span>
              <span class="text-xs text-ivory/40">calls</span>
            </div>
            <div class="mt-2 text-xs flex items-center gap-1.5 text-emerald-400">
              <ShieldCheckIcon class="w-3.5 h-3.5" />
              <span>Calibrated Rate Limiting Active</span>
            </div>
          </div>

          <!-- KPI 2: Average Latency -->
          <div class="relative overflow-hidden rounded-2xl border border-white/10 bg-black/30 p-5 backdrop-blur-sm">
            <div class="flex items-center justify-between text-ivory/60">
              <span class="text-xs uppercase tracking-wider font-semibold">Avg Response Time</span>
              <ClockIcon class="w-5 h-5 text-amber-400" />
            </div>
            <div class="mt-3 flex items-baseline gap-2">
              <span class="text-3xl font-bold font-mono text-theme-text">{{ gateMetrics?.avg_latency_ms ?? 0 }}</span>
              <span class="text-xs text-ivory/40">ms</span>
            </div>
            <div class="mt-2 text-xs flex items-center gap-1.5" :class="gateMetrics?.slow_requests_count > 0 ? 'text-amber-400' : 'text-ivory/40'">
              <BoltIcon class="w-3.5 h-3.5" />
              <span>{{ gateMetrics?.slow_requests_count ?? 0 }} slow requests (>150ms)</span>
            </div>
          </div>

          <!-- KPI 3: DB Query Budget -->
          <div class="relative overflow-hidden rounded-2xl border border-white/10 bg-black/30 p-5 backdrop-blur-sm">
            <div class="flex items-center justify-between text-ivory/60">
              <span class="text-xs uppercase tracking-wider font-semibold">Avg DB Queries / Req</span>
              <CircleStackIcon class="w-5 h-5 text-purple-400" />
            </div>
            <div class="mt-3 flex items-baseline gap-2">
              <span class="text-3xl font-bold font-mono text-theme-text">{{ gateMetrics?.avg_query_count ?? 0 }}</span>
              <span class="text-xs text-ivory/40">queries</span>
            </div>
            <div class="mt-2 text-xs flex items-center gap-1.5" :class="gateMetrics?.budget_warnings_count > 0 ? 'text-rose-400' : 'text-emerald-400'">
              <ExclamationTriangleIcon v-if="gateMetrics?.budget_warnings_count > 0" class="w-3.5 h-3.5" />
              <CheckCircleIcon v-else class="w-3.5 h-3.5" />
              <span>{{ gateMetrics?.budget_warnings_count ?? 0 }} budget breaches (>25)</span>
            </div>
          </div>

          <!-- KPI 4: Error Rate -->
          <div class="relative overflow-hidden rounded-2xl border border-white/10 bg-black/30 p-5 backdrop-blur-sm">
            <div class="flex items-center justify-between text-ivory/60">
              <span class="text-xs uppercase tracking-wider font-semibold">Error Rate (4xx / 5xx)</span>
              <ExclamationTriangleIcon class="w-5 h-5 text-rose-400" />
            </div>
            <div class="mt-3 flex items-baseline gap-2">
              <span class="text-3xl font-bold font-mono text-theme-text">{{ gateMetrics?.error_rate_percent ?? 0 }}%</span>
              <span class="text-xs text-ivory/40">({{ gateMetrics?.error_count ?? 0 }} errors)</span>
            </div>
            <div class="mt-2 text-xs flex items-center gap-1.5" :class="gateMetrics?.error_rate_percent > 5 ? 'text-rose-400' : 'text-ivory/40'">
              <span class="h-2 w-2 rounded-full" :class="gateMetrics?.error_rate_percent > 5 ? 'bg-rose-400' : 'bg-emerald-400'"></span>
              <span>Health threshold: &lt; 5.0%</span>
            </div>
          </div>
        </div>

        <!-- Hotspots & Client Origin Section -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Client Distribution -->
          <div class="rounded-2xl border border-white/10 bg-black/30 p-6 backdrop-blur-sm flex flex-col justify-between">
            <div>
              <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold uppercase tracking-wider text-ivory/80">Client Ingress Distribution</h3>
                <span class="text-xs text-ivory/40 font-mono">24h telemetry</span>
              </div>
              <p class="mt-1 text-xs text-ivory/50">Breakdown of requests handled across mobile app, browser website, and webhooks.</p>
              
              <div class="mt-5 space-y-4">
                <!-- Web -->
                <div>
                  <div class="flex items-center justify-between text-xs mb-1.5">
                    <span class="flex items-center gap-1.5 text-blue-400 font-medium">
                      <GlobeAltIcon class="w-3.5 h-3.5" /> Web Portal
                    </span>
                    <span class="font-mono text-ivory/70">{{ gateMetrics?.client_distribution?.web ?? 0 }} calls ({{ clientDistributionPercent.web }}%)</span>
                  </div>
                  <div class="h-2 w-full rounded-full bg-white/5 overflow-hidden">
                    <div class="h-full bg-blue-500 rounded-full transition-all duration-500" :style="{ width: `${clientDistributionPercent.web}%` }"></div>
                  </div>
                </div>

                <!-- Mobile -->
                <div>
                  <div class="flex items-center justify-between text-xs mb-1.5">
                    <span class="flex items-center gap-1.5 text-purple-400 font-medium">
                      <DevicePhoneMobileIcon class="w-3.5 h-3.5" /> Mobile App
                    </span>
                    <span class="font-mono text-ivory/70">{{ gateMetrics?.client_distribution?.mobile ?? 0 }} calls ({{ clientDistributionPercent.mobile }}%)</span>
                  </div>
                  <div class="h-2 w-full rounded-full bg-white/5 overflow-hidden">
                    <div class="h-full bg-purple-500 rounded-full transition-all duration-500" :style="{ width: `${clientDistributionPercent.mobile}%` }"></div>
                  </div>
                </div>

                <!-- Webhooks -->
                <div>
                  <div class="flex items-center justify-between text-xs mb-1.5">
                    <span class="flex items-center gap-1.5 text-emerald-400 font-medium">
                      <BoltIcon class="w-3.5 h-3.5" /> Signed Webhooks
                    </span>
                    <span class="font-mono text-ivory/70">{{ gateMetrics?.client_distribution?.webhook ?? 0 }} calls ({{ clientDistributionPercent.webhook }}%)</span>
                  </div>
                  <div class="h-2 w-full rounded-full bg-white/5 overflow-hidden">
                    <div class="h-full bg-emerald-500 rounded-full transition-all duration-500" :style="{ width: `${clientDistributionPercent.webhook}%` }"></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Gate Summary Alert -->
            <div class="mt-6 p-3.5 rounded-xl border border-admin/20 bg-admin/5 text-xs text-ivory/70 flex items-start gap-2.5">
              <ShieldCheckIcon class="w-4 h-4 text-admin shrink-0 mt-0.5" />
              <div>
                <span class="font-bold text-admin-light">Gate Protection Active:</span>
                Rate limiters prevent DDoS &amp; brute-force attacks while DB Query Guard stops N+1 performance regressions.
              </div>
            </div>
          </div>

          <!-- Top Endpoints Hotspot -->
          <div class="lg:col-span-2 rounded-2xl border border-white/10 bg-black/30 p-6 backdrop-blur-sm flex flex-col justify-between">
            <div>
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                  <h3 class="text-sm font-bold uppercase tracking-wider text-ivory/80">Route Diagnostics &amp; Hotspots</h3>
                  <p class="mt-1 text-xs text-ivory/50">Identify slowest endpoints and heavy database query consumers.</p>
                </div>
                <div class="flex items-center p-1 rounded-xl bg-white/[0.04] border border-white/10 self-start sm:self-auto">
                  <button 
                    @click="hotspotTab = 'slow'"
                    class="px-3 py-1 text-xs font-semibold rounded-lg transition-colors"
                    :class="hotspotTab === 'slow' ? 'bg-admin text-black' : 'text-ivory/60 hover:text-ivory'"
                  >
                    Slowest Routes
                  </button>
                  <button 
                    @click="hotspotTab = 'queries'"
                    class="px-3 py-1 text-xs font-semibold rounded-lg transition-colors"
                    :class="hotspotTab === 'queries' ? 'bg-admin text-black' : 'text-ivory/60 hover:text-ivory'"
                  >
                    High DB Queries
                  </button>
                </div>
              </div>

              <!-- Content list -->
              <div class="mt-4">
                <div v-if="hotspotTab === 'slow'">
                  <div v-if="!gateMetrics?.top_slow_endpoints?.length" class="p-8 text-center text-xs text-ivory/40">
                    No slow endpoint anomalies detected in the last 24 hours.
                  </div>
                  <div v-else class="space-y-2">
                    <div 
                      v-for="(ep, idx) in gateMetrics.top_slow_endpoints" 
                      :key="idx"
                      class="flex items-center justify-between p-2.5 rounded-xl bg-white/[0.02] border border-white/5 hover:border-white/10 transition-colors"
                    >
                      <div class="flex items-center gap-2.5 overflow-hidden">
                        <span class="px-2 py-0.5 text-[10px] font-mono font-bold rounded-md border shrink-0" :class="getMethodBadgeClass(ep.method)">
                          {{ ep.method }}
                        </span>
                        <span class="text-xs font-mono text-ivory/90 truncate">{{ ep.path }}</span>
                      </div>
                      <div class="flex items-center gap-3 shrink-0">
                        <span class="text-xs font-mono text-ivory/40">{{ ep.calls }} calls</span>
                        <span class="px-2 py-0.5 text-xs font-mono font-bold rounded-md border text-amber-400 bg-amber-500/10 border-amber-500/20">
                          {{ ep.avg_ms }} ms avg
                        </span>
                      </div>
                    </div>
                  </div>
                </div>

                <div v-else>
                  <div v-if="!gateMetrics?.top_queried_endpoints?.length" class="p-8 text-center text-xs text-ivory/40">
                    No high-query route anomalies recorded.
                  </div>
                  <div v-else class="space-y-2">
                    <div 
                      v-for="(ep, idx) in gateMetrics.top_queried_endpoints" 
                      :key="idx"
                      class="flex items-center justify-between p-2.5 rounded-xl bg-white/[0.02] border border-white/5 hover:border-white/10 transition-colors"
                    >
                      <div class="flex items-center gap-2.5 overflow-hidden">
                        <span class="px-2 py-0.5 text-[10px] font-mono font-bold rounded-md border shrink-0" :class="getMethodBadgeClass(ep.method)">
                          {{ ep.method }}
                        </span>
                        <span class="text-xs font-mono text-ivory/90 truncate">{{ ep.path }}</span>
                      </div>
                      <div class="flex items-center gap-3 shrink-0">
                        <span class="text-xs font-mono text-ivory/40">{{ ep.calls }} calls</span>
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
            
            <div class="mt-4 pt-3 border-t border-white/5 flex items-center justify-between text-xs text-ivory/40">
              <span>Thresholds: Latency Warning &gt; 150ms | Query Warning &gt; 25 queries</span>
              <span>Updated in real-time</span>
            </div>
          </div>
        </div>

        <!-- Telemetry Table Container -->
        <div class="rounded-2xl border border-admin/10 bg-black/20 backdrop-blur-sm overflow-hidden flex flex-col">
          <!-- Filters & Controls Bar -->
          <div class="border-b border-white/5 p-4 flex flex-col lg:flex-row gap-4 justify-between items-stretch lg:items-center bg-white/[0.02]">
            <div class="flex flex-wrap items-center gap-3">
              <!-- Client Type Filter -->
              <div class="flex items-center gap-2 rounded-xl bg-white/[0.03] border border-white/10 px-3 py-2">
                <FunnelIcon class="h-4 w-4 text-admin/60 shrink-0" />
                <select v-model="gateClientFilter" class="bg-transparent text-ivory/80 text-xs outline-none cursor-pointer">
                  <option value="all" class="bg-obsidian">All Clients</option>
                  <option value="web" class="bg-obsidian">Web Portal</option>
                  <option value="mobile" class="bg-obsidian">Mobile App</option>
                  <option value="webhook" class="bg-obsidian">Webhooks</option>
                </select>
              </div>

              <!-- Slow Toggle -->
              <button 
                @click="gateIsSlow = !gateIsSlow"
                class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-medium border transition-colors"
                :class="gateIsSlow ? 'bg-amber-500/20 border-amber-500/40 text-amber-300' : 'bg-white/[0.03] border-white/10 text-ivory/60 hover:text-ivory'"
              >
                <ClockIcon class="w-3.5 h-3.5" />
                Slow Only (&gt;150ms)
              </button>

              <!-- Budget Exceeded Toggle -->
              <button 
                @click="gateBudgetExceeded = !gateBudgetExceeded"
                class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-medium border transition-colors"
                :class="gateBudgetExceeded ? 'bg-rose-500/20 border-rose-500/40 text-rose-300' : 'bg-white/[0.03] border-white/10 text-ivory/60 hover:text-ivory'"
              >
                <CircleStackIcon class="w-3.5 h-3.5" />
                Budget Exceeded (&gt;25 DB)
              </button>
            </div>

            <!-- Right Controls: Search, Prune, Refresh -->
            <div class="flex items-center gap-2.5">
              <div class="flex items-center gap-2 rounded-xl bg-white/[0.03] border border-white/10 px-3 py-2 w-full sm:w-64">
                <MagnifyingGlassIcon class="h-4 w-4 text-ivory/40 shrink-0" />
                <input 
                  v-model="gateSearch" 
                  @input="onGateSearchInput"
                  type="text" 
                  placeholder="Filter path, IP, method..." 
                  class="bg-transparent text-ivory/80 text-xs outline-none w-full placeholder-ivory/30"
                />
              </div>

              <!-- Prune button -->
              <button 
                @click="showPruneModal = true"
                class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-medium text-rose-300 bg-rose-500/10 border border-rose-500/20 hover:bg-rose-500/20 transition-colors shrink-0"
                title="Prune telemetry data"
              >
                <TrashIcon class="w-4 h-4" />
                <span class="hidden sm:inline">Prune</span>
              </button>

              <!-- Refresh button -->
              <button 
                @click="fetchGateData"
                class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-medium text-admin-light bg-admin/10 border border-admin/20 hover:bg-admin/20 transition-colors shrink-0"
                title="Refresh gate data"
              >
                <ArrowPathIcon class="w-4 h-4" :class="{ 'animate-spin': gateLogsLoading || gateMetricsLoading }" />
                <span class="hidden sm:inline">Refresh</span>
              </button>
            </div>
          </div>

          <!-- Logs Table -->
          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="border-b border-white/5 bg-white/[0.02]">
                  <th class="p-3.5 text-[10px] font-bold uppercase tracking-widest text-ivory/40">Time</th>
                  <th class="p-3.5 text-[10px] font-bold uppercase tracking-widest text-ivory/40">Method &amp; Path</th>
                  <th class="p-3.5 text-[10px] font-bold uppercase tracking-widest text-ivory/40">Client</th>
                  <th class="p-3.5 text-[10px] font-bold uppercase tracking-widest text-ivory/40">Status</th>
                  <th class="p-3.5 text-[10px] font-bold uppercase tracking-widest text-ivory/40">Latency</th>
                  <th class="p-3.5 text-[10px] font-bold uppercase tracking-widest text-ivory/40">DB Queries</th>
                  <th class="p-3.5 text-[10px] font-bold uppercase tracking-widest text-ivory/40">Origin &amp; User</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-white/[0.03]">
                <tr v-if="gateLogsLoading">
                  <td colspan="7" class="p-12 text-center">
                    <div class="flex flex-col items-center gap-3">
                      <ArrowPathIcon class="w-6 h-6 animate-spin text-admin/50" />
                      <span class="text-sm text-ivory/50">Streaming gate telemetry...</span>
                    </div>
                  </td>
                </tr>
                <tr v-else-if="gateLogs.length === 0">
                  <td colspan="7" class="p-16 text-center">
                    <CpuChipIcon class="w-10 h-10 mx-auto mb-3 text-ivory/20" />
                    <p class="text-ivory/60 font-medium">No gate conversations recorded</p>
                    <p class="text-xs text-ivory/40 mt-1">Telemetry will appear here as users and devices communicate with the backend.</p>
                  </td>
                </tr>
                <tr v-for="log in gateLogs" :key="log.id" class="hover:bg-white/[0.03] transition-colors group">
                  <!-- Time -->
                  <td class="p-3.5 text-xs text-ivory/50 font-mono whitespace-nowrap">
                    {{ formatDateTime(log.created_at) }}
                  </td>

                  <!-- Method & Path -->
                  <td class="p-3.5">
                    <div class="flex items-center gap-2 max-w-md">
                      <span class="px-2 py-0.5 text-[10px] font-mono font-bold rounded-md border shrink-0" :class="getMethodBadgeClass(log.method)">
                        {{ log.method }}
                      </span>
                      <span class="text-xs font-mono text-ivory/90 truncate" :title="log.path">
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
                    <span class="px-2.5 py-0.5 text-xs font-mono font-bold rounded-lg border" :class="getStatusCodeClass(log.status_code)">
                      {{ log.status_code }}
                    </span>
                  </td>

                  <!-- Latency -->
                  <td class="p-3.5 whitespace-nowrap">
                    <div class="flex items-center gap-1.5">
                      <span class="text-xs font-mono" :class="log.is_slow ? 'text-amber-400 font-bold' : 'text-ivory/70'">
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
                        <span class="text-xs font-mono" :class="log.budget_exceeded ? 'text-rose-400 font-bold' : 'text-ivory/70'">
                          {{ log.query_count }} queries
                        </span>
                        <span v-if="log.budget_exceeded" class="px-1.5 py-0.2 rounded text-[9px] uppercase font-bold text-rose-300 bg-rose-500/20 border border-rose-500/30">
                          Budget &gt;25
                        </span>
                      </div>
                      <span class="text-[10px] font-mono text-ivory/40">
                        {{ log.query_duration_ms }} ms in DB
                      </span>
                    </div>
                  </td>

                  <!-- Origin & User -->
                  <td class="p-3.5 whitespace-nowrap">
                    <div class="flex flex-col">
                      <span class="text-xs font-medium text-ivory/80">
                        {{ log.user_name || 'Guest / Unauth' }}
                      </span>
                      <span class="text-[10px] font-mono text-ivory/40">
                        {{ log.ip_address || '0.0.0.0' }} &bull; {{ log.memory_kb }} KB
                      </span>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination Bar -->
          <div class="border-t border-white/5 p-4 flex flex-col sm:flex-row items-center justify-between gap-3 bg-white/[0.01]">
            <div class="text-xs text-ivory/50">
              Showing page <span class="font-mono text-ivory/80">{{ gatePagination.current_page }}</span> of <span class="font-mono text-ivory/80">{{ gatePagination.last_page }}</span>
              (<span class="font-mono text-ivory/80">{{ gatePagination.total }}</span> total conversations)
            </div>
            <div class="flex items-center gap-2">
              <button 
                @click="fetchGateLogs(gatePagination.current_page - 1)"
                :disabled="gatePagination.current_page <= 1 || gateLogsLoading"
                class="flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs border border-white/10 text-ivory/70 hover:bg-white/5 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
              >
                <ChevronLeftIcon class="w-3.5 h-3.5" />
                Previous
              </button>
              <button 
                @click="fetchGateLogs(gatePagination.current_page + 1)"
                :disabled="gatePagination.current_page >= gatePagination.last_page || gateLogsLoading"
                class="flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs border border-white/10 text-ivory/70 hover:bg-white/5 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
              >
                Next
                <ChevronRightIcon class="w-3.5 h-3.5" />
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- ======================================================== -->
      <!-- TAB 2: AUDIT LOGS (ADMIN ACTIONS)                        -->
      <!-- ======================================================== -->
      <div v-else class="space-y-6">
        <!-- Audit Table Container -->
        <div class="rounded-2xl border border-admin/10 bg-black/20 backdrop-blur-sm overflow-hidden flex flex-col">
          <!-- Filters Bar -->
          <div class="border-b border-white/5 p-5 flex flex-col sm:flex-row gap-4 justify-between items-center bg-white/[0.02]">
            <div class="flex items-center gap-3">
              <div class="flex items-center gap-2 rounded-xl bg-white/[0.03] border border-white/10 px-3 py-2">
                <FunnelIcon class="h-4 w-4 text-admin/60 shrink-0" />
                <select v-model="auditFilterType" class="bg-transparent text-ivory/80 text-sm outline-none cursor-pointer">
                  <option value="all" class="bg-obsidian">All Actions</option>
                  <option value="login" class="bg-obsidian">Logins</option>
                  <option value="appointment" class="bg-obsidian">Appointments</option>
                  <option value="payment" class="bg-obsidian">Payments</option>
                  <option value="user" class="bg-obsidian">Users</option>
                  <option value="barber" class="bg-obsidian">Barbers</option>
                  <option value="service" class="bg-obsidian">Services</option>
                  <option value="blog" class="bg-obsidian">Blog</option>
                  <option value="system" class="bg-obsidian">System</option>
                </select>
              </div>
            </div>
            <div class="flex items-center gap-2 rounded-xl bg-white/[0.03] border border-white/10 px-3 py-2 w-full sm:w-72">
              <MagnifyingGlassIcon class="h-4 w-4 text-ivory/40 shrink-0" />
              <input 
                v-model="auditSearch" 
                type="text" 
                placeholder="Search audit logs..." 
                class="bg-transparent text-ivory/80 text-sm outline-none w-full placeholder-ivory/30"
              />
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="border-b border-white/5 bg-white/[0.02]">
                  <th class="p-4 text-[10px] font-bold uppercase tracking-widest text-ivory/40">Time</th>
                  <th class="p-4 text-[10px] font-bold uppercase tracking-widest text-ivory/40">User</th>
                  <th class="p-4 text-[10px] font-bold uppercase tracking-widest text-ivory/40">Action</th>
                  <th class="p-4 text-[10px] font-bold uppercase tracking-widest text-ivory/40">Entity</th>
                  <th class="p-4 text-[10px] font-bold uppercase tracking-widest text-ivory/40">IP Address</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-white/[0.03]">
                <tr v-if="auditLoading">
                  <td colspan="5" class="p-12 text-center">
                    <div class="flex flex-col items-center gap-3">
                      <ArrowPathIcon class="w-6 h-6 animate-spin text-admin/50" />
                      <span class="text-sm text-ivory/50">Loading audit logs...</span>
                    </div>
                  </td>
                </tr>
                <tr v-else-if="filteredAuditLogs.length === 0">
                  <td colspan="5" class="p-16 text-center">
                    <ClipboardDocumentListIcon class="w-10 h-10 mx-auto mb-3 text-ivory/20" />
                    <p class="text-ivory/60 font-medium">No logs found</p>
                    <p class="text-sm text-ivory/40 mt-1">Try adjusting your filters or search query.</p>
                  </td>
                </tr>
                <tr v-for="log in filteredAuditLogs" :key="log.id" class="hover:bg-white/[0.03] transition-colors group">
                  <td class="p-4 text-sm text-ivory/50 whitespace-nowrap">
                    {{ new Date(log.created_at).toLocaleString() }}
                  </td>
                  <td class="p-4">
                    <div class="flex items-center gap-3">
                      <div class="h-8 w-8 rounded-lg bg-admin/10 border border-admin/20 flex items-center justify-center text-admin text-xs font-bold shrink-0">
                        {{ (log.user_name || '?').charAt(0).toUpperCase() }}
                      </div>
                      <div class="flex flex-col">
                        <span class="text-sm font-medium text-theme-text">{{ log.user_name || 'System' }}</span>
                        <span class="text-[10px] text-ivory/40 uppercase tracking-wider">{{ log.user_role || 'N/A' }}</span>
                      </div>
                    </div>
                  </td>
                  <td class="p-4">
                    <div class="flex items-center gap-2">
                      <div class="p-1.5 rounded-lg shrink-0 border" :class="getIconColorForAction(log.action)">
                        <component :is="getIconForAction(log.action)" class="w-3.5 h-3.5" />
                      </div>
                      <span class="text-sm font-medium text-theme-text capitalize">{{ log.action.replace(/_/g, ' ') }}</span>
                    </div>
                  </td>
                  <td class="p-4">
                    <span class="text-xs uppercase tracking-wider bg-white/[0.03] border border-white/10 px-2.5 py-1 rounded-lg text-ivory/50 font-medium">
                      {{ log.entity_type }} <span v-if="log.entity_id" class="text-admin/60">#{{ log.entity_id }}</span>
                    </span>
                  </td>
                  <td class="p-4 text-sm font-mono text-ivory/40">
                    {{ log.ip_address || 'N/A' }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Prune Telemetry Confirmation Modal -->
      <div 
        v-if="showPruneModal" 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
      >
        <div class="relative w-full max-w-md rounded-2xl border border-white/10 bg-obsidian p-6 shadow-2xl space-y-4">
          <div class="flex items-center gap-3">
            <div class="p-2.5 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400">
              <TrashIcon class="w-5 h-5" />
            </div>
            <div>
              <h3 class="text-base font-bold text-theme-text">Prune Gate Telemetry</h3>
              <p class="text-xs text-ivory/50">Clean up historical API and DB conversation logs.</p>
            </div>
          </div>

          <p class="text-xs text-ivory/70 leading-relaxed">
            Select the retention period. Logs older than the chosen number of days will be permanently removed from the database to keep storage lean and queries fast.
          </p>

          <div class="space-y-1.5">
            <label class="text-xs font-semibold text-ivory/80">Retain Logs For:</label>
            <select v-model="pruneDays" class="w-full rounded-xl bg-white/[0.04] border border-white/10 p-2.5 text-xs text-theme-text outline-none focus:border-admin/40">
              <option :value="1" class="bg-obsidian">Older than 24 hours</option>
              <option :value="7" class="bg-obsidian">Older than 7 days (Recommended)</option>
              <option :value="14" class="bg-obsidian">Older than 14 days</option>
              <option :value="30" class="bg-obsidian">Older than 30 days</option>
            </select>
          </div>

          <div class="flex items-center justify-end gap-2 pt-3 border-t border-white/10">
            <button 
              @click="showPruneModal = false"
              class="px-4 py-2 rounded-xl text-xs font-medium text-ivory/60 hover:text-ivory hover:bg-white/5 transition-colors"
            >
              Cancel
            </button>
            <button 
              @click="handlePrune"
              :disabled="pruning"
              class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-black bg-rose-500 hover:bg-rose-400 transition-colors disabled:opacity-50"
            >
              <ArrowPathIcon v-if="pruning" class="w-3.5 h-3.5 animate-spin" />
              <TrashIcon v-else class="w-3.5 h-3.5" />
              <span>Confirm &amp; Prune</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
