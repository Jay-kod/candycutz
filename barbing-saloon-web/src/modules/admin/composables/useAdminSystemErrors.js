import { ref, computed, watch, onMounted } from 'vue'
import client from '@/shared/api/client'
import { useToast } from '@/core/composables/useToast'

const SEVERITY_COLORS = {
  critical: 'bg-rose-500/10 text-rose-400 border-rose-500/20',
  error: 'bg-orange-500/10 text-orange-400 border-orange-500/20',
  warning: 'bg-amber-500/10 text-amber-400 border-amber-500/20',
  info: 'bg-blue-500/10 text-blue-400 border-blue-500/20',
}

const STATUS_COLORS = {
  open: 'bg-rose-500/10 text-rose-400 border-rose-500/20',
  acknowledged: 'bg-amber-500/10 text-amber-400 border-amber-500/20',
  resolved: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
  ignored: 'bg-zinc-500/10 text-zinc-400 border-zinc-500/20',
}

export function useAdminSystemErrors() {
  const toast = useToast()

  // ── List & Pagination State ──
  const errors = ref([])
  const loading = ref(true)
  const totalPages = ref(1)
  const currentPage = ref(1)
  const totalCount = ref(0)

  // ── Filters State ──
  const search = ref('')
  const statusFilter = ref('')
  const severityFilter = ref('')
  const categoryFilter = ref('')

  // ── Drawer State ──
  const drawerOpen = ref(false)
  const drawerLoading = ref(false)
  const selectedGroup = ref(null)
  const selectedEvents = ref([])
  const statusUpdating = ref(false)

  // ── Trace State ──
  const traceId = ref('')
  const traceLoading = ref(false)
  const traceResults = ref(null)
  const showTracePanel = ref(false)

  // ── Fetch Errors ──
  const fetchErrors = async () => {
    loading.value = true
    try {
      const params = { page: currentPage.value }
      if (search.value.trim()) params.q = search.value.trim()
      if (statusFilter.value) params.status = statusFilter.value
      if (severityFilter.value) params.severity = severityFilter.value
      if (categoryFilter.value) params.category = categoryFilter.value

      const res = await client.get('/admin/system/errors', { params })
      const data = res.data?.data || res.data || {}
      errors.value = data.data || []
      totalPages.value = data.last_page || 1
      currentPage.value = data.current_page || 1
      totalCount.value = data.total ?? errors.value.length
    } catch (err) {
      toast.error('Failed to load incident error groups')
      console.error('System errors fetch failed:', err)
    } finally {
      loading.value = false
    }
  }

  // ── Drawer Actions ──
  const openErrorDetail = async (group) => {
    drawerOpen.value = true
    drawerLoading.value = true
    selectedGroup.value = group
    selectedEvents.value = []
    try {
      const res = await client.get(`/admin/system/errors/${group.id}`)
      const data = res.data?.data || {}
      selectedGroup.value = data.group || group
      selectedEvents.value = data.events || []
    } catch (err) {
      toast.error('Failed to load error event records')
      console.error('Error detail fetch failed:', err)
    } finally {
      drawerLoading.value = false
    }
  }

  const closeDrawer = () => {
    drawerOpen.value = false
    selectedGroup.value = null
    selectedEvents.value = []
  }

  const updateStatus = async (newStatus, note = null) => {
    if (!selectedGroup.value) return
    statusUpdating.value = true
    try {
      const payload = { status: newStatus }
      if (note !== null) payload.resolution_note = note
      await client.patch(`/admin/system/errors/${selectedGroup.value.id}`, payload)
      selectedGroup.value.status = newStatus
      if (note !== null) selectedGroup.value.resolution_note = note
      toast.success(`Incident status updated to: ${newStatus}`)
      await fetchErrors()
    } catch (err) {
      toast.error('Failed to update incident status')
      console.error('Error status update failed:', err)
    } finally {
      statusUpdating.value = false
    }
  }

  // ── Trace Actions ──
  const executeTrace = async () => {
    const q = traceId.value.trim()
    if (!q) {
      toast.error('Please enter a valid request ID to trace')
      return
    }
    traceLoading.value = true
    traceResults.value = null
    showTracePanel.value = true
    try {
      const res = await client.get(`/admin/system/trace/${encodeURIComponent(q)}`)
      traceResults.value = res.data?.data || null
    } catch (err) {
      toast.error('Trace lookup failed')
      console.error('Request trace failed:', err)
    } finally {
      traceLoading.value = false
    }
  }

  const closeTrace = () => {
    showTracePanel.value = false
    traceResults.value = null
  }

  // ── Helpers ──
  const severityColor = (sev) => SEVERITY_COLORS[sev] || 'bg-zinc-500/10 text-zinc-400 border-zinc-500/20'
  const statusColor = (s) => STATUS_COLORS[s] || 'bg-zinc-500/10 text-zinc-400 border-zinc-500/20'

  const formatTime = (iso) => iso ? new Date(iso).toLocaleString('en-US', { dateStyle: 'medium', timeStyle: 'short' }) : '—'

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

  const goToPage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
      currentPage.value = page
      fetchErrors()
    }
  }

  const clearFilters = () => {
    search.value = ''
    statusFilter.value = ''
    severityFilter.value = ''
    categoryFilter.value = ''
    currentPage.value = 1
    fetchErrors()
  }

  // ── Computed Stats ──
  const stats = computed(() => {
    const list = errors.value || []
    return {
      total: totalCount.value,
      open: list.filter(e => e.status === 'open').length,
      critical: list.filter(e => e.severity === 'critical').length,
      resolved: list.filter(e => e.status === 'resolved').length,
    }
  })

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

  onMounted(fetchErrors)

  return {
    errors,
    loading,
    totalPages,
    currentPage,
    totalCount,
    search,
    statusFilter,
    severityFilter,
    categoryFilter,
    drawerOpen,
    drawerLoading,
    selectedGroup,
    selectedEvents,
    statusUpdating,
    traceId,
    traceLoading,
    traceResults,
    showTracePanel,
    stats,
    fetchErrors,
    openErrorDetail,
    closeDrawer,
    updateStatus,
    executeTrace,
    closeTrace,
    severityColor,
    statusColor,
    formatTime,
    timeAgo,
    goToPage,
    clearFilters,
  }
}

