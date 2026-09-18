import { ref } from 'vue';
import { barberApi } from '@/shared/api/old_barberApi';
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
      const response = await barberApi.getBookings();
      appointments.value = response.data;
    } catch (e) {
      toast.error('Failed to load appointments');
    } finally {
      loading.value = false;
    }
  };

  const approve = async (id) => {
    if (await confirm('Approve Booking', 'Are you sure you want to approve this booking manually?')) {
      try {
        await barberApi.approveBooking(id);
        await loadAppointments();
        toast.success('Booking approved and confirmed!');
      } catch (err) {
        toast.error('Failed to approve booking');
      }
    }
  };

  const cancel = async (id) => {
    if (await confirm('Cancel Booking', 'Are you sure you want to cancel this booking? This action cannot be undone.')) {
      try {
        await barberApi.cancelBooking(id);
        await loadAppointments();
        toast.success('Booking cancelled');
      } catch (err) {
        toast.error('Failed to cancel booking');
      }
    }
  };

  const complete = async (id, verificationCode = '') => {
    try {
      await barberApi.complete(id, { verification_code: verificationCode });
      await loadAppointments();
      toast.success('Booking verified and marked as completed!');
    } catch (err) {
      toast.error(err.response?.data?.error || 'Failed to verify booking');
      throw err;
    }
  };

  const markNoShow = async (id) => {
    try {
      await barberApi.noShow(id);
      await loadAppointments();
      toast.success('Booking marked as no-show');
    } catch (err) {
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
    markNoShow
  };
}
