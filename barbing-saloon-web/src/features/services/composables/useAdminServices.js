import { ref, computed } from 'vue';
import client from '@/shared/api/client';
import { useToast } from '@/core/composables/useToast';
import { useConfirm } from '@/core/composables/useConfirm';

export function useAdminServices() {
  const toast = useToast();
  const { confirm } = useConfirm();

  const services = ref([]);
  const loading = ref(true);
  const isRefreshing = ref(false);
  const search = ref('');
  const activeCategory = ref('all');
  const approvalFilter = ref('all'); // 'all', 'pending', 'approved', 'active', 'inactive'
  const sortOrder = ref('default'); // 'default', 'price-asc', 'price-desc', 'duration-asc', 'duration-desc', 'name-asc'
  const approvingId = ref(null);
  const togglingId = ref(null);

  const pendingCount = computed(() => {
    return services.value.filter(s => s.approval_status === 'pending').length;
  });

  const approvedCount = computed(() => {
    return services.value.filter(s => s.approval_status === 'approved' || !s.approval_status).length;
  });

  const activeCount = computed(() => {
    return services.value.filter(s => s.is_active).length;
  });

  const inactiveCount = computed(() => {
    return services.value.filter(s => !s.is_active).length;
  });

  const categories = computed(() => {
    const counts = {};
    services.value.forEach(s => {
      const cat = s.category_name || s.category || 'General';
      counts[cat] = (counts[cat] || 0) + 1;
    });

    const list = Object.keys(counts).sort().map(name => ({
      name,
      count: counts[name]
    }));

    return [
      { name: 'All Categories', key: 'all', count: services.value.length },
      ...list.map(c => ({ name: c.name, key: c.name, count: c.count }))
    ];
  });

  const stats = computed(() => {
    const list = services.value;
    const totalCount = list.length;
    const active = list.filter(s => s.is_active).length;
    const pending = list.filter(s => s.approval_status === 'pending').length;

    let avgPrice = 0;
    let minPrice = 0;
    let maxPrice = 0;
    let avgDuration = 0;

    if (totalCount > 0) {
      const prices = list.map(s => Number(s.price) || 0);
      const sumPrice = prices.reduce((acc, p) => acc + p, 0);
      avgPrice = Math.round(sumPrice / totalCount);
      minPrice = Math.min(...prices);
      maxPrice = Math.max(...prices);

      const durations = list.map(s => Number(s.duration_minutes) || 30);
      const sumDuration = durations.reduce((acc, d) => acc + d, 0);
      avgDuration = Math.round(sumDuration / totalCount);
    }

    return {
      totalCount,
      activeCount: active,
      pendingCount: pending,
      avgPrice,
      minPrice,
      maxPrice,
      avgDuration
    };
  });

  const filteredServices = computed(() => {
    let list = [...services.value];

    // 1. Approval / Status Filter
    if (approvalFilter.value === 'pending') {
      list = list.filter(s => s.approval_status === 'pending');
    } else if (approvalFilter.value === 'approved') {
      list = list.filter(s => s.approval_status === 'approved' || !s.approval_status);
    } else if (approvalFilter.value === 'active') {
      list = list.filter(s => s.is_active);
    } else if (approvalFilter.value === 'inactive') {
      list = list.filter(s => !s.is_active);
    }

    // 2. Category Filter
    if (activeCategory.value !== 'all') {
      list = list.filter(s => (s.category_name || s.category || 'General') === activeCategory.value);
    }

    // 3. Search Term
    if (search.value.trim()) {
      const q = search.value.toLowerCase().trim();
      list = list.filter(s => {
        const nameMatch = s.name?.toLowerCase().includes(q);
        const descMatch = s.description?.toLowerCase().includes(q);
        const catMatch = (s.category_name || s.category || '').toLowerCase().includes(q);
        const barberMatch = s.barber?.name?.toLowerCase().includes(q);
        return nameMatch || descMatch || catMatch || barberMatch;
      });
    }

    // 4. Sorting
    if (sortOrder.value === 'price-asc') {
      list.sort((a, b) => (Number(a.price) || 0) - (Number(b.price) || 0));
    } else if (sortOrder.value === 'price-desc') {
      list.sort((a, b) => (Number(b.price) || 0) - (Number(a.price) || 0));
    } else if (sortOrder.value === 'duration-asc') {
      list.sort((a, b) => (Number(a.duration_minutes) || 0) - (Number(b.duration_minutes) || 0));
    } else if (sortOrder.value === 'duration-desc') {
      list.sort((a, b) => (Number(b.duration_minutes) || 0) - (Number(a.duration_minutes) || 0));
    } else if (sortOrder.value === 'name-asc') {
      list.sort((a, b) => (a.name || '').localeCompare(b.name || ''));
    }

    return list;
  });

  async function loadServices(silentRefresh = false) {
    if (silentRefresh) {
      isRefreshing.value = true;
    } else {
      loading.value = true;
    }

    try {
      const response = await client.get('/services');
      services.value = response.data?.data || response.data || [];
    } catch (err) {
      toast.error(err.response?.data?.message || 'Failed to fetch services menu');
    } finally {
      loading.value = false;
      isRefreshing.value = false;
    }
  }

  async function approveService(service) {
    approvingId.value = service.id;
    try {
      const res = await client.patch(`/services/${service.id}/approve`);
      toast.success(res.data?.message || `"${service.name}" approved successfully!`);
      // Update local service record immediately
      const idx = services.value.findIndex(s => s.id === service.id);
      if (idx !== -1) {
        services.value[idx] = {
          ...services.value[idx],
          approval_status: 'approved',
          is_active: true
        };
      }
      await loadServices(true);
    } catch (err) {
      toast.error(err.response?.data?.message || 'Failed to approve service');
    } finally {
      approvingId.value = null;
    }
  }

  async function rejectService(service) {
    const ok = await confirm('Reject Service', `Are you sure you want to reject "${service.name}"?`);
    if (!ok) return;

    approvingId.value = service.id;
    try {
      const res = await client.patch(`/services/${service.id}/reject`);
      toast.success(res.data?.message || `"${service.name}" rejected.`);
      // Update local record
      const idx = services.value.findIndex(s => s.id === service.id);
      if (idx !== -1) {
        services.value[idx] = {
          ...services.value[idx],
          approval_status: 'rejected',
          is_active: false
        };
      }
      await loadServices(true);
    } catch (err) {
      toast.error(err.response?.data?.message || 'Failed to reject service');
    } finally {
      approvingId.value = null;
    }
  }

  async function toggleServiceActive(service) {
    togglingId.value = service.id;
    const previousState = service.is_active;
    const newState = !previousState;

    // Optimistic local update
    service.is_active = newState;

    try {
      await client.post(`/services/${service.id}`, {
        is_active: newState
      });
      toast.success(`"${service.name}" is now ${newState ? 'active' : 'inactive'}`);
    } catch (err) {
      // Revert optimistic update on failure
      service.is_active = previousState;
      toast.error(err.response?.data?.message || 'Failed to update service status');
    } finally {
      togglingId.value = null;
    }
  }

  async function deleteService(service) {
    const ok = await confirm(
      'Delete Service',
      `Are you sure you want to delete "${service.name}"? This action cannot be undone.`
    );
    if (!ok) return;

    try {
      await client.delete(`/services/${service.id}`);
      toast.success(`"${service.name}" has been removed`);
      services.value = services.value.filter(s => s.id !== service.id);
    } catch (err) {
      toast.error(err.response?.data?.message || 'Failed to delete service');
    }
  }

  function resetFilters() {
    search.value = '';
    activeCategory.value = 'all';
    approvalFilter.value = 'all';
    sortOrder.value = 'default';
  }

  return {
    services,
    loading,
    isRefreshing,
    search,
    activeCategory,
    approvalFilter,
    sortOrder,
    approvingId,
    togglingId,
    pendingCount,
    approvedCount,
    activeCount,
    inactiveCount,
    categories,
    stats,
    filteredServices,
    loadServices,
    approveService,
    rejectService,
    toggleServiceActive,
    deleteService,
    resetFilters
  };
}
