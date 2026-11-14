import { ref, computed } from 'vue';
import { adminApi } from '@/shared/api/old_adminApi';
import { useToast } from '@/core/composables/useToast';
import { useConfirm } from '@/core/composables/useConfirm';
import { TrophyIcon, SparklesIcon, ClockIcon } from '@heroicons/vue/24/outline';

export function useCustomers() {
  const toast = useToast();
  const { confirm } = useConfirm();

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

  // Modal & Action states
  const isCustomerModalOpen = ref(false);
  const customerModalMode = ref('create'); // 'create' | 'edit'
  const customerModalData = ref(null);
  const customerModalLoading = ref(false);

  const isPasswordModalOpen = ref(false);
  const passwordModalCustomer = ref(null);
  const passwordModalLoading = ref(false);

  const actionInProgress = ref(null);

  const filteredCustomers = computed(() => {
    const list = customers.value || [];
    if (!Array.isArray(list)) return [];
    
    let result = [...list];
    
    // Search query
    if (search.value) {
      const q = search.value.toLowerCase().trim();
      result = result.filter(c => (c.name || '').toLowerCase().includes(q) || (c.email || '').toLowerCase().includes(q) || (c.phone || '').includes(q));
    }

    // Channel filter
    if (channelFilter.value !== 'all') {
      result = result.filter(c => {
        const prov = (c.auth_provider || 'local').toLowerCase();
        const reg = (c.registered_via || '').toLowerCase();
        if (channelFilter.value === 'app') return reg.includes('app') || prov === 'app';
        if (channelFilter.value === 'social') return ['google', 'apple', 'facebook'].includes(prov);
        if (channelFilter.value === 'web') return prov === 'local' || reg.includes('registration') || reg.includes('web');
        return true;
      });
    }
    
    // Tab filters & Sort
    if (currentFilter.value === 'top_spenders' || sortOrder.value === 'highest_spent') {
      if (currentFilter.value === 'top_spenders') result = result.filter(c => (c.total_spent || 0) > 0);
      result.sort((a, b) => (b.total_spent || 0) - (a.total_spent || 0));
    } else if (currentFilter.value === 'most_active' || sortOrder.value === 'most_bookings') {
      if (currentFilter.value === 'most_active') result = result.filter(c => (c.total_bookings || 0) > 0);
      result.sort((a, b) => (b.total_bookings || 0) - (a.total_bookings || 0));
    } else if (currentFilter.value === 'newest' || sortOrder.value === 'newest') {
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
    if (!silent) loading.value = true;
    else isRefreshing.value = true;
    try {
      const response = await adminApi.customers();
      customers.value = response.data?.data || [];
    } catch {
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
      profileData.value = data?.customer ? {
        ...data.customer,
        total_bookings: data.total_bookings,
        total_spent: data.total_spent,
        appointments: data.appointments
      } : data;
    } catch {
      toast.error('Failed to load customer profile');
      profilePanel.value = null;
    } finally {
      profileLoading.value = false;
    }
  }
  
  function closeProfile() {
    profilePanel.value = null;
  }

  function openCreateModal() {
    customerModalMode.value = 'create';
    customerModalData.value = { name: '', email: '', phone: '', password: '' };
    isCustomerModalOpen.value = true;
  }

  function openEditModal(customer) {
    customerModalMode.value = 'edit';
    customerModalData.value = {
      id: customer.id,
      name: customer.name || '',
      email: customer.email || '',
      phone: customer.phone === 'N/A' ? '' : (customer.phone || '')
    };
    isCustomerModalOpen.value = true;
  }

  function closeCustomerModal() {
    isCustomerModalOpen.value = false;
    customerModalData.value = null;
  }

  async function saveCustomer(formData) {
    customerModalLoading.value = true;
    try {
      if (customerModalMode.value === 'create') {
        await adminApi.createCustomer(formData);
        toast.success(`Customer "${formData.name}" created successfully`);
      } else {
        await adminApi.updateCustomer(customerModalData.value.id, formData);
        toast.success(`Customer "${formData.name}" updated successfully`);
        if (profilePanel.value && profilePanel.value.id === customerModalData.value.id) {
          await viewProfile({ id: customerModalData.value.id });
        }
      }
      closeCustomerModal();
      await loadCustomers(true);
    } catch (err) {
      const msg = err.response?.data?.message || err.message || 'Failed to save customer';
      toast.error(msg);
    } finally {
      customerModalLoading.value = false;
    }
  }

  function openPasswordModal(customer) {
    passwordModalCustomer.value = customer;
    isPasswordModalOpen.value = true;
  }

  function closePasswordModal() {
    isPasswordModalOpen.value = false;
    passwordModalCustomer.value = null;
  }

  async function savePassword(newPassword) {
    if (!passwordModalCustomer.value) return;
    passwordModalLoading.value = true;
    try {
      await adminApi.resetCustomerPassword(passwordModalCustomer.value.id, { password: newPassword });
      toast.success(`Password reset successfully for ${passwordModalCustomer.value.name}`);
      closePasswordModal();
    } catch (err) {
      const msg = err.response?.data?.message || err.message || 'Failed to reset password';
      toast.error(msg);
    } finally {
      passwordModalLoading.value = false;
    }
  }

  async function toggleStatus(customer) {
    const willSuspend = customer.is_active;
    if (willSuspend) {
      const ok = await confirm(
        'Suspend Customer Account',
        `Are you sure you want to suspend ${customer.name}? They will be logged out and cannot book appointments until reactivated.`
      );
      if (!ok) return;
    }

    actionInProgress.value = customer.id;
    try {
      const res = await adminApi.updateCustomerStatus(customer.id, { is_active: !willSuspend });
      const updated = res.data?.data;
      customer.is_active = updated?.is_active ?? !willSuspend;
      customer.status = updated?.status ?? (customer.is_active ? 'active' : 'suspended');
      if (profileData.value && (profileData.value.id === customer.id || profilePanel.value?.id === customer.id)) {
        profileData.value.is_active = customer.is_active;
        profileData.value.status = customer.status;
      }
      toast.success(`${customer.name} is now ${customer.is_active ? 'Active' : 'Suspended'}`);
    } catch (err) {
      const msg = err.response?.data?.message || err.message || 'Failed to update customer status';
      toast.error(msg);
      await loadCustomers(true);
    } finally {
      actionInProgress.value = null;
    }
  }

  async function deleteCustomer(customer) {
    const ok = await confirm(
      'Delete Customer',
      `Are you sure you want to delete ${customer.name}? Their login account will be soft-deleted and all active sessions revoked. Historical appointment records will be preserved.`
    );
    if (!ok) return;

    actionInProgress.value = customer.id;
    try {
      await adminApi.deleteCustomer(customer.id);
      toast.success(`Customer ${customer.name} was deleted successfully`);
      if (profilePanel.value && profilePanel.value.id === customer.id) {
        closeProfile();
      }
      await loadCustomers(true);
    } catch (err) {
      const msg = err.response?.data?.message || err.message || 'Failed to delete customer';
      toast.error(msg);
    } finally {
      actionInProgress.value = null;
    }
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
    closeProfile,
    // Action states & handlers
    isCustomerModalOpen,
    customerModalMode,
    customerModalData,
    customerModalLoading,
    openCreateModal,
    openEditModal,
    closeCustomerModal,
    saveCustomer,
    isPasswordModalOpen,
    passwordModalCustomer,
    passwordModalLoading,
    openPasswordModal,
    closePasswordModal,
    savePassword,
    toggleStatus,
    deleteCustomer,
    actionInProgress
  };
}
