<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import AdminLayout from '@/portals/admin/layouts/AdminLayout.vue'
import { adminApi } from '@/shared/api/old_adminApi'
import { useToast } from '@/core/composables/useToast'
import {
  BugAntIcon,
  ExclamationCircleIcon,
  ExclamationTriangleIcon,
  CheckCircleIcon,
  XCircleIcon,
  ArrowPathIcon,
  MagnifyingGlassIcon,
  FunnelIcon,
  EyeIcon,
  XMarkIcon,
  FingerPrintIcon,
  ClockIcon,
  TagIcon,
  DocumentMagnifyingGlassIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
  InformationCircleIcon,
  ShieldExclamationIcon,
  LightBulbIcon,
  CodeBracketIcon,
  ArrowTopRightOnSquareIcon,
} from '@heroicons/vue/24/outline'

const toast = useToast()

// ── State ──
const errors = ref([])
const loading = ref(true)
const totalPages = ref(1)
const currentPage = ref(1)
const search = ref('')
const statusFilter = ref('')
const severityFilter = ref('')
const categoryFilter = ref('')

// Drawer
const drawerOpen = ref(false)
const drawerLoading = ref(false)
const selectedGroup = ref(null)
const selectedEvents = ref([])
const statusUpdating = ref(false)

// Trace
const traceId = ref('')
const traceLoading = ref(false)
const traceResults = ref(null)
const showTracePanel = ref(false)

// ── Fetch Errors ──
const fetchErrors = async () => {
  loading.value = true
  try {
    const params = { page: currentPage.value }
    if (search.value) params.q = search.value
    if (statusFilter.value) params.status = statusFilter.value
    if (severityFilter.value) params.severity = severityFilter.value
    if (categoryFilter.value) params.category = categoryFilter.value

    const res = await adminApi.systemErrors(params)
    const data = res.data?.data || res.data || {}
    errors.value = data.data || []
    totalPages.value = data.last_page || 1
    currentPage.value = data.current_page || 1
  } catch (err) {
    toast.error('Failed to load error groups')
    console.error(err)
  } finally {
    loading.value = false
  }
}

// ── Open Error Detail Drawer ──
const openErrorDetail = async (errorGroup) => {
  drawerOpen.value = true
  drawerLoading.value = true
  selectedGroup.value = errorGroup
  selectedEvents.value = []
  try {
    const res = await adminApi.systemErrorDetail(errorGroup.id)
    const data = res.data?.data || {}
    selectedGroup.value = data.group || errorGroup
    selectedEvents.value = data.events || []
  } catch (err) {
    toast.error('Failed to load error details')
    console.error(err)
  } finally {
    drawerLoading.value = false
  }
}

// ── Update Error Status ──
const updateStatus = async (newStatus) => {
  if (!selectedGroup.value) return
  statusUpdating.value = true
  try {
    const payload = { status: newStatus }
    await adminApi.updateSystemError(selectedGroup.value.id, payload)
    selectedGroup.value.status = newStatus
    toast.success(`Status changed to: ${newStatus}`)
    await fetchErrors() // Refresh list
  } catch (err) {
    toast.error('Status update failed')
    console.error(err)
  } finally {
    statusUpdating.value = false
  }
}

// ── Trace Request ──
const executeTrace = async () => {
  if (!traceId.value.trim()) {
    toast.error('Enter a request ID to trace')
    return
  }
  traceLoading.value = true
  traceResults.value = null
  showTracePanel.value = true
  try {
    const res = await adminApi.traceRequest(traceId.value.trim())
    traceResults.value = res.data?.data || null
  } catch (err) {
    toast.error('Trace lookup failed')
    console.error(err)
  } finally {
    traceLoading.value = false
  }
}

// ── Helpers ──
const severityColor = (sev) => {
  const map = {
    critical: 'bg-red-500/10 text-red-400 border-red-500/20',
    error: 'bg-orange-500/10 text-orange-400 border-orange-500/20',
    warning: 'bg-amber-500/10 text-amber-400 border-amber-500/20',
    info: 'bg-blue-500/10 text-blue-400 border-blue-500/20',
  }
  return map[sev] || 'bg-zinc-500/10 text-zinc-400 border-zinc-500/20'
}

const statusColor = (status) => {
  const map = {
    open: 'bg-red-500/10 text-red-400 border-red-500/20',
    acknowledged: 'bg-amber-500/10 text-amber-400 border-amber-500/20',
    resolved: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
    ignored: 'bg-zinc-500/10 text-zinc-500 border-zinc-500/20',
  }
  return map[status] || 'bg-zinc-500/10 text-zinc-400 border-zinc-500/20'
}

