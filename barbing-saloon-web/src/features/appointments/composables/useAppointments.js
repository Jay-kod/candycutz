import { ref } from 'vue';
import { adminApi } from '@/shared/api/old_adminApi';
import { useToast } from '@/core/composables/useToast';
import { useConfirm } from '@/core/composables/useConfirm';

export function useAppointments() {
  const toast = useToast();
  const { confirm } = useConfirm();

  const appointments = ref([]);
  const loading = ref(true);
  const isRefreshing = ref(false);

  const loadAppointments = async (silent = false) => {
    if (!silent) {
      loading.value = true;
    } else {
      isRefreshing.value = true;
    }
    
    try {
      const response = await adminApi.appointments();
      const raw = response.data?.data;
      appointments.value = Array.isArray(raw) ? raw : (raw?.items || []);
    } catch (err) {
      if (!silent) toast.error('Failed to load appointments');
    } finally {
      loading.value = false;
      isRefreshing.value = false;
    }
  };

  const approve = async (id) => {
    if (await confirm('Approve Booking', 'Are you sure you want to approve this booking manually?')) {
      try {
        await adminApi.approveAppointment(id);
        await loadAppointments(true);
        toast.success('Booking approved');
      } catch (err) {
        toast.error('Failed to approve');
      }
    }
  };

  const forceApprove = async (id) => {
    if (await confirm('Force Approve Booking', 'Are you sure you want to force approve this booking? This will bypass all payment clearances and disputes.')) {
      try {
        await adminApi.forceApproveAppointment(id);
        await loadAppointments(true);
        toast.success('Booking force approved successfully');
      } catch (err) {
        toast.error('Failed to force approve');
      }
    }
  };

  const cancel = async (id) => {
    if (await confirm('Cancel Booking', 'Are you sure you want to cancel this booking? This action cannot be undone.')) {
      try {
        await adminApi.cancelAppointment(id);
        await loadAppointments(true);
        toast.success('Booking cancelled');
      } catch (err) {
        toast.error('Failed to cancel');
      }
    }
  };

  return {
    appointments,
    loading,
    isRefreshing,
    loadAppointments,
    approve,
    forceApprove,
    cancel
  };
}
