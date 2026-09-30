<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import AdminLayout from '@/portals/admin/layouts/AdminLayout.vue'
import { adminApi } from '@/shared/api/old_adminApi'
import { useToast } from '@/core/composables/useToast'
import {
  HeartIcon,
  ServerStackIcon,
  CircleStackIcon,
  CpuChipIcon,
  FolderIcon,
  EnvelopeIcon,
  CreditCardIcon,
  QueueListIcon,
  ShieldCheckIcon,
  ArrowPathIcon,
  CheckCircleIcon,
  ExclamationTriangleIcon,
  XCircleIcon,
  ClockIcon,
  SignalIcon,
  BoltIcon,
  BeakerIcon,
  PlayIcon,
  InformationCircleIcon,
  ExclamationCircleIcon,
  WrenchScrewdriverIcon,
} from '@heroicons/vue/24/outline'

const toast = useToast()

// ── State ──
const overview = ref(null)
const healthDetail = ref(null)
const overviewLoading = ref(true)
const detailLoading = ref(true)
const selfTestRunning = ref(false)
const selfTestResults = ref(null)
const autoRefresh = ref(false)
const lastRefreshed = ref(null)
let refreshInterval = null

// ── Fetch Data ──
const fetchOverview = async () => {
  try {
    const res = await adminApi.systemOverview()
    overview.value = res.data?.data || null
  } catch (err) {
    console.error('System overview failed:', err)
  } finally {
    overviewLoading.value = false
  }
}

const fetchHealthDetail = async () => {
  try {
    const res = await adminApi.healthDetail()
    healthDetail.value = res.data?.data || null
  } catch (err) {
    console.error('Health detail failed:', err)
  } finally {
    detailLoading.value = false
  }
}

const refreshAll = async () => {
  overviewLoading.value = true
  detailLoading.value = true
  await Promise.all([fetchOverview(), fetchHealthDetail()])
  lastRefreshed.value = new Date()
  toast.success('Health data refreshed')
}

// ── Self-Test ──
const runDiagnostic = async (type = 'all') => {
  selfTestRunning.value = true
  selfTestResults.value = null
  try {
    const res = await adminApi.runSelfTest(type)
    selfTestResults.value = res.data?.data || null
    if (selfTestResults.value?.all_passed) {
      toast.success('All diagnostics passed')
    } else {
      toast.error('Some diagnostics failed — review results below')
    }
  } catch (err) {
    toast.error('Self-test request failed')
    console.error(err)
  } finally {
    selfTestRunning.value = false
  }
}

// ── Auto-Refresh ──
const toggleAutoRefresh = () => {
  autoRefresh.value = !autoRefresh.value
  if (autoRefresh.value) {
    refreshInterval = setInterval(refreshAll, 30000) // 30s
    toast.success('Auto-refresh enabled (30s)')
  } else {
    clearInterval(refreshInterval)
    refreshInterval = null
    toast.info('Auto-refresh disabled')
  }
}

// ── Helpers ──
const statusColor = (status) => {
  const map = {
    optimal: 'text-emerald-400',
    healthy: 'text-emerald-400',
    operational: 'text-emerald-400',
    connected: 'text-emerald-400',
    configured: 'text-emerald-400',
    attention: 'text-amber-400',
    warning: 'text-amber-400',
    degraded: 'text-orange-400',
    sandbox_or_missing: 'text-amber-400',
    not_configured: 'text-zinc-500',
    error: 'text-red-400',
    disconnected: 'text-red-400',
  }
  return map[status] || 'text-zinc-400'
}

const statusBg = (status) => {
  const map = {
    optimal: 'bg-emerald-500/10 border-emerald-500/20',
    healthy: 'bg-emerald-500/10 border-emerald-500/20',
    operational: 'bg-emerald-500/10 border-emerald-500/20',
    connected: 'bg-emerald-500/10 border-emerald-500/20',
    configured: 'bg-emerald-500/10 border-emerald-500/20',
    attention: 'bg-amber-500/10 border-amber-500/20',
    warning: 'bg-amber-500/10 border-amber-500/20',
    degraded: 'bg-orange-500/10 border-orange-500/20',
    sandbox_or_missing: 'bg-amber-500/10 border-amber-500/20',
    not_configured: 'bg-zinc-500/10 border-zinc-500/20',
    error: 'bg-red-500/10 border-red-500/20',
    disconnected: 'bg-red-500/10 border-red-500/20',
  }
  return map[status] || 'bg-zinc-500/10 border-zinc-500/20'
}

