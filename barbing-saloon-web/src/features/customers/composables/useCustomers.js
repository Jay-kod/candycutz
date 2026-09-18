import { ref, computed } from 'vue';
import { adminApi } from '@/shared/api/old_adminApi';
import { useToast } from '@/core/composables/useToast';
import { TrophyIcon, SparklesIcon } from '@heroicons/vue/24/outline';

export function useCustomers() {
  const toast = useToast();

  const customers = ref([]);
  const loading = ref(true);
  const search = ref('');
  const currentFilter = ref('all');

  const profilePanel = ref(null);
  const profileLoading = ref(false);
  const profileData = ref(null);

  const filteredCustomers = computed(() => {
    const list = customers.value || [];
    if (!Array.isArray(list)) return [];
    
    let result = [...list];
    
    // Apply Search
    if (search.value) {
      const q = search.value.toLowerCase();
      result = result.filter(c => {
        const name = c.name || '';
        const email = c.email || '';
        const phone = c.phone || '';
        return name.toLowerCase().includes(q) || 
               email.toLowerCase().includes(q) || 
               phone.includes(q);
      });
    }
    
    // Apply Sorting Filters
    if (currentFilter.value === 'top_spenders') {
      result.sort((a, b) => (b.total_spent || 0) - (a.total_spent || 0));
    } else if (currentFilter.value === 'most_active') {
      result.sort((a, b) => (b.total_bookings || 0) - (a.total_bookings || 0));
    } else if (currentFilter.value === 'newest') {
      result.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
    }
    
    return result;
  });

  const tabs = computed(() => {
    return [
      { label: 'All Customers', value: 'all', count: customers.value.length },
      { label: 'Top Spenders', value: 'top_spenders', icon: TrophyIcon },
      { label: 'Most Active', value: 'most_active', icon: SparklesIcon },
      { label: 'Newest', value: 'newest' }
    ];
  });

  function resetFilters() {
    search.value = '';
    currentFilter.value = 'all';
  }

  async function loadCustomers() {
    loading.value = true;
    try {
      const response = await adminApi.customers();
      customers.value = response.data.data || [];
    } catch (err) {
      console.error(err);
      toast.error('Failed to load customers');
    } finally {
      loading.value = false;
    }
  }

  async function viewProfile(customer) {
    profilePanel.value = customer;
    profileLoading.value = true;
    profileData.value = null;
    try {
      const response = await adminApi.customerProfile(customer.id);
      profileData.value = response.data.data;
    } catch (err) {
      console.error(err);
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
    search,
    currentFilter,
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
