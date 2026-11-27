import { ref } from 'vue';
import api from '@/shared/api/client';
import { useToast } from '@/core/composables/useToast';
import { useConfirm } from '@/core/composables/useConfirm';
import { extractVerificationError } from '@/features/appointments/utils/verificationCode';

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
      toast.error(extractVerificationError(err));
      throw err;
    }
  };

  const markNoShow = async (id) => {
    if (await confirm('Mark No-Show', 'Mark this client as a no-show? Their slot will be opened for other bookings.')) {
      try {
        await api.patch(`/barber/appointments/${id}/no-show`, { reason: 'Marked no-show by barber' });
        await loadAppointments();
        toast.success('Booking marked as no-show');
      } catch (err) {
        toast.error(err.response?.data?.message || 'Failed to mark as no-show');
      }
    }
  };

  const forfeitDeposit = async (id) => {
    if (await confirm('Forfeit Customer Deposit', 'Mark the customer\'s paid deposit as forfeited for this no-show?')) {
      try {
        await api.post(`/barber/appointments/${id}/forfeit-deposit`, { reason: 'Deposit forfeited by barber' });
        await loadAppointments();
        toast.success('Deposit marked as forfeited');
      } catch (err) {
        toast.error(err.response?.data?.message || 'Failed to forfeit deposit');
      }
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
    forfeitDeposit,
  };
}
