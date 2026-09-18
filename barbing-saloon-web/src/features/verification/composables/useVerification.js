import { ref } from 'vue';
import client from '@/shared/api/client';
import { useToast } from '@/core/composables/useToast';

export function useVerification() {
  const toast = useToast();
  
  const loading = ref(true);
  const isVerifying = ref(false);
  const verifications = ref([]);
  const stats = ref({ total: 0, verified_today: 0, pending: 0, expired: 0 });
  const currentFilter = ref('all');
  const search = ref('');
  const quickCode = ref('');
  
  let searchTimeout = null;

  async function loadData() {
    loading.value = true;
    try {
      const [statsRes, listRes] = await Promise.all([
        client.get('/admin/verifications/stats'),
        client.get('/admin/verifications', { params: { filter: currentFilter.value, search: search.value } })
      ]);
      stats.value = statsRes.data.data;
      verifications.value = listRes.data.data;
    } catch (err) {
      toast.error('Failed to load verification data');
    } finally {
      loading.value = false;
    }
  }

  function handleSearch() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(loadData, 300);
  }

  function setFilter(filter) {
    currentFilter.value = filter;
    loadData();
  }

  function quickVerify() {
    if (!quickCode.value.trim()) return;
    search.value = quickCode.value.trim();
    currentFilter.value = 'all';
    loadData();
  }

  async function submitVerification(id) {
    isVerifying.value = true;
    try {
      await client.patch(`/admin/verifications/${id}/verify`);
      toast.success('Appointment manually verified');
      await loadData();
      return true;
    } catch (err) {
      toast.error(err.response?.data?.error || 'Failed to verify appointment');
      return false;
    } finally {
      isVerifying.value = false;
    }
  }

  return {
    loading,
    isVerifying,
    verifications,
    stats,
    currentFilter,
    search,
    quickCode,
    loadData,
    handleSearch,
    setFilter,
    quickVerify,
    submitVerification
  };
}
