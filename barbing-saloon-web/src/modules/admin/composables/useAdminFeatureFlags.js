import { ref, computed } from 'vue';
import client from '@/shared/api/client';
import { useToast } from '@/core/composables/useToast';
import { useConfirm } from '@/core/composables/useConfirm';

export function useAdminFeatureFlags() {
  const toast = useToast();
  const { confirm } = useConfirm();

  const flags = ref([]);
  const loading = ref(true);
  const isRefreshing = ref(false);
  const saving = ref(false);
  const togglingId = ref(null);

  // Filters & Search
  const search = ref('');
  const statusFilter = ref('all'); // 'all', 'active', 'inactive'
  const platformFilter = ref('all'); // 'all', 'web', 'app', 'both'
  const sortOrder = ref('newest'); // 'newest', 'oldest', 'name-asc', 'rollout-desc'

  // Modal State
  const modalOpen = ref(false);
  const editingFlag = ref(null);
  const form = ref({
    key: '',
    name: '',
    description: '',
    enabled_for: ['web', 'app'],
    rollout_percentage: 100,
    is_active: true,
  });

  // KPI Metrics Computed
  const stats = computed(() => {
    const list = flags.value;
    const totalCount = list.length;
    const activeCount = list.filter(f => f.is_active).length;
    const inactiveCount = list.filter(f => !f.is_active).length;
    const webCount = list.filter(f => Array.isArray(f.enabled_for) && f.enabled_for.includes('web')).length;
    const appCount = list.filter(f => Array.isArray(f.enabled_for) && f.enabled_for.includes('app')).length;
    const crossPlatformCount = list.filter(
      f => Array.isArray(f.enabled_for) && f.enabled_for.includes('web') && f.enabled_for.includes('app')
    ).length;
    const activeRate = totalCount > 0 ? Math.round((activeCount / totalCount) * 100) : 0;

    return {
      totalCount,
      activeCount,
      inactiveCount,
      webCount,
      appCount,
      crossPlatformCount,
      activeRate,
    };
  });

  // Filtered and Sorted Flags
  const filteredFlags = computed(() => {
    let list = [...flags.value];

    // Status Filter
    if (statusFilter.value === 'active') {
      list = list.filter(f => f.is_active);
    } else if (statusFilter.value === 'inactive') {
      list = list.filter(f => !f.is_active);
    }

    // Platform Filter
    if (platformFilter.value === 'web') {
      list = list.filter(f => Array.isArray(f.enabled_for) && f.enabled_for.includes('web'));
    } else if (platformFilter.value === 'app') {
      list = list.filter(f => Array.isArray(f.enabled_for) && f.enabled_for.includes('app'));
    } else if (platformFilter.value === 'both') {
      list = list.filter(
        f => Array.isArray(f.enabled_for) && f.enabled_for.includes('web') && f.enabled_for.includes('app')
      );
    }

    // Search Query
    if (search.value.trim()) {
      const q = search.value.toLowerCase().trim();
      list = list.filter(f => {
        const keyMatch = f.key?.toLowerCase().includes(q);
        const nameMatch = f.name?.toLowerCase().includes(q);
        const descMatch = f.description?.toLowerCase().includes(q);
        return keyMatch || nameMatch || descMatch;
      });
    }

    // Sorting
    if (sortOrder.value === 'newest') {
      list.sort((a, b) => new Date(b.created_at || 0) - new Date(a.created_at || 0) || (b.id - a.id));
    } else if (sortOrder.value === 'oldest') {
      list.sort((a, b) => new Date(a.created_at || 0) - new Date(b.created_at || 0) || (a.id - b.id));
    } else if (sortOrder.value === 'name-asc') {
      list.sort((a, b) => (a.name || '').localeCompare(b.name || ''));
    } else if (sortOrder.value === 'rollout-desc') {
      list.sort((a, b) => (b.rollout_percentage ?? 100) - (a.rollout_percentage ?? 100));
    }

    return list;
  });

  // API Actions
  const fetchFlags = async (silent = false) => {
    if (silent) {
      isRefreshing.value = true;
    } else {
      loading.value = true;
    }

    try {
      const res = await client.get('/admin/feature-flags');
      const data = res.data?.data;
      if (Array.isArray(data)) {
        flags.value = data;
      } else if (data && Array.isArray(data.data)) {
        flags.value = data.data;
      } else {
        flags.value = [];
      }
    } catch (err) {
      console.error('[AdminFeatureFlags] Failed to fetch flags:', err);
      toast.error('Failed to load feature flags');
    } finally {
      loading.value = false;
      isRefreshing.value = false;
    }
  };

  const openCreateModal = () => {
    editingFlag.value = null;
    form.value = {
      key: '',
      name: '',
      description: '',
      enabled_for: ['web', 'app'],
      rollout_percentage: 100,
      is_active: true,
    };
    modalOpen.value = true;
  };

  const openEditModal = (flag) => {
    editingFlag.value = flag;
    form.value = {
      key: flag.key,
      name: flag.name,
      description: flag.description || '',
      enabled_for: Array.isArray(flag.enabled_for) ? [...flag.enabled_for] : ['web', 'app'],
      rollout_percentage: flag.rollout_percentage ?? 100,
      is_active: flag.is_active ?? true,
    };
    modalOpen.value = true;
  };

  const closeModal = () => {
    modalOpen.value = false;
    editingFlag.value = null;
  };

  const togglePlatform = (platform) => {
    const list = form.value.enabled_for;
    const idx = list.indexOf(platform);
    if (idx > -1) {
      if (list.length > 1) {
        list.splice(idx, 1);
      } else {
        toast.info('At least one platform must remain selected');
      }
    } else {
      list.push(platform);
    }
  };

  const saveFlag = async () => {
    if (!form.value.key || !form.value.name || !form.value.enabled_for.length) {
      toast.error('Key, name, and at least one platform are required');
      return;
    }

    saving.value = true;
    try {
      if (editingFlag.value) {
        const res = await client.put(`/admin/feature-flags/${editingFlag.value.id}`, form.value);
        const updated = res.data?.data;
        if (updated) {
          const idx = flags.value.findIndex(f => f.id === editingFlag.value.id);
          if (idx !== -1) flags.value[idx] = updated;
        }
        toast.success(`Feature flag "${form.value.name}" updated`);
      } else {
        const res = await client.post('/admin/feature-flags', form.value);
        const created = res.data?.data;
        if (created) {
          flags.value.unshift(created);
        } else {
          await fetchFlags(true);
        }
        toast.success(`Feature flag "${form.value.name}" created`);
      }
      closeModal();
    } catch (err) {
      console.error('[AdminFeatureFlags] Failed to save flag:', err);
      const msg = err.response?.data?.message || 'Failed to save feature flag';
      toast.error(msg);
    } finally {
      saving.value = false;
    }
  };

  const toggleFlag = async (flag) => {
    togglingId.value = flag.id;
    const previousState = flag.is_active;

    // Optimistic toggle
    flag.is_active = !previousState;

    try {
      await client.patch(`/admin/feature-flags/${flag.id}/toggle`);
      toast.success(`${flag.key} is now ${flag.is_active ? 'active' : 'inactive'}`);
    } catch (err) {
      console.error('[AdminFeatureFlags] Failed to toggle flag:', err);
      flag.is_active = previousState; // Revert
      toast.error(`Failed to toggle flag ${flag.key}`);
    } finally {
      togglingId.value = null;
    }
  };

  const deleteFlag = async (flag) => {
    const confirmed = await confirm({
      title: 'Delete Feature Flag',
      message: `Are you sure you want to delete "${flag.name}" (${flag.key})? Active clients may immediately fall back to default behavior.`,
      confirmText: 'Delete Flag',
      confirmVariant: 'danger',
    });

    if (!confirmed) return;

    try {
      await client.delete(`/admin/feature-flags/${flag.id}`);
      flags.value = flags.value.filter(f => f.id !== flag.id);
      toast.success(`Feature flag "${flag.name}" removed`);
    } catch (err) {
      console.error('[AdminFeatureFlags] Failed to delete flag:', err);
      toast.error('Failed to delete feature flag');
    }
  };

  const resetFilters = () => {
    search.value = '';
    statusFilter.value = 'all';
    platformFilter.value = 'all';
    sortOrder.value = 'newest';
  };

  return {
    flags,
    loading,
    isRefreshing,
    saving,
    togglingId,
    search,
    statusFilter,
    platformFilter,
    sortOrder,
    modalOpen,
    editingFlag,
    form,
    stats,
    filteredFlags,
    fetchFlags,
    openCreateModal,
    openEditModal,
    closeModal,
    togglePlatform,
    saveFlag,
    toggleFlag,
    deleteFlag,
    resetFilters,
  };
}

