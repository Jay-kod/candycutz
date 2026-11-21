import { ref } from 'vue';
import { adminApi } from '@/shared/api/old_adminApi';
import { useToast } from '@/core/composables/useToast';

export function useWalkIn() {
  const toast = useToast();
  
  const showWalkInModal = ref(false);
  const walkInSubmitting = ref(false);
  const servicesList = ref([]);
  const barbersList = ref([]);
  const walkInForm = ref({
    customer_name: '',
    customer_phone: '',
    customer_email: '',
    barber_id: '',
    service_id: '',
    appointment_date: new Date().toISOString().split('T')[0],
    appointment_time: new Date().toTimeString().slice(0, 5),
    payment_method: 'cash'
  });

  const loadServicesAndBarbers = async () => {
    try {
      const [sRes, bRes] = await Promise.all([
        adminApi.services(),
        adminApi.barbers()
      ]);
      servicesList.value = sRes.data.data || [];
      barbersList.value = (bRes.data.data || []).map(b => ({ id: b.id, name: b.name || b.user?.name || 'Barber' }));
    } catch (e) {
      // silently fail
    }
  };

  const submitWalkIn = async (onSuccess) => {
    if (!walkInForm.value.customer_name || !walkInForm.value.customer_phone || !walkInForm.value.service_id || !walkInForm.value.barber_id) {
      toast.error('Please fill in all required fields');
      return;
    }
    walkInSubmitting.value = true;
    try {
      await adminApi.createWalkIn(walkInForm.value);
      toast.success('Walk-in appointment created successfully!');
      showWalkInModal.value = false;
      walkInForm.value = {
        customer_name: '',
        customer_phone: '',
        customer_email: '',
        barber_id: '',
        service_id: '',
        appointment_date: new Date().toISOString().split('T')[0],
        appointment_time: new Date().toTimeString().slice(0, 5),
        payment_method: 'cash'
      };
      if (onSuccess) await onSuccess();
    } catch (err) {
      toast.error(err.response?.data?.error || 'Failed to create walk-in appointment');
    } finally {
      walkInSubmitting.value = false;
    }
  };

  return {
    showWalkInModal,
    walkInSubmitting,
    servicesList,
    barbersList,
    walkInForm,
    loadServicesAndBarbers,
    submitWalkIn
  };
}
