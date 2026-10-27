import { ref, computed, onMounted, onUnmounted, markRaw } from 'vue'
import client from '@/shared/api/client'
import { useToast } from '@/core/composables/useToast'
import {
  ServerStackIcon,
  CircleStackIcon,
  CpuChipIcon,
  FolderIcon,
  EnvelopeIcon,
  CreditCardIcon,
  QueueListIcon,
  ShieldCheckIcon,
  CheckCircleIcon,
  ExclamationTriangleIcon,
  XCircleIcon,
  ClockIcon,
  SignalIcon,
  BoltIcon,
  InformationCircleIcon,
  ExclamationCircleIcon,
} from '@heroicons/vue/24/outline'

const STATUS_COLOR_MAP = {
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
  error: 'text-rose-400',
  disconnected: 'text-rose-400',
}

const STATUS_BG_MAP = {
  optimal: 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400',
  healthy: 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400',
  operational: 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400',
  connected: 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400',
  configured: 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400',
  attention: 'bg-amber-500/10 border-amber-500/20 text-amber-400',
  warning: 'bg-amber-500/10 border-amber-500/20 text-amber-400',
  degraded: 'bg-orange-500/10 border-orange-500/20 text-orange-400',
  sandbox_or_missing: 'bg-amber-500/10 border-amber-500/20 text-amber-400',
  not_configured: 'bg-zinc-500/10 border-zinc-500/20 text-zinc-400',
  error: 'bg-rose-500/10 border-rose-500/20 text-rose-400',
  disconnected: 'bg-rose-500/10 border-rose-500/20 text-rose-400',
}

