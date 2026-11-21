import { ref, computed, onMounted } from 'vue';
import api from '@/shared/api/client';
import { useAuth } from '@/modules/auth/composables/useAuth';
import { useToast } from '@/core/composables/useToast';
import { useConfirm } from '@/core/composables/useConfirm';

export function useBarberServices() {
  const toast = useToast();
  const { confirm } = useConfirm();
  const { user } = useAuth();

  const loading = ref(true);
  const services = ref([]);
  const activeCategory = ref('all');
  const sourceFilter = ref('all'); // 'all', 'admin', 'mine', 'pending'

  const categories = computed(() => {
    const cats = new Set(services.value.map((s) => s.category_name || s.category?.name || 'General'));
    return [...cats].sort();
  });

  const adminServicesCount = computed(() => {
    return services.value.filter((s) => !s.barber_id).length;
  });

  const myServicesCount = computed(() => {
    return services.value.filter((s) => !!s.barber_id).length;
  });

  const pendingServicesCount = computed(() => {
    return services.value.filter((s) => s.approval_status === 'pending').length;
  });

  const filteredServices = computed(() => {
    let list = services.value;

    if (sourceFilter.value === 'admin') {
      list = list.filter((s) => !s.barber_id);
    } else if (sourceFilter.value === 'mine') {
      list = list.filter((s) => !!s.barber_id);
    } else if (sourceFilter.value === 'pending') {
      list = list.filter((s) => s.approval_status === 'pending');
    }

    if (activeCategory.value !== 'all') {
      list = list.filter(
        (s) => (s.category_name || s.category?.name || 'General') === activeCategory.value
      );
    }

    return list;
  });

  async function fetchServices() {
    loading.value = true;
    try {
      const res = await api.get('/services');
      const data = res.data?.data || [];
      services.value = Array.isArray(data) ? data : (data.items || []);
    } catch {
      try {
        const fallback = await api.get('/barber/services');
        const fallbackData = fallback.data?.data || [];
        services.value = Array.isArray(fallbackData) ? fallbackData : (fallbackData.items || []);
      } catch {
        toast.error('Failed to load services catalogue');
        services.value = [];
      }
    } finally {
      loading.value = false;
    }
  }

  function isMyService(service) {
    if (!service?.barber_id) return false;
    const currentBarberId = user.value?.barber_id || user.value?.id;
    return service.barber_id === currentBarberId || String(service.barber_id) === String(currentBarberId);
  }

  async function deleteService(service) {
    if (!service.barber_id) {
      toast.error('Official salon services cannot be deleted by barbers.');
      return;
    }

    const ok = await confirm(
      'Delete Service',
      `Are you sure you want to delete "${service.name}"? This action cannot be undone.`
    );
    if (!ok) return;

    try {
      await api.delete(`/services/${service.id}`);
      services.value = services.value.filter((s) => s.id !== service.id);
      toast.success('Service deleted successfully');
    } catch (err) {
      const msg = err.response?.data?.message || 'Failed to delete service';
      toast.error(msg);
    }
  }

  onMounted(() => {
    fetchServices();
  });

  return {
    loading,
    services,
    activeCategory,
    sourceFilter,
    categories,
    filteredServices,
    adminServicesCount,
    myServicesCount,
    pendingServicesCount,
    isMyService,
    fetchServices,
    deleteService,
  };
}
