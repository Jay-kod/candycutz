import { ref, computed } from 'vue';
import { adminApi } from '@/shared/api/old_adminApi';
import { useToast } from '@/core/composables/useToast';
import { TrophyIcon, SparklesIcon, ClockIcon } from '@heroicons/vue/24/outline';

export function useCustomers() {
  const toast = useToast();

  const customers = ref([]);
  const loading = ref(true);
  const isRefreshing = ref(false);
  const search = ref('');
  const currentFilter = ref('all');
  const channelFilter = ref('all');
  const sortOrder = ref('default');

  const profilePanel = ref(null);
  const profileLoading = ref(false);
  const profileData = ref(null);

  const filteredCustomers = computed(() => {
    const list = customers.value || [];
    if (!Array.isArray(list)) return [];
    
    let result = [...list];
    
    // Search query
    if (search.value) {
      const q = search.value.toLowerCase().trim();
      result = result.filter(c => {
        const name = c.name || '';
        const email = c.email || '';
        const phone = c.phone || '';
        return name.toLowerCase().includes(q) || 
               email.toLowerCase().includes(q) || 
               phone.includes(q);
      });
    }

    // Channel filter
    if (channelFilter.value !== 'all') {
      result = result.filter(c => {
        const prov = (c.auth_provider || 'local').toLowerCase();
        const reg = (c.registered_via || '').toLowerCase();
        if (channelFilter.value === 'app') {
          return reg.includes('app') || prov === 'app';
        } else if (channelFilter.value === 'social') {
          return prov === 'google' || prov === 'apple' || prov === 'facebook';
        } else if (channelFilter.value === 'web') {
          return prov === 'local' || reg.includes('registration') || reg.includes('web');
        }
        return true;
      });
    }
    
    // Tab filters
    if (currentFilter.value === 'top_spenders') {
      result = result.filter(c => (c.total_spent || 0) > 0);
      result.sort((a, b) => (b.total_spent || 0) - (a.total_spent || 0));
    } else if (currentFilter.value === 'most_active') {
      result = result.filter(c => (c.total_bookings || 0) > 0);
      result.sort((a, b) => (b.total_bookings || 0) - (a.total_bookings || 0));
    } else if (currentFilter.value === 'newest') {
      result.sort((a, b) => new Date(b.created_at || 0) - new Date(a.created_at || 0));
    }

    // Explicit sort dropdown
    if (sortOrder.value === 'highest_spent') {
      result.sort((a, b) => (b.total_spent || 0) - (a.total_spent || 0));
    } else if (sortOrder.value === 'most_bookings') {
      result.sort((a, b) => (b.total_bookings || 0) - (a.total_bookings || 0));
    } else if (sortOrder.value === 'newest') {
      result.sort((a, b) => new Date(b.created_at || 0) - new Date(a.created_at || 0));
    } else if (sortOrder.value === 'name_asc') {
      result.sort((a, b) => (a.name || '').localeCompare(b.name || ''));
    }
    
    return result;
  });

  const tabs = computed(() => {
    const list = customers.value || [];
    return [
      { label: 'All Clients', value: 'all', count: list.length },
      { label: 'Top Spenders', value: 'top_spenders', count: list.filter(c => (c.total_spent || 0) > 0).length, icon: TrophyIcon },
      { label: 'Most Active', value: 'most_active', count: list.filter(c => (c.total_bookings || 0) > 0).length, icon: SparklesIcon },
      { label: 'Newest', value: 'newest', icon: ClockIcon }
    ];
  });

  function resetFilters() {
    search.value = '';
    currentFilter.value = 'all';
    channelFilter.value = 'all';
    sortOrder.value = 'default';
  }

  async function loadCustomers(silent = false) {
    if (!silent) {
      loading.value = true;
    } else {
      isRefreshing.value = true;
    }

    try {
      const response = await adminApi.customers();
      customers.value = response.data?.data || [];
    } catch (err) {
      if (!silent) toast.error('Failed to load customers');
    } finally {
      loading.value = false;
      isRefreshing.value = false;
    }
  }

  async function viewProfile(customer) {
    profilePanel.value = customer;
    profileLoading.value = true;
    profileData.value = null;
    try {
      const response = await adminApi.customerProfile(customer.id);
      const data = response.data?.data;
      // Handle either { customer: {...}, appointments: [...] } or direct flat customer object
      profileData.value = data?.customer ? {
        ...data.customer,
        total_bookings: data.total_bookings,
        total_spent: data.total_spent,
        appointments: data.appointments
      } : data;
    } catch (err) {
      toast.error('Failed to load customer profile');
      profilePanel.value = null;
    } finally {
      profileLoading.value = false;
    }
  }
  
  function closeProfile() {
    profilePanel.value = null;
  }

  return {
    customers,
    loading,
    isRefreshing,
    search,
    currentFilter,
    channelFilter,
    sortOrder,
    filteredCustomers,
    tabs,
    resetFilters,
    loadCustomers,
    profilePanel,
    profileLoading,
    profileData,
    viewProfile,
    closeProfile
  };
}