export function useAdminHealthChecker() {
  const toast = useToast()

  const overview = ref(null)
  const healthDetail = ref(null)
  const overviewLoading = ref(true)
  const detailLoading = ref(true)
  const selfTestRunning = ref(false)
  const selfTestResults = ref(null)
  const autoRefresh = ref(false)
  const lastRefreshed = ref(null)
  let refreshInterval = null

  const fetchOverview = async () => {
    try {
      const res = await client.get('/admin/system/overview')
      overview.value = res.data?.data || res.data || null
    } catch (err) {
      console.error('System overview fetch failed:', err)
    } finally {
      overviewLoading.value = false
    }
  }

  const fetchHealthDetail = async () => {
    try {
      const res = await client.get('/admin/system/health/detail')
      healthDetail.value = res.data?.data || res.data || null
    } catch (err) {
      console.error('Health detail fetch failed:', err)
    } finally {
      detailLoading.value = false
    }
  }

  const refreshAll = async (silent = false) => {
    overviewLoading.value = true
    detailLoading.value = true
    await Promise.all([fetchOverview(), fetchHealthDetail()])
    lastRefreshed.value = new Date()
    if (!silent) toast.success('System diagnostics telemetry refreshed')
  }

  const runDiagnostic = async (type = 'all') => {
    selfTestRunning.value = true
    selfTestResults.value = null
    try {
      const res = await client.post('/admin/system/health/test', { type })
      selfTestResults.value = res.data?.data || res.data || null
      if (selfTestResults.value?.all_passed) {
        toast.success('All diagnostics verified successfully')
      } else {
        toast.error('Diagnostic anomalies detected — review report')
      }
    } catch (err) {
      toast.error('Self-test execution failed')
      console.error('Self-test error:', err)
    } finally {
      selfTestRunning.value = false
    }
  }

  const toggleAutoRefresh = () => {
    autoRefresh.value = !autoRefresh.value
    if (autoRefresh.value) {
      refreshInterval = setInterval(() => refreshAll(true), 30000)
      toast.success('Live telemetry enabled (30s interval)')
    } else {
      if (refreshInterval) clearInterval(refreshInterval)
      refreshInterval = null
      toast.info('Live telemetry paused')
    }
  }

  const statusColor = (s) => STATUS_COLOR_MAP[s] || 'text-zinc-400'
  const statusBg = (s) => STATUS_BG_MAP[s] || 'bg-white/[0.03] border-white/[0.08] text-zinc-400'
  const statusIcon = (s) => {
    if (['optimal', 'healthy', 'operational', 'connected', 'configured'].includes(s)) return markRaw(CheckCircleIcon)
    if (['attention', 'warning', 'degraded', 'sandbox_or_missing'].includes(s)) return markRaw(ExclamationTriangleIcon)
    if (['error', 'disconnected'].includes(s)) return markRaw(XCircleIcon)
    return markRaw(InformationCircleIcon)
  }

  const formatTime = (iso) => iso ? new Date(iso).toLocaleString('en-US', { dateStyle: 'medium', timeStyle: 'short' }) : '—'

  const overviewCards = computed(() => {
    const s = overview.value?.summary || {}
    return [
      { label: 'Database Core', value: s.database_connected ? 'Connected' : 'Offline', status: s.database_connected ? 'connected' : 'disconnected', icon: markRaw(CircleStackIcon) },
      { label: 'Open Errors', value: s.open_groups_count ?? '0', status: (s.critical_groups_count > 0) ? 'error' : (s.open_groups_count > 5 ? 'attention' : 'operational'), icon: markRaw(ExclamationCircleIcon) },
      { label: 'Critical Anomalies', value: s.critical_groups_count ?? '0', status: s.critical_groups_count > 0 ? 'error' : 'operational', icon: markRaw(ShieldCheckIcon) },
      { label: '5xx Events (24h)', value: s.errors_5xx_count ?? '0', status: (s.errors_5xx_count > 10) ? 'warning' : 'operational', icon: markRaw(BoltIcon) },
      { label: 'Telemetry Hits (24h)', value: s.events_24h_count ?? '0', status: 'operational', icon: markRaw(SignalIcon) },
      { label: 'System Snapshots', value: s.backups_count ?? '0', status: (s.backups_count ?? 0) === 0 ? 'warning' : 'operational', icon: markRaw(FolderIcon) },
      { label: 'Active Sessions', value: s.active_sessions_count ?? '0', status: 'operational', icon: markRaw(QueueListIcon) },
      { label: 'Latest Snapshot', value: s.latest_backup_time ? formatTime(s.latest_backup_time) : 'None Available', status: s.latest_backup_time ? 'operational' : 'warning', icon: markRaw(ClockIcon) },
    ]
  })

  const healthCards = computed(() => {
    const c = healthDetail.value?.checks || {}
    const cards = []
    if (c.database) {
      cards.push({
        title: 'Database Engine',
        icon: markRaw(CircleStackIcon),
        status: c.database.status,
        items: [
          { label: 'Driver Connection', value: c.database.connection || 'MySQL / MariaDB' },
          { label: 'Response Latency', value: `${c.database.latency_ms ?? 0}ms` },
          { label: 'Registered Tables', value: c.database.tables_count ?? 0 },
          ...(c.database.error ? [{ label: 'Exception', value: c.database.error, warn: true }] : []),
        ],
      })
    }
    if (c.cache) {
      cards.push({
        title: 'Cache & Session Store',
        icon: markRaw(CpuChipIcon),
        status: c.cache.status,
        items: [
          { label: 'Cache Driver', value: c.cache.driver || 'file / redis' },
          { label: 'Access Latency', value: `${c.cache.latency_ms ?? 0}ms` },
          ...(c.cache.error ? [{ label: 'Exception', value: c.cache.error, warn: true }] : []),
        ],
      })
    }
    if (c.storage) {
      cards.push({
        title: 'Storage & Disks',
        icon: markRaw(FolderIcon),
        status: c.storage.status,
        items: [
          { label: 'App Framework Disk', value: c.storage.framework_writable ? '✓ Writable' : '✗ Read Only' },
          { label: 'Public Media Disk', value: c.storage.public_disk_writable ? '✓ Writable' : '✗ Read Only' },
          { label: 'Backup Volume', value: c.storage.backup_dir_writable ? '✓ Writable' : '✗ Read Only' },
          { label: 'Available Free Space', value: c.storage.free_space_gb ? `${c.storage.free_space_gb} GB` : '—' },
          { label: 'Allocated Volume Space', value: c.storage.total_space_gb ? `${c.storage.total_space_gb} GB` : '—' },
        ],
      })
    }
    if (c.mail) {
      cards.push({
        title: 'Mail Subsystem',
        icon: markRaw(EnvelopeIcon),
        status: c.mail.status,
        items: [
          { label: 'Mailer Transport', value: c.mail.driver || 'smtp' },
          { label: 'SMTP Gateway', value: c.mail.host || '—' },
          { label: 'Gateway Port', value: c.mail.port || '—' },
          { label: 'TLS / Encryption', value: c.mail.encryption || 'none' },
          { label: 'Outbound Identity', value: c.mail.from_address || '—' },
        ],
      })
    }
    if (c.payments) {
      cards.push({
        title: 'Payment Gateways',
        icon: markRaw(CreditCardIcon),
        status: [c.payments.paystack?.status, c.payments.stripe?.status].includes('configured') ? 'configured' : 'warning',
        items: [
          { label: 'Paystack Gateway', value: c.payments.paystack?.status || 'unconfigured' },
          { label: 'Paystack Currency', value: c.payments.paystack?.currency || '—' },
          { label: 'Stripe Gateway', value: c.payments.stripe?.status || 'unconfigured' },
          { label: 'Stripe Currency', value: c.payments.stripe?.currency || '—' },
        ],
      })
    }
    if (c.queue) {
      cards.push({
        title: 'Asynchronous Queues',
        icon: markRaw(QueueListIcon),
        status: c.queue.status,
        items: [
          { label: 'Broker Driver', value: c.queue.driver || 'database' },
          { label: 'Failed Job Queue', value: c.queue.failed_jobs_count ?? 0 },
        ],
      })
    }
    if (c.environment) {
      cards.push({
        title: 'Execution Runtime',
        icon: markRaw(ServerStackIcon),
        status: c.environment.debug_mode ? 'warning' : 'operational',
        items: [
          { label: 'Environment Tier', value: c.environment.app_env || 'production' },
          { label: 'Debug Verbosity', value: c.environment.debug_mode ? '⚠ Enabled' : '✓ Secure (Disabled)' },
          { label: 'PHP Engine', value: c.environment.php_version || '8.2+' },
          { label: 'Laravel Framework', value: c.environment.laravel_version || '11.x' },
          { label: 'RAM Usage / Peak', value: `${c.environment.memory_usage_mb ?? 0} / ${c.environment.memory_peak_mb ?? 0} MB` },
          { label: 'Server Timezone', value: c.environment.server_timezone || 'UTC' },
        ],
      })
    }
    return cards
  })

  onMounted(() => refreshAll(true))
  onUnmounted(() => {
    if (refreshInterval) clearInterval(refreshInterval)
    refreshInterval = null
  })

  return {
    overview,
    healthDetail,
    overviewLoading,
    detailLoading,
    selfTestRunning,
    selfTestResults,
    autoRefresh,
    lastRefreshed,
    fetchOverview,
    fetchHealthDetail,
    refreshAll,
    runDiagnostic,
    toggleAutoRefresh,
    statusColor,
    statusBg,
    statusIcon,
    formatTime,
    overviewCards,
    healthCards,
  }
}