const formatTime = (iso) => {
  if (!iso) return '—'
  return new Date(iso).toLocaleString('en-US', { dateStyle: 'medium', timeStyle: 'short' })
}

const timeAgo = (iso) => {
  if (!iso) return ''
  const diff = Date.now() - new Date(iso).getTime()
  const mins = Math.floor(diff / 60000)
  if (mins < 1) return 'just now'
  if (mins < 60) return `${mins}m ago`
  const hours = Math.floor(mins / 60)
  if (hours < 24) return `${hours}h ago`
  const days = Math.floor(hours / 24)
  return `${days}d ago`
}

// ── Watchers & Lifecycle ──
let searchTimeout = null
watch(search, () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    currentPage.value = 1
    fetchErrors()
  }, 400)
})

watch([statusFilter, severityFilter, categoryFilter], () => {
  currentPage.value = 1
  fetchErrors()
})

const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
    fetchErrors()
  }
}

onMounted(fetchErrors)
</script>

<template>
  <AdminLayout>
    <section class="space-y-6 animate-fade-in">
      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-white flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-red-500/20 to-orange-500/20 border border-red-500/20 flex items-center justify-center">
              <BugAntIcon class="w-5 h-5 text-red-400" />
            </div>
            System Errors & Incidents
          </h1>
          <p class="text-sm text-zinc-400 mt-1">Monitor, investigate, and resolve system errors</p>
        </div>
        <button
          @click="fetchErrors"
          :disabled="loading"
          class="flex items-center gap-2 px-4 py-2 rounded-lg bg-white/[0.05] border border-white/[0.08] text-zinc-300 hover:bg-white/[0.08] transition-all text-xs font-medium disabled:opacity-50"
        >
          <ArrowPathIcon class="w-4 h-4" :class="{ 'animate-spin': loading }" />
          Refresh
        </button>
      </div>

      <!-- Request ID Trace Tool -->
      <div class="rounded-2xl bg-white/[0.02] border border-white/[0.06] p-5">
        <div class="flex items-center gap-3 mb-3">
          <FingerPrintIcon class="w-5 h-5 text-violet-400" />
          <h2 class="text-sm font-semibold text-white">Request ID Trace</h2>
        </div>
        <div class="flex gap-3">
          <input
            v-model="traceId"
            type="text"
            placeholder="Paste a request ID to trace (e.g. cc-abc123def456)"
            class="flex-1 px-4 py-2.5 rounded-lg bg-white/[0.04] border border-white/[0.08] text-white text-sm placeholder-zinc-500 font-mono focus:outline-none focus:border-violet-500/40 transition-colors"
            @keyup.enter="executeTrace"
          />
          <button
            @click="executeTrace"
            :disabled="traceLoading || !traceId.trim()"
            class="flex items-center gap-2 px-5 py-2.5 rounded-lg bg-violet-600/20 border border-violet-500/30 text-violet-300 text-sm font-medium hover:bg-violet-600/30 transition-all disabled:opacity-50"
          >
            <DocumentMagnifyingGlassIcon class="w-4 h-4" />
            Trace
          </button>
        </div>

        <!-- Trace Results -->
        <div v-if="showTracePanel" class="mt-4">
          <div v-if="traceLoading" class="flex items-center gap-3 py-4 justify-center">
            <ArrowPathIcon class="w-5 h-5 text-violet-400 animate-spin" />
            <p class="text-sm text-zinc-400">Searching trace…</p>
          </div>
          <div v-else-if="traceResults" class="space-y-3">
            <div class="flex items-center justify-between">
              <p class="text-xs text-zinc-400">
                Request: <span class="font-mono text-white">{{ traceResults.request_id }}</span>
              </p>
              <button @click="showTracePanel = false" class="text-xs text-zinc-500 hover:text-white transition-colors">
                Close
              </button>
            </div>

            <!-- Events -->
            <div v-if="traceResults.events?.length" class="space-y-2">
              <p class="text-xs text-zinc-500 font-medium">Error Events ({{ traceResults.events.length }})</p>
              <div
                v-for="ev in traceResults.events"
                :key="ev.id"
                class="rounded-lg bg-red-500/5 border border-red-500/10 p-3"
              >
                <div class="flex items-center gap-2 mb-1">
                  <span :class="['text-[10px] px-1.5 py-0.5 rounded-full border font-medium', severityColor(ev.severity)]">
                    {{ ev.severity }}
                  </span>
                  <span class="text-xs text-zinc-400">{{ ev.http_status }}</span>
                  <span class="text-xs text-zinc-500 ml-auto">{{ formatTime(ev.occurred_at) }}</span>
                </div>
                <p class="text-xs text-zinc-300 font-mono">{{ ev.message }}</p>
                <p v-if="ev.url" class="text-[10px] text-zinc-500 mt-1">{{ ev.url }}</p>
              </div>
            </div>

            <!-- Request Logs -->
            <div v-if="traceResults.request_logs?.length" class="space-y-2">
              <p class="text-xs text-zinc-500 font-medium">API Request Logs ({{ traceResults.request_logs.length }})</p>
              <div
                v-for="log in traceResults.request_logs"
                :key="log.id"
                class="rounded-lg bg-white/[0.03] border border-white/[0.06] p-3"
              >
                <div class="flex items-center gap-2 mb-1">
                  <span class="text-xs font-mono text-blue-400">{{ log.method }}</span>
                  <span class="text-xs text-zinc-300 font-mono truncate flex-1">{{ log.url }}</span>
                  <span class="text-xs text-zinc-500">{{ log.status_code }}</span>
                  <span class="text-xs text-zinc-500">{{ log.duration_ms }}ms</span>
                </div>
              </div>
            </div>

            <p v-if="!traceResults.events?.length && !traceResults.request_logs?.length" class="text-sm text-zinc-500 text-center py-4">
              No trace data found for this request ID
            </p>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="flex flex-wrap gap-3 items-center">
        <div class="relative flex-1 min-w-[200px]">
          <MagnifyingGlassIcon class="w-4 h-4 text-zinc-500 absolute left-3 top-1/2 -translate-y-1/2" />
          <input
            v-model="search"
            type="text"
            placeholder="Search error codes, messages, exceptions…"
            class="w-full pl-10 pr-4 py-2.5 rounded-lg bg-white/[0.04] border border-white/[0.08] text-white text-sm placeholder-zinc-500 focus:outline-none focus:border-white/[0.15] transition-colors"
          />
        </div>
        <select
          v-model="statusFilter"
          class="px-3 py-2.5 rounded-lg bg-white/[0.04] border border-white/[0.08] text-zinc-300 text-sm focus:outline-none appearance-none cursor-pointer"
        >
          <option value="">All Status</option>
          <option value="open">Open</option>
          <option value="acknowledged">Acknowledged</option>
          <option value="resolved">Resolved</option>
          <option value="ignored">Ignored</option>
        </select>
        <select
          v-model="severityFilter"
          class="px-3 py-2.5 rounded-lg bg-white/[0.04] border border-white/[0.08] text-zinc-300 text-sm focus:outline-none appearance-none cursor-pointer"
        >
          <option value="">All Severity</option>
          <option value="critical">Critical</option>
          <option value="error">Error</option>
          <option value="warning">Warning</option>
          <option value="info">Info</option>
        </select>
        <select
          v-model="categoryFilter"
          class="px-3 py-2.5 rounded-lg bg-white/[0.04] border border-white/[0.08] text-zinc-300 text-sm focus:outline-none appearance-none cursor-pointer"
        >
          <option value="">All Categories</option>
          <option value="auth">Auth</option>
          <option value="booking">Booking</option>
          <option value="payment">Payment</option>
          <option value="upload">Upload</option>
          <option value="external">External</option>
          <option value="system">System</option>
        </select>
      </div>

      <!-- Error Groups Table -->
      <div class="rounded-2xl bg-white/[0.02] border border-white/[0.06] overflow-hidden">
        <div class="px-5 py-4 border-b border-white/[0.06] flex items-center gap-3">
          <ExclamationCircleIcon class="w-5 h-5 text-red-400" />
          <h2 class="text-sm font-semibold text-white">Error Groups</h2>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="p-8 flex justify-center">
          <ArrowPathIcon class="w-6 h-6 text-zinc-400 animate-spin" />
        </div>

        <!-- Empty State -->
        <div v-else-if="!errors.length" class="p-12 text-center">
          <CheckCircleIcon class="w-12 h-12 text-emerald-500/40 mx-auto mb-3" />
          <p class="text-zinc-400 text-sm">No error groups found</p>
          <p class="text-zinc-500 text-xs mt-1">All systems are running clean</p>
        </div>

        <!-- Error List -->
        <div v-else class="divide-y divide-white/[0.04]">
          <div
            v-for="err in errors"
            :key="err.id"
            @click="openErrorDetail(err)"
            class="px-5 py-4 flex items-center gap-4 hover:bg-white/[0.02] transition-colors cursor-pointer group"
          >
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 mb-1.5">
                <span class="text-sm text-white font-mono font-medium">{{ err.error_code }}</span>
                <span :class="['text-[10px] px-1.5 py-0.5 rounded-full border font-medium', severityColor(err.severity)]">
                  {{ err.severity }}
                </span>
                <span :class="['text-[10px] px-1.5 py-0.5 rounded-full border font-medium', statusColor(err.status)]">
                  {{ err.status }}
                </span>
              </div>
              <p class="text-xs text-zinc-400 truncate">{{ err.sample_message }}</p>
              <div class="flex items-center gap-3 mt-1.5">
                <span v-if="err.exception_class" class="text-[10px] text-zinc-500 font-mono">{{ err.exception_class }}</span>
                <span class="text-[10px] text-zinc-600">·</span>
                <span class="text-[10px] text-zinc-500">{{ err.occurrences }} occurrences</span>
                <span class="text-[10px] text-zinc-600">·</span>
                <span class="text-[10px] text-zinc-500">Last seen {{ timeAgo(err.last_seen_at) }}</span>
              </div>
            </div>
            <EyeIcon class="w-4 h-4 text-zinc-500 opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0" />
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="totalPages > 1" class="px-5 py-3 border-t border-white/[0.06] flex items-center justify-between">
          <p class="text-xs text-zinc-500">Page {{ currentPage }} of {{ totalPages }}</p>
          <div class="flex items-center gap-2">
            <button
              @click="goToPage(currentPage - 1)"
              :disabled="currentPage <= 1"
              class="p-1.5 rounded-lg hover:bg-white/[0.06] text-zinc-400 transition-colors disabled:opacity-30"
            >
              <ChevronLeftIcon class="w-4 h-4" />
            </button>
            <button
              @click="goToPage(currentPage + 1)"
              :disabled="currentPage >= totalPages"
              class="p-1.5 rounded-lg hover:bg-white/[0.06] text-zinc-400 transition-colors disabled:opacity-30"
            >
              <ChevronRightIcon class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>

      <!-- Error Detail Drawer -->
      <Teleport to="body">
        <div v-if="drawerOpen" class="fixed inset-0 z-50 flex justify-end">
          <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="drawerOpen = false"></div>
          <div class="relative w-full max-w-lg bg-zinc-900 border-l border-white/[0.08] overflow-y-auto shadow-2xl">
            <!-- Drawer Header -->
            <div class="sticky top-0 z-10 bg-zinc-900/95 backdrop-blur-sm px-6 py-4 border-b border-white/[0.06] flex items-center justify-between">
              <h3 class="text-lg font-semibold text-white">Error Details</h3>
              <button @click="drawerOpen = false" class="p-1.5 rounded-lg hover:bg-white/[0.06] transition-colors">
                <XMarkIcon class="w-5 h-5 text-zinc-400" />
              </button>
            </div>

            <div v-if="drawerLoading" class="p-8 flex justify-center">
              <ArrowPathIcon class="w-6 h-6 text-zinc-400 animate-spin" />
            </div>

            <div v-else-if="selectedGroup" class="p-6 space-y-6">
              <!-- Error Identity -->
              <div>
                <p class="text-lg font-mono text-white font-semibold mb-2">{{ selectedGroup.error_code }}</p>
                <div class="flex flex-wrap gap-2 mb-3">
                  <span :class="['text-xs px-2 py-0.5 rounded-full border font-medium', severityColor(selectedGroup.severity)]">
                    {{ selectedGroup.severity }}
                  </span>
                  <span :class="['text-xs px-2 py-0.5 rounded-full border font-medium', statusColor(selectedGroup.status)]">
                    {{ selectedGroup.status }}
                  </span>
                  <span v-if="selectedGroup.category" class="text-xs px-2 py-0.5 rounded-full bg-zinc-500/10 text-zinc-400 border border-zinc-500/20">
                    {{ selectedGroup.category }}
                  </span>
                </div>
                <p class="text-sm text-zinc-300">{{ selectedGroup.sample_message }}</p>
              </div>

              <!-- Meta -->
              <div class="grid grid-cols-2 gap-3">
                <div class="rounded-lg bg-white/[0.03] border border-white/[0.06] p-3">
                  <p class="text-[10px] text-zinc-500 uppercase mb-1">Occurrences</p>
                  <p class="text-lg font-bold text-white">{{ selectedGroup.occurrences }}</p>
                </div>
                <div class="rounded-lg bg-white/[0.03] border border-white/[0.06] p-3">
                  <p class="text-[10px] text-zinc-500 uppercase mb-1">Exception</p>
                  <p class="text-xs text-zinc-300 font-mono truncate">{{ selectedGroup.exception_class || '—' }}</p>
                </div>
                <div class="rounded-lg bg-white/[0.03] border border-white/[0.06] p-3">
                  <p class="text-[10px] text-zinc-500 uppercase mb-1">First Seen</p>
                  <p class="text-xs text-zinc-300">{{ formatTime(selectedGroup.first_seen_at) }}</p>
                </div>
                <div class="rounded-lg bg-white/[0.03] border border-white/[0.06] p-3">
                  <p class="text-[10px] text-zinc-500 uppercase mb-1">Last Seen</p>
                  <p class="text-xs text-zinc-300">{{ formatTime(selectedGroup.last_seen_at) }}</p>
                </div>
              </div>

              <!-- Operator Hint -->
              <div v-if="selectedGroup.operator_hint" class="rounded-xl bg-amber-500/5 border border-amber-500/15 p-4">
                <div class="flex items-center gap-2 mb-2">
                  <LightBulbIcon class="w-4 h-4 text-amber-400" />
                  <p class="text-xs font-semibold text-amber-400">Operator Hint</p>
                </div>
                <p class="text-xs text-zinc-300">{{ selectedGroup.operator_hint }}</p>
              </div>

              <!-- Resolution Note -->
              <div v-if="selectedGroup.resolution_note" class="rounded-xl bg-emerald-500/5 border border-emerald-500/15 p-4">
                <div class="flex items-center gap-2 mb-2">
                  <InformationCircleIcon class="w-4 h-4 text-emerald-400" />
                  <p class="text-xs font-semibold text-emerald-400">Resolution Note</p>
                </div>
                <p class="text-xs text-zinc-300">{{ selectedGroup.resolution_note }}</p>
              </div>

              <!-- Status Updater -->
              <div>
                <p class="text-xs text-zinc-500 font-medium mb-2">Update Status</p>
                <div class="flex flex-wrap gap-2">
                  <button
                    v-for="s in ['open', 'acknowledged', 'resolved', 'ignored']"
                    :key="s"
                    @click="updateStatus(s)"
                    :disabled="statusUpdating || selectedGroup.status === s"
                    :class="[
                      'px-3 py-1.5 rounded-lg text-xs font-medium border transition-all disabled:opacity-40',
                      selectedGroup.status === s
                        ? 'bg-white/[0.08] border-white/[0.15] text-white'
                        : 'bg-white/[0.03] border-white/[0.06] text-zinc-400 hover:bg-white/[0.06]'
                    ]"
                  >
                    {{ s.charAt(0).toUpperCase() + s.slice(1) }}
                  </button>
                </div>
              </div>

              <!-- Recent Events -->
              <div>
                <p class="text-xs text-zinc-500 font-medium mb-3">Recent Events ({{ selectedEvents.length }})</p>
                <div v-if="!selectedEvents.length" class="text-xs text-zinc-500 text-center py-4">No events recorded</div>
                <div v-else class="space-y-2 max-h-[400px] overflow-y-auto">
                  <div
                    v-for="ev in selectedEvents"
                    :key="ev.id"
                    class="rounded-lg bg-white/[0.02] border border-white/[0.05] p-3"
                  >
                    <div class="flex items-center gap-2 mb-1.5">
                      <span :class="['text-[10px] px-1.5 py-0.5 rounded-full border font-medium', severityColor(ev.severity)]">
                        {{ ev.severity }}
                      </span>
                      <span class="text-[10px] text-zinc-500">HTTP {{ ev.http_status || '—' }}</span>
                      <span class="text-[10px] text-zinc-600 ml-auto">{{ formatTime(ev.occurred_at) }}</span>
                    </div>
                    <p class="text-xs text-zinc-300 mb-1">{{ ev.message }}</p>
                    <div v-if="ev.url" class="text-[10px] text-zinc-500 font-mono truncate mb-1">{{ ev.url }}</div>
                    <div v-if="ev.request_id" class="text-[10px] text-violet-400/70 font-mono">
                      Request: {{ ev.request_id }}
                    </div>
                    <!-- Stack Trace -->
                    <details v-if="ev.stack_trace" class="mt-2">
                      <summary class="text-[10px] text-zinc-500 cursor-pointer hover:text-zinc-300 transition-colors flex items-center gap-1">
                        <CodeBracketIcon class="w-3 h-3" />
                        Stack Trace
                      </summary>
                      <pre class="mt-1.5 p-2 rounded bg-black/30 text-[9px] text-zinc-400 font-mono overflow-x-auto max-h-[200px] overflow-y-auto whitespace-pre-wrap break-words">{{ ev.stack_trace }}</pre>
                    </details>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </Teleport>
    </section>
  </AdminLayout>
</template>
