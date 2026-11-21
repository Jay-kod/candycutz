import { ref, computed, onMounted } from 'vue';
import api from '@/shared/api/client';
import { useToast } from '@/core/composables/useToast';

export function useBarberWalkIn() {
  const toast = useToast();

  const loading = ref(true);
  const submitting = ref(false);
  const servicesList = ref([]);
  const recentWalkIns = ref([]);

  const getInitialForm = () => ({
    customer_name: '',
    customer_phone: '',
    customer_email: '',
    service_id: '',
    appointment_date: new Date().toISOString().split('T')[0],
    appointment_time: new Date().toTimeString().slice(0, 5),
    payment_method: 'cash',
  });

  const form = ref(getInitialForm());

  const currentDateFormatted = computed(() => {
    return new Date().toLocaleDateString('en-US', {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    });
  });

  const selectedService = computed(() => {
    if (!form.value.service_id) return null;
    return servicesList.value.find((s) => s.id === form.value.service_id) || null;
  });

  const selectedServiceName = computed(() => {
    return selectedService.value?.name || '';
  });

  const selectedServicePrice = computed(() => {
    return selectedService.value ? Number(selectedService.value.price) : 0;
  });

  const isFormValid = computed(() => {
    return Boolean(
      form.value.customer_name?.trim() &&
      form.value.customer_phone?.trim() &&
      form.value.service_id
    );
  });

  async function loadServices() {
    try {
      const res = await api.get('/barber/services');
      servicesList.value = res.data?.data || [];
    } catch {
      try {
        const fallback = await api.get('/services');
        servicesList.value = fallback.data?.data || [];
      } catch {
        servicesList.value = [];
      }
    }
  }

  async function loadRecentWalkIns() {
    try {
      const res = await api.get('/barber/appointments');
      const payload = res.data?.data || [];
      const items = Array.isArray(payload) ? payload : (payload.items || []);

      recentWalkIns.value = items
        .filter((a) => a.booking_type === 'walk_in' || a.source === 'walk_in' || a.is_walk_in)
        .sort((a, b) => b.id - a.id)
        .slice(0, 5);
    } catch {
      recentWalkIns.value = [];
    }
  }

  async function init() {
    loading.value = true;
    try {
      await Promise.all([loadServices(), loadRecentWalkIns()]);
    } finally {
      loading.value = false;
    }
  }

  function resetForm() {
    form.value = getInitialForm();
  }

  async function submitWalkIn() {
    if (!isFormValid.value) {
      toast.error('Please fill in customer name, phone number, and select a service');
      return;
    }

    submitting.value = true;
    try {
      const payload = {
        customer_name: form.value.customer_name.trim(),
        client_name: form.value.customer_name.trim(),
        customer_phone: form.value.customer_phone.trim(),
        client_phone: form.value.customer_phone.trim(),
        customer_email: form.value.customer_email?.trim() || null,
        service_id: Number(form.value.service_id),
        appointment_date: form.value.appointment_date,
        appointment_time: form.value.appointment_time,
        payment_method: form.value.payment_method,
        take_immediately: true,
      };

      await api.post('/barber/appointments/walk-in', payload);
      toast.success('Walk-in registered successfully!');
      resetForm();
      await loadRecentWalkIns();
    } catch (err) {
      const msg = err.response?.data?.message || err.response?.data?.error || 'Failed to register walk-in';
      toast.error(msg);
    } finally {
      submitting.value = false;
    }
  }

  onMounted(() => {
    init();
  });

  return {
    loading,
    submitting,
    form,
    servicesList,
    recentWalkIns,
    currentDateFormatted,
    selectedService,
    selectedServiceName,
    selectedServicePrice,
    isFormValid,
    init,
    resetForm,
    submitWalkIn,
  };
}