const statusIcon = (status) => {
  if (['optimal', 'healthy', 'operational', 'connected', 'configured'].includes(status)) return CheckCircleIcon
  if (['attention', 'warning', 'degraded', 'sandbox_or_missing'].includes(status)) return ExclamationTriangleIcon
  if (['error', 'disconnected'].includes(status)) return XCircleIcon
  return InformationCircleIcon
}

const formatTime = (iso) => {
  if (!iso) return '—'
  return new Date(iso).toLocaleString('en-US', { dateStyle: 'medium', timeStyle: 'short' })
}

// ── Overview KPI Cards ──
const overviewCards = computed(() => {
  const s = overview.value?.summary || {}
  return [
    { label: 'Database', value: s.database_connected ? 'Connected' : 'Down', status: s.database_connected ? 'connected' : 'disconnected', icon: CircleStackIcon },
    { label: 'Open Errors', value: s.open_groups_count ?? '—', status: (s.critical_groups_count > 0) ? 'error' : (s.open_groups_count > 5 ? 'attention' : 'operational'), icon: ExclamationCircleIcon },
    { label: 'Critical Issues', value: s.critical_groups_count ?? '—', status: s.critical_groups_count > 0 ? 'error' : 'operational', icon: ShieldCheckIcon },
    { label: '5xx Errors (24h)', value: s.errors_5xx_count ?? '—', status: (s.errors_5xx_count > 10) ? 'warning' : 'operational', icon: BoltIcon },
    { label: 'Events (24h)', value: s.events_24h_count ?? '—', status: 'operational', icon: SignalIcon },
    { label: 'Backups', value: s.backups_count ?? '—', status: (s.backups_count ?? 0) === 0 ? 'warning' : 'operational', icon: FolderIcon },
    { label: 'Active Sessions', value: s.active_sessions_count ?? '—', status: 'operational', icon: QueueListIcon },
    { label: 'Last Backup', value: s.latest_backup_time ? formatTime(s.latest_backup_time) : 'None', status: s.latest_backup_time ? 'operational' : 'warning', icon: ClockIcon },
  ]
})

