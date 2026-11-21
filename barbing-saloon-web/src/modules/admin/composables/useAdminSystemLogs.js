import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import client from '@/shared/api/client';
import { useToast } from '@/core/composables/useToast';
import {
  ClipboardDocumentListIcon,
  ShieldCheckIcon,
  ExclamationCircleIcon,
  UserIcon,
  CreditCardIcon,
  ArrowRightOnRectangleIcon
} from '@heroicons/vue/24/outline';

export function useAdminSystemLogs() {
  const toast = useToast();

  // Tabs: 'gate' | 'audit'
  const activeTab = ref('gate');

  // ==========================================
  // 1. Audit Logs State & Logic
  // ==========================================
  const auditLogs = ref([]);
  const auditLoading = ref(true);
  const auditSearch = ref('');
  const auditFilterType = ref('all');

  const fetchAuditLogs = async () => {
    auditLoading.value = true;
    try {
      const res = await client.get('/admin/logs');
      auditLogs.value = res.data?.data || [];
    } catch (err) {
      console.error('Failed to load audit logs:', err);
      toast.error('Failed to load audit logs');
    } finally {
      auditLoading.value = false;
    }
  };

  const filteredAuditLogs = computed(() => {
    let result = auditLogs.value;

    if (auditFilterType.value !== 'all') {
      result = result.filter(log => log.entity_type === auditFilterType.value || log.action === auditFilterType.value);
    }

    if (auditSearch.value) {
      const q = auditSearch.value.toLowerCase();
      result = result.filter(log =>
        (log.user_name || '').toLowerCase().includes(q) ||
        (log.action || '').toLowerCase().includes(q) ||
        (log.entity_type || '').toLowerCase().includes(q) ||
        (log.ip_address || '').toLowerCase().includes(q)
      );
    }

    return result;
  });

  const getIconForAction = (action) => {
    if (!action) return UserIcon;
    if (action === 'login') return ArrowRightOnRectangleIcon;
    if (action.includes('payment')) return CreditCardIcon;
    if (action.includes('booking') || action.includes('walkin') || action.includes('service')) return ShieldCheckIcon;
    if (action.includes('barber')) return UserIcon;
    if (action.includes('blog')) return ClipboardDocumentListIcon;
    if (action.includes('error') || action.includes('failed')) return ExclamationCircleIcon;
    return ShieldCheckIcon;
  };

  const getIconColorForAction = (action) => {
    if (!action) return 'text-[#FF6700] bg-[#FF6700]/10 border-[#FF6700]/20';
    if (action === 'login') return 'text-sky-400 bg-sky-500/10 border-sky-500/20';
    if (action.includes('verify') || action.includes('approve') || action.includes('complete') || action.includes('create')) {
      return 'text-emerald-400 bg-emerald-500/10 border-emerald-500/20';
    }
    if (action.includes('reject') || action.includes('cancel') || action.includes('delete') || action.includes('no_show')) {
      return 'text-rose-400 bg-rose-500/10 border-rose-500/20';
    }
    if (action.includes('update')) return 'text-amber-400 bg-amber-500/10 border-amber-500/20';
    return 'text-[#FF6700] bg-[#FF6700]/10 border-[#FF6700]/20';
  };

  // ==========================================
  // 2. Gate Telemetry State & Logic
  // ==========================================
  const gateMetrics = ref(null);
  const gateMetricsLoading = ref(true);
  const gateLogs = ref([]);
  const gateLogsLoading = ref(true);
  const gatePagination = ref({ current_page: 1, last_page: 1, total: 0, per_page: 25 });

  const gateClientFilter = ref('web');
  const gateIsSlow = ref(false);
  const gateBudgetExceeded = ref(false);
  const gateSearch = ref('');
  const hotspotTab = ref('slow');

  // Pruning modal state
  const showPruneModal = ref(false);
  const pruneDays = ref(7);
  const pruning = ref(false);

  const fetchGateMetrics = async () => {
    gateMetricsLoading.value = true;
    try {
      const res = await client.get('/admin/gate/metrics');
      gateMetrics.value = res.data?.data || null;
    } catch (err) {
      console.error('Failed to load gate metrics:', err);
    } finally {
      gateMetricsLoading.value = false;
    }
  };

  const fetchGateLogs = async (page = 1) => {
    gateLogsLoading.value = true;
    try {
      const params = {
        page,
        per_page: 25,
        client_type: gateClientFilter.value !== 'all' ? gateClientFilter.value : undefined,
        is_slow: gateIsSlow.value ? 'true' : undefined,
        budget_exceeded: gateBudgetExceeded.value ? 'true' : undefined,
        search: gateSearch.value.trim() || undefined
      };
      const res = await client.get('/admin/gate/logs', { params });
      const data = res.data?.data;
      gateLogs.value = data?.items || [];
      if (data?.pagination) {
        gatePagination.value = data.pagination;
      }
    } catch (err) {
      console.error('Failed to load gate logs:', err);
    } finally {
      gateLogsLoading.value = false;
    }
  };

  const fetchGateData = async () => {
    await Promise.all([fetchGateMetrics(), fetchGateLogs(1)]);
  };

  let searchDebounceTimer = null;
  const onGateSearchInput = () => {
    clearTimeout(searchDebounceTimer);
    searchDebounceTimer = setTimeout(() => {
      fetchGateLogs(1);
    }, 350);
  };

  watch([gateClientFilter, gateIsSlow, gateBudgetExceeded], () => {
    fetchGateLogs(1);
  });

  const handlePrune = async () => {
    pruning.value = true;
    try {
      const res = await client.post('/admin/gate/flush', { keep_days: Number(pruneDays.value) });
      const pruned = res.data?.data?.pruned_records ?? 0;
      toast.success(`Successfully pruned ${pruned} telemetry logs older than ${pruneDays.value} days.`);
      showPruneModal.value = false;
      await fetchGateData();
    } catch (err) {
      console.error('Failed to prune gate telemetry:', err);
      toast.error('Failed to prune telemetry logs.');
    } finally {
      pruning.value = false;
    }
  };

  // Helper formatting for Gate UI
  const getMethodBadgeClass = (method) => {
    switch (method?.toUpperCase()) {
      case 'GET': return 'text-sky-400 bg-sky-500/10 border-sky-500/20';
      case 'POST': return 'text-emerald-400 bg-emerald-500/10 border-emerald-500/20';
      case 'PUT':
      case 'PATCH': return 'text-amber-400 bg-amber-500/10 border-amber-500/20';
      case 'DELETE': return 'text-rose-400 bg-rose-500/10 border-rose-500/20';
      default: return 'text-gray-400 bg-gray-500/10 border-gray-500/20';
    }
  };

  const getClientBadgeClass = (type) => {
    switch (type) {
      case 'web': return 'text-sky-400 bg-sky-500/10 border-sky-500/20';
      case 'webhook': return 'text-emerald-400 bg-emerald-500/10 border-emerald-500/20';
      default: return 'text-gray-400 bg-gray-500/10 border-gray-500/20';
    }
  };

  const getStatusCodeClass = (status) => {
    if (status >= 200 && status < 300) return 'text-emerald-400 bg-emerald-500/10 border-emerald-500/20';
    if (status >= 300 && status < 400) return 'text-sky-400 bg-sky-500/10 border-sky-500/20';
    if (status >= 400 && status < 500) return 'text-amber-400 bg-amber-500/10 border-amber-500/20';
    return 'text-rose-400 bg-rose-500/10 border-rose-500/20';
  };

  const formatDateTime = (isoString) => {
    if (!isoString) return 'N/A';
    const d = new Date(isoString);
    return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ' ' + d.toLocaleDateString([], { month: 'short', day: 'numeric' });
  };

  const clientDistributionPercent = computed(() => {
    const dist = gateMetrics.value?.client_distribution || { web: 0, webhook: 0 };
    const total = (dist.web || 0) + (dist.webhook || 0);
    if (total === 0) return { web: 0, webhook: 0 };
    return {
      web: Math.round(((dist.web || 0) / total) * 100),
      webhook: Math.round(((dist.webhook || 0) / total) * 100)
    };
  });

  onMounted(() => {
    fetchGateData();
    fetchAuditLogs();
  });

  onUnmounted(() => {
    if (searchDebounceTimer) {
      clearTimeout(searchDebounceTimer);
    }
  });

  return {
    activeTab,
    auditLogs,
    auditLoading,
    auditSearch,
    auditFilterType,
    filteredAuditLogs,
    fetchAuditLogs,
    getIconForAction,
    getIconColorForAction,
    gateMetrics,
    gateMetricsLoading,
    gateLogs,
    gateLogsLoading,
    gatePagination,
    gateClientFilter,
    gateIsSlow,
    gateBudgetExceeded,
    gateSearch,
    hotspotTab,
    showPruneModal,
    pruneDays,
    pruning,
    fetchGateMetrics,
    fetchGateLogs,
    fetchGateData,
    onGateSearchInput,
    handlePrune,
    getMethodBadgeClass,
    getClientBadgeClass,
    getStatusCodeClass,
    formatDateTime,
    clientDistributionPercent
  };
}

