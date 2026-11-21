import { ref } from 'vue';
import api from '@/shared/api/client';
import { useToast } from '@/core/composables/useToast';
import { useConfirm } from '@/core/composables/useConfirm';

export function useBarberAppointments() {
  const toast = useToast();
  const { confirm } = useConfirm();

  const appointments = ref({ data: [] });
  const loading = ref(true);

  const loadAppointments = async () => {
    loading.value = true;
    try {
      const response = await api.get('/barber/appointments');
      const data = response.data?.data || [];
      appointments.value = {
        data: Array.isArray(data) ? data : (data.items || []),
      };
    } catch {
      toast.error('Failed to load appointments');
    } finally {
      loading.value = false;
    }
  };

  const approve = async (id) => {
    if (await confirm('Approve Booking', 'Are you sure you want to approve this booking manually?')) {
      try {
        await api.patch(`/barber/appointments/${id}/approve`);
        await loadAppointments();
        toast.success('Booking approved and confirmed!');
      } catch {
        toast.error('Failed to approve booking');
      }
    }
  };

  const cancel = async (id) => {
    if (await confirm('Cancel Booking', 'Are you sure you want to cancel this booking? This action cannot be undone.')) {
      try {
        await api.patch(`/barber/appointments/${id}/cancel`);
        await loadAppointments();
        toast.success('Booking cancelled');
      } catch {
        toast.error('Failed to cancel booking');
      }
    }
  };

  const complete = async (id, verificationCode = '') => {
    try {
      await api.patch(`/barber/appointments/${id}/complete`, { verification_code: verificationCode });
      await loadAppointments();
      toast.success('Booking verified and marked as completed!');
    } catch (err) {
      toast.error(err.response?.data?.message || err.response?.data?.error || 'Failed to verify booking');
      throw err;
    }
  };

  const markNoShow = async (id) => {
    try {
      await api.patch(`/barber/appointments/${id}/no-show`);
      await loadAppointments();
      toast.success('Booking marked as no-show');
    } catch {
      toast.error('Failed to mark as no-show');
    }
  };

  return {
    appointments,
    loading,
    loadAppointments,
    approve,
    cancel,
    complete,
    markNoShow,
  };
}
