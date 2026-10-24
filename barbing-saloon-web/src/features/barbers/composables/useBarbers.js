import { ref, computed } from 'vue';
import { adminApi } from '@/shared/api/old_adminApi';
import { useToast } from '@/core/composables/useToast';
import { useConfirm } from '@/core/composables/useConfirm';

export function useBarbers() {
  const toast = useToast();
  const { confirm } = useConfirm();

  const barbers = ref([]);
  const loading = ref(true);
  const isRefreshing = ref(false);
  const search = ref('');
  const currentFilter = ref('all');
  const specialtyFilter = ref('all');
  const sortOrder = ref('rating_desc');
  const selectedBarber = ref(null);

  const specialtiesList = computed(() => {
    const set = new Set();
    (barbers.value || []).forEach(b => {
      const specs = Array.isArray(b.specialties) ? b.specialties : [];
      specs.forEach(s => set.add(s));
    });
    return Array.from(set).sort();
  });

  const filteredBarbers = computed(() => {
    const list = barbers.value || [];
    if (!Array.isArray(list)) return [];

    let result = [...list];

    // Search query
    if (search.value) {
      const q = search.value.toLowerCase().trim();
      result = result.filter(b => {
        const name = (b.name || '').toLowerCase();
        const username = (b.username || '').toLowerCase();
        const email = (b.email || '').toLowerCase();
        const phone = (b.phone || '').toLowerCase();
        const specs = (b.specialties || []).join(' ').toLowerCase();
        return name.includes(q) || username.includes(q) || email.includes(q) || phone.includes(q) || specs.includes(q);
      });
    }

    // Status / Chair filter
    if (currentFilter.value !== 'all') {
      if (currentFilter.value === 'free_chair') {
        result = result.filter(b => b.chair_status === 'free' || (b.is_available && b.status === 'active'));
      } else {
        result = result.filter(b => b.status === currentFilter.value);
      }
    }

    // Specialty filter
    if (specialtyFilter.value !== 'all') {
      result = result.filter(b => (b.specialties || []).includes(specialtyFilter.value));
    }

    // Sort order
    if (sortOrder.value === 'rating_desc') {
      result.sort((a, b) => (Number(b.rating) || 5.0) - (Number(a.rating) || 5.0));
    } else if (sortOrder.value === 'experience_desc') {
      result.sort((a, b) => (Number(b.experience_years) || 0) - (Number(a.experience_years) || 0));
    } else if (sortOrder.value === 'name_asc') {
      result.sort((a, b) => (a.name || '').localeCompare(b.name || ''));
    } else if (sortOrder.value === 'newest') {
      result.sort((a, b) => (b.id || 0) - (a.id || 0));
    }

    return result;
  });

  const tabs = computed(() => {
    const list = barbers.value || [];
    return [
      { label: 'All Stylists', value: 'all', count: list.length },
      { label: 'Active', value: 'active', count: list.filter(b => b.status === 'active').length },
      { label: 'Free Chair', value: 'free_chair', count: list.filter(b => b.chair_status === 'free' || (b.is_available && b.status === 'active')).length },
      { label: 'Pending Approval', value: 'pending_approval', count: list.filter(b => b.status === 'pending_approval').length },
      { label: 'On Leave', value: 'on_leave', count: list.filter(b => b.status === 'on_leave').length }
    ];
  });

  function resetFilters() {
    search.value = '';
    currentFilter.value = 'all';
    specialtyFilter.value = 'all';
    sortOrder.value = 'rating_desc';
  }

  async function loadBarbers(silent = false) {
    if (!silent) {
      loading.value = true;
    } else {
      isRefreshing.value = true;
    }

    try {
      const res = await adminApi.barbers();
      barbers.value = res.data?.data || [];
    } catch (error) {
      if (!silent) toast.error('Failed to load barbers roster');
    } finally {
      loading.value = false;
      isRefreshing.value = false;
    }
  }

  async function deleteBarber(barber) {
    const ok = await confirm(
      'Delete Barber',
      `Are you sure you want to permanently remove ${barber.name}? Their login account will also be deleted. This action cannot be undone.`
    );

    if (!ok) return;

    try {
      await adminApi.deleteBarber(barber.id);
      toast.success('Barber removed successfully');
      await loadBarbers(true);
    } catch (error) {
      const errorMsg =
        error.details?.field_errors?.barber?.[0] ||
        error.response?.data?.errors?.barber?.[0] ||
        (typeof error.response?.data?.error === 'string' ? error.response?.data?.error : null) ||
        error.message ||
        error.response?.data?.message ||
        'Failed to delete barber';
      toast.error(errorMsg);
    }
  }

  async function changeStatus(barber, newStatus) {
    const statusLabels = {
      active: 'Active',
      pending_approval: 'Pending Approval',
      suspended: 'Suspended',
      on_leave: 'On Leave'
    };

    try {
      await adminApi.updateBarberStatus(barber.id, newStatus);
      barber.status = newStatus;
      toast.success(`${barber.name} status updated to ${statusLabels[newStatus] || newStatus}`);
    } catch (error) {
      toast.error('Failed to update barber status');
      await loadBarbers(true);
    }
  }

  return {
    barbers,
    loading,
    isRefreshing,
    search,
    currentFilter,
    specialtyFilter,
    sortOrder,
    selectedBarber,
    specialtiesList,
    filteredBarbers,
    tabs,
    resetFilters,
    loadBarbers,
    deleteBarber,
    changeStatus
  };
}