// ── Health Detail Cards ──
const healthCards = computed(() => {
  const c = healthDetail.value?.checks || {}
  const cards = []

  if (c.database) {
    cards.push({
      title: 'Database',
      icon: CircleStackIcon,
      status: c.database.status,
      items: [
        { label: 'Connection', value: c.database.connection },
        { label: 'Latency', value: `${c.database.latency_ms}ms` },
        { label: 'Tables', value: c.database.tables_count },
        ...(c.database.error ? [{ label: 'Error', value: c.database.error, warn: true }] : []),
      ],
    })
  }

  if (c.cache) {
    cards.push({
      title: 'Cache',
      icon: CpuChipIcon,
      status: c.cache.status,
      items: [
        { label: 'Driver', value: c.cache.driver },
        { label: 'Latency', value: `${c.cache.latency_ms}ms` },
        ...(c.cache.error ? [{ label: 'Error', value: c.cache.error, warn: true }] : []),
      ],
    })
  }

  if (c.storage) {
    cards.push({
      title: 'Storage',
      icon: FolderIcon,
      status: c.storage.status,
      items: [
        { label: 'Framework', value: c.storage.framework_writable ? '✓ Writable' : '✗ Not writable' },
        { label: 'Public Disk', value: c.storage.public_disk_writable ? '✓ Writable' : '✗ Not writable' },
        { label: 'Backup Dir', value: c.storage.backup_dir_writable ? '✓ Writable' : '✗ Not writable' },
        { label: 'Free Space', value: c.storage.free_space_gb ? `${c.storage.free_space_gb} GB` : '—' },
        { label: 'Total Space', value: c.storage.total_space_gb ? `${c.storage.total_space_gb} GB` : '—' },
      ],
    })
  }

  if (c.mail) {
    cards.push({
      title: 'Mail Service',
      icon: EnvelopeIcon,
      status: c.mail.status,
      items: [
        { label: 'Driver', value: c.mail.driver },
        { label: 'Host', value: c.mail.host || '—' },
        { label: 'Port', value: c.mail.port || '—' },
        { label: 'Encryption', value: c.mail.encryption || 'none' },
        { label: 'From', value: c.mail.from_address || '—' },
      ],
    })
  }

  if (c.payments) {
    cards.push({
      title: 'Payment Gateways',
      icon: CreditCardIcon,
      status: [c.payments.paystack?.status, c.payments.stripe?.status].includes('configured') ? 'configured' : 'warning',
      items: [
        { label: 'Paystack', value: c.payments.paystack?.status || '—' },
        { label: 'Paystack Currency', value: c.payments.paystack?.currency || '—' },
        { label: 'Stripe', value: c.payments.stripe?.status || '—' },
        { label: 'Stripe Currency', value: c.payments.stripe?.currency || '—' },
      ],
    })
  }

  if (c.queue) {
    cards.push({
      title: 'Queue & Jobs',
      icon: QueueListIcon,
      status: c.queue.status,
      items: [
        { label: 'Driver', value: c.queue.driver },
        { label: 'Failed Jobs', value: c.queue.failed_jobs_count },
      ],
    })
  }

  if (c.environment) {
    cards.push({
      title: 'Environment',
      icon: ServerStackIcon,
      status: c.environment.debug_mode ? 'warning' : 'operational',
      items: [
        { label: 'App Env', value: c.environment.app_env },
        { label: 'Debug Mode', value: c.environment.debug_mode ? '⚠ Enabled' : '✓ Disabled' },
        { label: 'PHP', value: c.environment.php_version },
        { label: 'Laravel', value: c.environment.laravel_version },
        { label: 'Memory', value: `${c.environment.memory_usage_mb} / ${c.environment.memory_peak_mb} MB` },
        { label: 'Timezone', value: c.environment.server_timezone },
      ],
    })
  }

  return cards
})

// ── Lifecycle ──
onMounted(() => {
  fetchOverview()
  fetchHealthDetail()
})

onUnmounted(() => {
  if (refreshInterval) clearInterval(refreshInterval)
})
</script>

<template>
  <AdminLayout>
    <section class="space-y-8 animate-fade-in">
      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-white flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500/20 to-teal-500/20 border border-emerald-500/20 flex items-center justify-center">
              <HeartIcon class="w-5 h-5 text-emerald-400" />
            </div>
            Health Checker
          </h1>
          <p class="text-sm text-zinc-400 mt-1">Real-time system health monitoring and diagnostics</p>
        </div>
        <div class="flex items-center gap-3">
          <span v-if="lastRefreshed" class="text-xs text-zinc-500">
            Last refreshed: {{ formatTime(lastRefreshed.toISOString()) }}
          </span>
          <button
            @click="toggleAutoRefresh"
            :class="[
              'flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium transition-all border',
              autoRefresh
                ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-400 hover:bg-emerald-500/20'
                : 'bg-white/[0.03] border-white/[0.06] text-zinc-400 hover:bg-white/[0.06]'
            ]"
          >
            <SignalIcon class="w-4 h-4" :class="{ 'animate-pulse': autoRefresh }" />
            {{ autoRefresh ? 'Live' : 'Auto-Refresh' }}
          </button>
          <button
            @click="refreshAll"
            :disabled="overviewLoading && detailLoading"
            class="flex items-center gap-2 px-4 py-2 rounded-lg bg-white/[0.05] border border-white/[0.08] text-zinc-300 hover:bg-white/[0.08] transition-all text-xs font-medium disabled:opacity-50"
          >
            <ArrowPathIcon class="w-4 h-4" :class="{ 'animate-spin': overviewLoading }" />
            Refresh
          </button>
        </div>
      </div>

      <!-- Overall System Status Banner -->
      <div
        v-if="overview"
        :class="[
          'rounded-2xl p-5 border flex items-center gap-4',
          statusBg(overview.status)
        ]"
      >
        <component :is="statusIcon(overview.status)" :class="['w-8 h-8', statusColor(overview.status)]" />
        <div>
          <p :class="['text-lg font-semibold', statusColor(overview.status)]">
            System Status: {{ (overview.status || 'unknown').charAt(0).toUpperCase() + (overview.status || 'unknown').slice(1) }}
          </p>
          <p class="text-xs text-zinc-400">
            Server time: {{ formatTime(overview.server_time) }}
          </p>
        </div>
      </div>

      <!-- KPI Overview Cards -->
      <div v-if="overviewLoading" class="grid gap-4 grid-cols-2 lg:grid-cols-4">
        <div v-for="i in 8" :key="i" class="h-28 rounded-xl bg-white/[0.03] animate-pulse border border-white/[0.04]"></div>
      </div>
      <div v-else class="grid gap-4 grid-cols-2 lg:grid-cols-4">
        <div
          v-for="card in overviewCards"
          :key="card.label"
          :class="[
            'rounded-xl p-4 border transition-all hover:border-white/[0.12]',
            statusBg(card.status)
          ]"
        >
          <div class="flex items-center justify-between mb-3">
            <component :is="card.icon" :class="['w-5 h-5', statusColor(card.status)]" />
            <component :is="statusIcon(card.status)" :class="['w-4 h-4', statusColor(card.status)]" />
          </div>
          <p class="text-xs text-zinc-400 mb-0.5">{{ card.label }}</p>
          <p class="text-lg font-semibold text-white">{{ card.value }}</p>
        </div>
      </div>

      <!-- Top Active Errors (from overview) -->
      <div v-if="overview?.top_errors?.length" class="rounded-2xl bg-white/[0.02] border border-white/[0.06] overflow-hidden">
        <div class="px-5 py-4 border-b border-white/[0.06] flex items-center gap-3">
          <ExclamationCircleIcon class="w-5 h-5 text-red-400" />
          <h2 class="text-sm font-semibold text-white">Top Active Error Groups</h2>
        </div>
        <div class="divide-y divide-white/[0.04]">
          <div
            v-for="err in overview.top_errors"
            :key="err.id"
            class="px-5 py-3 flex items-center justify-between hover:bg-white/[0.02] transition-colors"
          >
            <div class="flex-1 min-w-0">
              <p class="text-sm text-white truncate font-mono">{{ err.error_code }}</p>
              <p class="text-xs text-zinc-400 truncate">{{ err.sample_message }}</p>
            </div>
            <div class="flex items-center gap-4 ml-4">
              <span class="text-xs text-zinc-500">{{ err.occurrences }} hits</span>
              <span :class="[
                'text-xs px-2 py-0.5 rounded-full border font-medium',
                err.severity === 'critical' ? 'bg-red-500/10 border-red-500/20 text-red-400'
                  : err.severity === 'error' ? 'bg-orange-500/10 border-orange-500/20 text-orange-400'
                  : 'bg-amber-500/10 border-amber-500/20 text-amber-400'
              ]">
                {{ err.severity }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Detailed Health Checks -->
      <div>
        <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
          <WrenchScrewdriverIcon class="w-5 h-5 text-zinc-400" />
          Component Health Report
        </h2>
        <div v-if="detailLoading" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
          <div v-for="i in 7" :key="i" class="h-48 rounded-xl bg-white/[0.03] animate-pulse border border-white/[0.04]"></div>
        </div>
        <div v-else class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
          <div
            v-for="card in healthCards"
            :key="card.title"
            :class="[
              'rounded-xl border p-5 transition-all hover:border-white/[0.12]',
              statusBg(card.status)
            ]"
          >
            <div class="flex items-center justify-between mb-4">
              <div class="flex items-center gap-3">
                <div :class="['w-9 h-9 rounded-lg flex items-center justify-center border', statusBg(card.status)]">
                  <component :is="card.icon" :class="['w-5 h-5', statusColor(card.status)]" />
                </div>
                <h3 class="text-sm font-semibold text-white">{{ card.title }}</h3>
              </div>
              <span :class="[
                'text-xs px-2 py-0.5 rounded-full border font-medium capitalize',
                statusBg(card.status),
                statusColor(card.status)
              ]">
                {{ card.status }}
              </span>
            </div>
            <div class="space-y-2">
              <div
                v-for="item in card.items"
                :key="item.label"
                class="flex items-center justify-between text-xs"
              >
                <span class="text-zinc-500">{{ item.label }}</span>
                <span :class="item.warn ? 'text-red-400 font-mono text-[10px] max-w-[180px] truncate' : 'text-zinc-300'">
                  {{ item.value }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Self-Test Panel -->
      <div class="rounded-2xl bg-white/[0.02] border border-white/[0.06] overflow-hidden">
        <div class="px-5 py-4 border-b border-white/[0.06] flex items-center justify-between">
          <div class="flex items-center gap-3">
            <BeakerIcon class="w-5 h-5 text-purple-400" />
            <h2 class="text-sm font-semibold text-white">Interactive Self-Diagnostics</h2>
          </div>
          <p class="text-xs text-zinc-500">Run live tests against system components</p>
        </div>
        <div class="p-5">
          <div class="flex flex-wrap gap-3 mb-6">
            <button
              @click="runDiagnostic('all')"
              :disabled="selfTestRunning"
              class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-purple-600/20 to-violet-600/20 border border-purple-500/30 text-purple-300 hover:from-purple-600/30 hover:to-violet-600/30 transition-all text-sm font-medium disabled:opacity-50"
            >
              <PlayIcon class="w-4 h-4" />
              Run All Tests
            </button>
            <button
              @click="runDiagnostic('database')"
              :disabled="selfTestRunning"
              class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/[0.03] border border-white/[0.06] text-zinc-300 hover:bg-white/[0.06] transition-all text-sm font-medium disabled:opacity-50"
            >
              <CircleStackIcon class="w-4 h-4" />
              Test Database
            </button>
            <button
              @click="runDiagnostic('cache')"
              :disabled="selfTestRunning"
              class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/[0.03] border border-white/[0.06] text-zinc-300 hover:bg-white/[0.06] transition-all text-sm font-medium disabled:opacity-50"
            >
              <CpuChipIcon class="w-4 h-4" />
              Test Cache
            </button>
            <button
              @click="runDiagnostic('storage')"
              :disabled="selfTestRunning"
              class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/[0.03] border border-white/[0.06] text-zinc-300 hover:bg-white/[0.06] transition-all text-sm font-medium disabled:opacity-50"
            >
              <FolderIcon class="w-4 h-4" />
              Test Storage
            </button>
          </div>

          <!-- Test Running Indicator -->
          <div v-if="selfTestRunning" class="flex items-center gap-3 py-6 justify-center">
            <ArrowPathIcon class="w-6 h-6 text-purple-400 animate-spin" />
            <p class="text-sm text-zinc-400">Running diagnostics…</p>
          </div>

          <!-- Test Results -->
          <div v-if="selfTestResults && !selfTestRunning" class="space-y-3">
            <div class="flex items-center gap-2 mb-4">
              <component
                :is="selfTestResults.all_passed ? CheckCircleIcon : XCircleIcon"
                :class="['w-5 h-5', selfTestResults.all_passed ? 'text-emerald-400' : 'text-red-400']"
              />
              <p :class="['text-sm font-semibold', selfTestResults.all_passed ? 'text-emerald-400' : 'text-red-400']">
                {{ selfTestResults.all_passed ? 'All Diagnostics Passed' : 'Issues Detected' }}
              </p>
            </div>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
              <div
                v-for="(result, key) in selfTestResults.results"
                :key="key"
                :class="[
                  'rounded-xl p-4 border',
                  result.passed ? 'bg-emerald-500/5 border-emerald-500/15' : 'bg-red-500/5 border-red-500/15'
                ]"
              >
                <div class="flex items-center gap-2 mb-2">
                  <component
                    :is="result.passed ? CheckCircleIcon : XCircleIcon"
                    :class="['w-4 h-4', result.passed ? 'text-emerald-400' : 'text-red-400']"
                  />
                  <h4 class="text-sm font-semibold text-white capitalize">{{ key }}</h4>
                </div>
                <p class="text-xs text-zinc-400">{{ result.message }}</p>
                <p v-if="result.latency_ms" class="text-xs text-zinc-500 mt-1">
                  Latency: <span class="text-zinc-300">{{ result.latency_ms }}ms</span>
                </p>
                <p v-if="result.error" class="text-xs text-red-400 mt-1 font-mono truncate">
                  {{ result.error }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </AdminLayout>
</template>
